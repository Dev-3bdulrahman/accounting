<?php

use Illuminate\Support\Facades\Route;
use Dev3bdulrahman\Accounting\Http\Controllers\Api\AccountApiController;
use Dev3bdulrahman\Accounting\Http\Controllers\Api\JournalEntryApiController;
use Dev3bdulrahman\Accounting\Http\Controllers\Api\ExpenseApiController;

Route::prefix('api/v1/accounting')->middleware(['auth:sanctum', 'throttle:60,1', 'api.tenant'])->group(function () {
    // Chart of Accounts
    Route::get('accounts', [AccountApiController::class, 'index'])
        ->middleware('can:accounting.accounts.view')
        ->name('api.v1.accounting.accounts.index');

    Route::post('accounts', [AccountApiController::class, 'store'])
        ->middleware('can:accounting.accounts.create')
        ->name('api.v1.accounting.accounts.store');

    Route::get('accounts/{account}', [AccountApiController::class, 'show'])
        ->middleware('can:accounting.accounts.view')
        ->name('api.v1.accounting.accounts.show');

    Route::put('accounts/{account}', [AccountApiController::class, 'update'])
        ->middleware('can:accounting.accounts.edit')
        ->name('api.v1.accounting.accounts.update');

    Route::delete('accounts/{account}', [AccountApiController::class, 'destroy'])
        ->middleware('can:accounting.accounts.delete')
        ->name('api.v1.accounting.accounts.destroy');

    // Journal Entries
    Route::get('journal-entries', [JournalEntryApiController::class, 'index'])
        ->middleware('can:accounting.entries.view')
        ->name('api.v1.accounting.entries.index');

    Route::post('journal-entries', [JournalEntryApiController::class, 'store'])
        ->middleware('can:accounting.entries.create')
        ->name('api.v1.accounting.entries.store');

    Route::get('journal-entries/{journalEntry}', [JournalEntryApiController::class, 'show'])
        ->middleware('can:accounting.entries.view')
        ->name('api.v1.accounting.entries.show');

    Route::post('journal-entries/{journalEntry}/post', [JournalEntryApiController::class, 'post'])
        ->middleware('can:accounting.entries.post')
        ->name('api.v1.accounting.entries.post');

    Route::delete('journal-entries/{journalEntry}', [JournalEntryApiController::class, 'destroy'])
        ->middleware('can:accounting.entries.delete')
        ->name('api.v1.accounting.entries.destroy');

    // Expenses
    Route::get('expenses', [ExpenseApiController::class, 'index'])
        ->middleware('can:accounting.expenses.view')
        ->name('api.v1.accounting.expenses.index');

    Route::post('expenses', [ExpenseApiController::class, 'store'])
        ->middleware('can:accounting.expenses.create')
        ->name('api.v1.accounting.expenses.store');

    Route::get('expenses/{expense}', [ExpenseApiController::class, 'show'])
        ->middleware('can:accounting.expenses.view')
        ->name('api.v1.accounting.expenses.show');

    Route::put('expenses/{expense}', [ExpenseApiController::class, 'update'])
        ->middleware('can:accounting.expenses.update')
        ->name('api.v1.accounting.expenses.update');

    Route::delete('expenses/{expense}', [ExpenseApiController::class, 'destroy'])
        ->middleware('can:accounting.expenses.delete')
        ->name('api.v1.accounting.expenses.destroy');

    Route::post('expenses/{expense}/approve', [ExpenseApiController::class, 'approve'])
        ->middleware('can:accounting.expenses.approve')
        ->name('api.v1.accounting.expenses.approve');
});
