<?php

namespace App\Livewire\Tenant\Components;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Tenant\Tickets\TickDepartment;
use App\Models\Tenant\Tickets\TickRequest;
use App\Models\Tenant\Tickets\TickStatus;
use App\Models\Tenant\Tickets\TickRequestHistory;
use App\Models\Auth\User;
use App\Models\Auth\Tenant;
use App\Models\Tenant\Items\Items;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TicketRequestModal extends Component
{
    use WithPagination;

    // Propiedades del Modal
    public $isOpen = false;
    public $productId = null;
    public $productName = null;
    public $productCode = null;
    public $title = 'Nueva Solicitud';
    public $selectedRequestId = null; // Guardamos solo el ID para evitar errores de hidratación multitenant

    // Campos del Formulario
    public $department_id;
    public $detail;
    
    // Filtros del Historial
    public $dateFrom;
    public $dateTo;
    public $search = '';
    public $perPage = 5;

    // Estado del módulo
    public $isModuleActive = false;

    public function mount($productId = null)
    {
        $this->productId = $productId;
        $this->dateFrom = now()->subMonths(3)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        
        // Ejecutar check solo en el mount inicial si no está en sesión
        $this->checkModuleStatus();
    }

    public function boot()
    {
        // En boot solo aseguramos la conexión si es necesario
        // Pero intentamos ser lo más ligeros posible
        if (!tenancy()->initialized) {
            $this->ensureTenantConnection();
        }
    }

    private function checkModuleStatus()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) {
            $this->isModuleActive = false;
            return;
        }

        // Usar caché de sesión para evitar consultas redundantes a la BD central en cada request
        $cacheKey = 'module_marketing_active_v2_' . $tenantId;
        if (session()->has($cacheKey)) {
            $this->isModuleActive = session($cacheKey);
            return;
        }

        try {
            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                $this->isModuleActive = false;
            } else {
                // 1. Verificar en base de datos central (Oficial)
                $this->isModuleActive = DB::connection('mysql')->table('vnt_merchant_moduls')
                    ->join('vnt_moduls', 'vnt_merchant_moduls.modulId', '=', 'vnt_moduls.id')
                    ->where('vnt_merchant_moduls.merchantId', $tenant->merchant_type_id)
                    ->where('vnt_moduls.migration', 'marketing')
                    ->where('vnt_moduls.status', 1)
                    ->exists();

                // 2. Fallback: Si no está en central, verificar si las tablas existen en el tenant
                // Esto permite que funcione si el usuario corrió las migraciones manualmente
                if (!$this->isModuleActive) {
                    $this->ensureTenantConnection();
                    $this->isModuleActive = \Illuminate\Support\Facades\Schema::connection('tenant')->hasTable('tick_departments');
                }
            }
            
            // Guardar en sesión por 10 minutos (opcionalmente podrías usar cache() de Laravel)
            session([$cacheKey => $this->isModuleActive]);

        } catch (\Exception $e) {
            Log::error("Error verificando estatus de módulo mercadeo: " . $e->getMessage());
            $this->isModuleActive = false;
        }
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) return;

        // Intentar obtener el tenant de la sesión si es posible para evitar otra consulta
        $tenant = Tenant::find($tenantId);
        if (!$tenant) return;

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        
        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }
    }

    #[On('openTicketModal')]
    public function open($productId = null, $title = null)
    {
        if (!$this->isModuleActive) return;

        $this->productId = $productId;
        $this->productName = null;
        $this->productCode = null;
        
        if ($productId) {
            $this->ensureTenantConnection();
            $product = Items::find($productId);
            if ($product) {
                $this->productName = $product->name;
                $this->productCode = $product->internal_code;
            }
        }

        if ($title) $this->title = $title;
        
        $this->resetValidation();
        $this->reset(['department_id', 'detail', 'selectedRequestId']);
        $this->isOpen = true;
    }

    public function close()
    {
        $this->isOpen = false;
    }

    #[On('viewTicket')]
    public function view($id)
    {
        if (!$this->isModuleActive) return;

        $this->ensureTenantConnection();
        $this->selectedRequestId = $id;
        $this->productCode = null;
        
        $request = TickRequest::with('product')->find($id);
        if ($request) {
            $this->title = 'Detalle de Solicitud #' . $id;
            $this->productName = $request->product->name ?? null;
            $this->productCode = $request->product->internal_code ?? null;
        }

        $this->resetValidation();
        $this->isOpen = true;
    }

    public function updateStatus($statusName)
    {
        if (!$this->isModuleActive) return;

        $this->ensureTenantConnection();
        if (!$this->selectedRequestId) return;

        try {
            DB::connection('tenant')->beginTransaction();

            $status = TickStatus::where('name', $statusName)->first();
            
            if (!$status) {
                throw new \Exception("El estado '{$statusName}' no está configurado en el sistema.");
            }

            $request = TickRequest::find($this->selectedRequestId);
            $oldStatusId = $request->status_id;
            $request->update(['status_id' => $status->id]);

            // Usar el detalle del editor como mensaje, o un mensaje por defecto si está vacío
            $historyMessage = !empty(strip_tags($this->detail)) 
                ? $this->detail 
                : 'Estado cambiado a: ' . $status->name;

            TickRequestHistory::create([
                'request_id' => $request->id,
                'from_status_id' => $oldStatusId,
                'to_status_id' => $status->id,
                'user_id' => auth()->id(),
                'message' => $historyMessage,
            ]);

            DB::connection('tenant')->commit();

            $this->reset(['detail']); // Limpiar editor para el siguiente comentario
            
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Estado actualizado a ' . $status->name
            ]);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function save()
    {
        if (!$this->isModuleActive) return;

        $this->ensureTenantConnection();
        $this->validate([
            'department_id' => [
                'required',
                Rule::exists('tenant.tick_departments', 'id')
            ],
            'detail' => 'required|min:10',
        ]);

        try {
            DB::connection('tenant')->beginTransaction();

            // Buscar estado inicial (asumiendo que ID 1 es 'Abierto' o similar)
            $initialStatus = TickStatus::where('name', 'like', '%Abierto%')
                ->orWhere('id', 1)
                ->first();

            $request = TickRequest::create([
                'department_id' => $this->department_id,
                'status_id' => $initialStatus->id ?? 1,
                'product_id' => $this->productId,
                'created_by' => auth()->id(),
                'detail' => $this->detail,
            ]);

            // Crear entrada en el historial
            TickRequestHistory::create([
                'request_id' => $request->id,
                'from_status_id' => null,
                'to_status_id' => $request->status_id,
                'user_id' => auth()->id(),
                'message' => $this->detail,
            ]);

            DB::connection('tenant')->commit();

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Solicitud enviada correctamente'
            ]);

            $this->reset(['department_id', 'detail']);
            // No cerramos el modal para que vea la actualización en el listado inferior
            // o lo cerramos si el usuario lo prefiere. Por ahora, lo mantenemos abierto.
            
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        $this->ensureTenantConnection();

        $departments = collect();
        $requests = collect();
        $selectedRequest = null;

        if ($this->isModuleActive) {
            try {
                $departments = TickDepartment::where('status', 1)->orderBy('name')->get();

                if ($this->selectedRequestId) {
                    $selectedRequest = TickRequest::with(['status', 'department', 'creator', 'history.status', 'history.user'])
                        ->find($this->selectedRequestId);
                }

                $requests = TickRequest::with(['status', 'department', 'creator'])
                    ->when($this->productId, function($q) {
                        return $q->where('product_id', $this->productId);
                    })
                    ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
                    ->where(function($q) {
                        $q->where('detail', 'like', '%' . $this->search . '%')
                        ->orWhere('id', 'like', '%' . $this->search . '%');
                    })
                    ->orderBy('id', 'desc')
                    ->paginate($this->perPage);
            } catch (\Exception $e) {
                Log::error("Error consultando tablas de tickets: " . $e->getMessage());
                $this->isModuleActive = false; // Desactivar si hay error de tabla inexistente
            }
        }

        return view('livewire.tenant.components.ticket-request-modal', [
            'departments' => $departments,
            'requests' => $requests,
            'selectedRequest' => $selectedRequest,
            'isModuleActive' => $this->isModuleActive
        ]);
    }
}
