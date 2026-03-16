<?php

namespace App\Livewire\Tenant\Components;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Items\InvItemsCutDetails as CutDetail;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvItemsDimensions;
use App\Models\Tenant\Customer\VntContacts as Customer;
use App\Models\Tenant\Remissions\InvRemissions;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Livewire\Attributes\On;

class InvItemsCutDetails extends Component
{
    use WithPagination;

    public $isOpen = false;
    public $isNew = false; // Estado para mostrar formulario
    
    // Propiedades del Formulario
    public $search = '';
    public $perPage = 10;
    public $selectedItemId;
    public $customerId;
    public $remissionId;
    public $repeats = 1;
    public $profileLength = 3000; // Por defecto 3000mm
    public $observations;
    public $justification;
    public $currentCutId = null; // ID de grupo para múltiples cortes seguidos
    
    // Búsqueda en Selectores Premium
    public $itemSearch = '';
    public $customerSearch = '';
    public $remissionSearch = '';
    
    // Grupo de corte seleccionado en la vista principal
    public $selectedCutGroupId = null;
    
    // Gestión de Cortes (mm)
    public $cuts = []; // Array de mm
    public $kerf = 5; // Merma de 5mm
    
    // Totales calculados
    public $accumulated = 0;
    public $remaining = 0;
    public $accumulatedCm = 0;
    public $remainingCm = 0;

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) return;

        $tenant = Tenant::find($tenantId);
        if (!$tenant) return;

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        
        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[On('openCutDetails')]
    public function openModal()
    {
        $this->ensureTenantConnection();
        $this->resetForm();
        $this->isOpen = true;
        $this->isNew = false;
    }

    public function createNew()
    {
        $this->isNew = true;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->selectedItemId = null;
        $this->customerId = null;
        $this->remissionId = null;
        $this->repeats = 1;
        $this->profileLength = 3000;
        $this->observations = '';
        $this->justification = '';
        $this->cuts = [];
        $this->currentCutId = null;
        $this->calculateTotals();
    }

    public function updatedSelectedItemId($value)
    {
        if ($value) {
            $dimensions = InvItemsDimensions::on('tenant')->where('item_id', $value)->first();
            if ($dimensions && $dimensions->long > 0) {
                $this->profileLength = $dimensions->long * 10; // Convertir cm a mm
            } else {
                $this->profileLength = 3000; // Valor por defecto
            }
        }
        $this->calculateTotals();
    }

    public function addCut()
    {
        $this->cuts[] = '';
        $this->calculateTotals();
    }

    public function removeCut($index)
    {
        unset($this->cuts[$index]);
        $this->cuts = array_values($this->cuts);
        $this->calculateTotals();
    }

    public function updatedCuts()
    {
        $this->calculateTotals();
    }

    public function updatedRemissionId($value)
    {
        if ($value) {
            $this->justification = '';
        }
    }

    public function calculateTotals()
    {
        $sumCuts = 0;
        $totalKerf = 0;
        $activeCutsCount = 0;

        foreach ($this->cuts as $cut) {
            $val = floatval($cut);
            if ($val > 0) {
                $sumCuts += $val;
                $activeCutsCount++;
            }
        }

        // Cada corte (segmento) implica un corte físico, sumamos kerf por cada uno
        $totalKerf = $activeCutsCount * $this->kerf;
        
        $this->accumulated = $sumCuts + $totalKerf;
        $this->remaining = $this->profileLength - $this->accumulated;
        
        $this->accumulatedCm = $this->accumulated / 10;
        $this->remainingCm = $this->remaining / 10;
    }

    public function save()
    {
        $this->ensureTenantConnection();
        
        \Log::info('Tentando guardar Plan de Corte', [
            'selectedItemId' => $this->selectedItemId,
            'customerId' => $this->customerId,
            'remissionId' => $this->remissionId,
            'profileLength' => $this->profileLength,
            'num_cortes' => count($this->cuts),
            'accumulated' => $this->accumulated
        ]);

        try {
            $this->validate([
                'selectedItemId' => 'required',
                'customerId' => 'required',
                'repeats' => 'required|integer|min:1',
                'profileLength' => 'required',
                'cuts' => 'required|array|min:1',
                'justification' => $this->remissionId ? 'nullable' : 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de Validación al guardar Plan de Corte', [
                'errors' => $e->validator->errors()->toArray()
            ]);
            
            $this->dispatch('swal', [
                'icon' => 'error',
                'text' => 'Por favor complete la justificación o seleccione una remisión.'
            ]);
            return;
        }

        if ($this->accumulated > $this->profileLength) {
            \Log::warning('Intento de guardar Plan de Corte fallido: Acumulado supera largo', [
                'accumulated' => $this->accumulated,
                'profileLength' => $this->profileLength
            ]);
            $this->dispatch('swal', [
                'icon' => 'error',
                'text' => 'El acumulado no puede superar el largo del perfil.'
            ]);
            return;
        }

        try {
            // Generar cut_id agrupador o mantener el de la sesión actual
            if (!$this->currentCutId) {
                $lastCutId = CutDetail::on('tenant')->max('cut_id') ?? 0;
                $this->currentCutId = $lastCutId + 1;
            }
            $newCutId = $this->currentCutId;

            $planCmArr = [];
            $planMmArr = [];

            foreach ($this->cuts as $cutValue) {
                if (floatval($cutValue) <= 0) continue;
                
                $planMmArr[] = $cutValue;
                $planCmArr[] = floatval($cutValue) / 10;
            }

            // Persistir registros
            $newRecord = CutDetail::on('tenant')->create([
                'item_id' => $this->selectedItemId,
                'repeat_in' => $this->repeats,
                'plan_centimeters' => implode(', ', $planCmArr),
                'plan_millimeters' => implode(', ', $planMmArr),
                'production_order_id' => $this->remissionId ?? 0,
                'accumulated' => $this->accumulated,
                'remaining' => $this->remaining,
                'status' => 1,
                'cut_id' => $newCutId,
                'customer_id' => $this->customerId,
                'notes' => $this->observations,
                'created_by' => auth()->user()->name ?? 'System',
                'justification' => $this->justification,
                'accumulated_cm' => $this->accumulatedCm,
                'remaining_cm' => $this->remainingCm,
                'length_cm' => $this->profileLength / 10,
                'length_mm' => $this->profileLength,
            ]);

            \Log::info('Plan de Corte guardado exitosamente', ['id' => $newRecord->id, 'cut_id' => $newCutId]);

            // Solo reseteamos el producto y los cortes. Mantenemos el cliente, remisión y currentCutId para seguir agregando en el mismo grupo.
            $this->reset(['selectedItemId', 'cuts', 'observations', 'justification']);
            $this->cuts = [''];
            
            $this->dispatch('swal', [
                'icon' => 'success',
                'text' => 'Plan de corte guardado correctamente.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error Fatal al guardar Plan de Corte', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('swal', [
                'icon' => 'error',
                'text' => 'Ocurrió un error inesperado al guardar. Revise los logs del servidor.'
            ]);
        }
    }

    public function render()
    {
        $this->ensureTenantConnection();

        $itemsQuery = Items::on('tenant')->active();
        if ($this->itemSearch) {
            $itemsQuery->where(function($q) {
                $q->where('name', 'like', '%' . $this->itemSearch . '%')
                  ->orWhere('internal_code', 'like', '%' . $this->itemSearch . '%');
            });
        }
        $items = $itemsQuery->orderBy('name')->take(15)->get();
        if ($this->selectedItemId && !$items->contains('id', $this->selectedItemId)) {
            $selectedItem = Items::on('tenant')->find($this->selectedItemId);
            if ($selectedItem) $items->push($selectedItem);
        }

        $customersQuery = Customer::on('tenant')->active();
        if ($this->customerSearch) {
            $customersQuery->where(function($q) {
                $q->where('firstName', 'like', '%' . $this->customerSearch . '%')
                  ->orWhere('lastName', 'like', '%' . $this->customerSearch . '%')
                  ->orWhere('id', 'like', '%' . $this->customerSearch . '%');
            });
        }
        $customers = $customersQuery->orderBy('firstName')->take(15)->get();
        if ($this->customerId && !$customers->contains('id', $this->customerId)) {
            $selectedCustomer = Customer::on('tenant')->where('id', $this->customerId)->first();
            if ($selectedCustomer) $customers->push($selectedCustomer);
        }
        
        $remissions = $this->customerId 
            ? InvRemissions::on('tenant')
                ->whereHas('quote', function($q) {
                    $q->where('customerId', $this->customerId);
                })
                ->when($this->remissionSearch, function($q) {
                    $q->where('consecutive', 'like', '%' . $this->remissionSearch . '%');
                })
                ->orderBy('created_at', 'desc')
                ->get()
            : [];

        // Obtener grupos de corte para el selector principal (Resuelve error ONLY_FULL_GROUP_BY de MySQL)
        $cutGroups = CutDetail::on('tenant')
            ->select('cut_id', 'customer_id')
            ->selectRaw('MAX(created_at) as created_at')
            ->with('customer:id,firstName,lastName')
            ->whereNotNull('cut_id')
            ->groupBy('cut_id', 'customer_id')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
            

        $cutDetails = CutDetail::on('tenant')
            ->with(['item', 'customer'])
            ->where('cut_id', $this->selectedCutGroupId) // Forzamos que coincida con el ID seleccionado (será vacío si no hay selección)
            ->where(function($query) {
                $query->whereHas('item', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('internal_code', 'like', '%' . $this->search . '%');
                })
                ->orWhere('plan_centimeters', 'like', '%' . $this->search . '%')
                ->orWhere('created_by', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.tenant.components.inv-items-cut-details', [
            'cutDetails' => $cutDetails,
            'items' => $items,
            'customers' => $customers,
            'remissions' => $remissions,
            'cutGroups' => $cutGroups,
        ]);
    }
}
