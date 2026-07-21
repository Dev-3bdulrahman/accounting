<?php

namespace Dev3bdulrahman\Accounting\Listeners;

use Dev3bdulrahman\Accounting\Models\Account;
use Dev3bdulrahman\Accounting\Services\JournalEntryService;
use Dev3bdulrahman\Sales\Events\PaymentReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class CreateJournalEntryOnPaymentReceived implements ShouldQueue
{
    public function __construct(
        private JournalEntryService $journalEntryService
    ) {}

    public function handle(PaymentReceived $event): void
    {
        try {
            $payment = $event->payment;
            $amount = $payment->amount;

            if (!$amount || $amount <= 0) {
                Log::warning('Accounting: Payment #{number} has no amount, skipping journal entry.', [
                    'number' => $payment->payment_number,
                ]);
                return;
            }

            $cashAccount = $this->findAccountByType($event->companyId, 'cash_or_bank');
            $receivableAccount = $this->findAccountByType($event->companyId, 'accounts_receivable');

            if (!$cashAccount || !$receivableAccount) {
                Log::warning('Accounting: Cannot create journal entry for Payment #{number}. Required accounts not found.', [
                    'number' => $payment->payment_number,
                    'company_id' => $event->companyId,
                    'has_cash' => (bool) $cashAccount,
                    'has_receivable' => (bool) $receivableAccount,
                ]);
                return;
            }

            $this->journalEntryService->create([
                'company_id' => $event->companyId,
                'entry_number' => 'PMT-' . $payment->payment_number,
                'entry_date' => $payment->payment_date ?? now()->format('Y-m-d'),
                'description' => 'Auto-entry for Payment #' . $payment->payment_number,
                'status' => 'posted',
                'created_by' => $event->userId,
                'lines' => [
                    [
                        'account_id' => $cashAccount->id,
                        'debit' => $amount,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $receivableAccount->id,
                        'debit' => 0,
                        'credit' => $amount,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Accounting: Failed to create journal entry for PaymentReceived event.', [
                'payment_id' => $event->payment->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function findAccountByType(int $companyId, string $type): ?Account
    {
        $query = Account::where('company_id', $companyId)
            ->where('is_active', true);

        return match ($type) {
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

            default => null,
        };
    }
}
