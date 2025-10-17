<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Allow null passwords for app users (authenticated via Firebase)
            $table->string('password')->nullable()->change();
            // Add metadata JSON to store Firestore user details
            if (!Schema::hasColumn('users', 'metadata')) {
                $table->json('metadata')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert password back to not nullable if desired
            $table->string('password')->nullable(false)->change();
            if (Schema::hasColumn('users', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
    }
};
