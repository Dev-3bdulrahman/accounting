<?php
namespace Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\BankAccounts;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Dev3bdulrahman\Accounting\Models\BankAccount;
class Index extends Component
{
    use WithPagination;
    public string $search = '';
    public bool $showModal = false;
    public ?int $editId = null;
    public string $bank_name='', $account_name='', $account_number='', $iban='', $swift_code='', $currency='SAR';
    public float $opening_balance = 0;
    public bool $is_active = true;
    protected function rules(): array {
        return ['bank_name'=>'required|string|max:255','account_name'=>'required|string|max:255','account_number'=>'required|string|max:50|unique:accounting_bank_accounts,account_number,'.($this->editId??'NULL'),'currency'=>'required|string|max:3','opening_balance'=>'required|numeric|min:0'];
    }
    public function openModal(?int $id = null): void {
        $this->resetValidation(); $this->editId = $id;
        if ($id) { $b=BankAccount::findOrFail($id);$this->bank_name=$b->bank_name;$this->account_name=$b->account_name;$this->account_number=$b->account_number;$this->iban=$b->iban??'';$this->swift_code=$b->swift_code??'';$this->currency=$b->currency;$this->opening_balance=$b->opening_balance;$this->is_active=$b->is_active; }
        else { $this->bank_name='';$this->account_name='';$this->account_number='';$this->iban='';$this->swift_code='';$this->currency='SAR';$this->opening_balance=0;$this->is_active=true; }
        $this->showModal = true;
    }
    public function closeModal(): void { $this->showModal = false; }
    public function save(): void {
        $this->validate();
        $companyId = session('active_company_id') ?? auth()->user()->company_id;
        $data = ['bank_name'=>$this->bank_name,'account_name'=>$this->account_name,'account_number'=>$this->account_number,'iban'=>$this->iban??null,'swift_code'=>$this->swift_code??null,'currency'=>$this->currency,'opening_balance'=>$this->opening_balance,'current_balance'=>$this->editId?null:$this->opening_balance,'is_active'=>$this->is_active,'company_id'=>$companyId];
        if ($this->editId) { unset($data['current_balance']); BankAccount::findOrFail($this->editId)->update($data); }
        else BankAccount::create($data);
        $this->closeModal();
        $this->dispatch('swal', ['title'=>__('Success'),'text'=>__('Bank account saved.'),'icon'=>'success']);
    }
    public function deleteBankAccount(int $id): void { BankAccount::findOrFail($id)->delete(); }
    #[Layout('layouts.admin')]
    #[Title('Bank Accounts')]
    public function render() {
        $companyId = session('active_company_id') ?? auth()->user()->company_id;
        $bankAccounts = BankAccount::where('company_id',$companyId)->when($this->search,fn($q)=>$q->where('bank_name','like','%'.$this->search.'%')->orWhere('account_name','like','%'.$this->search.'%'))->paginate(20);
        return view('accounting::livewire.admin.bank-accounts.index', compact('bankAccounts'));
    }
}
