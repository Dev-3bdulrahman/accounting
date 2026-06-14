<?php
namespace Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\Taxes;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Dev3bdulrahman\Accounting\Models\Tax;
class Index extends Component
{
    use WithPagination;
    public string $search = '';
    public bool $showModal = false;
    public ?int $editId = null;
    public string $name = '', $code = '', $type = 'percentage', $scope = 'both';
    public float $rate = 0;
    public bool $is_active = true;
    protected function rules(): array {
        return [
            'name'  => 'required|string|max:255',
            'code'  => 'required|string|max:20|unique:accounting_taxes,code,' . ($this->editId ?? 'NULL'),
            'type'  => 'required|in:percentage,fixed',
            'scope' => 'required|in:sale,purchase,both',
            'rate'  => 'required|numeric|min:0',
        ];
    }
    public function openModal(?int $id = null): void {
        $this->resetValidation();
        $this->editId = $id;
        if ($id) {
            $t = Tax::findOrFail($id);
            $this->name = $t->name; $this->code = $t->code;
            $this->type = $t->type; $this->scope = $t->scope;
            $this->rate = $t->rate; $this->is_active = $t->is_active;
        } else {
            $this->name = ''; $this->code = ''; $this->type = 'percentage';
            $this->scope = 'both'; $this->rate = 0; $this->is_active = true;
        }
        $this->showModal = true;
    }
    public function closeModal(): void { $this->showModal = false; $this->editId = null; }
    public function save(): void {
        $this->validate();
        $companyId = session('active_company_id') ?? auth()->user()->company_id;
        $data = ['name'=>$this->name,'code'=>$this->code,'type'=>$this->type,'scope'=>$this->scope,'rate'=>$this->rate,'is_active'=>$this->is_active,'company_id'=>$companyId];
        $this->editId ? Tax::findOrFail($this->editId)->update($data) : Tax::create($data);
        $this->closeModal();
        $this->dispatch('swal', ['title'=>__('Success'),'text'=>__('Tax saved.'),'icon'=>'success']);
    }
    public function deleteTax(int $id): void { Tax::findOrFail($id)->delete(); }
    #[Layout('layouts.admin')]
    #[Title('Taxes')]
    public function render() {
        $companyId = session('active_company_id') ?? auth()->user()->company_id;
        $taxes = Tax::where('company_id', $companyId)->when($this->search, fn($q)=>$q->where('name','like','%'.$this->search.'%'))->paginate(20);
        return view('accounting::livewire.admin.taxes.index', compact('taxes'));
    }
}
