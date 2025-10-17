<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return; // Only for MySQL
        }

        // users: drop unique(email), make email/password nullable, firebase_uid unique, metadata longText
        if (Schema::hasTable('users')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    try { $table->dropUnique('users_email_unique'); } catch (\Throwable $e) {}
                    try { $table->dropUnique(['email']); } catch (\Throwable $e) {}
                });
            } catch (\Throwable $e) {}

            try { DB::statement('ALTER TABLE `users` MODIFY `email` VARCHAR(255) NULL'); } catch (\Throwable $e) {}
            try { DB::statement('ALTER TABLE `users` MODIFY `password` VARCHAR(255) NULL'); } catch (\Throwable $e) {}

            try {
                Schema::table('users', function (Blueprint $table) {
                    try { $table->unique('firebase_uid', 'users_firebase_uid_unique'); } catch (\Throwable $e) {}
                });
            } catch (\Throwable $e) {}

            if (!Schema::hasColumn('users', 'metadata')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->longText('metadata')->nullable()->after('status');
                });
            } else {
                try { DB::statement('ALTER TABLE `users` MODIFY `metadata` LONGTEXT NULL'); } catch (\Throwable $e) {}
            }
        }

        // services: category_id nullable; images/metadata longText
        if (Schema::hasTable('services')) {
            try { DB::statement('ALTER TABLE `services` MODIFY `category_id` BIGINT NULL'); } catch (\Throwable $e) {}
            if (Schema::hasColumn('services', 'images')) {
                try { DB::statement('ALTER TABLE `services` MODIFY `images` LONGTEXT NULL'); } catch (\Throwable $e) {}
            }
            if (Schema::hasColumn('services', 'metadata')) {
                try { DB::statement('ALTER TABLE `services` MODIFY `metadata` LONGTEXT NULL'); } catch (\Throwable $e) {}
            }
        }

        // service_bookings: relax nullables to match Firestore data variability
        if (Schema::hasTable('service_bookings')) {
            foreach ([
                'scheduled_at' => 'DATETIME NULL',
                'completed_at' => 'DATETIME NULL',
                'customer_address' => 'TEXT NULL',
                'latitude' => 'DECIMAL(10,7) NULL',
                'longitude' => 'DECIMAL(10,7) NULL',
                'customer_notes' => 'LONGTEXT NULL'
            ] as $col => $type) {
                try { DB::statement("ALTER TABLE `service_bookings` MODIFY `$col` $type"); } catch (\Throwable $e) {}
            }
        }

        // service_providers: firebase_id unique
        if (Schema::hasTable('service_providers') && Schema::hasColumn('service_providers', 'firebase_id')) {
            try {
                Schema::table('service_providers', function (Blueprint $table) {
                    try { $table->unique('firebase_id', 'service_providers_firebase_id_unique'); } catch (\Throwable $e) {}
                });
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        // No-op normalizer
    }
};
