<?php

namespace App\Livewire\Tenant\PettyCash;

use Livewire\Component;
use Livewire\WithPagination;
//Modelos
use App\Models\Tenant\PettyCash\VntDetailPettyCash;
use App\Models\Auth\Tenant;
use App\Models\Tenant\PettyCash\VntReasonsPettyCash;
use App\Models\Tenant\MethodPayments\VntMethodPayMents;
//Servicios
use App\Services\Tenant\TenantManager;
use App\Livewire\Tenant\PettyCash\Services\DetailPettyCashServices;
use App\Traits\HasCompanyConfiguration;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DetailPettyCash extends Component
{
    use WithPagination, HasCompanyConfiguration;

    public $pettyCash_id;
    public $detailMovement;
    public $typeMovement;
    public $reasonMovement;
    public $methodPayMovement;
    public $valueDetail;
    public $observations;

    //Propiedades para la tabla
    public $showModalMovement = false;
    public $search = '';
    public $sortField = 'invoiceId';
    public $sortDirection = 'desc';
    public $perPage = 6;

    protected $rules =[
        'typeMovement' => 'required',
        'reasonMovement' => 'required|integer',
        'methodPayMovement' => 'required|integer',
        'valueDetail' => 'required',
        'observations' => 'required'
        //'warehouseId' => 'required|integer', // Added validation for warehouseId
    ];

    protected $listeners = ['refreshDetail' => '$refresh'];

    public function getTypeMovementsProperty()
    {
        $movements = [];

        if ($this->canDoIncome()) {
            $movements['i'] = 'INGRESO';
        }

        if ($this->canDoEgress()) {
            $movements['e'] = 'EGRESO';
        }

        return $movements;
    }

    public function getValuesDetail()
    {
        return VntDetailPettyCash::where('pettyCashId', $this->pettyCash_id)->where('status', 1)
            ->with('methodPayments')
            ->when($this->search, function($query){
                $query->where('invoiceId', 'like', '%' . $this->search . '%')
                    ->orWhere('id', 'like', '%' . $this->search . '%');
            })->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function mount($pettyCash_id){
        // Inicializar configuración de empresa
        //$this->initializeCompanyConfiguration();

        // DEBUG: Limpiar caché para testing
        $this->clearConfigurationCache();

        // DEBUG: Log para verificar inicialización
        Log::info('🔍 DetailPettyCash mount() ejecutado', [
            'currentCompanyId' => $this->currentCompanyId,
            'currentPlainId' => $this->currentPlainId,
            'configService_exists' => $this->configService ? 'YES' : 'NO'
        ]);
        
        $this->pettyCash_id = $pettyCash_id;
        $this->loadDetailsData();
    }

    public function sortBy($field){
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
        $this->resetPage();
    }

    public function canDoMovement(): bool
    {
        $this->initializeCompanyConfiguration();
        $result = $this->isOptionEnabled(18);
        $value = $this->getOptionValue(18);

        Log::info('🔍 canDoMovement() verificación', [
            'companyId' => $this->currentCompanyId,
            'option_id' => 18,
            'result' => $result ? 'TRUE' : 'FALSE',
            'option_value' => $value,
            'configService_exists' => $this->configService ? 'YES' : 'NO',
            'method_called' => 'isOptionEnabled(18) y getOptionValue(18)'
        ]);
        return $result;
    }

    public function canDoIncome(): bool
    {
        $this->initializeCompanyConfiguration();
        $result = $this->isOptionEnabled(15);
        $value = $this->getOptionValue(15); 
        Log::info('🔍 canDoIncome() verificación', [
            'companyId' => $this->currentCompanyId,
            'option_id' => 15,
            'result' => $result ? 'TRUE' : 'FALSE',
            'option_value' => $value,
            'configService_exists' => $this->configService ? 'YES' : 'NO',
            'method_called' => 'isOptionEnabled(15) y getOptionValue(15)'
        ]);
        return $result;
    }

    public function canDoEgress(): bool
    {
        $this->initializeCompanyConfiguration();
        $result = $this->isOptionEnabled(16);
        $value = $this->getOptionValue(16);
        Log::info('🔍 canDoEgress() verificación', [
            'companyId' => $this->currentCompanyId,
            'option_id' => 16,
            'result' => $result ? 'TRUE' : 'FALSE',
            'option_value' => $value,
            'configService_exists' => $this->configService ? 'YES' : 'NO',
            'method_called' => 'isOptionEnabled(16) y getOptionValue(16)'
        ]);
        return $result; 
    }

    public function createMovement(){
        $this->showModalMovement=true;
        //dd($this->canDoIncome());
    }

    public function getReasonsProperty()
    {
        $this->ensureTenantConnection();
        
        if (empty($this->typeMovement)) {
            return collect(); // Return empty if no type is selected
        }

        return VntReasonsPettyCash::where('id', '!=', 5)
            ->where('type', $this->typeMovement)
            ->get();
    }

    public function getMethodPaymentProperty()
    {
        $this->ensureTenantConnection();

        return VntMethodPayMents::where('status', 1)->where('type', 2)->get();
    }

    public function render()
    {
        $this->ensureTenantConnection();
        $values = $this->getValuesDetail();
        return view('livewire.tenant.petty-cash.detail-petty-cash', [
            'detailPettyCash' => $values,
            'typeMovements' => $this->typeMovements
        ]);
    }

    public function loadDetailsData(){
        $this->ensureTenantConnection();
        $values = VntDetailPettyCash::where('pettyCashId', $this->pettyCash_id)->first();
    }

    public function save(){
        try{
            $this->ensureTenantConnection();
            $this->validate();

            $detailPettyCashService = app(DetailPettyCashServices::class);

            $detailPettyCashService->createMovement([
                'status' => 1,
                'value' => $this->valueDetail,
                'created_at' => Carbon::now(),
                'pettyCashId' => $this->pettyCash_id,
                'reasonPettyCashId' => $this->reasonMovement,
                'methodPaymentId' => $this->methodPayMovement,
                'observations' => $this->observations
            ]);

            $this->resetForm();
            $this->showModalMovement=false;

            session()->flash('message', 'Registro realizado exitosamente');

            $this->dispatch('refreshDetail');

        }catch(\Exception $e){
            session()->flash('error', 'Error no se realizó correctamente' . $e->getMessage());
            $this->resetForm();
        }
    }

    public function deleteMovement($detailMovement){
        $this->ensureTenantConnection();
        $typeMovement=VntDetailPettyCash::find($detailMovement)->reasonsPettyCash;
        $movement=VntDetailPettyCash::findOrFail($detailMovement);
        try{
            if($typeMovement->type == "i"){
                $income=VntDetailPettyCash::selectRaw('SUM(value) AS sumIncomes')->
                                            where('pettyCashId', $this->pettyCash_id)
                                            ->where('status',1)
                                            ->whereIn('reasonPettyCashId', [1,2,5,6])
                                            ->value('sumValues');
                dd($income);
            }elseif($typeMovement->type == "e"){
                $movement->update(['status' => 0]);
                session()->flash('message', 'Registro eliminado exitosamente');
            }

        }catch(\Exception $e){
            session()->flash('error', 'Error no se realizó correctamente' . $e->getMessage());
            $this->resetForm();
        }
    }


    private function ensureTenantConnection(): void
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            throw new \Exception('No tenant selected');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            throw new \Exception('Invalid tenant');
        }

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }

    private function resetForm(){
        $this->typeMovement='';
        $this->reasonMovement='';
        $this->methodPayMovement='';
        $this->valueDetail='';
        $this->observations='';
    }

    public function cancel()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->showModalMovement = false;
    } 
}
