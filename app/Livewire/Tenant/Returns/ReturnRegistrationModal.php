<?php

namespace App\Livewire\Tenant\Returns;

use App\Models\Tenant\Remissions\InvRemissions;
use App\Models\Tenant\Sales\VntReturn;
use App\Models\Tenant\Sales\VntReturnEvidence;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\DB;

class ReturnRegistrationModal extends Component
{
    use WithFileUploads;
    public $isOpen = false;
    public $remissionId;
    protected $remission;
    public $items = []; // Estructura: [ ['detail_id' => X, 'item_id' => Y, 'description' => Z, 'qty' => Q, 'return_qty' => 0, 'observation' => ''] ]

    protected $listeners = ['openReturnRegistration' => 'loadRemission'];

    public function boot()
    {
        \Log::info("🔌 Booting ReturnRegistrationModal", ['tenant_id' => session('tenant_id')]);
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
        tenancy()->initialize($tenant);
    }

    public function loadRemission($id)
    {
        $this->ensureTenantConnection();
        \Log::info("🔄 Cargando remisión para devolución", ['id' => $id]);
        
        $this->remissionId = $id;
        $this->remission = InvRemissions::with('details.item')->find($id);
        
        if (!$this->remission) {
            \Log::error("❌ Remisión no encontrada", ['id' => $id]);
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Remisión no encontrada']);
            return;
        }

        \Log::info("✅ Remisión encontrada", ['consecutive' => $this->remission->consecutive, 'details_count' => $this->remission->details->count()]);

        $this->items = [];
        foreach ($this->remission->details as $detail) {
            // Calcular cuánto se ha devuelto ya de este ítem
            $returnedQty = VntReturn::where('remission_id', $id)
                ->where('item_id', $detail->itemId)
                ->where('status', '<>', 5) // No anuladas
                ->sum('commercial_qty');

            $this->items[] = [
                'detail_id' => $detail->id,
                'item_id' => $detail->itemId,
                'description' => $detail->item->name ?? $detail->description ?? 'Producto sin nombre',
                'codigo' => $detail->item->internal_code ?? 'N/A',
                'original_qty' => $detail->quantity,
                'previously_returned' => $returnedQty,
                'available_qty' => $detail->quantity - $returnedQty,
                'return_qty' => 0,
                'observation' => ''
            ];
        }

        \Log::info("📦 Items cargados para el modal", ['count' => count($this->items)]);
        $this->isOpen = true;
    }

    public function close()
    {
        $this->isOpen = false;
        $this->remission = null;
        $this->reset(['items', 'remissionId']);
    }

    public function totalReturnWithMotive($motive)
    {
        if (empty($motive)) {
            $this->dispatch('show-toast', type: 'error', message: 'El motivo es obligatorio para devolución total.');
            return;
        }

        try {
            DB::connection('tenant')->beginTransaction();

            foreach ($this->items as $item) {
                if ($item['available_qty'] > 0) {
                    VntReturn::create([
                        'remission_id' => $this->remissionId,
                        'item_id' => $item['item_id'],
                        'user_id' => Auth::id(),
                        'requested_at' => now(),
                        'original_qty' => $item['original_qty'],
                        'commercial_qty' => $item['available_qty'],
                        'status' => 1, // Comercial
                        'obs_commercial' => $motive,
                    ]);
                }
            }

            DB::connection('tenant')->commit();

            $this->dispatch('show-toast', type: 'success', message: 'Devolución total registrada correctamente.');
            $this->isOpen = false;
            $this->dispatch('refreshReturns');

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function save()
    {
        $toSave = array_filter($this->items, function($item) {
            return $item['return_qty'] > 0;
        });

        if (empty($toSave)) {
            $this->dispatch('show-toast', ['type' => 'warning', 'message' => 'Debe ingresar al menos una cantidad a devolver.']);
            return;
        }

        foreach ($toSave as $item) {
            if ($item['return_qty'] > $item['available_qty']) {
                $this->dispatch('show-toast', ['type' => 'error', 'message' => "La cantidad a devolver de {$item['description']} excede lo disponible."]);
                return;
            }
        }

        try {
            DB::connection('tenant')->beginTransaction();

            // Crear las devoluciones
            foreach ($toSave as $item) {
                VntReturn::create([
                    'remission_id' => $this->remissionId,
                    'item_id' => $item['item_id'],
                    'user_id' => Auth::id(),
                    'requested_at' => now(),
                    'original_qty' => $item['original_qty'],
                    'commercial_qty' => $item['return_qty'],
                    'status' => 1, // Comercial
                    'obs_commercial' => $item['observation'] ?: 'Devolución parcial iniciada.',
                ]);
            }

            DB::connection('tenant')->commit();

            $this->dispatch('show-toast', type: 'success', message: 'Devolución registrada correctamente.');
            $this->isOpen = false;
            $this->dispatch('refreshReturns'); // Por si está abierta la lista de devoluciones

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error al guardar: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        if ($this->remissionId && !$this->remission) {
            $this->ensureTenantConnection();
            $this->remission = InvRemissions::with(['details.item', 'quote'])->find($this->remissionId);
        }

        return view('livewire.tenant.returns.return-registration-modal', [
            'remission' => $this->remission
        ]);
    }
}
