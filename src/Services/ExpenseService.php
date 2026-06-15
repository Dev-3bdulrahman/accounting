<?php

namespace Dev3bdulrahman\Accounting\Services;

use Dev3bdulrahman\Accounting\Models\Expense;
use Illuminate\Pagination\LengthAwarePaginator;

class ExpenseService
{
    /**
     * List expenses with filters.
     */
    public function listExpenses(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Expense::query()->with(['category', 'creator']);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('expense_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('expense_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('expense_date', 'desc')->paginate($perPage);
    }

    /**
     * Create a new expense.
     */
    public function create(array $data): Expense
    {
        $data['company_id'] = $data['company_id'] ?? (session('active_company_id') ?: auth()->user()->company_id);
        $data['branch_id'] = $data['branch_id'] ?? auth()->user()->branch_id ?? null;
        $data['created_by'] = $data['created_by'] ?? auth()->id();
        $data['status'] = $data['status'] ?? 'draft';

        return Expense::create($data);
    }

    /**
     * Update an existing expense.
     */
    public function update(Expense $expense, array $data): Expense
    {
        $expense->update($data);

        return $expense;
    }

    /**
     * Delete an expense.
     */
    public function delete(Expense $expense): bool
    {
        return $expense->delete();
    }

    /**
     * Approve an expense (change status to 'approved').
     */
    public function approve(Expense $expense): Expense
    {
        if ($expense->status === 'approved') {
            throw new \LogicException(__('Expense is already approved.'));
        }

        $expense->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return $expense;
    }
}
