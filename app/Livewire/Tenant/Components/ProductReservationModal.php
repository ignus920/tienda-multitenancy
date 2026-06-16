<?php

namespace App\Livewire\Tenant\Components;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\Reservation;
use App\Models\Tenant\Items\ReservationStatus;
use App\Models\Tenant\Customer\VntCompany;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductReservationModal extends Component
{
    use WithPagination;

    // Propiedades de control del Modal
    public $isOpen = false;
    public $productId = null;
    public $productName = null;
    public $productCode = null;

    // Campos del Formulario de Reserva
    public $stock_type = ''; // 1: En stock, 2: En tránsito
    public $advance_payment = ''; // 6: Sin anticipo, 11: 50%, 17: Mayor al 50%
    public $quantity;
    public $due_date;
    public $customer_id;
    public $description;

    // Buscador de Clientes
    public $customerSearch = '';
    public $selectedCustomerName = '';

    // Edición de Estados
    public $showEditStatusModal = false;
    public $editingReservationId = null;
    public $newStatusId = '';
    public $statusObservations = '';

    protected $listeners = ['openReservationModal' => 'open'];

    public function boot()
    {
        if (!tenancy()->initialized) {
            $this->ensureTenantConnection();
        }
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

    #[On('openReservationModal')]
    public function open($productId = null)
    {
        $this->productId = $productId;
        $this->productName = null;
        $this->productCode = null;
        
        if ($productId) {
            $this->ensureTenantConnection();
            $product = Items::find($productId);
            if ($product) {
                $this->productName = $product->name;
                $this->productCode = $product->internal_code ?? $product->sku;
            }
        }

        $this->resetValidation();
        $this->reset([
            'stock_type', 'advance_payment', 'quantity', 'due_date', 
            'customer_id', 'description', 'customerSearch', 'selectedCustomerName',
            'showEditStatusModal', 'editingReservationId', 'newStatusId', 'statusObservations'
        ]);
        
        // Poner fecha de vencimiento por defecto a 10 días
        $this->due_date = now()->addDays(10)->format('Y-m-d');
        $this->isOpen = true;
    }

    public function updatedStockType()
    {
        $this->calculateDueDate();
    }

    public function updatedAdvancePayment()
    {
        $this->calculateDueDate();
    }

    private function calculateDueDate()
    {
        if ($this->stock_type == '1') {
            // En stock: calcular fecha según anticipo
            if ($this->advance_payment == '6') {
                $this->due_date = now()->addDays(6)->format('Y-m-d');
            } elseif ($this->advance_payment == '11') {
                $this->due_date = now()->addDays(11)->format('Y-m-d');
            } elseif ($this->advance_payment == '17') {
                $this->due_date = now()->addDays(17)->format('Y-m-d');
            } else {
                $this->due_date = now()->addDays(10)->format('Y-m-d');
            }
        }
    }

    public function close()
    {
        $this->isOpen = false;
    }

    // Seleccionar cliente desde el buscador
    public function selectCustomer($id, $name)
    {
        $this->customer_id = $id;
        $this->selectedCustomerName = $name;
        $this->customerSearch = '';
    }

    public function clearCustomer()
    {
        $this->customer_id = null;
        $this->selectedCustomerName = '';
    }

    public function save()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'stock_type' => 'required',
            'advance_payment' => 'required',
            'quantity' => 'required|integer|min:1',
            'due_date' => 'required|date|after_or_equal:today',
            'customer_id' => 'required|exists:tenant.vnt_companies,id',
            'description' => 'required|string|min:3',
        ]);

        // Validar cantidades contra existencias físicas o tránsito
        if ($this->stock_type == '1') {
            // En Stock
            $alreadyReserved = Reservation::where('item_id', $this->productId)
                ->where('stock_type', '1')
                ->where('status_id', 1) // Solo reservas activas/registradas
                ->sum('quantity');

            $stockDisponible = (int) DB::connection('tenant')
                ->table('inv_items_store')
                ->where('itemId', $this->productId)
                ->sum('stock_items_store');

            if (($alreadyReserved + $this->quantity) > $stockDisponible) {
                $this->addError('quantity', "La cantidad reservada supera el saldo disponible (en stock: {$stockDisponible}, ya reservado: {$alreadyReserved})");
                return;
            }
        } elseif ($this->stock_type == '2') {
            // En Tránsito
            $alreadyReserved = Reservation::where('item_id', $this->productId)
                ->where('stock_type', '2')
                ->where('status_id', 1) // Solo reservas activas/registradas
                ->sum('quantity');

            $transitoDisponible = (int) DB::connection('tenant')
                ->table('imp_imports')
                ->where('item_id', $this->productId)
                ->sum('qty_requested');

            if (($alreadyReserved + $this->quantity) > $transitoDisponible) {
                $this->addError('quantity', "La cantidad reservada supera el saldo disponible en tránsito (en tránsito: {$transitoDisponible}, ya reservado: {$alreadyReserved})");
                return;
            }
        }

        try {
            Reservation::create([
                'quantity' => $this->quantity,
                'customer_id' => $this->customer_id,
                'item_id' => $this->productId,
                'due_date' => $this->due_date,
                'advance_payment' => $this->advance_payment,
                'status_id' => 1, // 1: Registrado
                'description' => $this->description,
                'user_id' => auth()->id() ?? 1,
                'stock_type' => $this->stock_type,
            ]);

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Reserva creada exitosamente.'
            ]);

            $this->reset(['stock_type', 'advance_payment', 'quantity', 'customer_id', 'selectedCustomerName', 'description']);
            $this->due_date = now()->addDays(10)->format('Y-m-d');

        } catch (\Exception $e) {
            Log::error("Error guardando reserva: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }

    // Modal de cambio de estado
    public function openEditStatus($reservationId)
    {
        $this->ensureTenantConnection();
        $res = Reservation::find($reservationId);
        if ($res) {
            $this->editingReservationId = $reservationId;
            $this->newStatusId = $res->status_id;
            $this->statusObservations = $res->obs ?? '';
            $this->showEditStatusModal = true;
        }
    }

    public function closeEditStatus()
    {
        $this->showEditStatusModal = false;
        $this->reset(['editingReservationId', 'newStatusId', 'statusObservations']);
    }

    public function updateStatus()
    {
        $this->ensureTenantConnection();
        
        $rules = [
            'newStatusId' => 'required|exists:tenant.inv_reservation_statuses,id',
        ];

        if ($this->newStatusId == 2) {
            $rules['statusObservations'] = 'required|string|min:3';
        } elseif ($this->newStatusId == 4) {
            $rules['statusObservations'] = 'required|string|min:5';
        } else {
            $rules['statusObservations'] = 'nullable|string';
        }

        $this->validate($rules, [
            'statusObservations.required' => $this->newStatusId == 2 ? 'Debe registrar la OP correspondiente.' : 'Debe justificar la anulación.',
        ]);

        try {
            $res = Reservation::find($this->editingReservationId);
            if ($res) {
                $res->update([
                    'status_id' => $this->newStatusId,
                    'obs' => $this->statusObservations
                ]);

                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => 'Estado de la reserva actualizado.'
                ]);
            }
            $this->closeEditStatus();
        } catch (\Exception $e) {
            Log::error("Error actualizando estado de reserva: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        $this->ensureTenantConnection();
        
        $customers = [];
        if (strlen($this->customerSearch) > 0) {
            $customers = VntCompany::where('businessName', 'like', '%' . $this->customerSearch . '%')
                ->orWhere('identification', 'like', '%' . $this->customerSearch . '%')
                ->limit(15)
                ->get();
        } else {
            $customers = VntCompany::orderBy('businessName', 'asc')
                ->limit(15)
                ->get();
        }

        $statuses = ReservationStatus::all();

        $reservations = collect();
        if ($this->productId) {
            $reservations = Reservation::with(['customer', 'status', 'user'])
                ->where('item_id', $this->productId)
                ->where('due_date', '>=', now()->subDays(15)->format('Y-m-d'))
                ->orderBy('status_id', 'asc')
                ->orderBy('created_at', 'desc')
                ->paginate(5);
        }

        return view('livewire.tenant.components.product-reservation-modal', [
            'customers' => $customers,
            'statuses' => $statuses,
            'reservations' => $reservations
        ]);
    }
}
