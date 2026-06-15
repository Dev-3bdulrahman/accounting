<?php

namespace Dev3bdulrahman\Accounting\Events;

use Dev3bdulrahman\Accounting\Models\Expense;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpenseApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Expense $expense,
        public int $userId,
        public int $companyId,
    ) {}
}
