<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_configs', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_id')->unique()->default('primary_theme_v1');
            $table->string('name')->default('ProServe Default');
            $table->string('name_ar')->nullable()->default('بروسيرف الافتراضي');
            $table->integer('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->string('brightness', 20)->default('light');

            // Primary Colors
            $table->bigInteger('primary_dark_blue')->default(0xFF040C1E);
            $table->bigInteger('primary_teal')->default(0xFF43A196);
            $table->bigInteger('secondary_teal')->default(0xFF4CB7B0);

            // Neutral Colors
            $table->bigInteger('gray_light')->default(0xFFF5F5F5);
            $table->bigInteger('gray_medium')->default(0xFFE0E0E0);
            $table->bigInteger('gray_dark')->default(0xFF757575);

            // Text Colors
            $table->bigInteger('text_dark')->default(0xFF212121);
            $table->bigInteger('text_light')->default(0xFFFFFFFF);
            $table->bigInteger('text_muted')->default(0xFF9E9E9E);

            // Semantic Colors
            $table->bigInteger('color_error')->default(0xFFE53935);
            $table->bigInteger('color_success')->default(0xFF43A047);
            $table->bigInteger('color_warning')->default(0xFFFFA000);
            $table->bigInteger('color_info')->default(0xFF1E88E5);

            // Surface Colors
            $table->bigInteger('color_surface')->default(0xFFFFFFFF);
            $table->bigInteger('color_background')->default(0xFFF5F5F5);
            $table->bigInteger('color_card')->default(0xFFFFFFFF);
            $table->bigInteger('color_divider')->default(0xFFBDBDBD);

            // Status Colors (Order Management)
            $table->bigInteger('status_pending')->default(0xFFFF9800);
            $table->bigInteger('status_confirmed')->default(0xFF2196F3);
            $table->bigInteger('status_in_progress')->default(0xFFFFC107);
            $table->bigInteger('status_completed')->default(0xFF4CAF50);
            $table->bigInteger('status_cancelled')->default(0xFFF44336);
            $table->bigInteger('status_default')->default(0xFF9E9E9E);

            // Button Colors
            $table->bigInteger('button_primary')->default(0xFF43A196);
            $table->bigInteger('button_secondary')->default(0xFF757575);
            $table->bigInteger('button_danger')->default(0xFFE53935);
            $table->bigInteger('button_success')->default(0xFF43A047);
            $table->bigInteger('button_text')->default(0xFFFFFFFF);
            $table->bigInteger('button_text_dark')->default(0xFF212121);

            // Chat Colors
            $table->bigInteger('chat_bubble_me')->default(0xFF43A196);
            $table->bigInteger('chat_bubble_other')->default(0xFFE0E0E0);
            $table->bigInteger('chat_background')->default(0xFFF5F5F5);
            $table->bigInteger('chat_icon')->default(0xFF43A196);

            // Rating Colors
            $table->bigInteger('rating_active')->default(0xFFFFC107);
            $table->bigInteger('rating_inactive')->default(0xFFE0E0E0);

            // Navigation Colors
            $table->bigInteger('nav_active')->default(0xFF43A196);
            $table->bigInteger('nav_inactive')->default(0xFF9E9E9E);

            // Accent Colors
            $table->bigInteger('accent_teal')->default(0xFF009688);
            $table->bigInteger('accent_orange')->default(0xFFFF5722);
            $table->bigInteger('accent_red')->default(0xFFF44336);
            $table->bigInteger('accent_green')->default(0xFF4CAF50);
            $table->bigInteger('accent_blue')->default(0xFF2196F3);
            $table->bigInteger('accent_amber')->default(0xFFFFC107);

            // Input Colors
            $table->bigInteger('input_fill')->default(0xFFF5F5F5);
            $table->bigInteger('input_border')->default(0xFFE0E0E0);
            $table->bigInteger('input_hint')->default(0xFF9E9E9E);

            // Slider Colors
            $table->bigInteger('slider_background')->default(0xFFF5F5F5);
            $table->bigInteger('slider_dot_active')->default(0xFF43A196);
            $table->bigInteger('slider_dot_inactive')->default(0xFFE0E0E0);

            // Utility Colors
            $table->bigInteger('color_shadow')->default(0x40000000);
            $table->bigInteger('color_overlay')->default(0x80000000);

            // Theme Data (JSON for flexibility)
            $table->json('theme_data')->nullable();

            // Sync tracking
            $table->timestamp('last_synced_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('firebase_id');
            $table->index('is_active');
        });

        // Theme change history for audit
        Schema::create('theme_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_config_id')->constrained('theme_configs')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('field_changed', 100);
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->timestamp('changed_at')->useCurrent();

            $table->index(['theme_config_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_change_logs');
        Schema::dropIfExists('theme_configs');
    }
};
