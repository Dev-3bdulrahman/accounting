<?php

namespace Dev3bdulrahman\Accounting\Events;

use Dev3bdulrahman\Accounting\Models\JournalEntry;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JournalEntryPosted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public JournalEntry $entry,
        public int $userId,
        public int $companyId,
    ) {}
}
