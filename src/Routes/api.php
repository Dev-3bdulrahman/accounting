<?php

use Illuminate\Support\Facades\Route;
use Dev3bdulrahman\Accounting\Http\Controllers\Api\AccountApiController;
use Dev3bdulrahman\Accounting\Http\Controllers\Api\JournalEntryApiController;

Route::prefix('api/v1/accounting')->middleware(['api', 'auth'])->group(function () {
    // Chart of Accounts
    Route::get('accounts', [AccountApiController::class, 'index'])->middleware('can:accounting.accounts.view');
    Route::post('accounts', [AccountApiController::class, 'store'])->middleware('can:accounting.accounts.create');
    Route::get('accounts/{id}', [AccountApiController::class, 'show'])->middleware('can:accounting.accounts.view');
    Route::put('accounts/{id}', [AccountApiController::class, 'update'])->middleware('can:accounting.accounts.edit');
    Route::delete('accounts/{id}', [AccountApiController::class, 'destroy'])->middleware('can:accounting.accounts.delete');

    // Journal Entries
    Route::get('journal-entries', [JournalEntryApiController::class, 'index'])->middleware('can:accounting.entries.view');
    Route::post('journal-entries', [JournalEntryApiController::class, 'store'])->middleware('can:accounting.entries.create');
    Route::get('journal-entries/{id}', [JournalEntryApiController::class, 'show'])->middleware('can:accounting.entries.view');
    Route::delete('journal-entries/{id}', [JournalEntryApiController::class, 'destroy'])->middleware('can:accounting.entries.delete');
});
