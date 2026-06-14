<?php
namespace Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\CostCenters;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Dev3bdulrahman\Accounting\Models\CostCenter;
class Index extends Component
{
    use WithPagination;
    public string $search = '';
    public bool $showModal = false;
    public ?int $editId = null;
    public string $name = '', $code = '';
    public ?int $parent_id = null;
    public bool $is_active = true;
    protected function rules(): array {
        return ['name'=>'required|string|max:255','code'=>'required|string|max:20|unique:accounting_cost_centers,code,'.($this->editId??'NULL')];
    }
    public function openModal(?int $id = null): void {
        $this->resetValidation(); $this->editId = $id;
        if ($id) { $c = CostCenter::findOrFail($id); $this->name=$c->name;$this->code=$c->code;$this->parent_id=$c->parent_id;$this->is_active=$c->is_active; }
        else { $this->name='';$this->code='';$this->parent_id=null;$this->is_active=true; }
        $this->showModal = true;
    }
    public function closeModal(): void { $this->showModal = false; }
    public function save(): void {
        $this->validate();
        $companyId = session('active_company_id') ?? auth()->user()->company_id;
        $data = ['name'=>$this->name,'code'=>$this->code,'parent_id'=>$this->parent_id,'is_active'=>$this->is_active,'company_id'=>$companyId];
        $this->editId ? CostCenter::findOrFail($this->editId)->update($data) : CostCenter::create($data);
        $this->closeModal();
        $this->dispatch('swal', ['title'=>__('Success'),'text'=>__('Cost Center saved.'),'icon'=>'success']);
    }
    public function deleteCenter(int $id): void { CostCenter::findOrFail($id)->delete(); }
    #[Layout('layouts.admin')]
    #[Title('Cost Centers')]
    public function render() {
        $companyId = session('active_company_id') ?? auth()->user()->company_id;
        $centers = CostCenter::where('company_id',$companyId)->when($this->search,fn($q)=>$q->where('name','like','%'.$this->search.'%'))->paginate(20);
        $parents = CostCenter::where('company_id',$companyId)->get();
        return view('accounting::livewire.admin.cost-centers.index', compact('centers','parents'));
    }
}
