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
        // 1. Chart of Accounts (دليل الحسابات)
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('code'); // Account Code e.g. 1101
            $table->string('name'); // Account Name e.g. Cash in Hand
            $table->string('type'); // asset, liability, equity, revenue, expense
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('code');
            $table->index('type');
            $table->index('is_active');
        });

        // 2. Journal Entries (القيود اليومية)
        Schema::create('accounting_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->string('entry_number');
            $table->date('entry_date');
            $table->text('description')->nullable();
            $table->string('status')->default('draft'); // draft, posted
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('entry_number');
            $table->index('status');
        });

        // 3. Journal Entry Lines (تفاصيل القيود)
        Schema::create('accounting_journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('accounting_journal_entries')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('accounting_accounts')->onDelete('cascade');
            $table->decimal('debit', 15, 4)->default(0.0000);
            $table->decimal('credit', 15, 4)->default(0.0000);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index('journal_entry_id');
            $table->index('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_journal_entry_lines');
        Schema::dropIfExists('accounting_journal_entries');
        Schema::dropIfExists('accounting_accounts');
    }
};
