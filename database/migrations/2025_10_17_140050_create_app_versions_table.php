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
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version');
            $table->integer('build_number');
            $table->enum('platform', ['android', 'ios']);
            $table->boolean('force_update')->default(false);
            $table->text('update_message')->nullable();
            $table->string('download_url')->nullable();
            $table->timestamps();
            
            $table->unique(['version', 'platform']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
