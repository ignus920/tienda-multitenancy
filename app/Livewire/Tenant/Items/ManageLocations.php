<?php

namespace App\Livewire\Tenant\Items;

use Livewire\Component;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvItemsLocations;
use App\Models\Tenant\Items\InvStore;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Log;

class ManageLocations extends Component
{
    public $itemId;
    public $itemName;
    public $locationName;
    public $editingLocationId = null;
    
    // Propiedades para crear nueva ubicación
    public $showCreateForm = false;
    public $newLocationName;
    public $selectedStoreId;

    protected $listeners = ['refreshLocations' => '$refresh'];

    public function mount($itemId)
    {
        $this->itemId = $itemId;
        $this->ensureTenantConnection();
        $this->loadItem();
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
    private function loadItem()
    {
        $item = Items::find($this->itemId);
        $this->itemName = $item ? $item->name : 'Item no encontrado';
    }

    public function getLocationsProperty()
    {
        $this->ensureTenantConnection();
        
        return InvItemsLocations::with('store')
            ->where('itemId', $this->itemId)
            ->get();
    }

    public function getStoresProperty()
    {
        $this->ensureTenantConnection();
        
        // Obtener todas las tiendas activas
        return InvStore::where('status', 1)
            ->orderBy('name')
            ->get();
    }

    public function toggleCreateForm()
    {
        $this->showCreateForm = !$this->showCreateForm;
        if (!$this->showCreateForm) {
            $this->resetCreateForm();
        }
    }

    public function createLocation()
    {
        $this->validate([
            'newLocationName' => 'required|string|max:255',
            'selectedStoreId' => 'required',
        ], [
            'newLocationName.required' => 'El nombre de la ubicación es obligatorio',
            'newLocationName.max' => 'El nombre no puede exceder 255 caracteres',
            'selectedStoreId.required' => 'Debe seleccionar una tienda',
        ]);

        try {
            $this->ensureTenantConnection();
            
            // Validar manualmente que la tienda existe
            $store = InvStore::find($this->selectedStoreId);
            if (!$store) {
                session()->flash('location_error', 'La tienda seleccionada no es válida.');
                return;
            }
            
            InvItemsLocations::create([
                'itemId' => $this->itemId,
                'storeId' => $this->selectedStoreId,
                'locationId' => $this->newLocationName,
                'stock_item_location' => 0,
            ]);

            session()->flash('location_message', 'Ubicación creada correctamente.');
            $this->resetCreateForm();
            $this->showCreateForm = false;
        } catch (\Exception $e) {
            Log::error('Error al crear ubicación: ' . $e->getMessage());
            session()->flash('location_error', 'Error al crear la ubicación.');
        }
    }

    public function updateLocation($locationId)
    {
        $this->validate([
            'locationName' => 'required|string|max:255',
        ], [
            'locationName.required' => 'El nombre de la ubicación es obligatorio',
            'locationName.max' => 'El nombre no puede exceder 255 caracteres',
        ]);

        try {
            $this->ensureTenantConnection();
            
            $location = InvItemsLocations::find($locationId);
            
            if ($location) {
                $location->update([
                    'locationId' => $this->locationName
                ]);

                session()->flash('location_message', 'Ubicación actualizada correctamente.');
                $this->resetForm();
            }
        } catch (\Exception $e) {
            Log::error('Error al actualizar ubicación: ' . $e->getMessage());
            session()->flash('location_error', 'Error al actualizar la ubicación.');
        }
    }

    public function editLocation($locationId)
    {
        $this->ensureTenantConnection();
        $location = InvItemsLocations::find($locationId);
        
        if ($location) {
            $this->editingLocationId = $locationId;
            $this->locationName = $location->locationId;
        }
    }

    public function cancelEdit()
    {
        $this->resetForm();
    }

    public function deleteLocation($locationId)
    {
        try {
            $this->ensureTenantConnection();
            
            $location = InvItemsLocations::find($locationId);
            
            if ($location) {
                $location->delete();
                session()->flash('location_message', 'Ubicación eliminada correctamente.');
            }
        } catch (\Exception $e) {
            Log::error('Error al eliminar ubicación: ' . $e->getMessage());
            session()->flash('location_error', 'Error al eliminar la ubicación.');
        }
    }

    private function resetForm()
    {
        $this->locationName = '';
        $this->editingLocationId = null;
        $this->resetErrorBag();
    }

    private function resetCreateForm()
    {
        $this->newLocationName = '';
        $this->selectedStoreId = null;
        $this->resetErrorBag();
    }

    public function closeModal()
    {
        $this->dispatch('closeLocationsModal');
    }

    public function render()
    {
        return view('livewire.tenant.items.manage-locations');
    }

    
}
