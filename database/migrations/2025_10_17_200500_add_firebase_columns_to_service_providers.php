<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->string('firebase_id')->nullable()->unique()->after('id');
            $table->timestamp('last_synced_at')->nullable()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn(['firebase_id', 'last_synced_at']);
        });
    }
};
