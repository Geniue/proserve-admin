<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Promo codes must be created first (offers references promo_codes)
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_id')->nullable()->unique();

            // Code
            $table->string('code', 50)->unique();
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();

            // Targeting
            $table->string('target_audience', 50)->default('customer');
            $table->jsonb('applicable_services')->nullable();
            $table->jsonb('target_user_ids')->nullable();
            $table->boolean('first_order_only')->default(false);

            // Discount mechanics
            $table->string('discount_type', 50); // percentage | fixed
            $table->decimal('discount_value', 10, 2);
            $table->decimal('max_discount', 10, 2)->nullable();
            $table->decimal('min_order_value', 10, 2)->nullable();

            // Limits
            $table->integer('total_usage_limit')->nullable();
            $table->integer('per_user_limit')->default(1);
            $table->integer('total_used')->default(0);

            // Scheduling
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->boolean('is_active')->default(true);

            // Sync
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
            $table->index(['starts_at', 'expires_at']);
            $table->index('target_audience');
        });

        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_id')->nullable()->unique();

            // Content (bilingual)
            $table->string('title_en');
            $table->string('title_ar');
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('badge_en', 100)->nullable();
            $table->string('badge_ar', 100)->nullable();

            // Visual
            $table->text('image_url')->nullable();
            $table->string('gradient_start', 7)->nullable();
            $table->string('gradient_end', 7)->nullable();

            // Targeting
            $table->string('target_audience', 50)->default('customer');
            $table->jsonb('target_user_ids')->nullable();
            $table->jsonb('target_services')->nullable();
            $table->jsonb('target_cities')->nullable();
            $table->boolean('target_new_users_only')->default(false);
            $table->integer('min_orders_required')->default(0);

            // Offer mechanics
            $table->string('offer_type', 50);
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->decimal('max_discount', 10, 2)->nullable();
            $table->decimal('min_order_value', 10, 2)->nullable();
            $table->decimal('bonus_earning_pct', 5, 2)->nullable();

            // Linked promo code
            $table->foreignId('promo_code_id')->nullable()->constrained('promo_codes')->nullOnDelete();

            // Scheduling
            $table->timestamp('starts_at');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->integer('priority')->default(0);

            // Analytics
            $table->integer('impressions')->default(0);
            $table->integer('clicks')->default(0);
            $table->integer('redemptions')->default(0);

            // Sync
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index('target_audience');
            $table->index('is_active');
            $table->index(['starts_at', 'expires_at']);
            $table->index('offer_type');
        });

        Schema::create('promo_code_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')->constrained('promo_codes')->cascadeOnDelete();
            $table->string('firebase_uid');
            $table->string('order_firebase_id')->nullable();
            $table->decimal('discount_applied', 10, 2);
            $table->timestamp('redeemed_at')->useCurrent();

            $table->index('promo_code_id');
            $table->index('firebase_uid');
            $table->unique(['promo_code_id', 'firebase_uid', 'order_firebase_id'], 'promo_redemption_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_code_redemptions');
        Schema::dropIfExists('offers');
        Schema::dropIfExists('promo_codes');
    }
};
