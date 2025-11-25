<?php

namespace App\Livewire\Tenant\PettyCash;

use Livewire\Component;
use Livewire\WithPagination;
//Modelos
use App\Models\Auth\Tenant;
use App\Models\Tenant\PettyCash\PettyCash as PettyCashModel;
use App\Models\Tenant\PettyCash\VntDetailPettyCash;
//Servicios
use Illuminate\Support\Facades\Auth;
use App\Services\Tenant\TenantManager;
use App\Traits\HasCompanyConfiguration;
use Carbon\Carbon;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PettyCash extends Component
{
    use WithPagination, HasCompanyConfiguration;

    public $pettyCash_id;
    public $base;
    public $showDetail = false;
    //public $warehouseId; // Added for dynamic warehouse selection

    //Propiedades para la tabla
    public $showModal = false;
    public $search = '';
    public $sortField = 'consecutive';
    public $sortDirection = 'desc';
    public $perPage = 10;

    //Messages
    public $errorMessage = '';

    protected $rules =[
        'base' => 'required|integer',
        //'warehouseId' => 'required|integer', // Added validation for warehouseId
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount(){
        $this->ensureTenantConnection();
        // Inicializar configuración de empresa
        $this->initializeCompanyConfiguration();

        // DEBUG: Limpiar caché para testing
        $this->clearConfigurationCache();

        // DEBUG: Log para verificar inicialización
        Log::info('🔍 PettyCash mount() ejecutado', [
            'currentCompanyId' => $this->currentCompanyId,
            'currentPlainId' => $this->currentPlainId,
            'configService_exists' => $this->configService ? 'YES' : 'NO'
        ]);
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
        $this->resetPage();
    }
    
    public function create()
    {
        $this->showModal = true;
    }

    public function save(){
        try{
            $this->ensureTenantConnection();
    
            $exists=$this->PettyCashExits(6);
    
            if ($exists) {
                $this->addError('base', 'No se puede registrar, hay cajas abiertas');
            }else{
                $this->resetErrorBag('base');
                $this->validate();
            
                // Determine the next consecutive number for the given warehouse
                $lastConsecutive = PettyCashModel::where('warehouseId', 6)->where('user')->max('consecutive');
            
                $newConsecutive = $lastConsecutive ? $lastConsecutive + 1 : 1;
            
                $pettyCashData = [
                    'base' => $this->base,
                    'consecutive' => $newConsecutive, // Use the calculated consecutive
                    'status' => 1,
                    'created_at' => Carbon::now(),
                    'userIdOpen' => Auth::id(),
                    'warehouseId' => 6,//$this->warehouseId, // Use the dynamic warehouseId
                    'cashier' => Auth::id(),
                ];
            
                $newPettyCashId=PettyCashModel::create($pettyCashData);
                $pettyCash_id=$newPettyCashId->id;
                $this->saveDetailPettyCash($pettyCash_id);
                session()->flash('message', 'Registro realizado exitosamente.');
            
                $this->resetValidation();
                $this->resetForm();
            
                $this->showModal = false;
            }
        }catch(\Exception $e){
            session()->flash('error', 'El registro realizó correctamente.'. $e->getMessage());
        }
    }

    public function PettyCashExits($warehouseId){
        $this->ensureTenantConnection();

        return PettyCashModel::where('status', 1)->where('warehouseId', $warehouseId)->exists();
    }

    public function saveDetailPettyCash($pettyCash_id){
        try{
            $this->ensureTenantConnection();
            
            
            $dataDetailPettyCash = [
                'status' => 1,
                'value' => $this->base,
                'created_at' => Carbon::now(),
                'pettyCashId' => $pettyCash_id,
                'reasonPettyCashId' => 5,
                'methodPaymentId' => 1,
                'observations' => 'Apertura de caja'
            ];
    
            VntDetailPettyCash::create($dataDetailPettyCash);
        }catch(\Exception $e){
            session()->flash('error', 'Error al registrar el detalle: ' . $e->getMessage());
        }
    }

    public function viewDetail($pettyCash_id){
        $this->pettyCash_id=$pettyCash_id;
        $this->showDetail=true;
    }


    public function render()
    {   
        $this->ensureTenantConnection();
        $petty_cashes = PettyCashModel::query()
            ->select('vnt_petty_cash.*', 'u.name')
            ->join('rap.users as u', 'u.id', '=', 'vnt_petty_cash.userIdOpen')
            ->when($this->search, function ($query) {
                $query->where('vnt_petty_cash.consecutive', 'like', '%' . $this->search . '%')
                    ->orWhere('u.name', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.petty-cash.petty-cash', [
            'boxes'=>$petty_cashes
        ]);
    }

    public function canOpenPettyCash(): bool{
        $result = $this->isOptionEnabled(17);
        $value = $this->getOptionValue(17);

        Log::info('🔍 canOpenPettyCash() verificación', [
            'companyId' => $this->currentCompanyId,
            'option_id' => 17,
            'result' => $result ? 'TRUE' : 'FALSE',
            'option_value' => $value,
            'configService_exists' => $this->configService ? 'YES' : 'NO',
            'method_called' => 'isOptionEnabled(17) y getOptionValue(17)'
        ]);
        return $result;
    }

    public function cancel()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->showModal = false;
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

    private function resetForm(){
        $this->base='';
    }
}
