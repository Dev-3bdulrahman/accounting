<?php

namespace Dev3bdulrahman\Accounting\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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

        // Register Livewire Components
        Livewire::component('accounting::admin.accounts.index', \Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\Accounts\Index::class);
        Livewire::component('accounting::admin.bank-accounts.index', \Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\BankAccounts\Index::class);
        Livewire::component('accounting::admin.expenses.index', \Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\Expenses\Index::class);
        Livewire::component('accounting::admin.journal-entries.index', \Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\JournalEntries\Index::class);
        Livewire::component('accounting::admin.taxes.index', \Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\Taxes\Index::class);
    }
}
