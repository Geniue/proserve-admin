<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreignId('category_id')->nullable()->change();
            $table->text('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
            $table->foreign('category_id')->references('id')->on('service_categories')->onDelete('cascade');
        });
    }
};
