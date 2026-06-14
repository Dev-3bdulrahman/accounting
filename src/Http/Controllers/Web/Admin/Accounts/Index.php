<?php

namespace Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\Accounts;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Dev3bdulrahman\Accounting\Models\Account;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterType = '';

    // Modal state
    public bool $showModal = false;
    public ?int $editId = null;

    // Form fields
    public string $code = '';
    public string $name = '';
    public string $type = 'asset';
    public ?int $parent_id = null;
    public string $description = '';
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'code'        => 'required|string|max:20|unique:accounting_accounts,code,' . ($this->editId ?? 'NULL'),
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id'   => 'nullable|exists:accounting_accounts,id',
            'description' => 'nullable|string|max:1000',
            'is_active'   => 'boolean',
        ];
    }

    public function openModal(?int $id = null): void
    {
        $this->resetValidation();
        $this->editId = $id;

        if ($id) {
            $account = Account::findOrFail($id);
            $this->code        = $account->code;
            $this->name        = $account->name;
            $this->type        = $account->type;
            $this->parent_id   = $account->parent_id;
            $this->description = $account->description ?? '';
            $this->is_active   = $account->is_active;
        } else {
            $this->code = $this->generateNextCode();
            $this->name = '';
            $this->type = 'asset';
            $this->parent_id = null;
            $this->description = '';
            $this->is_active = true;
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editId = null;
    }

    public function save(): void
    {
        $this->validate();

        $companyId = session('active_company_id') ?? auth()->user()->company_id;

        if ($this->editId) {
            $account = Account::findOrFail($this->editId);
            $account->update([
                'code'        => $this->code,
                'name'        => $this->name,
                'type'        => $this->type,
                'parent_id'   => $this->parent_id,
                'description' => $this->description ?: null,
                'is_active'   => $this->is_active,
            ]);
        } else {
            Account::create([
                'company_id'  => $companyId,
                'code'        => $this->code,
                'name'        => $this->name,
                'type'        => $this->type,
                'parent_id'   => $this->parent_id,
                'description' => $this->description ?: null,
                'is_active'   => $this->is_active,
            ]);
        }

        $this->closeModal();
        $this->dispatch('swal', [
            'title' => __('accounting::accounting.save'),
            'text'  => $this->editId
                ? __('Account updated successfully.')
                : __('Account created successfully.'),
            'icon'  => 'success',
        ]);
    }

    public function deleteAccount(int $id): void
    {
        $account = Account::findOrFail($id);
        $account->delete();
        $this->dispatch('swal', [
            'title' => __('Deleted'),
            'text'  => __('Account deleted.'),
            'icon'  => 'success',
        ]);
    }

    private function generateNextCode(): string
    {
        $last = Account::orderByDesc('id')->first();
        if (!$last) return '1000';
        $next = (intval($last->code) ?: 1000) + 10;
        return (string) $next;
    }

    #[Layout('layouts.admin')]
    #[Title('Chart of Accounts')]
    public function render()
    {
        $companyId = session('active_company_id') ?? auth()->user()->company_id;

        $accounts = Account::where('company_id', $companyId)
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            }))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->orderBy('code')
            ->paginate(20);

        $parentAccounts = Account::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('accounting::livewire.admin.accounts.index', [
            'accounts'       => $accounts,
            'parentAccounts' => $parentAccounts,
        ]);
    }
}
