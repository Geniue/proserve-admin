<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('accounting_accounts')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type', 40)->index();
            $table->string('normal_balance', 10)->default('debit');
            $table->text('description')->nullable();
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('accounting_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number')->unique();
            $table->date('entry_date')->index();
            $table->string('type', 40)->default('manual')->index();
            $table->string('status', 20)->default('draft')->index();
            $table->decimal('total_debit', 12, 2)->default(0);
            $table->decimal('total_credit', 12, 2)->default(0);
            $table->text('memo')->nullable();
            $table->nullableMorphs('reference');
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('accounting_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->string('vendor_name');
            $table->date('expense_date')->index();
            $table->foreignId('expense_account_id')->constrained('accounting_accounts')->restrictOnDelete();
            $table->foreignId('payment_account_id')->constrained('accounting_accounts')->restrictOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('accounting_journal_entries')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('payment_method', 40)->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('accounting_journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('accounting_journal_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounting_accounts')->restrictOnDelete();
            $table->unsignedSmallInteger('line_number')->default(1);
            $table->string('description')->nullable();
            $table->decimal('debit', 12, 2)->default(0);
            $table->decimal('credit', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['account_id', 'journal_entry_id']);
        });

        $now = now();
        DB::table('accounting_accounts')->insert([
            ['code' => '1000', 'name' => 'Cash on Hand', 'type' => 'asset', 'normal_balance' => 'debit', 'opening_balance' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '1010', 'name' => 'Bank Account', 'type' => 'asset', 'normal_balance' => 'debit', 'opening_balance' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'type' => 'asset', 'normal_balance' => 'debit', 'opening_balance' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'liability', 'normal_balance' => 'credit', 'opening_balance' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '3000', 'name' => 'Owner Equity', 'type' => 'equity', 'normal_balance' => 'credit', 'opening_balance' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '4000', 'name' => 'Service Revenue', 'type' => 'revenue', 'normal_balance' => 'credit', 'opening_balance' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '5000', 'name' => 'Provider Payouts', 'type' => 'expense', 'normal_balance' => 'debit', 'opening_balance' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '5100', 'name' => 'Operating Expenses', 'type' => 'expense', 'normal_balance' => 'debit', 'opening_balance' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '5200', 'name' => 'Refunds', 'type' => 'expense', 'normal_balance' => 'debit', 'opening_balance' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_journal_lines');
        Schema::dropIfExists('accounting_expenses');
        Schema::dropIfExists('accounting_journal_entries');
        Schema::dropIfExists('accounting_accounts');
    }
};
