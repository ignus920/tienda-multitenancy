<?php

namespace App\Livewire\Tenant\Components;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
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
    public $supplier_id;
    public $detail;
    
    // Control de Solicitudes a Proveedores
    public $activeTab = 'internal'; // 'internal' o 'supplier'
    public $canCreateSupplierRequest = false;
    public $hasDefaultSupplier = false;

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
        
        // El perfil 1 (Gerente) o usuarios con perfil de Importaciones pueden crear solicitudes a proveedores
        $user = auth()->user();
        if ($user) {
            // Perfiles autorizados: Gerencia (1), Administradores o Jefe de Importaciones (Camilo)
            // Agregamos también la validación por nombre/correo de Camilo por si tiene otro perfil
            $isCamilo = strpos(strtolower($user->name), 'camilo') !== false || strpos(strtolower($user->email), 'camilo') !== false;
            $this->canCreateSupplierRequest = in_array($user->profile_id, [1, 2, 8]) || $isCamilo;
        }

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
        $this->supplier_id = null;
        $this->hasDefaultSupplier = false;
        $this->activeTab = 'internal';
        
        if ($productId) {
            $this->ensureTenantConnection();
            $product = Items::with('importSetup')->find($productId);
            if ($product) {
                $this->productName = $product->name;
                $this->productCode = $product->internal_code;
                
                // Preseleccionar el proveedor del producto si existe
                if ($product->importSetup && $product->importSetup->supplier_id) {
                    $this->supplier_id = $product->importSetup->supplier_id;
                    $this->hasDefaultSupplier = true;
                }
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

    public function saveComment()
    {
        if (!$this->isModuleActive) return;

        $this->ensureTenantConnection();
        if (!$this->selectedRequestId) return;

        if (empty(trim(strip_tags($this->detail)))) {
            $this->addError('detail', 'El detalle no puede estar vacío.');
            return;
        }

        try {
            DB::connection('tenant')->beginTransaction();

            $request = TickRequest::find($this->selectedRequestId);
            
            TickRequestHistory::create([
                'request_id' => $request->id,
                'from_status_id' => $request->status_id,
                'to_status_id' => $request->status_id,
                'user_id' => auth()->id(),
                'message' => $this->detail,
            ]);

            DB::connection('tenant')->commit();

            $this->reset(['detail']);
            
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Comentario guardado correctamente'
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
        
        if ($this->activeTab === 'supplier') {
            $this->validate([
                'supplier_id' => 'required',
                'detail' => 'required|min:10',
            ]);
        } else {
            $this->validate([
                'department_id' => [
                    'required',
                    Rule::exists('tenant.tick_departments', 'id')
                ],
                'detail' => 'required|min:10',
            ]);
        }

        try {
            DB::connection('tenant')->beginTransaction();

            // Buscar estado inicial (asumiendo que ID 1 es 'Abierto' o similar)
            $initialStatus = TickStatus::where('name', 'like', '%Abierto%')
                ->orWhere('id', 1)
                ->first();

            $initialStatusId = $initialStatus->id ?? 1;

            if ($this->activeTab === 'supplier') {
                // Verificar si existe una solicitud previa ya resuelta del mismo producto y proveedor
                // Los estados resueltos suelen ser "Solucionado" (id 3 o similar) o "Cerrado" (id 4)
                $solvedStatusIds = TickStatus::where('name', 'like', '%Solucionado%')
                    ->orWhere('name', 'like', '%Resuelto%')
                    ->orWhere('name', 'like', '%Cerrado%')
                    ->orWhereIn('id', [3, 4])
                    ->pluck('id')
                    ->toArray();

                $existingRequest = TickRequest::where('product_id', $this->productId)
                    ->where('supplier_id', $this->supplier_id)
                    ->whereIn('status_id', $solvedStatusIds)
                    ->first();

                if ($existingRequest) {
                    // Reactivar la solicitud existente
                    $oldStatusId = $existingRequest->status_id;
                    $existingRequest->update([
                        'status_id' => $initialStatusId,
                        'is_reactivated' => 1,
                        'detail' => $this->detail // Actualizamos el detalle con el nuevo requerimiento
                    ]);

                    // Crear historial para el seguimiento
                    TickRequestHistory::create([
                        'request_id' => $existingRequest->id,
                        'from_status_id' => $oldStatusId,
                        'to_status_id' => $initialStatusId,
                        'user_id' => auth()->id(),
                        'message' => 'Solicitud reactivada: ' . $this->detail,
                    ]);

                    $this->dispatch('show-toast', [
                        'type' => 'success',
                        'message' => 'Solicitud previa reactivada'
                    ]);
                } else {
                    // Crear nueva solicitud de proveedor
                    $request = TickRequest::create([
                        'supplier_id' => $this->supplier_id,
                        'status_id' => $initialStatusId,
                        'product_id' => $this->productId,
                        'created_by' => auth()->id(),
                        'detail' => $this->detail,
                    ]);

                    TickRequestHistory::create([
                        'request_id' => $request->id,
                        'from_status_id' => null,
                        'to_status_id' => $request->status_id,
                        'user_id' => auth()->id(),
                        'message' => $this->detail,
                    ]);

                    $this->dispatch('show-toast', [
                        'type' => 'success',
                        'message' => 'Solicitud al proveedor enviada'
                    ]);
                }
            } else {
                // Flujo normal de solicitudes internas
                $request = TickRequest::create([
                    'department_id' => $this->department_id,
                    'status_id' => $initialStatusId,
                    'product_id' => $this->productId,
                    'created_by' => auth()->id(),
                    'detail' => $this->detail,
                ]);

                TickRequestHistory::create([
                    'request_id' => $request->id,
                    'from_status_id' => null,
                    'to_status_id' => $request->status_id,
                    'user_id' => auth()->id(),
                    'message' => $this->detail,
                ]);

                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => 'Solicitud enviada correctamente'
                ]);
            }

            DB::connection('tenant')->commit();

            $this->reset(['department_id', 'detail']);
            
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
                    $selectedRequest = TickRequest::with([
                        'status', 
                        'department', 
                        'creator', 
                        'history.status', 
                        'history.user', 
                        'supplier', 
                        'product.principalImage', 
                        'product.principalBodegaImage'
                    ])->find($this->selectedRequestId);
                }

                $requests = TickRequest::with(['status', 'department', 'creator', 'supplier'])
                    ->when($this->productId, function($q) {
                        return $q->where('product_id', $this->productId);
                    })
                    ->when(!$this->selectedRequestId && auth()->user()?->profile_id != 17 && $this->activeTab === 'supplier', function($q) {
                        // En modo creación: si está en pestaña proveedor, filtrar por proveedor
                        return $q->whereNotNull('supplier_id');
                    })
                    ->when(!$this->selectedRequestId && auth()->user()?->profile_id != 17 && $this->activeTab === 'internal', function($q) {
                        // En modo creación: si está en pestaña interna, filtrar por departamento
                        return $q->whereNotNull('department_id');
                    })
                    ->when(auth()->user()?->profile_id == 17, function($q) {
                        // Si el usuario logueado es proveedor, forzar a ver solo las de proveedor (su ID)
                        return $q->where('supplier_id', auth()->id());
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

    #[Computed]
    public function suppliers()
    {
        $this->ensureTenantConnection();
        $sessionTenant = session('tenant_id');

        return \App\Models\Auth\User::select('users.id', 'users.name')
            ->join('vnt_contacts', 'users.contact_id', '=', 'vnt_contacts.id')
            ->whereHas('tenants', function ($query) use ($sessionTenant) {
                $query->where('tenants.id', $sessionTenant);
            })
            ->where('users.profile_id', 17)
            ->where('vnt_contacts.status', 1)
            ->whereNull('vnt_contacts.deleted_at')
            ->distinct()
            ->get();
    }
}
