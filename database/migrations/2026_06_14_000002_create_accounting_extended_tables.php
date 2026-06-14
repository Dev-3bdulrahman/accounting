<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fiscal Years
        Schema::create('accounting_fiscal_years', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();
            $table->softDeletes();
        });

        // Journal Books (e.g. Sales Journal, Purchase Journal, General)
        Schema::create('accounting_journals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['general', 'sales', 'purchase', 'cash', 'bank'])->default('general');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Taxes
        Schema::create('accounting_taxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('rate', 10, 4)->default(0);
            $table->enum('scope', ['sale', 'purchase', 'both'])->default('both');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('account_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // Tax Rates (historical rates per fiscal year)
        Schema::create('accounting_tax_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tax_id')->index();
            $table->decimal('rate', 10, 4);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();
        });

        // Expense Categories
        Schema::create('accounting_expense_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->string('color')->nullable();
            $table->unsignedBigInteger('account_id')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Expenses
        Schema::create('accounting_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->index();
            $table->string('reference')->unique();
            $table->date('expense_date');
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('SAR');
            $table->enum('status', ['draft', 'approved', 'rejected', 'paid'])->default('draft')->index();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // Bank Accounts
        Schema::create('accounting_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('bank_name');
            $table->string('account_name');
            $table->string('account_number')->unique();
            $table->string('iban')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('currency', 3)->default('SAR');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('account_id')->nullable()->index(); // Chart of Accounts link
            $table->timestamps();
            $table->softDeletes();
        });

        // Bank Transactions
        Schema::create('accounting_bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_account_id')->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('reference')->nullable();
            $table->date('transaction_date');
            $table->text('description')->nullable();
            $table->enum('type', ['debit', 'credit'])->index();
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2)->nullable();
            $table->boolean('is_reconciled')->default(false)->index();
            $table->unsignedBigInteger('journal_entry_id')->nullable()->index();
            $table->timestamps();
        });

        // Cost Centers
        Schema::create('accounting_cost_centers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->string('code')->unique();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_cost_centers');
        Schema::dropIfExists('accounting_bank_transactions');
        Schema::dropIfExists('accounting_bank_accounts');
        Schema::dropIfExists('accounting_expenses');
        Schema::dropIfExists('accounting_expense_categories');
        Schema::dropIfExists('accounting_tax_rates');
        Schema::dropIfExists('accounting_taxes');
        Schema::dropIfExists('accounting_journals');
        Schema::dropIfExists('accounting_fiscal_years');
    }
};
