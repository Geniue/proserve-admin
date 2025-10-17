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
        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->decimal('custom_price', 10, 2)->nullable();
            $table->text('bio')->nullable();
            $table->integer('experience_years')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->integer('completed_jobs')->default(0);
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->boolean('is_available')->default(true);
            $table->json('availability_schedule')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_providers');
    }
};
