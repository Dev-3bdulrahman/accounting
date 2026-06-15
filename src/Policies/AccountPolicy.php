<?php

namespace Dev3bdulrahman\Accounting\Policies;

use App\Models\User;
use Dev3bdulrahman\Accounting\Models\Account;

class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('accounting.accounts.view');
    }

    public function view(User $user, Account $account): bool
    {
        return $user->can('accounting.accounts.view') && $account->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->can('accounting.accounts.create');
    }

    public function update(User $user, Account $account): bool
    {
        return $user->can('accounting.accounts.update') && $account->company_id === $user->company_id;
    }

    public function delete(User $user, Account $account): bool
    {
        return $user->can('accounting.accounts.delete') && $account->company_id === $user->company_id;
    }
}
