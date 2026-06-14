<?php
namespace Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\Expenses;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Dev3bdulrahman\Accounting\Models\Expense;
use Dev3bdulrahman\Accounting\Models\ExpenseCategory;
use Illuminate\Support\Str;
class Index extends Component
{
    use WithPagination;
    public string $search = '', $filterStatus = '';
    public bool $showModal = false;
    public ?int $editId = null;
    public string $description = '', $expense_date = '', $currency = 'SAR', $notes = '';
    public float $amount = 0;
    public ?int $category_id = null;
    protected function rules(): array {
        return ['description'=>'required|string|max:500','expense_date'=>'required|date','amount'=>'required|numeric|min:0.01','category_id'=>'nullable|exists:accounting_expense_categories,id'];
    }
    public function openModal(?int $id = null): void {
        $this->resetValidation(); $this->editId = $id;
        if ($id) { $e=Expense::findOrFail($id);$this->description=$e->description;$this->expense_date=$e->expense_date->format('Y-m-d');$this->amount=$e->amount;$this->currency=$e->currency;$this->notes=$e->notes??'';$this->category_id=$e->category_id; }
        else { $this->description='';$this->expense_date=now()->format('Y-m-d');$this->amount=0;$this->currency='SAR';$this->notes='';$this->category_id=null; }
        $this->showModal = true;
    }
    public function closeModal(): void { $this->showModal = false; }
    public function save(): void {
        $this->validate();
        $companyId = session('active_company_id') ?? auth()->user()->company_id;
        $data = ['description'=>$this->description,'expense_date'=>$this->expense_date,'amount'=>$this->amount,'currency'=>$this->currency,'notes'=>$this->notes??null,'category_id'=>$this->category_id,'company_id'=>$companyId,'created_by'=>auth()->id(),'status'=>'draft'];
        if (!$this->editId) $data['reference'] = 'EXP-'.strtoupper(Str::random(8));
        $this->editId ? Expense::findOrFail($this->editId)->update($data) : Expense::create($data);
        $this->closeModal();
        $this->dispatch('swal', ['title'=>__('Success'),'text'=>__('Expense saved.'),'icon'=>'success']);
    }
    public function approveExpense(int $id): void {
        Expense::findOrFail($id)->update(['status'=>'approved','approved_by'=>auth()->id(),'approved_at'=>now()]);
        $this->dispatch('swal', ['title'=>__('Approved'),'text'=>__('Expense approved.'),'icon'=>'success']);
    }
    public function deleteExpense(int $id): void { Expense::findOrFail($id)->delete(); }
    #[Layout('layouts.admin')]
    #[Title('Expenses')]
    public function render() {
        $companyId = session('active_company_id') ?? auth()->user()->company_id;
        $expenses = Expense::where('company_id',$companyId)->with(['category','creator'])
            ->when($this->search,fn($q)=>$q->where('description','like','%'.$this->search.'%'))
            ->when($this->filterStatus,fn($q)=>$q->where('status',$this->filterStatus))
            ->orderByDesc('expense_date')->paginate(20);
        $categories = ExpenseCategory::where('company_id',$companyId)->where('is_active',true)->get();
        return view('accounting::livewire.admin.expenses.index', compact('expenses','categories'));
    }
}
