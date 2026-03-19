<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\SyncToFirestore;

class Offer extends Model
{
    use SyncToFirestore;

    protected $fillable = [
        'firebase_id',
        'title_en',
        'title_ar',
        'description_en',
        'description_ar',
        'badge_en',
        'badge_ar',
        'image_url',
        'gradient_start',
        'gradient_end',
        'target_audience',
        'target_user_ids',
        'target_services',
        'target_cities',
        'target_new_users_only',
        'min_orders_required',
        'offer_type',
        'discount_value',
        'max_discount',
        'min_order_value',
        'bonus_earning_pct',
        'promo_code_id',
        'starts_at',
        'expires_at',
        'is_active',
        'sort_order',
        'priority',
        'impressions',
        'clicks',
        'redemptions',
        'last_synced_at',
    ];

    protected $casts = [
        'target_user_ids' => 'array',
        'target_services' => 'array',
        'target_cities' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'target_new_users_only' => 'boolean',
        'discount_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'bonus_earning_pct' => 'decimal:2',
        'sort_order' => 'integer',
        'priority' => 'integer',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'redemptions' => 'integer',
        'min_orders_required' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Offer $offer) {
            if (!$offer->firebase_id) {
                $offer->firebase_id = Str::random(20);
            }
        });
    }

    // ── Relationships ──

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function scopeForAudience($query, string $audience)
    {
        return $query->where(function ($q) use ($audience) {
            $q->where('target_audience', $audience)
              ->orWhere('target_audience', 'all');
        });
    }

    // ── Firestore Sync ──

    public function getFirestoreCollection(): string
    {
        return 'offers';
    }

    public function getFirestoreDocumentId(): string
    {
        return $this->firebase_id ?? $this->id;
    }

    public function toFirestoreArray(): array
    {
        return [
            'titleEn' => $this->title_en,
            'titleAr' => $this->title_ar,
            'descriptionEn' => $this->description_en,
            'descriptionAr' => $this->description_ar,
            'badgeEn' => $this->badge_en,
            'badgeAr' => $this->badge_ar,
            'imageUrl' => $this->image_url,
            'gradientStart' => $this->gradient_start,
            'gradientEnd' => $this->gradient_end,
            'targetAudience' => $this->target_audience,
            'targetServices' => $this->target_services,
            'targetNewUsersOnly' => $this->target_new_users_only,
            'minOrdersRequired' => $this->min_orders_required,
            'offerType' => $this->offer_type,
            'discountValue' => $this->discount_value ? (float) $this->discount_value : null,
            'maxDiscount' => $this->max_discount ? (float) $this->max_discount : null,
            'minOrderValue' => $this->min_order_value ? (float) $this->min_order_value : null,
            'bonusEarningPct' => $this->bonus_earning_pct ? (float) $this->bonus_earning_pct : null,
            'promoCode' => $this->promoCode?->code,
            'startsAt' => $this->starts_at?->toIso8601String(),
            'expiresAt' => $this->expires_at?->toIso8601String(),
            'isActive' => $this->is_active,
            'sortOrder' => $this->sort_order ?? 0,
            'priority' => $this->priority ?? 0,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
