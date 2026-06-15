<?php

namespace Dev3bdulrahman\Accounting\Listeners;

use App\Services\AuditLogService;
use Dev3bdulrahman\Accounting\Events\JournalEntryPosted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogJournalEntryPosted implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private AuditLogService $auditLogService,
    ) {}

    /**
     * Handle the JournalEntryPosted event.
     */
    public function handle(JournalEntryPosted $event): void
    {
        try {
            $entry = $event->entry;
            $entry->load('lines');

            $totalAmount = $entry->lines->sum('debit');

            $this->auditLogService->log(
                action: 'journal_entry_posted',
                companyId: $event->companyId,
                userId: $event->userId,
                model: $entry,
                oldValues: null,
                newValues: [
                    'journal_entry_id' => $entry->id,
                    'entry_number' => $entry->entry_number,
                    'total_amount' => $totalAmount,
                ],
            );
        } catch (\Throwable $e) {
            Log::error('LogJournalEntryPosted: Failed to log journal entry posted.', [
                'error' => $e->getMessage(),
                'entry_id' => $event->entry->id ?? null,
                'user_id' => $event->userId ?? null,
            ]);
        }
    }
}
