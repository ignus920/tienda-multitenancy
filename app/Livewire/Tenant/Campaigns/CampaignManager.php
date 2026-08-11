<?php

namespace App\Livewire\Tenant\Campaigns;

use Livewire\Component;
use App\Models\Tenant\Campaigns\Campaign;
use Livewire\WithPagination;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Log;

class CampaignManager extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'desc';
    public $isModalOpen = false;
    public $isDeliveryModalOpen = false;

    // Propiedad para el modal de carga/éxito
    public $showModal = false; 

    // Campos del formulario de campaña
    public $campaignId, $name, $description, $start_date, $end_date, $gift_quantity, $assignment_type, $status;

    // Campos del formulario de entrega
    public $selectedCampaignId, $selectedCustomerId;

    // Propiedades para el listado de clientes en el modal
    public $customerSearch = '';

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

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleCampaignStatus($id)
    {
        $this->ensureTenantConnection();
        $campaign = Campaign::findOrFail($id);
        $campaign->status = $campaign->status === 'activo' ? 'pausado' : 'activo';
        $campaign->save();

        session()->flash('success', 'Estado de la campaña actualizado correctamente.');
    }

    public function render()
    {
        $this->ensureTenantConnection();
        
        $campaigns = Campaign::where('name', 'like', '%' . $this->search . '%')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage, ['*'], 'page');

        $deliveredCustomers = collect();
        if ($this->campaignId) {
            $campaign = Campaign::find($this->campaignId);
            if ($campaign) {
                $deliveredCustomers = $campaign->customers()
                    ->select('vnt_companies.*')
                    ->where(function($q) {
                        $q->where('businessName', 'like', '%' . $this->customerSearch . '%')
                          ->orWhere('firstName', 'like', '%' . $this->customerSearch . '%')
                          ->orWhere('lastName', 'like', '%' . $this->customerSearch . '%')
                          ->orWhere('identification', 'like', '%' . $this->customerSearch . '%');
                    })
                    ->paginate(5, ['*'], 'customerPage');
            }
        }

        $activeCampaigns = collect();
        $customers = collect();

        if ($this->isDeliveryModalOpen) {
            $activeCampaigns = Campaign::where('status', 'activo')
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->get();
            
            $customers = \App\Models\Tenant\Customer\VntCompany::orderBy('businessName', 'asc')->get();
        }

        return view('livewire.tenant.campaigns.campaign-manager', [
            'campaigns' => $campaigns,
            'deliveredCustomers' => $deliveredCustomers,
            'activeCampaigns' => $activeCampaigns,
            'customers' => $customers
        ])->layout('layouts.app');
    }

    public function openModal()
    {
        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    public function openDeliveryModal()
    {
        $this->selectedCampaignId = null;
        $this->selectedCustomerId = null;
        $this->isDeliveryModalOpen = true;
    }

    public function closeDeliveryModal()
    {
        $this->isDeliveryModalOpen = false;
        $this->selectedCampaignId = null;
        $this->selectedCustomerId = null;
    }

    private function resetForm()
    {
        $this->campaignId = null;
        $this->name = '';
        $this->description = '';
        $this->start_date = '';
        $this->end_date = '';
        $this->gift_quantity = '';
        $this->assignment_type = '';
        $this->status = 'activo';
    }

    public function edit($id)
    {
        $this->ensureTenantConnection();
        $campaign = Campaign::findOrFail($id);
        $this->campaignId = $campaign->id;
        $this->name = $campaign->name;
        $this->description = $campaign->description;
        $this->start_date = $campaign->start_date->format('Y-m-d');
        $this->end_date = $campaign->end_date->format('Y-m-d');
        $this->gift_quantity = $campaign->gift_quantity;
        $this->assignment_type = $campaign->assignment_type;
        $this->status = $campaign->status;

        $this->isModalOpen = true;
    }

    public function save()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'name' => 'required|min:3',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'gift_quantity' => 'required|numeric|min:1',
            'assignment_type' => 'required',
        ]);

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'gift_quantity' => $this->gift_quantity,
            'assignment_type' => $this->assignment_type,
            'status' => $this->status ?? 'activo',
        ];

        if ($this->campaignId) {
            Campaign::find($this->campaignId)->update($data);
            $msg = 'Campaña actualizada exitosamente.';
        } else {
            Campaign::create($data);
            $msg = 'Campaña creada exitosamente.';
        }

        session()->flash('success', $msg);
        $this->closeModal();
    }

    public function deliverGift(\App\Services\Tenant\Campaigns\CampaignService $campaignService)
    {
        $this->ensureTenantConnection();
        
        $this->validate([
            'selectedCampaignId' => 'required|exists:tenant.cmp_campaigns,id',
            'selectedCustomerId' => 'required|exists:tenant.vnt_companies,id',
        ], [
            'selectedCampaignId.required' => 'Debe seleccionar una campaña.',
            'selectedCustomerId.required' => 'Debe seleccionar un cliente.',
        ]);

        $campaign = Campaign::find($this->selectedCampaignId);
        $customer = \App\Models\Tenant\Customer\VntCompany::find($this->selectedCustomerId);

        // Validar elegibilidad usando el servicio
        if (!$campaignService->isEligible($customer, $campaign)) {
            $this->addError('selectedCustomerId', 'El cliente no es elegible o ya recibió un regalo en esta campaña.');
            return;
        }

        // Registrar entrega
        $success = $campaignService->registerGiftDelivery($customer, $campaign);

        if ($success) {
            session()->flash('success', 'Regalo entregado y registrado exitosamente.');
            $this->closeDeliveryModal();
        } else {
            $this->addError('selectedCampaignId', 'No se pudo registrar la entrega. Verifique el stock de la campaña.');
        }
    }
}
