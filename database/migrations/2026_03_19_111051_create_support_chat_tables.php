<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_user_id')->index();
            $table->string('user_name')->nullable();
            $table->string('user_phone', 50)->nullable();
            $table->string('user_type', 50)->nullable();
            $table->string('status', 50)->default('active')->index();
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('message_count')->default(0);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('support_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('support_chat_sessions')->cascadeOnDelete();
            $table->string('firebase_message_id')->nullable();
            $table->string('sender_type', 50);
            $table->string('sender_id')->nullable();
            $table->text('message')->nullable();
            $table->text('image_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_chat_messages');
        Schema::dropIfExists('support_chat_sessions');
    }
};
