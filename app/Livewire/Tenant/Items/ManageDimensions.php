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
    public $scale_1_qty;
    public $scale_1_discount;
    public $scale_2_qty;
    public $scale_2_discount;
    public $box_discount;
    public $min_packing_qty;
    public $min_packing_val;
    public $add_packing_val;
    public $max_packing_qty;
    public $packing_note;
    public $packing_note_max;

    public function mount($itemId)
    {
        $this->itemId = $itemId;
        $this->edit($this->itemId);
        $this->ensureTenantConnection();
    }

    public function saveInfoDimensions()
    {
        $validator = \Illuminate\Support\Facades\Validator::make([
            'high' => $this->high,
            'long' => $this->long,
            'width' => $this->width,
            'weight' => $this->weight,
            'quntityxbox' => $this->quntityxbox,
        ], [
            'high' => 'required|numeric',
            'long' => 'required|numeric',
            'width' => 'required|numeric',
            'weight' => 'required|numeric',
            'quntityxbox' => 'required|numeric',
        ], [
            'required' => 'Por favor complete todos los campos obligatorios de dimensiones (Alto, Largo, Ancho, Peso, Cant. Caja).',
            'numeric' => 'Los campos de dimensiones deben ser valores numéricos.'
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => $validator->errors()->first()]);
            return;
        }

        $this->ensureTenantConnection();
        $infoItem = [
            'item_id' => $this->itemId,
            'high' => $this->high,
            'long' => $this->long,
            'width' => $this->width,
            'voltage' => $this->voltage,
            'power' => $this->power,
            'weight' => $this->weight,
            'quntityxbox' => $this->quntityxbox,
            'scale_1_qty' => $this->scale_1_qty === '' ? null : $this->scale_1_qty,
            'scale_1_discount' => $this->scale_1_discount === '' ? null : $this->scale_1_discount,
            'scale_2_qty' => $this->scale_2_qty === '' ? null : $this->scale_2_qty,
            'scale_2_discount' => $this->scale_2_discount === '' ? null : $this->scale_2_discount,
            'box_discount' => $this->box_discount === '' ? null : $this->box_discount,
            'min_packing_qty' => $this->min_packing_qty ?: 0,
            'min_packing_val' => $this->min_packing_val ?: 0.00,
            'add_packing_val' => $this->add_packing_val ?: 0.00,
            'max_packing_qty' => $this->max_packing_qty ?: 0,
            'packing_note' => $this->packing_note ?: null,
            'packing_note_max' => $this->packing_note_max ?: null
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
            $this->scale_1_qty = $itemDimension->scale_1_qty;
            $this->scale_1_discount = $itemDimension->scale_1_discount;
            $this->scale_2_qty = $itemDimension->scale_2_qty;
            $this->scale_2_discount = $itemDimension->scale_2_discount;
            $this->box_discount = $itemDimension->box_discount;
            $this->min_packing_qty = $itemDimension->min_packing_qty;
            $this->min_packing_val = $itemDimension->min_packing_val;
            $this->add_packing_val = $itemDimension->add_packing_val;
            $this->max_packing_qty = $itemDimension->max_packing_qty;
            $this->packing_note = $itemDimension->packing_note;
            $this->packing_note_max = $itemDimension->packing_note_max;
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
        $this->scale_1_qty = '';
        $this->scale_1_discount = '';
        $this->scale_2_qty = '';
        $this->scale_2_discount = '';
        $this->box_discount = '';
        $this->min_packing_qty = '';
        $this->min_packing_val = '';
        $this->add_packing_val = '';
        $this->max_packing_qty = '';
        $this->packing_note = '';
        $this->packing_note_max = '';
        $this->itemId = '';
        $this->dimensions_id = '';
    }
}
