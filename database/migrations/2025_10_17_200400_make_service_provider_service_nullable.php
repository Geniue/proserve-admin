<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->foreignId('service_id')->nullable()->change();
            // Re-add the foreign key constraint with nullOnDelete to keep integrity
            $table->foreign('service_id')->references('id')->on('services')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable(false)->change();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }
};
