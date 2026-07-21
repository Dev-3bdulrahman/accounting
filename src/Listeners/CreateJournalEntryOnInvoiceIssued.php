<?php

namespace Dev3bdulrahman\Accounting\Listeners;

use Dev3bdulrahman\Accounting\Models\Account;
use Dev3bdulrahman\Accounting\Services\JournalEntryService;
use Dev3bdulrahman\Sales\Events\InvoiceIssued;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class CreateJournalEntryOnInvoiceIssued implements ShouldQueue
{
    public function __construct(
        private JournalEntryService $journalEntryService
    ) {}

    public function handle(InvoiceIssued $event): void
    {
        try {
            $invoice = $event->invoice;
            $invoice->loadMissing('items');

            $grandTotal = $invoice->grand_total;

            if (!$grandTotal || $grandTotal <= 0) {
                Log::warning('Accounting: Invoice #{number} has no grand total, skipping journal entry.', [
                    'number' => $invoice->invoice_number,
                ]);
                return;
            }

            $receivableAccount = $this->findAccountByType($event->companyId, 'accounts_receivable');
            $revenueAccount = $this->findAccountByType($event->companyId, 'sales_revenue');

            if (!$receivableAccount || !$revenueAccount) {
                Log::warning('Accounting: Cannot create journal entry for Invoice #{number}. Required accounts not found.', [
                    'number' => $invoice->invoice_number,
                    'company_id' => $event->companyId,
                    'has_receivable' => (bool) $receivableAccount,
                    'has_revenue' => (bool) $revenueAccount,
                ]);
                return;
            }

            $this->journalEntryService->create([
                'company_id' => $event->companyId,
                'entry_number' => 'INV-' . $invoice->invoice_number,
                'entry_date' => $invoice->invoice_date ?? now()->format('Y-m-d'),
                'description' => 'Auto-entry for Invoice #' . $invoice->invoice_number,
                'status' => 'posted',
                'created_by' => $event->userId,
                'lines' => [
                    [
                        'account_id' => $receivableAccount->id,
                        'debit' => $grandTotal,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $revenueAccount->id,
                        'debit' => 0,
                        'credit' => $grandTotal,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Accounting: Failed to create journal entry for InvoiceIssued event.', [
                'invoice_id' => $event->invoice->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function findAccountByType(int $companyId, string $type): ?Account
    {
        $query = Account::where('company_id', $companyId)
            ->where('is_active', true);

        return match ($type) {
            'accounts_receivable' => $query->where(function ($q) {
                $q->where('code', 'like', '12%')
                  ->orWhere(function ($q2) {
                      $q2->where('type', 'asset')
                         ->where(function ($q3) {
                             $q3->where('name', 'like', '%receivable%')
                                ->orWhere('name', 'like', '%عملاء%')
                                ->orWhere('name', 'like', '%العملاء%')
                                ->orWhere('name', 'like', '%مدين%');
                         });
                  });
            })->first() ?? Account::where('company_id', $companyId)->where('is_active', true)->where('type', 'asset')->first(),

            'sales_revenue' => $query->where('type', 'revenue')->first() 
                ?? Account::where('company_id', $companyId)->where('is_active', true)
                    ->where(function ($q) {
                        $q->where('name', 'like', '%مبيعات%')
                          ->orWhere('name', 'like', '%إيراد%')
                          ->orWhere('name', 'like', '%sales%');
                    })->first(),

            default => null,
        };
    }
}
