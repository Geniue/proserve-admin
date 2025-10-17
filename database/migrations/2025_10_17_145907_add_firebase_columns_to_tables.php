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
        // Add firebase_uid to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('firebase_uid')->nullable()->unique()->after('id');
            $table->timestamp('last_synced_at')->nullable()->after('updated_at');
        });

        // Add firebase_id to services table
        Schema::table('services', function (Blueprint $table) {
            $table->string('firebase_id')->nullable()->unique()->after('id');
            $table->timestamp('last_synced_at')->nullable()->after('updated_at');
        });

        // Add firebase_id to service_categories table
        Schema::table('service_categories', function (Blueprint $table) {
            $table->string('firebase_id')->nullable()->unique()->after('id');
            $table->timestamp('last_synced_at')->nullable()->after('updated_at');
        });

        // Add firebase_id to service_bookings table
        Schema::table('service_bookings', function (Blueprint $table) {
            $table->string('firebase_id')->nullable()->unique()->after('id');
            $table->timestamp('last_synced_at')->nullable()->after('updated_at');
        });

        // Add firebase_id to banners table
        Schema::table('banners', function (Blueprint $table) {
            $table->string('firebase_id')->nullable()->unique()->after('id');
            $table->timestamp('last_synced_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['firebase_uid', 'last_synced_at']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['firebase_id', 'last_synced_at']);
        });

        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropColumn(['firebase_id', 'last_synced_at']);
        });

        Schema::table('service_bookings', function (Blueprint $table) {
            $table->dropColumn(['firebase_id', 'last_synced_at']);
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['firebase_id', 'last_synced_at']);
        });
    }
};
