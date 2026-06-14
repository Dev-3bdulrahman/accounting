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
