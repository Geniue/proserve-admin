<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update pages table
        Schema::table('pages', function (Blueprint $table) {
            if (!Schema::hasColumn('pages', 'title')) {
                $table->string('title')->after('id');
                $table->string('slug')->unique()->after('title');
                $table->text('content')->after('slug');
                $table->text('meta_description')->nullable()->after('content');
                $table->boolean('is_active')->default(true)->after('meta_description');
                $table->softDeletes();
            }
        });

        // Update faqs table
        Schema::table('faqs', function (Blueprint $table) {
            if (!Schema::hasColumn('faqs', 'question')) {
                $table->text('question')->after('id');
                $table->text('answer')->after('question');
                $table->string('category')->nullable()->after('answer');
                $table->integer('sort_order')->default(0)->after('category');
                $table->boolean('is_active')->default(true)->after('sort_order');
            }
        });

        // Update navigation_items table
        Schema::table('navigation_items', function (Blueprint $table) {
            if (!Schema::hasColumn('navigation_items', 'label')) {
                $table->string('label')->after('id');
                $table->string('icon')->nullable()->after('label');
                $table->string('route')->nullable()->after('icon');
                $table->foreignId('parent_id')->nullable()->after('route')->constrained('navigation_items')->onDelete('cascade');
                $table->integer('sort_order')->default(0)->after('parent_id');
                $table->boolean('is_visible')->default(true)->after('sort_order');
            }
        });

        // Update onboarding_screens table
        Schema::table('onboarding_screens', function (Blueprint $table) {
            if (!Schema::hasColumn('onboarding_screens', 'title')) {
                $table->string('title')->after('id');
                $table->text('description')->after('title');
                $table->string('image_url')->after('description');
                $table->integer('sort_order')->default(0)->after('image_url');
            }
        });

        // Update app_versions table
        Schema::table('app_versions', function (Blueprint $table) {
            if (!Schema::hasColumn('app_versions', 'version')) {
                $table->string('version')->after('id');
                $table->integer('build_number')->after('version');
                $table->enum('platform', ['android', 'ios'])->after('build_number');
                $table->boolean('force_update')->default(false)->after('platform');
                $table->text('update_message')->nullable()->after('force_update');
                $table->string('download_url')->nullable()->after('update_message');
            }
        });

        // Update feature_flags table
        Schema::table('feature_flags', function (Blueprint $table) {
            if (!Schema::hasColumn('feature_flags', 'key')) {
                $table->string('key')->unique()->after('id');
                $table->boolean('is_enabled')->default(false)->after('key');
                $table->text('description')->nullable()->after('is_enabled');
            }
        });

        // Update notifications table
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'title')) {
                $table->string('title')->after('id');
                $table->text('body')->after('title');
                $table->string('type')->nullable()->after('body');
                $table->foreignId('user_id')->nullable()->after('type')->constrained()->onDelete('cascade');
                $table->json('data')->nullable()->after('user_id');
                $table->timestamp('read_at')->nullable()->after('data');
                $table->timestamp('sent_at')->nullable()->after('read_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove columns in reverse order
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['title', 'body', 'type', 'user_id', 'data', 'read_at', 'sent_at']);
        });

        Schema::table('feature_flags', function (Blueprint $table) {
            $table->dropColumn(['key', 'is_enabled', 'description']);
        });

        Schema::table('app_versions', function (Blueprint $table) {
            $table->dropColumn(['version', 'build_number', 'platform', 'force_update', 'update_message', 'download_url']);
        });

        Schema::table('onboarding_screens', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'image_url', 'sort_order']);
        });

        Schema::table('navigation_items', function (Blueprint $table) {
            $table->dropColumn(['label', 'icon', 'route', 'parent_id', 'sort_order', 'is_visible']);
        });

        Schema::table('faqs', function (Blueprint $table) {
            $table->dropColumn(['question', 'answer', 'category', 'sort_order', 'is_active']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['title', 'slug', 'content', 'meta_description', 'is_active']);
            $table->dropSoftDeletes();
        });
    }
};
