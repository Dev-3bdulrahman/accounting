<?php

namespace Dev3bdulrahman\Accounting\Listeners;

use Dev3bdulrahman\Accounting\Models\Account;
use Dev3bdulrahman\Accounting\Services\JournalEntryService;
use Dev3bdulrahman\Purchases\Events\SupplierInvoiceIssued;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class CreateJournalEntryOnSupplierInvoiceIssued implements ShouldQueue
{
    public function __construct(
        private JournalEntryService $journalEntryService
    ) {}

    public function handle(SupplierInvoiceIssued $event): void
    {
        try {
            $invoice = $event->supplierInvoice;
            $invoice->loadMissing('items');

            $grandTotal = $invoice->grand_total;

            if (!$grandTotal || $grandTotal <= 0) {
                Log::warning('Accounting: Supplier Invoice #{number} has no grand total, skipping journal entry.', [
                    'number' => $invoice->invoice_number,
                ]);
                return;
            }

            $payableAccount = $this->findAccountByType($event->companyId, 'accounts_payable');
            $expenseAccount = $this->findAccountByType($event->companyId, 'purchase_expense');

            if (!$payableAccount || !$expenseAccount) {
                Log::warning('Accounting: Cannot create journal entry for Supplier Invoice #{number}. Required accounts not found.', [
                    'number' => $invoice->invoice_number,
                    'company_id' => $event->companyId,
                    'has_payable' => (bool) $payableAccount,
                    'has_expense' => (bool) $expenseAccount,
                ]);
                return;
            }

            $this->journalEntryService->create([
                'company_id' => $event->companyId,
                'entry_number' => 'PINV-' . $invoice->invoice_number,
                'entry_date' => $invoice->invoice_date ?? now()->format('Y-m-d'),
                'description' => 'Auto-entry for Supplier Invoice #' . $invoice->invoice_number,
                'status' => 'posted',
                'created_by' => $event->userId,
                'lines' => [
                    [
                        'account_id' => $expenseAccount->id,
                        'debit' => $grandTotal,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $payableAccount->id,
                        'debit' => 0,
                        'credit' => $grandTotal,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Accounting: Failed to create journal entry for SupplierInvoiceIssued event.', [
                'invoice_id' => $event->supplierInvoice->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function findAccountByType(int $companyId, string $type): ?Account
    {
        $query = Account::where('company_id', $companyId)
            ->where('is_active', true);

        return match ($type) {
            'accounts_payable' => $query->where(function ($q) {
                $q->where('type', 'liability')
                  ->where(function ($q2) {
                      $q2->where('name', 'like', '%payable%')
                         ->orWhere('name', 'like', '%مورد%')
                         ->orWhere('name', 'like', '%المورد%')
                         ->orWhere('name', 'like', '%دائن%');
                  });
            })->first(),

            'purchase_expense' => $query->where(function ($q) {
                $q->whereIn('type', ['expense', 'asset'])
                  ->where(function ($q2) {
                      $q2->where('name', 'like', '%purchase%')
                         ->orWhere('name', 'like', '%cost%')
                         ->orWhere('name', 'like', '%inventory%')
                         ->orWhere('name', 'like', '%مشتريات%')
                         ->orWhere('name', 'like', '%المشتريات%')
                         ->orWhere('name', 'like', '%مخزون%')
                         ->orWhere('name', 'like', '%تكلفة%');
                  });
            })->first() ?? Account::where('company_id', $companyId)->where('is_active', true)->where('type', 'expense')->first(),

            default => null,
        };
    }
}
