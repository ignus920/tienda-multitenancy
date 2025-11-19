<?php

namespace App\Livewire\Tenant\PettyCash;

use Livewire\Component;
//Modelos
use App\Models\Auth\Tenant;
use App\Models\Tenant\PettyCash\PettyCash as PettyCashModel;
//Servicios
use Illuminate\Support\Facades\Auth;
use App\Services\Tenant\TenantManager;
use Carbon\Carbon;

class PettyCash extends Component
{
    public $pettyCash_id;
    public $base;
    //public $warehouseId; // Added for dynamic warehouse selection

    //Propiedades para la tabla
    public $showModal = false;
    public $search = '';
    public $sortField = 'consecutive';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $rules =[
        'base' => 'required|integer',
        //'warehouseId' => 'required|integer', // Added validation for warehouseId
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
        $this->resetPage();
        /*$this->sortDirection = $this->sortField === $field 
            ? $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc'
            : 'asc';

        $this->sortField = $field;*/
    }
    
    public function create()
    {
        //$this->resetExcept(['categories', 'types']); // No reseteamos las listas de opciones
        $this->showModal = true;
    }

    public function save(){
        $this->ensureTenantConnection();
        $this->validate();

        // Determine the next consecutive number for the given warehouse
        $lastConsecutive = PettyCashModel::where('warehouseId', 6)
                                        ->max('consecutive');

        $newConsecutive = $lastConsecutive ? $lastConsecutive + 1 : 1;

        $pettyCashData = [
            'base' => $this->base,
            'consecutive' => $newConsecutive, // Use the calculated consecutive
            'status' => 1,
            'created_at' => Carbon::now(),
            'userIdOpen' => Auth::id(),
            'warehouseId' => 6,//$this->warehouseId, // Use the dynamic warehouseId
            'cashier' => 6,
        ];

        PettyCashModel::create($pettyCashData);
        session()->flash('message', 'Caja creada correctamente.');

        $this->resetValidation();
        $this->reset([
            'base'
        ]);

        $this->showModal = false;
    }

    public function render()
    {   
        $this->ensureTenantConnection();
        $petty_cashes=PettyCashModel::query()
            ->when($this->search, function($query){
                $query->where('consecutive', 'like', '%' . $this->search . '%')
                        ->orWhere('cashier', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.petty-cash.petty-cash', [
            'boxes'=>$petty_cashes
        ]);
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return redirect()->route('tenant.select');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            return redirect()->route('tenant.select');
        }

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }
}
