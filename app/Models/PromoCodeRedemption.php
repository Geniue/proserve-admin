<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCodeRedemption extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'promo_code_id',
        'firebase_uid',
        'order_firebase_id',
        'discount_applied',
        'redeemed_at',
    ];

    protected $casts = [
        'discount_applied' => 'decimal:2',
        'redeemed_at' => 'datetime',
    ];

    // ── Relationships ──

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    // ── Scopes ──

    public function scopeToday($query)
    {
        return $query->whereDate('redeemed_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('redeemed_at', now()->month)
            ->whereYear('redeemed_at', now()->year);
    }
}
