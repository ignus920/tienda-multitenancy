<?php

namespace App\Livewire\Tenant\VntCompany;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\VntCompany;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;

class VntCompanyForm extends Component
{
    use WithPagination;

    protected $listeners = [
        'type-identification-changed' => 'updateTypeIdentification',
        'regime-changed' => 'updateRegime',
        'fiscal-responsibility-changed' => 'updateFiscalResponsibility'
    ];

    public $search = '';
    public $showModal = false;
    public $editingId = null;
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';

    // Propiedades del formulario
    public $businessName = '';
    public $billingEmail = '';
    public $firstName = '';
    public $lastName = '';
    public $secondName = '';
    public $secondLastName = '';
    public $integrationDataId = '';
    public $identification = '';
    public $checkDigit = '';
    public $status = 1;
    public $typePerson = '';
    public $typeIdentificationId = '';
    public $regimeId = '';
    public $code_ciiu = '';
    public $fiscalResponsabilityId = '';
    public $verification_digit = '';

     


    protected function rules()
    {
        // Reglas base aplicables siempre
        $baseRules = [
            'identification' => 'required|string|max:15|unique:vnt_companies,identification',
            'typePerson' => 'required|string|in:Natural,Juridica',
            'typeIdentificationId' => 'required|integer|exists:central.cnf_type_identifications,id',
            'regimeId' => 'nullable|integer',
            'status' => 'nullable|integer|in:0,1',
            'billingEmail' => 'nullable|email|max:255',
            'checkDigit' => 'nullable|integer|max:99',
            'integrationDataId' => 'nullable|integer',
            'code_ciiu' => 'nullable|string|max:255',
            'fiscalResponsabilityId' => 'nullable|integer',
            'verification_digit' => 'nullable|string|max:1',
        ];

        // Caso 1: Persona JURÍDICA
        if ($this->typePerson === 'Juridica') {
            return array_merge($baseRules, [
                'businessName' => 'required|string|max:255',
                'firstName' => 'nullable|string|max:255',
                'lastName' => 'nullable|string|max:255',
                'secondName' => 'nullable|string|max:255',
                'secondLastName' => 'nullable|string|max:255',
            ]);
        }

        // Caso 2: Persona NATURAL
        if ($this->typePerson === 'Natural') {
            return array_merge($baseRules, [
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'businessName' => 'nullable|string|max:255',
                'secondName' => 'nullable|string|max:255',
            ]);
        }

        return $baseRules;
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
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

    public function render()
    {
        $this->ensureTenantConnection();
        $items = VntCompany::query()
            ->when($this->search, function ($query) {
                $query->where('businessName', 'like', '%' . $this->search . '%')
                    ->orWhere('identification', 'like', '%' . $this->search . '%')
                    ->orWhere('firstName', 'like', '%' . $this->search . '%')
                    ->orWhere('lastName', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.vnt-company.vnt-company-form', [
            'items' => $items
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->ensureTenantConnection();
        $company = VntCompany::findOrFail($id);
        $this->editingId = $id;
        $this->typeIdentificationId = $company->typeIdentificationId;
        $this->identification = $company->identification;
        $this->firstName = $company->firstName;
        $this->secondName = $company->secondName;
        $this->lastName = $company->lastName;
        $this->businessName = $company->businessName;
        $this->billingEmail = $company->billingEmail;
        $this->typePerson = $company->typePerson;
        $this->regimeId = $company->regimeId;
        $this->fiscalResponsabilityId = $company->fiscalResponsabilityId;
        $this->code_ciiu = $company->code_ciiu;
        $this->checkDigit = $company->checkDigit;
        $this->status = $company->status ?? 1; // Load existing status or default to active
        $this->showModal = true;
    }

    public function save()
    {
        //dd($this->all()); //
        $this->ensureTenantConnection();
        $this->validate();

        $data = [
            'typeIdentificationId' => $this->typeIdentificationId,
            'identification' => $this->identification,
            'firstName' => $this->firstName,
            'secondName' => $this->secondName,
            'lastName' => $this->lastName,
            'secondLastName' => $this->secondLastName,
            'businessName' => $this->businessName,
            'billingEmail' => $this->billingEmail,
            'typePerson' => $this->typePerson,
            'checkDigit' =>  (int)$this->typeIdentificationId == 1 ?  null : $this->checkDigit,
            'code_ciiu' =>  (int)$this->typeIdentificationId == 1 ? '0' : $this->code_ciiu,
            'regimeId' =>  (int)$this->typeIdentificationId == 1 ?  2 : $this->regimeId,
            'fiscalResponsabilityId' =>  (int)$this->typeIdentificationId == 1 ? 1 : $this->fiscalResponsabilityId,
            'status' => $this->status,
        ];

        if ($this->editingId) {
            $item = VntCompany::findOrFail($this->editingId);
            $item->update($data);
            session()->flash('message', 'Registro actualizado exitosamente.');
        } else {
            VntCompany::create($data);
            session()->flash('message', 'Registro creado exitosamente.');
        }

        $this->resetForm();
        $this->showModal = false;
    }

    public function delete($id)
    {
        $this->ensureTenantConnection();
        VntCompany::findOrFail($id)->delete();
        session()->flash('message', 'Registro eliminado exitosamente.');
    }

    public function exportExcel()
    {
        // TODO: Implementar exportación a Excel
        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => 'Exportación a Excel - En desarrollo'
        ]);
    }

    public function exportPdf()
    {
        // TODO: Implementar exportación a PDF
        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => 'Exportación a PDF - En desarrollo'
        ]);
    }

    public function exportCsv()
    {
        // TODO: Implementar exportación a CSV
        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => 'Exportación a CSV - En desarrollo'
        ]);
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->businessName = '';
        $this->firstName = '';
        $this->billingEmail = '';
        $this->identification = '';
        $this->integrationDataId = '';
        $this->lastName = '';
        $this->checkDigit = '';
        $this->status = 1; // Default to active for new records
        $this->secondName = '';
        $this->typeIdentificationId = '';
        $this->typePerson = '';
        $this->code_ciiu = '';
        $this->regimeId = '';
        $this->fiscalResponsabilityId = '';
        $this->verification_digit = '';

        $this->resetErrorBag();
    }

    public function updateTypeIdentification($typeIdentificationId)
    {
        $this->typeIdentificationId = $typeIdentificationId;
    }

    public function updateRegime($regimeId)
    {
        $this->regimeId = $regimeId;
    }

    public function updateFiscalResponsibility($fiscalResponsibilityId)
    {
        $this->fiscalResponsabilityId = $fiscalResponsibilityId;
    }

    public function toggleStatus()
    {
        $this->status = $this->status ? 0 : 1;
    }

    public function updatedStatus($value)
    {
        // Convert boolean to integer for database storage
        $this->status = $value ? 1 : 0;
    }

    public function validateSendAndEdit(){
         

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
