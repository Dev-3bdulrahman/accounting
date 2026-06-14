<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin/accounting')->middleware(['web', 'auth'])->group(function () {
    Route::get('/accounts',        \Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\Accounts\Index::class)->name('admin.accounting.accounts.index');
    Route::get('/journal-entries', \Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\JournalEntries\Index::class)->name('admin.accounting.journal-entries.index');
    Route::get('/taxes',           \Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\Taxes\Index::class)->name('admin.accounting.taxes.index');
    Route::get('/expenses',        \Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\Expenses\Index::class)->name('admin.accounting.expenses.index');
    Route::get('/bank-accounts',   \Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\BankAccounts\Index::class)->name('admin.accounting.bank-accounts.index');
    Route::get('/cost-centers',    \Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\CostCenters\Index::class)->name('admin.accounting.cost-centers.index');
});
