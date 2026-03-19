# ProServe Offers & Promotions System — Laravel Filament Dashboard

## Overview

A comprehensive offers, promotions, and promo code management system for the ProServe home services platform. Manages targeted promotional campaigns for **customers** and **technicians** with real-time sync to Firebase Firestore.

---

## 🏗️ Architecture

```
ADMIN (Filament) ──writes──▶ PostgreSQL (offers, promo_codes tables)
         │
         ├──syncs──▶ Firestore /offers/{offerId}
         ├──syncs──▶ Firestore /promo_codes/{codeId}
         └──pushes─▶ FCM notification (optional, for flash deals)

FLUTTER APP ──reads──▶ Firestore /offers (real-time listener)
            ──validates──▶ Firestore /promo_codes (on apply)
            ──writes──▶ Firestore /promo_code_redemptions/{id}
```

---

## 📊 Database Schema

### PostgreSQL Tables

```sql
-- ══════════════════════════════════════════════════
-- OFFERS TABLE — promotional banners/campaigns
-- ══════════════════════════════════════════════════
CREATE TABLE offers (
    id BIGSERIAL PRIMARY KEY,
    firebase_id VARCHAR(255) UNIQUE,

    -- Content (bilingual)
    title_en VARCHAR(255) NOT NULL,
    title_ar VARCHAR(255) NOT NULL,
    description_en TEXT,
    description_ar TEXT,
    badge_en VARCHAR(100),          -- e.g. "NEW", "HOT", "FLASH"
    badge_ar VARCHAR(100),

    -- Visual
    image_url TEXT,                  -- Banner image (Firebase Storage or CDN)
    gradient_start VARCHAR(7),       -- Hex color for card gradient
    gradient_end VARCHAR(7),

    -- Targeting
    target_audience VARCHAR(50) NOT NULL DEFAULT 'customer',
        -- 'customer' | 'technician' | 'all'
    target_user_ids JSONB,           -- NULL = everyone, or specific UIDs
    target_services JSONB,           -- NULL = all services, or [serviceId, ...]
    target_cities JSONB,             -- NULL = everywhere, or ["Riyadh", ...]
    target_new_users_only BOOLEAN DEFAULT false,
    min_orders_required INTEGER DEFAULT 0,  -- For loyalty-tier offers

    -- Offer mechanics
    offer_type VARCHAR(50) NOT NULL,
        -- 'percentage_discount' | 'fixed_discount' | 'cashback'
        -- 'free_addon' | 'bundle_deal' | 'bonus_earnings'
        -- 'priority_listing' | 'first_order' | 'referral_bonus'
        -- 'flash_deal' | 'seasonal'
    discount_value DECIMAL(10,2),    -- Amount or percentage
    max_discount DECIMAL(10,2),      -- Cap for percentage discounts
    min_order_value DECIMAL(10,2),   -- Minimum basket to qualify
    bonus_earning_pct DECIMAL(5,2),  -- For technician bonus earnings

    -- Linked promo code (optional)
    promo_code_id BIGINT REFERENCES promo_codes(id) ON DELETE SET NULL,

    -- Scheduling
    starts_at TIMESTAMP NOT NULL,
    expires_at TIMESTAMP,            -- NULL = no expiry
    is_active BOOLEAN DEFAULT true,
    sort_order INTEGER DEFAULT 0,
    priority INTEGER DEFAULT 0,      -- Higher = shown first

    -- Analytics
    impressions INTEGER DEFAULT 0,
    clicks INTEGER DEFAULT 0,
    redemptions INTEGER DEFAULT 0,

    -- Sync
    last_synced_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),

    INDEX idx_audience (target_audience),
    INDEX idx_active (is_active),
    INDEX idx_dates (starts_at, expires_at),
    INDEX idx_type (offer_type)
);

-- ══════════════════════════════════════════════════
-- PROMO CODES TABLE
-- ══════════════════════════════════════════════════
CREATE TABLE promo_codes (
    id BIGSERIAL PRIMARY KEY,
    firebase_id VARCHAR(255) UNIQUE,

    -- Code
    code VARCHAR(50) UNIQUE NOT NULL,
    description_en TEXT,
    description_ar TEXT,

    -- Targeting
    target_audience VARCHAR(50) NOT NULL DEFAULT 'customer',
        -- 'customer' | 'technician' | 'all'
    applicable_services JSONB,       -- NULL = all services
    target_user_ids JSONB,           -- NULL = everyone (public code)
    first_order_only BOOLEAN DEFAULT false,

    -- Discount mechanics
    discount_type VARCHAR(50) NOT NULL, -- 'percentage' | 'fixed'
    discount_value DECIMAL(10,2) NOT NULL,
    max_discount DECIMAL(10,2),      -- Cap for percentage
    min_order_value DECIMAL(10,2),

    -- Limits
    total_usage_limit INTEGER,       -- NULL = unlimited
    per_user_limit INTEGER DEFAULT 1,
    total_used INTEGER DEFAULT 0,

    -- Scheduling
    starts_at TIMESTAMP NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT true,

    -- Sync
    last_synced_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),

    INDEX idx_code (code),
    INDEX idx_active (is_active),
    INDEX idx_dates (starts_at, expires_at),
    INDEX idx_audience (target_audience)
);

-- ══════════════════════════════════════════════════
-- PROMO CODE REDEMPTIONS (tracking)
-- ══════════════════════════════════════════════════
CREATE TABLE promo_code_redemptions (
    id BIGSERIAL PRIMARY KEY,
    promo_code_id BIGINT REFERENCES promo_codes(id),
    firebase_uid VARCHAR(255) NOT NULL,
    order_firebase_id VARCHAR(255),
    discount_applied DECIMAL(10,2) NOT NULL,
    redeemed_at TIMESTAMP DEFAULT NOW(),

    INDEX idx_code (promo_code_id),
    INDEX idx_user (firebase_uid),
    UNIQUE (promo_code_id, firebase_uid, order_firebase_id)
);
```

### Firestore Collections

```typescript
// ── /offers/{offerId} ──
{
  offerId: string,              // = PostgreSQL firebase_id
  titleEn: string,
  titleAr: string,
  descriptionEn: string,
  descriptionAr: string,
  badgeEn?: string,
  badgeAr?: string,
  imageUrl?: string,
  gradientStart?: string,       // Hex color
  gradientEnd?: string,
  targetAudience: 'customer' | 'technician' | 'all',
  targetServices?: string[],    // serviceIds or null
  targetNewUsersOnly: boolean,
  minOrdersRequired: number,
  offerType: string,
  discountValue?: number,
  maxDiscount?: number,
  minOrderValue?: number,
  bonusEarningPct?: number,
  promoCode?: string,           // If linked to a code
  startsAt: Timestamp,
  expiresAt?: Timestamp,
  isActive: boolean,
  sortOrder: number,
  priority: number,
  createdAt: Timestamp,
  updatedAt: Timestamp
}

// ── /promo_codes/{codeId} ──
{
  code: string,                 // "WELCOME20", "TECH50", etc.
  descriptionEn?: string,
  descriptionAr?: string,
  targetAudience: 'customer' | 'technician' | 'all',
  applicableServices?: string[],
  firstOrderOnly: boolean,
  discountType: 'percentage' | 'fixed',
  discountValue: number,
  maxDiscount?: number,
  minOrderValue?: number,
  totalUsageLimit?: number,
  perUserLimit: number,
  totalUsed: number,
  startsAt: Timestamp,
  expiresAt: Timestamp,
  isActive: boolean,
  createdAt: Timestamp
}

// ── /promo_code_redemptions/{redemptionId} ──
{
  promoCodeId: string,
  userId: string,
  orderId?: string,
  discountApplied: number,
  redeemedAt: Timestamp
}
```

---

## 🎛️ Filament Resources

### 1. OfferResource

**Navigation:** `Promotions > Offers`

**List View Columns:**
| Column | Type | Notes |
|--------|------|-------|
| Image | ImageColumn | 40x40 thumbnail |
| Title (EN) | TextColumn | Searchable |
| Title (AR) | TextColumn | |
| Audience | BadgeColumn | customer=blue, technician=orange, all=green |
| Offer Type | BadgeColumn | Color-coded by type |
| Discount | TextColumn | "20%" or "50 SAR" |
| Promo Code | TextColumn | Linked code if any |
| Period | TextColumn | "Mar 1 → Mar 31" |
| Status | IconColumn | ✅ active, ❌ expired, ⏳ scheduled |
| Impressions | TextColumn | Counter |
| Redemptions | TextColumn | Counter |
| Sync | BadgeColumn | synced/pending/failed |

**Filters:**
- Target audience (customer/technician/all)
- Offer type
- Active/Expired/Scheduled
- Date range

**Form Sections:**

```
┌─ Content (Bilingual) ─────────────────────┐
│ Title EN*         │ Title AR*              │
│ Description EN    │ Description AR         │
│ Badge EN          │ Badge AR               │
│ Image Upload      │ Gradient Colors        │
└───────────────────────────────────────────┘

┌─ Targeting ───────────────────────────────┐
│ Audience*: [Customer ▾] [Tech ▾] [All ▾] │
│ Services: [Multi-select or All]           │
│ Cities: [Multi-select or All]             │
│ New Users Only: [Toggle]                  │
│ Min Orders Required: [Number]             │
│ Specific Users: [Tags/UIDs]              │
└───────────────────────────────────────────┘

┌─ Offer Mechanics ─────────────────────────┐
│ Type*: [Dropdown: percentage/fixed/...]   │
│ Discount Value: [Number]                  │
│ Max Discount Cap: [Number]                │
│ Min Order Value: [Number]                 │
│ Bonus Earning %: [Number] (tech only)     │
│ Link Promo Code: [Select existing]        │
└───────────────────────────────────────────┘

┌─ Scheduling ──────────────────────────────┐
│ Starts At*: [DateTimePicker]              │
│ Expires At: [DateTimePicker]              │
│ Active: [Toggle]                          │
│ Sort Order: [Number]                      │
│ Priority: [Number]                        │
└───────────────────────────────────────────┘

┌─ Analytics (Read-only, on Edit) ──────────┐
│ Impressions: 1,234                        │
│ Clicks: 567                               │
│ Redemptions: 89                           │
│ Conversion Rate: 7.2%                     │
└───────────────────────────────────────────┘
```

**Actions:**
- `Edit` — Standard edit
- `Force Sync` — Push immediately to Firestore
- `Duplicate` — Clone offer with new dates
- `Toggle Active` — Quick enable/disable
- `Send Push Notification` — FCM blast for flash deals

**Bulk Actions:**
- Sync to Firestore
- Activate / Deactivate
- Delete

---

### 2. PromoCodeResource

**Navigation:** `Promotions > Promo Codes`

**List View Columns:**
| Column | Type | Notes |
|--------|------|-------|
| Code | TextColumn | Bold, searchable, copyable |
| Description | TextColumn | EN description |
| Audience | BadgeColumn | customer/technician/all |
| Type | BadgeColumn | percentage=blue, fixed=green |
| Value | TextColumn | "20%" or "50 SAR" |
| Usage | TextColumn | "45/100" or "45/∞" |
| Period | TextColumn | Start → End |
| Status | IconColumn | active/expired/depleted |
| Sync | BadgeColumn | |

**Filters:**
- Audience
- Type (percentage/fixed)
- Status (active/expired/depleted)
- Date range

**Form:**

```
┌─ Code Details ────────────────────────────┐
│ Code*: [Text] (auto-uppercase)            │
│   [Generate Random Code] button           │
│ Description EN    │ Description AR         │
└───────────────────────────────────────────┘

┌─ Targeting ───────────────────────────────┐
│ Audience*: [Customer ▾] [Tech ▾] [All ▾] │
│ Applicable Services: [Multi-select]       │
│ Specific Users: [Tags/UIDs]              │
│ First Order Only: [Toggle]                │
└───────────────────────────────────────────┘

┌─ Discount Rules ──────────────────────────┐
│ Type*: [Percentage ▾] [Fixed ▾]          │
│ Value*: [Number]                          │
│ Max Discount: [Number] (% only)           │
│ Min Order Value: [Number]                 │
└───────────────────────────────────────────┘

┌─ Limits & Scheduling ────────────────────┐
│ Total Usage Limit: [Number or empty=∞]   │
│ Per User Limit*: [Number, default=1]      │
│ Starts At*: [DateTimePicker]              │
│ Expires At*: [DateTimePicker]             │
│ Active: [Toggle]                          │
└───────────────────────────────────────────┘

┌─ Redemption History (Relation) ──────────┐
│ Table: User UID | Order ID | Amount | At │
│ ... paginated list of redemptions        │
└───────────────────────────────────────────┘
```

**Actions:**
- `Force Sync` — Push to Firestore
- `Copy Code` — Copy to clipboard
- `View Redemptions`
- `Deactivate`

---

### 3. Dashboard Widgets

```php
// PromotionStatsWidget
class PromotionStatsWidget extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Active Offers', Offer::active()->count())
                ->description('Currently visible')
                ->color('success'),

            Card::make('Active Promo Codes', PromoCode::active()->count())
                ->description('Redeemable codes')
                ->color('primary'),

            Card::make('Today\'s Redemptions', PromoCodeRedemption::today()->count())
                ->description('Codes used today')
                ->color('warning'),

            Card::make('This Month Revenue Impact',
                number_format(PromoCodeRedemption::thisMonth()->sum('discount_applied'), 2) . ' SAR')
                ->description('Total discounts given')
                ->color('danger'),
        ];
    }
}
```

---

## 🔄 Sync Implementation

### Model: Offer

```php
// app/Models/Offer.php
class Offer extends Model
{
    use SyncableWithFirestore;

    protected $casts = [
        'target_user_ids' => 'array',
        'target_services' => 'array',
        'target_cities' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'target_new_users_only' => 'boolean',
    ];

    public function getFirestoreCollection(): string { return 'offers'; }
    public function getFirestoreDocumentId(): string { return $this->firebase_id; }

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
            'discountValue' => $this->discount_value,
            'maxDiscount' => $this->max_discount,
            'minOrderValue' => $this->min_order_value,
            'bonusEarningPct' => $this->bonus_earning_pct,
            'promoCode' => $this->promoCode?->code,
            'startsAt' => $this->starts_at?->toIso8601String(),
            'expiresAt' => $this->expires_at?->toIso8601String(),
            'isActive' => $this->is_active,
            'sortOrder' => $this->sort_order ?? 0,
            'priority' => $this->priority ?? 0,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => now()->toIso8601String(),
        ];
    }

    // Scopes
    public function scopeActive($query) {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function scopeForAudience($query, string $audience) {
        return $query->where(function($q) use ($audience) {
            $q->where('target_audience', $audience)
              ->orWhere('target_audience', 'all');
        });
    }

    public function promoCode() {
        return $this->belongsTo(PromoCode::class);
    }
}
```

### Model: PromoCode

```php
// app/Models/PromoCode.php
class PromoCode extends Model
{
    use SyncableWithFirestore;

    protected $casts = [
        'applicable_services' => 'array',
        'target_user_ids' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'first_order_only' => 'boolean',
    ];

    public function getFirestoreCollection(): string { return 'promo_codes'; }
    public function getFirestoreDocumentId(): string { return $this->firebase_id; }

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
            'discountValue' => $this->discount_value,
            'maxDiscount' => $this->max_discount,
            'minOrderValue' => $this->min_order_value,
            'totalUsageLimit' => $this->total_usage_limit,
            'perUserLimit' => $this->per_user_limit ?? 1,
            'totalUsed' => $this->total_used ?? 0,
            'startsAt' => $this->starts_at?->toIso8601String(),
            'expiresAt' => $this->expires_at?->toIso8601String(),
            'isActive' => $this->is_active,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }

    // Check if code is currently valid
    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at > now()) return false;
        if ($this->expires_at && $this->expires_at < now()) return false;
        if ($this->total_usage_limit && $this->total_used >= $this->total_usage_limit) return false;
        return true;
    }

    public function scopeActive($query) {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>', now());
    }

    public function redemptions() {
        return $this->hasMany(PromoCodeRedemption::class);
    }

    public function offers() {
        return $this->hasMany(Offer::class);
    }
}
```

### Lifecycle Hook: Auto-Sync on Create

```php
// In OfferObserver (registered in AppServiceProvider)
class OfferObserver
{
    public function creating(Offer $offer)
    {
        if (!$offer->firebase_id) {
            // Generate a Firestore-compatible document ID
            $offer->firebase_id = Str::random(20);
        }
    }

    public function created(Offer $offer)
    {
        // Sync to Firestore immediately
        dispatch(function () use ($offer) {
            $offer->pushToFirestore('create');
        })->afterResponse();
    }

    public function updated(Offer $offer)
    {
        dispatch(function () use ($offer) {
            $offer->pushToFirestore('update');
        })->afterResponse();
    }

    public function deleted(Offer $offer)
    {
        dispatch(function () use ($offer) {
            $offer->pushToFirestore('delete');
        })->afterResponse();
    }
}

// Same pattern for PromoCodeObserver
```

---

## 🔔 Push Notification for Flash Deals

```php
// Filament Action on Offer resource
Tables\Actions\Action::make('sendPush')
    ->icon('heroicon-o-bell-alert')
    ->label('Send Push')
    ->color('warning')
    ->visible(fn (Offer $record) => $record->is_active)
    ->requiresConfirmation()
    ->modalHeading('Send Push Notification')
    ->modalDescription('This will send a push notification to all targeted users.')
    ->action(function (Offer $record) {
        dispatch(new SendOfferPushNotification($record));
        Notification::make()->title('Push notification queued')->success()->send();
    });
```

```php
// app/Jobs/SendOfferPushNotification.php
class SendOfferPushNotification implements ShouldQueue
{
    public function handle()
    {
        $offer = $this->offer;
        $messaging = app(Messaging::class);

        // Build notification per language
        $audiences = match ($offer->target_audience) {
            'customer' => User::where('user_type', 'customer'),
            'technician' => User::where('user_type', 'technician'),
            'all' => User::query(),
        };

        $users = $audiences->whereNotNull('fcm_token')->get();

        foreach ($users as $user) {
            $title = $user->language === 'ar' ? $offer->title_ar : $offer->title_en;
            $body = $user->language === 'ar' ? $offer->description_ar : $offer->description_en;

            try {
                $message = CloudMessage::withTarget('token', $user->fcm_token)
                    ->withNotification(Notification::create($title, $body))
                    ->withData(['type' => 'offer', 'offerId' => $offer->firebase_id]);

                $messaging->send($message);
            } catch (\Exception $e) {
                Log::warning("FCM send failed for {$user->firebase_uid}: {$e->getMessage()}");
            }
        }
    }
}
```

---

## 📋 Offer Types Reference

| Type | Audience | Mechanics | Example |
|------|----------|-----------|---------|
| `percentage_discount` | Customer | X% off order | "20% off all plumbing" |
| `fixed_discount` | Customer | X SAR off order | "50 SAR off your order" |
| `cashback` | Customer | X% back as wallet credit | "10% cashback on electrical" |
| `free_addon` | Customer | Free add-on service | "Free inspection with AC repair" |
| `bundle_deal` | Customer | Book X, get Y% off | "Book 3 services, save 25%" |
| `first_order` | Customer | Special first-order discount | "Welcome! Your first order: 30% off" |
| `referral_bonus` | All | Reward for referrals | "Refer a friend, both get 50 SAR" |
| `flash_deal` | All | Time-limited super deal | "Next 2 hours: 40% off!" |
| `seasonal` | All | Holiday/seasonal promo | "Ramadan Special: 25% off" |
| `bonus_earnings` | Technician | Extra X% on completed orders | "Earn 15% bonus this week" |
| `priority_listing` | Technician | Appear first in matching | "Get priority for 7 days" |

---

## 🧪 Seeder: Sample Data

```php
// database/seeders/OfferSeeder.php
class OfferSeeder extends Seeder
{
    public function run()
    {
        // ── Customer Offers ──
        Offer::create([
            'firebase_id' => Str::random(20),
            'title_en' => '🔥 Welcome Offer!',
            'title_ar' => '🔥 عرض ترحيبي!',
            'description_en' => '30% off your first order on ProServe',
            'description_ar' => 'خصم 30% على طلبك الأول في بروسيرف',
            'badge_en' => 'NEW USER',
            'badge_ar' => 'مستخدم جديد',
            'target_audience' => 'customer',
            'target_new_users_only' => true,
            'offer_type' => 'first_order',
            'discount_value' => 30,
            'max_discount' => 100,
            'gradient_start' => '#43A196',
            'gradient_end' => '#4CB7B0',
            'starts_at' => now(),
            'expires_at' => now()->addMonths(3),
            'is_active' => true,
            'priority' => 10,
        ]);

        Offer::create([
            'firebase_id' => Str::random(20),
            'title_en' => '⚡ Flash Deal: Plumbing',
            'title_ar' => '⚡ عرض سريع: السباكة',
            'description_en' => '40% off all plumbing services — today only!',
            'description_ar' => 'خصم 40% على جميع خدمات السباكة — اليوم فقط!',
            'badge_en' => 'FLASH',
            'badge_ar' => 'سريع',
            'target_audience' => 'customer',
            'offer_type' => 'flash_deal',
            'discount_value' => 40,
            'max_discount' => 200,
            'gradient_start' => '#FF6B35',
            'gradient_end' => '#FF8F6B',
            'starts_at' => now(),
            'expires_at' => now()->addDay(),
            'is_active' => true,
            'priority' => 20,
        ]);

        // ── Technician Offers ──
        Offer::create([
            'firebase_id' => Str::random(20),
            'title_en' => '💰 Bonus Week!',
            'title_ar' => '💰 أسبوع المكافآت!',
            'description_en' => 'Earn 20% extra on every completed order this week',
            'description_ar' => 'اكسب 20% إضافية على كل طلب مكتمل هذا الأسبوع',
            'badge_en' => 'BONUS',
            'badge_ar' => 'مكافأة',
            'target_audience' => 'technician',
            'offer_type' => 'bonus_earnings',
            'bonus_earning_pct' => 20,
            'gradient_start' => '#4CAF50',
            'gradient_end' => '#66BB6A',
            'starts_at' => now(),
            'expires_at' => now()->addWeek(),
            'is_active' => true,
            'priority' => 15,
        ]);

        // ── Promo Codes ──
        PromoCode::create([
            'firebase_id' => Str::random(20),
            'code' => 'WELCOME30',
            'description_en' => '30% off your first order',
            'description_ar' => 'خصم 30% على أول طلب',
            'target_audience' => 'customer',
            'first_order_only' => true,
            'discount_type' => 'percentage',
            'discount_value' => 30,
            'max_discount' => 100,
            'per_user_limit' => 1,
            'starts_at' => now(),
            'expires_at' => now()->addMonths(6),
            'is_active' => true,
        ]);

        PromoCode::create([
            'firebase_id' => Str::random(20),
            'code' => 'PUMP50',
            'description_en' => '50 SAR off orders above 200 SAR',
            'description_ar' => 'خصم 50 ريال للطلبات فوق 200 ريال',
            'target_audience' => 'all',
            'discount_type' => 'fixed',
            'discount_value' => 50,
            'min_order_value' => 200,
            'total_usage_limit' => 500,
            'per_user_limit' => 2,
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);
    }
}
```

---

## 🚀 Implementation Checklist

### Backend (Laravel)
- [ ] Create migrations: `offers`, `promo_codes`, `promo_code_redemptions`
- [ ] Create models with `SyncableWithFirestore` trait
- [ ] Create model observers for auto-sync
- [ ] Build `OfferResource` Filament resource
- [ ] Build `PromoCodeResource` Filament resource
- [ ] Build `PromotionStatsWidget` dashboard widget
- [ ] Create `SendOfferPushNotification` job
- [ ] Add offer/promo sync to scheduled sync jobs
- [ ] Run seeders for sample data

### Frontend (Flutter)
- [ ] Create `ProServeOffer` model (from Firestore)
- [ ] Create `PromoCode` model (from Firestore)
- [ ] Create `OffersProvider` (ChangeNotifier) per audience
- [ ] Create `PromoCodeService` for validation
- [ ] Rewrite `SpecialDeals` widget with real data
- [ ] Rewrite `OffersScreen` with professional UI
- [ ] Fix cart discount code validation (Firestore-based)
- [ ] Add offer detail bottom sheet
- [ ] Add countdown timers for flash deals
- [ ] Handle promo code copy-to-clipboard
- [ ] Add translations (EN/AR)

---

**Last Updated:** March 19, 2026
**System:** ProServe Offers & Promotions
**Target:** Laravel Filament 3 + Flutter + Firestore
