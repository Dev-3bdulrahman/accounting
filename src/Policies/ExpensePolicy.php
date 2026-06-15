<?php

namespace Dev3bdulrahman\Accounting\Policies;

use App\Models\User;
use Dev3bdulrahman\Accounting\Models\Expense;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('accounting.expenses.view');
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->can('accounting.expenses.view') && $expense->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->can('accounting.expenses.create');
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->can('accounting.expenses.update') && $expense->company_id === $user->company_id;
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->can('accounting.expenses.delete') && $expense->company_id === $user->company_id;
    }

    public function approve(User $user, Expense $expense): bool
    {
        return $user->can('accounting.expenses.approve') && $expense->company_id === $user->company_id;
    }
}
