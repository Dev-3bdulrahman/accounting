<?php

namespace Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\JournalEntries;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Dev3bdulrahman\Accounting\Models\JournalEntry;
use Dev3bdulrahman\Accounting\Models\JournalEntryLine;
use Dev3bdulrahman\Accounting\Models\Account;
use Illuminate\Support\Str;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';

    // Create modal
    public bool $showCreateModal = false;
    public string $newEntryDate = '';
    public string $newEntryDesc = '';
    public array $newLines = [];

    // View modal
    public bool $showViewModal = false;
    public ?JournalEntry $viewingEntry = null;

    public function mount(): void
    {
        $this->newEntryDate = now()->format('Y-m-d');
        $this->newLines = $this->emptyLines();
    }

    public function openCreateModal(): void
    {
        $this->newEntryDate = now()->format('Y-m-d');
        $this->newEntryDesc = '';
        $this->newLines = $this->emptyLines();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function addLine(): void
    {
        $this->newLines[] = ['account_id' => '', 'description' => '', 'debit' => 0, 'credit' => 0];
    }

    public function removeLine(int $index): void
    {
        unset($this->newLines[$index]);
        $this->newLines = array_values($this->newLines);
    }

    public function saveEntry(): void
    {
        $this->validate([
            'newEntryDate' => 'required|date',
            'newEntryDesc' => 'required|string|max:500',
        ]);

        $lines = collect($this->newLines)->filter(fn($l) => !empty($l['account_id']));

        $totalDebit  = $lines->sum('debit');
        $totalCredit = $lines->sum('credit');

        if ($lines->isEmpty()) {
            $this->addError('lines', __('accounting::accounting.balance_error'));
            return;
        }

        if (abs($totalDebit - $totalCredit) > 0.001) {
            $this->addError('lines', __('accounting::accounting.balance_error'));
            return;
        }

        $companyId = session('active_company_id') ?? auth()->user()->company_id;

        $entry = JournalEntry::create([
            'company_id'   => $companyId,
            'entry_number' => 'JE-' . strtoupper(Str::random(8)),
            'entry_date'   => $this->newEntryDate,
            'description'  => $this->newEntryDesc,
            'status'       => 'draft',
            'created_by'   => auth()->id(),
        ]);

        foreach ($lines as $line) {
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $line['account_id'],
                'description'      => $line['description'] ?? null,
                'debit'            => $line['debit'] ?? 0,
                'credit'           => $line['credit'] ?? 0,
            ]);
        }

        $this->closeCreateModal();
        $this->dispatch('swal', [
            'title' => __('Success'),
            'text'  => __('Journal entry created successfully.'),
            'icon'  => 'success',
        ]);
    }

    public function postEntry(int $id): void
    {
        $entry = JournalEntry::with('lines')->findOrFail($id);
        $totalDebit  = $entry->lines->sum('debit');
        $totalCredit = $entry->lines->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.001) {
            $this->dispatch('swal', [
                'title' => __('Error'),
                'text'  => __('accounting::accounting.balance_error'),
                'icon'  => 'error',
            ]);
            return;
        }

        $entry->update(['status' => 'posted']);
        $this->dispatch('swal', [
            'title' => __('Posted'),
            'text'  => __('Journal entry posted successfully.'),
            'icon'  => 'success',
        ]);
    }

    public function viewEntry(int $id): void
    {
        $this->viewingEntry = JournalEntry::with(['lines.account'])->findOrFail($id);
        $this->showViewModal = true;
    }

    private function emptyLines(): array
    {
        return [
            ['account_id' => '', 'description' => '', 'debit' => 0, 'credit' => 0],
            ['account_id' => '', 'description' => '', 'debit' => 0, 'credit' => 0],
        ];
    }

    #[Layout('layouts.admin')]
    #[Title('Journal Entries')]
    public function render()
    {
        $companyId = session('active_company_id') ?? auth()->user()->company_id;

        $entries = JournalEntry::where('company_id', $companyId)
            ->with('lines')
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('entry_number', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            }))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderByDesc('entry_date')
            ->paginate(20);

        $accounts = Account::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('accounting::livewire.admin.journal-entries.index', compact('entries', 'accounts'));
    }
}
