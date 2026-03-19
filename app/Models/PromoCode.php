<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\SyncToFirestore;

class PromoCode extends Model
{
    use SyncToFirestore;

    protected $fillable = [
        'firebase_id',
        'code',
        'description_en',
        'description_ar',
        'target_audience',
        'applicable_services',
        'target_user_ids',
        'first_order_only',
        'discount_type',
        'discount_value',
        'max_discount',
        'min_order_value',
        'total_usage_limit',
        'per_user_limit',
        'total_used',
        'starts_at',
        'expires_at',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'applicable_services' => 'array',
        'target_user_ids' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'first_order_only' => 'boolean',
        'discount_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'total_usage_limit' => 'integer',
        'per_user_limit' => 'integer',
        'total_used' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (PromoCode $code) {
            if (!$code->firebase_id) {
                $code->firebase_id = Str::random(20);
            }
            $code->code = strtoupper($code->code);
        });

        static::updating(function (PromoCode $code) {
            $code->code = strtoupper($code->code);
        });
    }

    // ── Relationships ──

    public function redemptions()
    {
        return $this->hasMany(PromoCodeRedemption::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    // ── Methods ──

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at > now()) return false;
        if ($this->expires_at && $this->expires_at < now()) return false;
        if ($this->total_usage_limit && $this->total_used >= $this->total_usage_limit) return false;
        return true;
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>', now());
    }

    // ── Firestore Sync ──

    public function getFirestoreCollection(): string
    {
        return 'promo_codes';
    }

    public function getFirestoreDocumentId(): string
    {
        return $this->firebase_id ?? $this->id;
    }

    public function toFirestoreArray(): array
    {
        return [
            'code' => strtoupper($this->code),
            'descriptionEn' => $this->description_en,
            'descriptionAr' => $this->description_ar,
            'targetAudience' => $this->target_audience,
            'applicableServices' => $this->applicable_services,
            'firstOrderOnly' => $this->first_order_only,
            'discountType' => $this->discount_type,
            'discountValue' => (float) $this->discount_value,
            'maxDiscount' => $this->max_discount ? (float) $this->max_discount : null,
            'minOrderValue' => $this->min_order_value ? (float) $this->min_order_value : null,
            'totalUsageLimit' => $this->total_usage_limit,
            'perUserLimit' => $this->per_user_limit ?? 1,
            'totalUsed' => $this->total_used ?? 0,
            'startsAt' => $this->starts_at?->toIso8601String(),
            'expiresAt' => $this->expires_at?->toIso8601String(),
            'isActive' => $this->is_active,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
