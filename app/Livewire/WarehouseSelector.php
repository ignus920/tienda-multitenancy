<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Central\VntWarehouse;
use App\Models\Central\VntContact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WarehouseSelector extends Component
{
    public $showModal = false;
    public $warehouses = [];
    public $currentWarehouseId = null;
    public $loading = false;

    protected $listeners = ['openWarehouseSelector' => 'open'];

    public function mount()
    {
        $this->loadWarehouses();
    }

    public function open()
    {
        $this->loadWarehouses();
        $this->showModal = true;
    }

    public function close()
    {
        $this->showModal = false;
    }

    public function loadWarehouses()
    {
        try {
            $user = Auth::user();
            
            if (!$user || !$user->contact_id) {
                Log::warning('Usuario sin contacto asociado', ['user_id' => $user?->id]);
                $this->warehouses = [];
                $this->currentWarehouseId = null;
                return;
            }

            $contact = VntContact::on('central')
                ->with('warehouse.company')
                ->find($user->contact_id);

            if (!$contact || !$contact->warehouse) {
                Log::warning('Contacto sin sucursal asociada', ['contact_id' => $user->contact_id]);
                $this->warehouses = [];
                $this->currentWarehouseId = null;
                return;
            }

            $this->currentWarehouseId = $contact->warehouseId;
            $companyId = $contact->warehouse->companyId;

            // Obtener todas las sucursales de la empresa
            $this->warehouses = VntWarehouse::on('central')
                ->where('companyId', $companyId)
                ->where('status', true)
                ->orderBy('name')
                ->get()
                ->toArray();

        } catch (\Exception $e) {
            Log::error('Error al cargar sucursales', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            $this->warehouses = [];
            $this->currentWarehouseId = null;
        }
    }

    public function selectWarehouse($warehouseId)
    {
        $this->loading = true;

        try {
            $user = Auth::user();

            if (!$user || !$user->contact_id) {
                $this->dispatch('warehouse-error', [
                    'title' => 'Error',
                    'message' => 'No se encontró un contacto asociado a tu usuario.',
                    'icon' => 'error'
                ]);
                $this->loading = false;
                return;
            }

            // Validar que la sucursal existe y pertenece a la empresa del usuario
            $contact = VntContact::on('central')->find($user->contact_id);
            
            if (!$contact || !$contact->warehouse) {
                $this->dispatch('warehouse-error', [
                    'title' => 'Error',
                    'message' => 'No se pudo verificar tu sucursal actual.',
                    'icon' => 'error'
                ]);
                $this->loading = false;
                return;
            }

            $warehouse = VntWarehouse::on('central')
                ->where('id', $warehouseId)
                ->where('companyId', $contact->warehouse->companyId)
                ->where('status', true)
                ->first();

            if (!$warehouse) {
                $this->dispatch('warehouse-error', [
                    'title' => 'Error',
                    'message' => 'La sucursal seleccionada no es válida.',
                    'icon' => 'error'
                ]);
                $this->loading = false;
                return;
            }

            // Actualizar el warehouseId en vnt_contacts
            $contact->warehouseId = $warehouseId;
            $contact->save();

            $this->currentWarehouseId = $warehouseId;

            Log::info('Sucursal actualizada', [
                'user_id' => $user->id,
                'contact_id' => $contact->id,
                'warehouse_id' => $warehouseId,
                'warehouse_name' => $warehouse->name
            ]);

            $this->dispatch('warehouse-updated', [
                'title' => '¡Sucursal actualizada!',
                'message' => "Ahora estás operando en: {$warehouse->name}",
                'icon' => 'success'
            ]);

            $this->loading = false;
            $this->showModal = false;

        } catch (\Exception $e) {
            Log::error('Error al actualizar sucursal', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'warehouse_id' => $warehouseId
            ]);

            $this->dispatch('warehouse-error', [
                'title' => 'Error',
                'message' => 'No se pudo actualizar la sucursal. Por favor, intenta nuevamente.',
                'icon' => 'error'
            ]);

            $this->loading = false;
        }
    }

    public function render()
    {
        return view('livewire.warehouse-selector');
    }
}
