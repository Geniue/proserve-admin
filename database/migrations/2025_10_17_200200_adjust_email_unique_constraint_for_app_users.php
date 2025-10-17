<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the table constraint first (Postgres names it the same)
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_email_unique');
        // Then drop the index if present
        DB::statement('DROP INDEX IF EXISTS users_email_unique');
        // Create a partial unique index to enforce uniqueness only for admin users (firebase_uid IS NULL)
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS users_email_unique_admin ON users (email) WHERE firebase_uid IS NULL');
    }

    public function down(): void
    {
        // Drop the partial index and recreate the global unique index
        DB::statement('DROP INDEX IF EXISTS users_email_unique_admin');
        DB::statement('ALTER TABLE users ADD CONSTRAINT users_email_unique UNIQUE (email)');
    }
};
