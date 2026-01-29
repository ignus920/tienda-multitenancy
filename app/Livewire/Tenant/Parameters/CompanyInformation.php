<?php

namespace App\Livewire\Tenant\Parameters;

use Livewire\Component;
use App\Models\Tenant\CnfInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use App\Models\Central\VntWarehouse;
use Livewire\WithPagination;

class CompanyInformation extends Component
{
    use WithPagination;
    use \App\Traits\Livewire\WithExport;

    public $warehouses = [];
    public $cnfInvoiceId = null;
    public $warehouseId = '';
    public $token = '';
    public $numeration = '';
    public $showModal = false;

    //Propiedades para la tabla
    public $search = '';
    public $sortField = 'token';
    public $sortDirection = 'asc';
    public $perPage = 5;

    //Campos validación
    public $warehouseIdExists = false;
    public $validatingWarehouseId = false;

    protected $rules = [
        'token' => 'required',
        'warehouseId' => 'required',
        'numeration' => 'required|integer'
    ];

    protected $messages = [
        'token.required' => 'El token es obligatorio.',
        'warehouseId' => 'La sucursal es obligatoria.',
        'numeration' => 'La númeración es obligatoria.'
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
    }

    public function getCompanyInformation()
    {
        $query = DB::table('users as u')
            ->join('user_tenants as uXt', 'uXt.user_id', '=', 'u.id')
            ->join('tenants as t', 't.id', '=', 'uXt.tenant_id')
            ->join('vnt_companies as v', 'v.id', '=', 't.company_id')
            ->join('vnt_warehouses as w', 'w.companyId', '=', 'v.id')
            ->join('cities as c', 'c.id', '=', 'w.cityId')
            ->join('cnf_type_identifications as ti', 'ti.id', '=', 'v.typeIdentificationId')
            ->where('u.id', Auth::id())
            ->where('w.main', 1)
            ->select(
                'v.businessName',
                'w.address',
                'c.name as city',
                'ti.acronym',
                'v.identification'
            )
            ->get();
        return $query;
    }

    public function createInvoiceInformation()
    {
        $this->showModal = true;
    }

    public function saveCompanyInvoice()
    {
        $this->ensureTenantConnection();
        $this->validate();

        if ($this->warehouseIdExists) {
            $this->addError('warehouseId', 'Esta sucursal ya esta registrada.');
            return;
        }

        $data = [
            'token' => $this->token,
            'id_warehouses' => $this->warehouseId,
            'numeracion' => $this->numeration,
            'facturador' => 'http://127.0.0.1:8000/api'
        ];

        try {
            if ($this->cnfInvoiceId) {
                $infoInvoice = CnfInvoice::findOrFail($this->cnfInvoiceId);
                $infoInvoice->update($data);
                session()->flash('message', 'Información actualizada correctamente.');
                $this->resetValidation();
                $this->resetForm();
            } else {
                $invoice = CnfInvoice::create($data);
                session()->flash('message', 'Información creada correctamente.');
                $this->resetForm();
            }
        } catch (\Exception $e) {
            Log::error($e);
            session()->flash('error', 'Error no se realizó correctamente: ' . $e->getMessage());
            $this->resetForm();
        }
    }

    public function editCompanyInvoice($cnfId)
    {
        $this->clearValidationErrors();
        $this->ensureTenantConnection();
        $data = CnfInvoice::with('warehouseRap')->findOrFail($cnfId);
        $this->cnfInvoiceId = $data->id;
        $this->token = $data->token;
        $this->warehouseId = $data->id_warehouses;
        $this->numeration = $data->numeracion;
        $this->showModal = true;
    }

    public function cancel()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->showModal = false;
    }

    private function resetForm()
    {
        $this->cnfInvoiceId = '';
        $this->token = '';
        $this->warehouseId = '';
        $this->numeration = '';
        $this->showModal = '';
        $this->warehouseIdExists = false;
        $this->validatingWarehouseId = false;
    }

    public function render()
    {
        $this->loadWarehouses();

        $this->ensureTenantConnection();
        $infoInvoice = CnfInvoice::query()
            ->with(['warehouseRap'])
            ->when($this->search, function ($query) {
                $query->where('token', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.parameters.company-information', [
            'companyInformation' => $this->getCompanyInformation(),
            'infoInvoices' => $infoInvoice,
        ]);
    }

    public function updatedWarehouseId()
    {
        $this->validateWarehouseId();
    }

    public function validateWarehouseId()
    {
        if (empty($this->warehouseId)) {
            $this->warehouseIdExists = false;
            $this->validatingWarehouseId = false;
        }

        $this->validatingWarehouseId = true;

        try {
            $this->ensureTenantConnection();
            $query = CnfInvoice::where('id_warehouses', $this->warehouseId);

            if ($this->cnfInvoiceId) {
                $query->where('id', '!=', $this->cnfInvoiceId);
            }
            $this->warehouseIdExists = $query->exists();
        } catch (\Exception $e) {
            // Log error but don't break the form
            Log::error('Error validating warehouseId exists', [
                'error' => $e->getMessage(),
                'warehouseId' => $this->warehouseId
            ]);
            $this->warehouseIdExists = false;
        } finally {
            $this->validatingWarehouseId = false;
        }
    }

    public function clearValidationErrors()
    {
        $this->resetErrorBag(['warehouseId']);
        $this->warehouseIdExists = false;
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

    private function getTenantId()
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            throw new \Exception('No tenant selected');
        }
        return $tenantId;
    }

    private function loadWarehouses(): void
    {
        $sessionTenant = $this->getTenantId();

        // Obtener el tenant desde la base de datos usando el ID de sesión
        $tenant = Tenant::find($sessionTenant);

        if (!$tenant || !$tenant->company_id) {
            $this->warehouses = collect([]);
            return;
        }

        // Las bodegas están en la base de datos central (vnt_warehouses)
        // Traer todos los almacenes que coincidan con ese company_id
        $this->warehouses = VntWarehouse::where('companyId', $tenant->company_id)
            ->where('status', true)
            ->with('company')
            ->orderBy('name')
            ->get();
    }
}
