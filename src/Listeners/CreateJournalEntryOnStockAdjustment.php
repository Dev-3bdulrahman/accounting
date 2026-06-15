<?php

namespace Dev3bdulrahman\Accounting\Listeners;

use Dev3bdulrahman\Accounting\Models\Account;
use Dev3bdulrahman\Accounting\Services\JournalEntryService;
use Dev3bdulrahman\Inventory\Events\StockAdjustmentApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class CreateJournalEntryOnStockAdjustment implements ShouldQueue
{
    public function __construct(
        private JournalEntryService $journalEntryService
    ) {}

    public function handle(StockAdjustmentApproved $event): void
    {
        try {
            $adjustment = $event->adjustment;
            $adjustment->loadMissing('items');

            $totalValue = $adjustment->items->sum(function ($item) {
                return abs($item->quantity * ($item->cost ?? $item->unit_cost ?? 0));
            });

            if ($totalValue <= 0) {
                Log::warning('Accounting: Stock Adjustment #{id} has zero total value, skipping journal entry.', [
                    'id' => $adjustment->id,
                ]);
                return;
            }

            $inventoryAccount = $this->findAccountByType($event->companyId, 'inventory');
            $cogsAccount = $this->findAccountByType($event->companyId, 'cost_of_goods');

            if (!$inventoryAccount || !$cogsAccount) {
                Log::warning('Accounting: Cannot create journal entry for Stock Adjustment #{id}. Required accounts not found.', [
                    'id' => $adjustment->id,
                    'company_id' => $event->companyId,
                    'has_inventory' => (bool) $inventoryAccount,
                    'has_cogs' => (bool) $cogsAccount,
                ]);
                return;
            }

            // Determine if this is an increase or decrease adjustment
            $netQuantity = $adjustment->items->sum('quantity');
            $isIncrease = $netQuantity >= 0;

            $lines = $isIncrease
                ? [
                    [
                        'account_id' => $inventoryAccount->id,
                        'debit' => $totalValue,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $cogsAccount->id,
                        'debit' => 0,
                        'credit' => $totalValue,
                    ],
                ]
                : [
                    [
                        'account_id' => $cogsAccount->id,
                        'debit' => $totalValue,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $inventoryAccount->id,
                        'debit' => 0,
                        'credit' => $totalValue,
                    ],
                ];

            $this->journalEntryService->create([
                'company_id' => $event->companyId,
                'entry_number' => 'ADJ-' . $adjustment->id,
                'entry_date' => $adjustment->adjustment_date ?? now()->format('Y-m-d'),
                'description' => 'Auto-entry for Stock Adjustment #' . $adjustment->id,
                'status' => 'posted',
                'created_by' => $event->userId,
                'lines' => $lines,
            ]);
        } catch (\Throwable $e) {
            Log::error('Accounting: Failed to create journal entry for StockAdjustmentApproved event.', [
                'adjustment_id' => $event->adjustment->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function findAccountByType(int $companyId, string $type): ?Account
    {
        $query = Account::where('company_id', $companyId)
            ->where('is_active', true);

        return match ($type) {
            'inventory' => $query->where(function ($q) {
                $q->where('type', 'asset')
                  ->where('name', 'like', '%inventory%');
            })->first(),

            'cost_of_goods' => $query->where('type', 'expense')->first(),

            default => null,
        };
    }
}
