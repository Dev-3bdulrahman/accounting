<?php

namespace Dev3bdulrahman\Accounting\Listeners;

use Dev3bdulrahman\Accounting\Models\Account;
use Dev3bdulrahman\Accounting\Services\JournalEntryService;
use Dev3bdulrahman\Purchases\Events\SupplierPaymentMade;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class CreateJournalEntryOnSupplierPayment implements ShouldQueue
{
    public function __construct(
        private JournalEntryService $journalEntryService
    ) {}

    public function handle(SupplierPaymentMade $event): void
    {
        try {
            $supplierPayment = $event->supplierPayment;
            $amount = $supplierPayment->amount;

            if (!$amount || $amount <= 0) {
                Log::warning('Accounting: Supplier Payment #{number} has no amount, skipping journal entry.', [
                    'number' => $supplierPayment->payment_number,
                ]);
                return;
            }

            $payableAccount = $this->findAccountByType($event->companyId, 'accounts_payable');
            $cashAccount = $this->findAccountByType($event->companyId, 'cash_or_bank');

            if (!$payableAccount || !$cashAccount) {
                Log::warning('Accounting: Cannot create journal entry for Supplier Payment #{number}. Required accounts not found.', [
                    'number' => $supplierPayment->payment_number,
                    'company_id' => $event->companyId,
                    'has_payable' => (bool) $payableAccount,
                    'has_cash' => (bool) $cashAccount,
                ]);
                return;
            }

            $this->journalEntryService->create([
                'company_id' => $event->companyId,
                'entry_number' => 'SPMT-' . $supplierPayment->payment_number,
                'entry_date' => $supplierPayment->payment_date ?? now()->format('Y-m-d'),
                'description' => 'Auto-entry for Supplier Payment #' . $supplierPayment->payment_number,
                'status' => 'posted',
                'created_by' => $event->userId,
                'lines' => [
                    [
                        'account_id' => $payableAccount->id,
                        'debit' => $amount,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $cashAccount->id,
                        'debit' => 0,
                        'credit' => $amount,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Accounting: Failed to create journal entry for SupplierPaymentMade event.', [
                'payment_id' => $event->supplierPayment->id ?? null,
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
            })->first() ?? Account::where('company_id', $companyId)->where('is_active', true)->where('type', 'liability')->first(),

            'cash_or_bank' => $query->where(function ($q) {
                $q->where('code', 'like', '11%')
                  ->orWhere(function ($q2) {
                      $q2->where('type', 'asset')
                         ->where(function ($q3) {
                             $q3->where('name', 'like', '%cash%')
                                ->orWhere('name', 'like', '%bank%')
                                ->orWhere('name', 'like', '%صندوق%')
                                ->orWhere('name', 'like', '%بنك%')
                                ->orWhere('name', 'like', '%خزينة%');
                         });
                  });
            })->first() ?? Account::where('company_id', $companyId)->where('is_active', true)->where('type', 'asset')->first(),

            default => null,
        };
    }
}
