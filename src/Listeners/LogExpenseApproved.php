<?php

namespace Dev3bdulrahman\Accounting\Listeners;

use App\Services\AuditLogService;
use Dev3bdulrahman\Accounting\Events\ExpenseApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogExpenseApproved implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private AuditLogService $auditLogService,
    ) {}

    /**
     * Handle the ExpenseApproved event.
     */
    public function handle(ExpenseApproved $event): void
    {
        try {
            $expense = $event->expense;

            $this->auditLogService->log(
                action: 'expense_approved',
                companyId: $event->companyId,
                userId: $event->userId,
                model: $expense,
                oldValues: null,
                newValues: [
                    'expense_id' => $expense->id,
                    'amount' => $expense->amount,
                    'category_id' => $expense->category_id,
                    'description' => $expense->description,
                    'expense_date' => $expense->expense_date?->format('Y-m-d'),
                ],
            );
        } catch (\Throwable $e) {
            Log::error('LogExpenseApproved: Failed to log expense approved.', [
                'error' => $e->getMessage(),
                'expense_id' => $event->expense->id ?? null,
                'user_id' => $event->userId ?? null,
            ]);
        }
    }
}
