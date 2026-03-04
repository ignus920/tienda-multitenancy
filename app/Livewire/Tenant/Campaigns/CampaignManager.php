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

    // Propiedad para el modal de carga/éxito
    public $showModal = false; 

    // Campos del formulario
    public $campaignId, $name, $description, $start_date, $end_date, $gift_quantity, $assignment_type, $status;

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
            ->paginate($this->perPage);

        return view('livewire.tenant.campaigns.campaign-manager', [
            'campaigns' => $campaigns
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
}
