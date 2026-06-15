<?php

namespace Dev3bdulrahman\Accounting\Services;

use Dev3bdulrahman\Accounting\Models\JournalEntry;
use Dev3bdulrahman\Accounting\Models\JournalEntryLine;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class JournalEntryService
{
    /**
     * List journal entries with search and filters.
     */
    public function listEntries(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = JournalEntry::query()->with('lines.account');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('entry_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('entry_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('entry_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        return $query->orderBy('entry_date', 'desc')->paginate($perPage);
    }

    /**
     * Find a journal entry by ID with lines loaded.
     */
    public function find(int $id): JournalEntry
    {
        return JournalEntry::with('lines.account')->findOrFail($id);
    }

    /**
     * Create a new journal entry with lines (validates debits = credits).
     */
    public function create(array $data): JournalEntry
    {
        $lines = $data['lines'] ?? [];

        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($lines as $line) {
            $totalDebit += (float) ($line['debit'] ?? 0);
            $totalCredit += (float) ($line['credit'] ?? 0);
        }

        if (abs($totalDebit - $totalCredit) > 0.0001) {
            throw new \InvalidArgumentException(
                __('Journal entry is unbalanced. Total Debit (:debit) must equal Total Credit (:credit)', [
                    'debit' => $totalDebit,
                    'credit' => $totalCredit,
                ])
            );
        }

        return DB::transaction(function () use ($data, $lines) {
            $companyId = $data['company_id'] ?? (session('active_company_id') ?: auth()->user()->company_id);

            $entry = JournalEntry::create([
                'company_id' => $companyId,
                'branch_id' => $data['branch_id'] ?? auth()->user()->branch_id ?? null,
                'entry_number' => $data['entry_number'],
                'entry_date' => $data['entry_date'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            foreach ($lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'description' => $line['description'] ?? null,
                ]);
            }

            return $entry->load('lines.account');
        });
    }

    /**
     * Post a journal entry (change status to 'posted', validate debits == credits).
     */
    public function post(JournalEntry $entry): JournalEntry
    {
        if ($entry->status === 'posted') {
            throw new \LogicException(__('Journal entry is already posted.'));
        }

        $entry->load('lines');

        $totalDebit = $entry->lines->sum('debit');
        $totalCredit = $entry->lines->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.0001) {
            throw new \InvalidArgumentException(
                __('Cannot post unbalanced journal entry. Debit (:debit) != Credit (:credit)', [
                    'debit' => $totalDebit,
                    'credit' => $totalCredit,
                ])
            );
        }

        $entry->update(['status' => 'posted']);

        return $entry;
    }

    /**
     * Reverse a journal entry (create reversal entry with opposite debit/credit amounts).
     */
    public function reverse(JournalEntry $entry): JournalEntry
    {
        $entry->load('lines');

        return DB::transaction(function () use ($entry) {
            $reversalEntry = JournalEntry::create([
                'company_id' => $entry->company_id,
                'branch_id' => $entry->branch_id,
                'entry_number' => $entry->entry_number . '-REV',
                'entry_date' => now()->format('Y-m-d'),
                'description' => __('Reversal of :number', ['number' => $entry->entry_number]),
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            foreach ($entry->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $reversalEntry->id,
                    'account_id' => $line->account_id,
                    'debit' => $line->credit,
                    'credit' => $line->debit,
                    'description' => __('Reversal: :desc', ['desc' => $line->description ?? '']),
                ]);
            }

            return $reversalEntry->load('lines.account');
        });
    }

    /**
     * Delete a journal entry (only allow if status is 'draft').
     */
    public function delete(JournalEntry $entry): bool
    {
        if ($entry->status !== 'draft') {
            throw new \LogicException(__('Only draft journal entries can be deleted.'));
        }

        return $entry->delete();
    }
}
