<?php

namespace Dev3bdulrahman\Accounting\Policies;

use App\Models\User;
use Dev3bdulrahman\Accounting\Models\JournalEntry;

class JournalEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('accounting.entries.view');
    }

    public function view(User $user, JournalEntry $journalEntry): bool
    {
        return $user->can('accounting.entries.view') && $journalEntry->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->can('accounting.entries.create');
    }

    public function update(User $user, JournalEntry $journalEntry): bool
    {
        return $user->can('accounting.entries.update') && $journalEntry->company_id === $user->company_id;
    }

    public function delete(User $user, JournalEntry $journalEntry): bool
    {
        return $user->can('accounting.entries.delete') && $journalEntry->company_id === $user->company_id;
    }

    public function post(User $user, JournalEntry $journalEntry): bool
    {
        return $user->can('accounting.entries.post') && $journalEntry->company_id === $user->company_id;
    }
}
