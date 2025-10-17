<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return; // Only for PostgreSQL
        }

        // users: email nullable, password nullable, firebase_uid unique, metadata JSONB
        if (Schema::hasTable('users')) {
            try { DB::statement('ALTER TABLE "users" ALTER COLUMN "email" DROP NOT NULL'); } catch (\Throwable $e) {}
            try { DB::statement('ALTER TABLE "users" ALTER COLUMN "password" DROP NOT NULL'); } catch (\Throwable $e) {}
            try { DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS users_firebase_uid_unique ON "users" ("firebase_uid")'); } catch (\Throwable $e) {}

            if (!Schema::hasColumn('users', 'metadata')) {
                try { DB::statement('ALTER TABLE "users" ADD COLUMN "metadata" jsonb NULL'); } catch (\Throwable $e) {}
            } else {
                try { DB::statement('ALTER TABLE "users" ALTER COLUMN "metadata" TYPE jsonb USING "metadata"::jsonb'); } catch (\Throwable $e) {}
            }
        }

        // services: category_id nullable; images/metadata JSONB if present
        if (Schema::hasTable('services')) {
            try { DB::statement('ALTER TABLE "services" ALTER COLUMN "category_id" DROP NOT NULL'); } catch (\Throwable $e) {}
            if (Schema::hasColumn('services', 'images')) {
                try { DB::statement('ALTER TABLE "services" ALTER COLUMN "images" TYPE jsonb USING "images"::jsonb'); } catch (\Throwable $e) {}
            }
            if (Schema::hasColumn('services', 'metadata')) {
                try { DB::statement('ALTER TABLE "services" ALTER COLUMN "metadata" TYPE jsonb USING "metadata"::jsonb'); } catch (\Throwable $e) {}
            }
        }

        // service_bookings: relax common nullable fields
        if (Schema::hasTable('service_bookings')) {
            foreach (['scheduled_at','completed_at','customer_address','latitude','longitude','customer_notes'] as $col) {
                try { DB::statement("ALTER TABLE \"service_bookings\" ALTER COLUMN \"$col\" DROP NOT NULL"); } catch (\Throwable $e) {}
            }
        }

        // service_providers: firebase_id unique if present
        if (Schema::hasTable('service_providers') && Schema::hasColumn('service_providers', 'firebase_id')) {
            try { DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS service_providers_firebase_id_unique ON "service_providers" ("firebase_id")'); } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        // No-op normalizer
    }
};
