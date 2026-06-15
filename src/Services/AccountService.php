<?php

namespace Dev3bdulrahman\Accounting\Services;

use Dev3bdulrahman\Accounting\Models\Account;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AccountService
{
    /**
     * List accounts with search and filters.
     */
    public function listAccounts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Account::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        return $query->orderBy('code', 'asc')->paginate($perPage);
    }

    /**
     * Find an account by ID.
     */
    public function find(int $id): Account
    {
        return Account::findOrFail($id);
    }

    /**
     * Create a new account with unique code validation per company.
     */
    public function create(array $data): Account
    {
        $companyId = $data['company_id'] ?? (session('active_company_id') ?: auth()->user()->company_id);

        $exists = Account::where('company_id', $companyId)
            ->where('code', $data['code'])
            ->exists();

        if ($exists) {
            throw new \InvalidArgumentException(__('Account code already exists for this company.'));
        }

        $data['company_id'] = $companyId;

        return Account::create($data);
    }

    /**
     * Update an existing account.
     */
    public function update(Account $account, array $data): Account
    {
        if (isset($data['code']) && $data['code'] !== $account->code) {
            $exists = Account::where('company_id', $account->company_id)
                ->where('code', $data['code'])
                ->where('id', '!=', $account->id)
                ->exists();

            if ($exists) {
                throw new \InvalidArgumentException(__('Account code already exists for this company.'));
            }
        }

        $account->update($data);

        return $account;
    }

    /**
     * Delete an account (guard against deletion if has entry lines).
     */
    public function delete(Account $account): bool
    {
        if ($account->entryLines()->exists()) {
            throw new \LogicException(__('Cannot delete account with existing journal entry lines.'));
        }

        return $account->delete();
    }

    /**
     * Get hierarchical account tree for a company.
     */
    public function getTree(int $companyId): Collection
    {
        $accounts = Account::where('company_id', $companyId)
            ->orderBy('code', 'asc')
            ->get();

        return $this->buildTree($accounts);
    }

    /**
     * Build a tree structure from flat collection using parent_id.
     */
    private function buildTree(Collection $accounts, ?int $parentId = null): Collection
    {
        return $accounts
            ->where('parent_id', $parentId)
            ->map(function ($account) use ($accounts) {
                $account->children_tree = $this->buildTree($accounts, $account->id);
                return $account;
            })
            ->values();
    }
}
