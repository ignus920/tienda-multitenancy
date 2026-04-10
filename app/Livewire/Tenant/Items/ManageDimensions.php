<?php

namespace App\Livewire\Tenant\Items;

use Livewire\Component;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant\Items\InvItemsDimensions;

class ManageDimensions extends Component
{
    public $itemId;
    public $dimensions_id;
    public $high;
    public $long;
    public $width;
    public $voltage;
    public $power;
    public $weight;
    public $quntityxbox;

    public function mount($itemId)
    {
        $this->itemId = $itemId;
        $this->edit($this->itemId);
        $this->ensureTenantConnection();
    }

    public function saveInfoDimensions()
    {
        $this->ensureTenantConnection();
        $infoItem = [
            'item_id' => $this->itemId,
            'high' => $this->high,
            'long' => $this->long,
            'width' => $this->width,
            'voltage' => $this->voltage,
            'power' => $this->power,
            'weight' => $this->weight,
            'quntityxbox' => $this->quntityxbox
        ];
        //dd($infoItem);
        try {
            if ($this->dimensions_id) {
                $item_dimensions = InvItemsDimensions::findOrFail($this->dimensions_id);
                $item_dimensions->update($infoItem);
                session()->flash('message', 'Registro actualizado exitosamente.');
            } else {
                InvItemsDimensions::create($infoItem);
                session()->flash('message', 'Registro realizado exitosamente.');
            }
            $this->resetForm();
            $this->closeItemsModal();
        } catch (\Exception $e) {
            Log::error("Error al guardar la información: " . $e->getMessage());
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
            return;
        }
    }

    public function closeItemsModal()
    {
        $this->dispatch('closeItemsModal');
    }

    public function edit($itemId = null)
    {
        if (!$itemId) {
            return;
        }

        $this->ensureTenantConnection();
        $itemDimension = InvItemsDimensions::where('item_id', $itemId)->first();

        if ($itemDimension) {
            $this->dimensions_id = $itemDimension->id;
            $this->high = $itemDimension->high;
            $this->long = $itemDimension->long;
            $this->width = $itemDimension->width;
            $this->voltage = $itemDimension->voltage;
            $this->power = $itemDimension->power;
            $this->weight = $itemDimension->weight;
            $this->quntityxbox = $itemDimension->quntityxbox;
        }
    }

    public function render()
    {
        return view('livewire.tenant.items.manage-dimensions');
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

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        tenancy()->initialize($tenant);
    }

    private function resetForm()
    {
        $this->high = '';
        $this->long = '';
        $this->width = '';
        $this->voltage = '';
        $this->power = '';
        $this->weight = '';
        $this->quntityxbox = '';
        $this->itemId = '';
        $this->dimensions_id = '';
    }
}
