<?php

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\PromoCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OfferSeeder extends Seeder
{
    public function run(): void
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
