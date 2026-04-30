<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'type',
        'normal_balance',
        'description',
        'opening_balance',
        'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (AccountingAccount $account) {
            if (empty($account->normal_balance) && $account->type) {
                $account->normal_balance = self::normalBalanceForType($account->type);
            }
        });
    }

    public static function typeOptions(): array
    {
        return [
            'asset' => 'Asset',
            'liability' => 'Liability',
            'equity' => 'Equity',
            'revenue' => 'Revenue',
            'expense' => 'Expense',
        ];
    }

    public static function normalBalanceOptions(): array
    {
        return [
            'debit' => 'Debit',
            'credit' => 'Credit',
        ];
    }

    public static function normalBalanceForType(string $type): string
    {
        return in_array($type, ['liability', 'equity', 'revenue'], true) ? 'credit' : 'debit';
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function journalLines()
    {
        return $this->hasMany(AccountingJournalLine::class, 'account_id');
    }

    public function expenseLines()
    {
        return $this->hasMany(AccountingExpense::class, 'expense_account_id');
    }

    public function paymentLines()
    {
        return $this->hasMany(AccountingExpense::class, 'payment_account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->code} - {$this->name}";
    }

    public function getCurrentBalanceAttribute(): float
    {
        $debits = (float) $this->journalLines()
            ->whereHas('journalEntry', fn ($query) => $query->where('status', 'posted'))
            ->sum('debit');

        $credits = (float) $this->journalLines()
            ->whereHas('journalEntry', fn ($query) => $query->where('status', 'posted'))
            ->sum('credit');

        $movement = $this->normal_balance === 'credit'
            ? $credits - $debits
            : $debits - $credits;

        return round((float) $this->opening_balance + $movement, 2);
    }
}
