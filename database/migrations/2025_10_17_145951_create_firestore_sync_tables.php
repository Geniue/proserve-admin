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
        Schema::create('firestore_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('collection', 100)->index();
            $table->string('document_id')->nullable();
            $table->string('action', 50); // fetch, create, update, delete
            $table->string('direction', 50); // firestore_to_postgres, postgres_to_firestore
            $table->string('status', 50); // success, failed, pending
            $table->text('error_message')->nullable();
            $table->timestamp('attempted_at');
            $table->timestamp('completed_at')->nullable();
            
            $table->index(['collection', 'status']);
            $table->index('attempted_at');
        });

        Schema::create('firestore_sync_status', function (Blueprint $table) {
            $table->id();
            $table->string('collection', 100)->unique();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('last_document_timestamp')->nullable();
            $table->integer('total_documents')->default(0);
            $table->string('status', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firestore_sync_logs');
        Schema::dropIfExists('firestore_sync_status');
    }
};
