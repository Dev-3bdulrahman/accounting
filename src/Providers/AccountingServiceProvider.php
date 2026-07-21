<?php

namespace Dev3bdulrahman\Accounting\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Dev3bdulrahman\Accounting\Models\Account;
use Dev3bdulrahman\Accounting\Models\JournalEntry;
use Dev3bdulrahman\Accounting\Models\Expense;
use Dev3bdulrahman\Accounting\Policies\AccountPolicy;
use Dev3bdulrahman\Accounting\Policies\JournalEntryPolicy;
use Dev3bdulrahman\Accounting\Policies\ExpensePolicy;
use Dev3bdulrahman\Accounting\Events\JournalEntryPosted;
use Dev3bdulrahman\Accounting\Events\ExpenseApproved;
use Dev3bdulrahman\Accounting\Listeners\LogJournalEntryPosted;
use Dev3bdulrahman\Accounting\Listeners\LogExpenseApproved;
use Dev3bdulrahman\Accounting\Listeners\CreateJournalEntryOnInvoiceIssued;
use Dev3bdulrahman\Accounting\Listeners\CreateJournalEntryOnPaymentReceived;
use Dev3bdulrahman\Accounting\Listeners\CreateJournalEntryOnSupplierInvoiceIssued;
use Dev3bdulrahman\Accounting\Listeners\CreateJournalEntryOnSupplierPayment;
use Dev3bdulrahman\Accounting\Listeners\CreateJournalEntryOnStockAdjustment;

class AccountingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../Views', 'accounting');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../Translations', 'accounting');

        // Register Policies
        Gate::policy(Account::class, AccountPolicy::class);
        Gate::policy(JournalEntry::class, JournalEntryPolicy::class);
        Gate::policy(Expense::class, ExpensePolicy::class);

        // Register Event Listeners
        Event::listen(JournalEntryPosted::class, LogJournalEntryPosted::class);
        Event::listen(ExpenseApproved::class, LogExpenseApproved::class);

        // Cross-package accounting integration listeners
        Event::listen(\Dev3bdulrahman\Sales\Events\InvoiceIssued::class, CreateJournalEntryOnInvoiceIssued::class);
        Event::listen(\Dev3bdulrahman\Sales\Events\PaymentReceived::class, CreateJournalEntryOnPaymentReceived::class);
        Event::listen(\Dev3bdulrahman\Purchases\Events\SupplierInvoiceIssued::class, CreateJournalEntryOnSupplierInvoiceIssued::class);
        Event::listen(\Dev3bdulrahman\Purchases\Events\SupplierPaymentMade::class, CreateJournalEntryOnSupplierPayment::class);
        Event::listen(\Dev3bdulrahman\Inventory\Events\StockAdjustmentApproved::class, CreateJournalEntryOnStockAdjustment::class);

        // Register Livewire Components
        if (class_exists(Livewire::class)) {
            Livewire::component('accounting-accounts',        \Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\Accounts\Index::class);
            Livewire::component('accounting-bank-accounts',   \Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\BankAccounts\Index::class);
            Livewire::component('accounting-expenses',        \Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\Expenses\Index::class);
            Livewire::component('accounting-journal-entries', \Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\JournalEntries\Index::class);
            Livewire::component('accounting-taxes',           \Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\Taxes\Index::class);
            Livewire::component('accounting-cost-centers',    \Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\CostCenters\Index::class);
        }
    }
}
