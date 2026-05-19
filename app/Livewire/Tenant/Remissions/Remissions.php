<?php

namespace App\Livewire\Tenant\Remissions;

use App\Models\Tenant\Remissions\InvRemissions;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use App\Traits\HasCompanyConfiguration;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Central\VntWarehouse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;
use App\Services\Facturacion\TenantConfigManager;
use App\Services\Facturacion\FacturacionService;
use App\Services\Facturacion\InvoiceDataBuilder;
use App\Models\Tenant\Invoices\VntInvoices;
use App\Models\Tenant\Invoices\VntInvoicesXsales;
use App\Services\Tenant\Campaigns\CampaignService;
use App\Traits\Livewire\HasDynamicButtons;
use App\Models\Central\UsrPermissionProfile;
use App\Livewire\Tenant\Components\ObservationsModal;
use App\Models\Tenant\Sales\VntObservation;
use App\Models\Tenant\Sales\VntReturn;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant\Inventory\InventoryConfirmation;
use App\Models\Tenant\Sales\VntOrderAuthorization;

class Remissions extends Component
{
    use WithPagination, HasCompanyConfiguration, HasDynamicButtons;

    // Propiedades para búsqueda y selección
    public $search = '';
    public $perPage = 10;
    public $selectedRemissions = [];
    public $selectAll = false;

    // Propiedades para búsqueda avanzada
    public $searchNit = '';
    public $searchName = '';
    public $searchQuote = '';
    public $searchStartDate = '';
    public $searchEndDate = '';
    public $showAdvancedSearch = false;

    // Propiedades para el modal de detalle
    public $showDetailModal = false;
    public $selectedRemission = null;
    public $selectedItemDetails = null;

    // Propiedades para modal de facturación
    public $showInvoiceModal = false;
    public $selectedCustomer = null;
    public $selectedRemissionsData = [];
    public $totalItems = 0;
    public $moduleKey = 'remissions';
    public $statusFilter = '';

    // Propiedades para justificación de reimpresión
    public $showPrintJustificationModal = false;
    public $printJustificationText = '';
    public $remissionIdToPrint = null;

    public $summaryCounts = [
        'registradas' => 0,
        'alistamiento' => 0,
        'sin_entregar' => 0,
        'sin_facturar' => 0,
        'consultas_nuevas' => 0,
        'sin_autorizacion' => 0
    ];

    public $showConfirmationsModal = false;

    // Permisos
    public $canEditRemission = false;

    protected $paginationTheme = 'tailwind';

    /**
     * Se ejecuta al iniciar el componente para asegurar la conexión con el tenant.
     */
    public function boot()
    {
        $this->ensureTenantConnection();
    }

    /**
     * Inicializa el componente, configurando la conexión y la empresa.
     */
    public function mount()
    {
        $this->ensureTenantConnection();
        $this->initializeCompanyConfiguration();

        // Inicializar fechas con rango de ±7 días respecto a hoy
        $this->searchStartDate = now()->subDays(7)->format('Y-m-d');
        $this->searchEndDate = now()->addDays(7)->format('Y-m-d');

        // Verificar permisos de edición para remisiones
        $user = auth()->user();
        if ($user && $user->profile_id) {
            // Buscar el ID del permiso 'remisiones'
            $permission = \App\Models\Central\UsrPermission::where('name', 'Ventas')->first();

            if ($permission) {
                $permissionProfile = UsrPermissionProfile::where('profileId', $user->profile_id)
                    ->where('permissionId', $permission->id)
                    ->first();

                $this->canEditRemission = $permissionProfile && $permissionProfile->editer == 1;
            }
        }
    }

    /**
     * Se ejecuta cuando la propiedad de búsqueda cambia, reseteando la paginación.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSearchNit()
    {
        $this->resetPage();
    }
    public function updatingSearchName()
    {
        $this->resetPage();
    }
    public function updatingSearchQuote()
    {
        $this->resetPage();
    }
    public function updatingSearchStartDate()
    {
        $this->resetPage();
    }
    public function updatingSearchEndDate()
    {
        $this->resetPage();
    }

    /**
     * Maneja la selección de todas las remisiones en la página actual
     */
    public function updatedSelectAll($value)
    {
        $this->ensureTenantConnection();
        if ($value) {
            // Obtener primera remisión para determinar el cliente
            $firstRemission = InvRemissions::with('quote.customer')
                ->when($this->search, function ($query) {
                    $this->applyBaseFilters($query);
                })
                ->where('status', '!=', 'ANULADO')
                ->whereDoesntHave('invoiceSale')
                ->first();

            if ($firstRemission && $firstRemission->quote && $firstRemission->quote->customer) {
                $customerId = $firstRemission->quote->customerId;

                // Solo seleccionar remisiones del mismo cliente
                $this->selectedRemissions = InvRemissions::query()
                    ->when($this->search, function ($query) {
                        $this->applyBaseFilters($query);
                    })
                    ->where('status', '!=', 'ANULADO')
                    ->whereDoesntHave('invoiceSale')
                    ->whereHas('quote', function ($query) use ($customerId) {
                        $query->where('customerId', $customerId);
                    })
                    ->pluck('id')
                    ->map(fn($id) => (string) $id)
                    ->toArray();
            } else {
                $this->selectedRemissions = [];
            }
        } else {
            $this->selectedRemissions = [];
        }
    }

    /**
     * Maneja la selección individual de remisiones (validando mismo cliente)
     */
    public function updatedSelectedRemissions($value)
    {
        $this->ensureTenantConnection();

        if (empty($this->selectedRemissions)) {
            return;
        }

        // Obtener cliente de las remisiones ya seleccionadas
        $selectedCustomerId = null;
        if (!empty($this->selectedRemissions)) {
            $existingRemission = InvRemissions::with('quote.customer')
                ->whereIn('id', $this->selectedRemissions)
                ->first();

            if ($existingRemission && $existingRemission->quote) {
                $selectedCustomerId = $existingRemission->quote->customerId;
            }
        }

        // Si hay un cliente ya seleccionado, validar que las nuevas selecciones sean del mismo cliente
        if ($selectedCustomerId) {
            $validRemissions = InvRemissions::with('quote.customer')
                ->whereIn('id', $this->selectedRemissions)
                ->whereHas('quote', function ($query) use ($selectedCustomerId) {
                    $query->where('customerId', $selectedCustomerId);
                })
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();

            // Si hay remisiones de diferentes clientes, mantener solo las válidas
            if (count($validRemissions) !== count($this->selectedRemissions)) {
                $this->selectedRemissions = $validRemissions;

                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'Solo se pueden seleccionar remisiones del mismo cliente. Se han deseleccionado las remisiones de otros clientes.'
                ]);
            }
        }
    }

    /**
     * Limpia todos los filtros de búsqueda
     */
    public function clearFilters()
    {
        $this->search = '';
        $this->searchNit = '';
        $this->searchName = '';
        $this->searchQuote = '';
        $this->searchStartDate = now()->subDays(7)->format('Y-m-d');
        $this->searchEndDate = now()->addDays(7)->format('Y-m-d');
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function setStatusFilter($status)
    {
        if ($this->statusFilter === $status) {
            $this->statusFilter = '';
        } else {
            $this->statusFilter = $status;
        }
        $this->resetPage();
    }

    /**
     * Abre el modal de observaciones detalladas despachando un evento al componente independiente
     */
    public function openObservationsModal($id)
    {
        $this->dispatch('openObservations', referenceId: $id, referenceType: 'remission', title: 'Observaciones del Pedido')->to(ObservationsModal::class);
    }

    /**
     * Anula un pedido y registra el motivo en la tabla de observaciones
     */
    public function annulRemission($id, $reason)
    {
        $this->ensureTenantConnection();

        try {
            DB::connection('tenant')->beginTransaction();

            $remission = InvRemissions::find($id);
            if (!$remission) {
                throw new \Exception("Pedido no encontrado.");
            }

            // Actualizar estado del pedido
            $remission->update(['status' => 'ANULADO']);

            // ✅ NUEVO: También anular la cotización asociada si existe
            if ($remission->quote) {
                $remission->quote->update(['status' => 'ANULADO']);
                Log::info('📋 Cotización asociada anulada automáticamente', [
                    'quote_id' => $remission->quote->id,
                    'remission_id' => $id
                ]);
            }

            // Registrar el motivo de anulación
            VntObservation::create([
                'reference_id' => $id,
                'reference_type' => 'remission',
                'observation_type' => 'annulment_reason',
                'observation' => $reason,
                'userId' => auth()->id()
            ]);

            DB::connection('tenant')->commit();

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => "Pedido #{$remission->consecutive} anulado correctamente."
            ]);
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error("Error al anular pedido: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => "Error: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Abre el modal de confirmación de facturación
     */
    public function facturarMasivo()
    {
        if (empty($this->selectedRemissions)) {
            $this->dispatch('show-toast', [
                'type' => 'warning',
                'message' => 'Por favor selecciona al menos una remisión.'
            ]);
            return;
        }

        // Verificar módulo de facturación
        if (!$this->isInvoiceModuleActive) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'El módulo de facturación no está activo.'
            ]);
            return;
        }

        $this->ensureTenantConnection();

        try {
            // Cargar remisiones con detalles y cliente (solo las NO facturadas)
            $remisiones = InvRemissions::with(['quote.customer', 'details.item'])
                ->whereIn('id', $this->selectedRemissions)
                ->where('status', '!=', 'ANULADO')
                ->whereDoesntHave('invoiceSale') // Solo remisiones que NO estén en vnt_invoicesXsales
                ->get();

            if ($remisiones->isEmpty()) {
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'No se encontraron remisiones válidas para facturar.'
                ]);
                return;
            }

            // Obtener el cliente (todas las remisiones deben ser del mismo cliente)
            $customer = $remisiones->first()->quote->customer;
            $this->selectedCustomer = [
                'id' => $customer->id,
                'businessName' => $customer->businessName,
                'firstName' => $customer->firstName,
                'lastName' => $customer->lastName,
                'identification' => $customer->identification,
                'customer_name' => $customer->customer_name
            ];

            // Preparar datos de las remisiones para el modal
            $this->selectedRemissionsData = [];
            $this->totalItems = 0;

            foreach ($remisiones as $remission) {
                $itemsCount = $remission->details->count();
                $this->totalItems += $itemsCount;

                $this->selectedRemissionsData[] = [
                    'id' => $remission->id,
                    'consecutive' => $remission->consecutive,
                    'items_count' => $itemsCount,
                    'created_at' => $remission->created_at->format('d/m/Y H:i'),
                    'total_value' => $remission->details->sum(function ($detail) {
                        return $detail->quantity * $detail->value;
                    })
                ];
            }

            Log::info('📋 Preparando modal de facturación', [
                'remisiones_count' => count($this->selectedRemissionsData),
                'customer_id' => $this->selectedCustomer['id'] ?? null,
                'customer_name' => $this->selectedCustomer['businessName'] ?? $this->selectedCustomer['firstName'],
                'total_items' => $this->totalItems
            ]);

            // Mostrar modal de confirmación
            $this->showInvoiceModal = true;
        } catch (\Exception $e) {
            Log::error('❌ Error preparando modal de facturación: ' . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al preparar la facturación: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cerrar modal de facturación
     */
    public function closeInvoiceModal()
    {
        $this->showInvoiceModal = false;
        $this->selectedCustomer = null;
        $this->selectedRemissionsData = [];
        $this->totalItems = 0;
    }

    /**
     * Confirmar y procesar la facturación desde el modal
     */
    public function confirmarFacturacion()
    {
        if (empty($this->selectedRemissions)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No hay remisiones seleccionadas para facturar.'
            ]);
            return;
        }

        $this->ensureTenantConnection();

        try {
            $remisiones = InvRemissions::with(['quote.customer', 'details.item'])
                ->whereIn('id', $this->selectedRemissions)
                ->where('status', '!=', 'ANULADO')
                ->whereDoesntHave('invoiceSale') // Solo remisiones que NO estén en vnt_invoicesXsales
                ->get();

            if ($remisiones->isEmpty()) {
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'No se encontraron remisiones válidas para facturar.'
                ]);
                return;
            }

            // Verificar configuración de facturación del tenant
            $tenant = session('tenant_id') ? Tenant::find(session('tenant_id')) : null;
            if (!$tenant) {
                throw new \Exception('No se pudo identificar el tenant');
            }

            $hasFacturacionConfig = TenantConfigManager::hasFacturacionConfig($tenant);
            if (!$hasFacturacionConfig) {
                throw new \Exception('No hay configuración de facturación para este tenant');
            }

            $facturadas = 0;
            $errores = 0;

            Log::info('🚀 Iniciando Facturación Confirmada desde Modal', [
                'remisiones_count' => $remisiones->count(),
                'remisiones_ids' => $this->selectedRemissions,
                'customer_id' => $this->selectedCustomer['id'] ?? null,
                'tenant_id' => $tenant->id
            ]);

            // Verificar si hay múltiples remisiones para agrupar
            if ($remisiones->count() > 1) {
                // VALIDACIÓN PREVIA: Informar sobre items con diferentes precios
                $this->validateItemPricingForGrouping($remisiones);

                // FACTURACIÓN AGRUPADA
                try {
                    $this->facturarRemisionesAgrupadas($remisiones, $tenant);
                    $facturadas = $remisiones->count();
                } catch (\Exception $e) {
                    $errores = $remisiones->count();
                    Log::error('❌ Error facturando remisiones agrupadas', [
                        'remisiones_ids' => $remisiones->pluck('id')->toArray(),
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                // FACTURACIÓN INDIVIDUAL (una sola remisión)
                foreach ($remisiones as $remission) {
                    try {
                        $this->facturarRemisionIndividual($remission, $tenant);
                        $facturadas++;
                    } catch (\Exception $e) {
                        $errores++;
                        Log::error('❌ Error facturando remisión individual', [
                            'remission_id' => $remission->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            // Cerrar modal
            $this->closeInvoiceModal();

            // Limpiar selección
            $this->selectedRemissions = [];
            $this->selectAll = false;

            // Mostrar resultado
            if ($facturadas > 0 && $errores == 0) {
                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => "✅ {$facturadas} remisiones facturadas y emitidas exitosamente."
                ]);
            } elseif ($facturadas > 0 && $errores > 0) {
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => "⚠️ {$facturadas} facturadas exitosamente, {$errores} con errores. Revise los detalles en los logs."
                ]);
            } else {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => "❌ No se pudo facturar ninguna remisión. Revise la configuración de facturación y los datos de los productos."
                ]);
            }
        } catch (\Exception $e) {
            $this->closeInvoiceModal();
            Log::error('❌ Error en confirmarFacturacion: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al procesar facturación: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Aplica los filtros base a la consulta
     */
    private function applyBaseFilters($query)
    {
        $query->where(function ($q) {
            $q->where('consecutive', 'like', '%' . $this->search . '%')
                ->orWhereHas('quote.customer', function ($sub) {
                    $sub->where('firstName', 'like', '%' . $this->search . '%')
                        ->orWhere('lastName', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('invoice', function ($sub) {
                    $sub->where('status', 'like', '%' . $this->search . '%')
                        ->orWhere('invoiceNumber', 'like', '%' . $this->search . '%');
                });
        });

        // Búsqueda avanzada
        if ($this->searchNit) {
            $query->whereHas('quote.customer', function ($q) {
                $q->where('identification', 'like', '%' . $this->searchNit . '%');
            });
        }

        if ($this->searchName) {
            $query->whereHas('quote.customer', function ($q) {
                $q->where('firstName', 'like', '%' . $this->searchName . '%')
                    ->orWhere('lastName', 'like', '%' . $this->searchName . '%');
            });
        }

        if ($this->searchQuote) {
            $query->whereHas('quote', function ($q) {
                $q->where('consecutive', 'like', '%' . $this->searchQuote . '%');
            });
        }

        // Si hay un filtro de tarjeta (statusFilter) activo, no se filtrará por rango de fechas en el listado
        if (empty($this->statusFilter)) {
            if ($this->searchStartDate) {
                $query->whereDate('created_at', '>=', $this->searchStartDate);
            }

            if ($this->searchEndDate) {
                $query->whereDate('created_at', '<=', $this->searchEndDate);
            }
        }
    }

    /**
     * Asegura que exista una conexión válida con el tenant basada en la sesión.
     */
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

    /**
     * Carga y muestra los detalles de una remisión específica en un modal.
     * 
     * @param int $id ID de la remisión
     */
    public function viewDetails($id)
    {
        $this->ensureTenantConnection();
        // Usar '$remission' como el nombre de la variable local para el modelo
        $remission = InvRemissions::with([
            'quote.customer',
            'quote.warehouse',
            'quote.branch',
            'details.item',
            'store',
            'deliveryTypeModel',
            'methodPayment'
        ])->find($id);

        if ($remission) {
            // Convertir el modelo a un arreglo
            $remissionArray = $remission->toArray();

            // Pre-formatear fechas y agregar manualmente los valores de los accesor
            $remissionArray['created_at_formatted'] = $remission->created_at->format('d/m/Y H:i');
            $remissionArray['delivery_date_formatted'] = $remission->deliveryDate ? \Carbon\Carbon::parse($remission->deliveryDate)->format('d/m/Y') : 'N/A';
            $remissionArray['seller_name'] = $remission->seller_name; // Se añade la clave que faltaba

            // Asegurarse de que el accesor anidado también exista
            if (isset($remissionArray['quote'])) {
                $remissionArray['quote']['customer_name'] = $remission->quote->customer_name;
            }

            // Guardar el arreglo final y completo en la propiedad pública '$selectedRemission'
            $this->selectedRemission = $remissionArray;

            // La lógica de logging puede seguir usando el modelo original
            $seller = $remission->getUser();
            Log::info('👁️ Ver detalles de remisión', [
                'remission_id' => $id,
                'consecutive' => $remission->consecutive ?? 'N/A',
                'seller_name' => $seller ? ($seller->name . ' ' . ($seller->lastName ?? '')) : 'N/A'
            ]);
        } else {
            $this->selectedRemission = null;
        }

        $this->selectedItemDetails = null;
        $this->showDetailModal = true;
    }

    /**
     * Carga las imágenes de bodega y accesorios de un ítem específico.
     * 
     * @param int $itemId ID del ítem
     */
    public function viewItemWarehouseDetails($itemId)
    {
        $this->ensureTenantConnection();

        $item = \App\Models\Tenant\Items\Items::with([
            'accessories.insumo',
            'imageGallery' => function ($query) {
                $query->where('type_show', 'BODEGA')->whereNull('deleted_at');
            }
        ])->find($itemId);

        if ($item) {
            $this->selectedItemDetails = [
                'id' => $item->id,
                'name' => $item->name,
                'internal_code' => $item->internal_code,
                'description' => $item->description,
                'picking' => $item->picking,
                'stock_bodega' => $item->stock_bodega,
                'qty_per_box' => $item->qty_per_box,
                'images' => $item->imageGallery->map(function ($img) {
                    return [
                        'url' => $img->getImageUrl(),
                        'thumbnail' => $img->getThumbnailUrl()
                    ];
                })->toArray(),
                'accessories' => $item->accessories->map(function ($acc) {
                    return [
                        'name' => $acc->insumo->name ?? 'N/A',
                        'internal_code' => $acc->insumo->internal_code ?? 'N/A',
                        'description' => $acc->insumo->description ?? ''
                    ];
                })->toArray()
            ];

            Log::info('📦 Viendo detalles de bodega del ítem', ['item_id' => $itemId, 'name' => $item->name]);
        }
    }

    /**
     * Renderiza la vista del componente con el listado de remisiones filtrado.
     */
    /**
     * Redirige al cotizador para editar una remisión existente
     */
    public function editarRemision($id)
    {
        $agent = new Agent();

        if ($agent->isMobile() || $agent->isTablet()) {
            return redirect()->route('tenant.quoter.products.mobile.remission', ['remissionId' => $id]);
        }

        return redirect()->route('tenant.quoter.products.desktop.remission', ['remissionId' => $id]);
    }

    /**
     * Método de entrada para imprimir remisión con validación de reimpresión
     */
    public function printRemission($id)
    {
        $this->ensureTenantConnection();
        $remission = InvRemissions::findOrFail($id);

        // Si ya ha sido impresa anteriormente, solicitar justificación
        if ($remission->print_count > 0) {
            $this->remissionIdToPrint = $id;
            $this->printJustificationText = '';
            $this->showPrintJustificationModal = true;
            return;
        }

        // Si es la primera vez, imprimir directamente
        $this->executePrintRemission($id);
    }

    /**
     * Confirma la reimpresión tras recibir la justificación
     */
    public function confirmPrintWithJustification()
    {
        if (empty(trim($this->printJustificationText))) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Debe ingresar una justificación para la reimpresión.'
            ]);
            return;
        }

        $this->ensureTenantConnection();

        // Guardar la observación de reimpresión
        VntObservation::create([
            'reference_id' => $this->remissionIdToPrint,
            'reference_type' => 'remission',
            'observation_type' => 'reprint',
            'observation' => 'REIMPRESIÓN: ' . $this->printJustificationText,
            'userId' => auth()->id(),
        ]);

        $id = $this->remissionIdToPrint;
        $this->showPrintJustificationModal = false;

        $this->executePrintRemission($id);
    }

    /**
     * Lógica real de generación de impresión (antes printRemission)
     */
    public function executePrintRemission($id)
    {
        Log::info('🖨️ printRemission llamado', ['remission_id' => $id]);

        $this->ensureTenantConnection();
        $this->initializeCompanyConfiguration();

        try {
            Log::info('🔄 Iniciando carga de remisión...');

            // Cargar la remisión paso a paso para debug
            Log::info('🔄 Cargando remisión básica...');
            $remission = InvRemissions::findOrFail($id);
            Log::info('📄 Remisión básica cargada', ['consecutive' => $remission->consecutive]);

            Log::info('🔄 Cargando detalles...');
            try {
                $remission->load('details');
                Log::info('📋 Detalles cargados', ['count' => $remission->details->count()]);
            } catch (\Exception $detailError) {
                Log::error('❌ Error cargando detalles', ['error' => $detailError->getMessage()]);
                throw $detailError;
            }

            Log::info('🔄 Cargando cliente desde quote...');
            try {
                $remission->load('quote.customer');
                Log::info('👤 Cliente cargado', ['customer_id' => $remission->quote->customerId ?? 'N/A']);
            } catch (\Exception $customerError) {
                Log::error('❌ Error cargando cliente', ['error' => $customerError->getMessage()]);
                // Continuar sin cliente para debug
            }

            // Nota: No cargamos warehouse aquí porque se consultará directamente desde central en getCompanyInfo()
            Log::info('🔄 WarehouseId de la remisión: ' . $remission->warehouseId);

            Log::info('🔄 Cargando items de los detalles...');
            try {
                $remission->load('details.item');
                Log::info('📦 Items cargados');

                // Debug: verificar si hay items null
                $nullItems = $remission->details->whereNull('item')->count();
                if ($nullItems > 0) {
                    Log::warning('⚠️ Hay items null', ['null_count' => $nullItems]);
                }
            } catch (\Exception $itemError) {
                Log::error('❌ Error cargando items', ['error' => $itemError->getMessage()]);
            }

            Log::info('🔄 Cargando usuario...');
            try {
                $remission->load('user');
                Log::info('👤 Usuario cargado');
            } catch (\Exception $userError) {
                Log::error('❌ Error cargando usuario', ['error' => $userError->getMessage()]);
            }

            // Obtener información de la empresa
            $company = $this->getCompanyInfo($remission);
            Log::info('🏢 Empresa cargada', ['company' => $company->businessName ?? 'N/A']);

            // Calcular el peso total de los items
            $totalWeight = DB::connection('tenant')->table('inv_detail_remissions')
                ->join('inv_items_dimensions', 'inv_detail_remissions.itemId', '=', 'inv_items_dimensions.item_id')
                ->where('inv_detail_remissions.remissionId', $id)
                ->sum('inv_items_dimensions.weight');

            Log::info('⚖️ Peso total calculado:', ['totalWeight' => $totalWeight]);

            $observations = DB::connection('tenant')->table('inv_remissions')
                ->where('quoteId', $id)
                ->select('observations_delivery', 'obs')->first();

            // Determinar el formato de impresión según configuración
            $printFormat = $this->getPrintCopiesLimit(); // 0 = POS, 1 = Carta
            Log::info('🎯 Formato determinado desde configuración', ['printFormat' => $printFormat]);

            // Validar y asignar regalo de campaña
            $campaignService = app(CampaignService::class);
            $assignedCampaign = $campaignService->checkAndAssignGift($remission);
            $giftObservation = $assignedCampaign ? "\n*** OBSEQUIO: {$assignedCampaign->name} ***" : "";

            $data = [
                'quote' => $remission, // Pasamos la remisión como 'quote' para reusar la vista
                'customer' => $remission->quote->customer ?? null,
                'company' => $company,
                'documentTitle' => 'REMISIÓN',
                'showQR' => true,
                'defaultObservations' => 'Sin observaciones.' . $giftObservation,
                'giftObservation' => $giftObservation,
                'totalWeight' => $totalWeight,
                'observations_delivery' => $observations->observations_delivery ?? null,
                'obs' => $observations->obs ?? null,
            ];
            Log::info('📝 Datos preparados para la vista');

            $viewName = ($printFormat === 1)
                ? 'livewire.tenant.quoter.print.print-carta'
                : 'livewire.tenant.quoter.print.print-pos';
            Log::info('🎨 Vista seleccionada', ['viewName' => $viewName]);

            Log::info('🔄 Iniciando generación de HTML...');
            try {
                $html = view($viewName, $data)->render();
                Log::info('✅ HTML generado exitosamente', ['length' => strlen($html)]);
            } catch (\Exception $viewError) {
                Log::error('❌ Error generando vista', ['error' => $viewError->getMessage()]);
                throw $viewError;
            }

            $tempFileName = 'quote_' . $id . '_' . time() . '.html';
            $tempPath = storage_path('app/temp/' . $tempFileName);
            Log::info('📁 Archivo temporal', ['fileName' => $tempFileName, 'path' => $tempPath]);

            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
                Log::info('📂 Directorio temp creado');
            }

            file_put_contents($tempPath, $html);
            Log::info('💾 Archivo guardado', ['size' => filesize($tempPath) . ' bytes']);

            $printUrl = route('quoter.print.temp', ['file' => $tempFileName]);
            Log::info('🔗 URL generada', ['url' => $printUrl]);

            $this->dispatch('open-print-window', [
                'url' => $printUrl,
                'format' => $printFormat === 1 ? 'carta' : 'pos'
            ]);
            Log::info('🚀 Evento dispatch enviado');

            // Incrementar el contador de impresiones
            $remission->increment('print_count');

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Remisión #' . $remission->consecutive . ' preparada para impresión (' . ($printFormat === 1 ? 'Formato Carta' : 'Formato POS') . ')'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error en printRemission: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al preparar impresión: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Método para imprimir factura (basado en una remisión facturada)
     * Utiliza el api_data_id si existe para obtener el PDF oficial de Alegra.
     */
    public function printInvoice($id)
    {
        Log::info('🖨️ Remissions.printInvoice llamado', ['remission_id' => $id]);

        $this->ensureTenantConnection();
        $this->initializeCompanyConfiguration();

        try {
            // 1. Buscar la remisión y su factura asociada
            $remission = InvRemissions::with('invoice')->findOrFail($id);
            $invoice = $remission->invoice;

            if (!$invoice) {
                Log::warning('⚠️ No se encontró factura vinculada a la remisión', ['remission_id' => $id]);
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'Esta remisión aún no tiene una factura asociada.'
                ]);
                return;
            }

            Log::info('🔍 Factura vinculada encontrada', [
                'invoice_id' => $invoice->id,
                'api_data_id' => $invoice->api_data_id
            ]);

            // 2. Obtener configuración de facturación
            $tenant = session('tenant_id') ? Tenant::find(session('tenant_id')) : null;
            if (!$tenant) {
                throw new \Exception('No se pudo identificar el tenant');
            }

            $facturacionService = new FacturacionService($tenant);
            $hasFacturacionConfig = TenantConfigManager::hasFacturacionConfig($tenant);

            // 3. Lógica de impresión (Prioridad Alegra)
            if ($hasFacturacionConfig && $invoice->api_data_id) {
                Log::info('🔗 Usando api_data_id de Alegra para obtener PDF', ['api_id' => $invoice->api_data_id]);

                $apiResponse = $facturacionService->getInvoicePdf($invoice->api_data_id);

                // Analizar estructura de respuesta para depurar (igual que en Quoter)
                $respData = $apiResponse['data'] ?? [];

                // Intentar obtener URL de varios posibles campos
                $printUrl = $respData['pdf'] ??
                    $respData['publicUrl'] ??
                    ($respData['data']['publicUrl'] ?? null);

                if ($apiResponse['success'] && !empty($printUrl)) {
                    Log::info('✅ URL de documento Alegra encontrada', ['url' => $printUrl]);

                    $this->dispatch('open-print-window', [
                        'url' => $printUrl,
                        'format' => 'carta'
                    ]);
                    return;
                } else {
                    Log::warning('⚠️ No se obtuvo URL válida de Alegra. ¿Está emitida?', [
                        'response' => $apiResponse
                    ]);
                    $this->dispatch('show-toast', [
                        'type' => 'warning',
                        'message' => 'No se pudo obtener el PDF de Alegra. Verifique si la factura ya fue emitida.'
                    ]);
                }
            } else {
                Log::info('ℹ️ Facturación no configurada o sin api_data_id. Se usaría impresión local.');
                $this->dispatch('show-toast', [
                    'type' => 'info',
                    'message' => 'La factura no está en Alegra todavía. Se requiere emisión previa.'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('❌ Error en Remissions.printInvoice: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al procesar impresión de factura: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtiene el formato de impresión desde la configuración
     */
    public function getPrintCopiesLimit(): int
    {
        try {
            return $this->getOptionValue(3) ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Obtiene información de la empresa
     */
    private function getCompanyInfo($remission = null)
    {
        Log::info('🏢 getCompanyInfo llamado para remisión');

        try {
            $userId = auth()->id();

            if (!$userId) {
                Log::warning('⚠️ No hay usuario autenticado para getCompanyInfo');
                throw new \Exception('Usuario no autenticado');
            }

            // Ejecutar la consulta proporcionada por el usuario adaptada a Query Builder
            $companyData = DB::connection('central')->table('users as u')
                ->join('user_tenants as uXt', 'uXt.user_id', '=', 'u.id')
                ->join('tenants as t', 't.id', '=', 'uXt.tenant_id')
                ->join('vnt_companies as v', 'v.id', '=', 't.company_id')
                ->join('vnt_warehouses as w', 'w.companyId', '=', 'v.id')
                ->join('cities as c', 'c.id', '=', 'w.cityId')
                ->join('cnf_type_identifications as ti', 'ti.id', '=', 'v.typeIdentificationId')
                ->where('u.id', $userId)
                ->where('w.main', 1)
                ->select([
                    'v.businessName',
                    'w.address as billingAddress',
                    'c.name as city',
                    'ti.acronym',
                    'v.identification',
                    'v.checkDigit',
                    'v.billingEmail' // Campo extra útil para facturación
                ])
                ->first();

            if ($companyData) {
                Log::info('🏢 Datos empresa obtenidos exitosamente', (array)$companyData);
                return $companyData;
            } else {
                Log::warning('⚠️ No se encontraron datos de empresa para el usuario ID: ' . $userId);
                throw new \Exception('Datos de empresa no encontrados');
            }
        } catch (\Exception $e) {
            Log::error('❌ Error en getCompanyInfo: ' . $e->getMessage());

            // Datos por defecto si hay error o no se encuentra el registro
            return (object) [
                'businessName' => 'EMPRESA DE PRUEBA',
                'billingAddress' => 'Dirección de prueba',
                'city' => 'Ciudad Prueba',
                'acronym' => 'NIT',
                'identification' => '123456789',
                'billingEmail' => 'test@empresa.com'
            ];
        }

        Log::info('🏢 Datos empresa preparados para remisión', $companyData);

        return (object) $companyData;
    }

    public function render()
    {
        $this->ensureTenantConnection();

        // Obtener el store (bodega) del usuario autenticado desde su contacto
        $user = auth()->user();
        $storeId = null;

        if ($user && $user->contact_id) {
            try {
                $contact = \App\Models\Central\VntContact::on('central')->find($user->contact_id);
                if ($contact) {
                    $storeId = $contact->store;
                }
            } catch (\Exception $e) {
                Log::warning('⚠️ Error obteniendo store del usuario desde contacto', [
                    'user_id' => $user->id,
                    'contact_id' => $user->contact_id,
                    'error' => $e->getMessage()
                ]);
                // Si hay error, mostrar todas las remisiones (sin filtrar por store)
                $storeId = null;
            }
        }

        Log::info('📋 Remissions render() - Iniciando carga de remisiones', [
            'search' => $this->search,
            'perPage' => $this->perPage,
            'user_id' => $user?->id,
            'contact_id' => $user?->contact_id,
            'store_id' => $storeId
        ]);

        // Calcular conteos para las tarjetas de resumen
        $baseQuery = InvRemissions::query()
            ->when($storeId, function ($query) use ($storeId) {
                $query->where('warehouseId', $storeId);
            })
            ->whereHas('authorizations', function ($q) {
                $q->whereIn('auth_type', ['empaque', 'despacho', 'pago'])
                    ->where('status', 1);
            });

        $this->summaryCounts = [
            'registradas' => (clone $baseQuery)->where('status', 'REGISTRADO')->count(),
            'alistamiento' => (clone $baseQuery)->where('status', 'ALISTAMIENTO')->count(),
            'sin_entregar' => (clone $baseQuery)->where('status', '!=', 'ENTREGADO')->count(),
            'sin_facturar' => (clone $baseQuery)->whereDoesntHave('invoiceSale')->count(),
            'consultas_nuevas' => InventoryConfirmation::where('status', 1)->count(),
            'sin_autorizacion' => InvRemissions::where('status', '!=', 'ANULADO')
                ->where('status', '!=', 'ENTREGADO')
                ->whereDoesntHave('authorizations', function ($q) {
                    $q->where('auth_type', 'despacho')->where('status', 1);
                })->count(),
        ];

        // Consulta de remisiones con relaciones y filtros de búsqueda
        $remissions = InvRemissions::with(['quote.customer', 'quote.warehouse', 'quote.branch', 'details', 'store', 'invoice', 'deliveryTypeModel', 'methodPayment', 'authorizations'])
            ->when($this->statusFilter !== 'sin_autorizacion', function ($q) {
                $q->whereHas('authorizations', function ($sub) {
                    $sub->whereIn('auth_type', ['empaque', 'despacho', 'pago'])
                        ->where('status', 1);
                });
            })
            ->when($storeId, function ($query) use ($storeId) {
                // Filtrar por store del usuario (warehouseId en inv_remissions = store del contacto)
                $query->where('warehouseId', $storeId);
                Log::info('🔍 Aplicando filtro por store en remisiones', [
                    'store_id' => $storeId,
                    'field' => 'warehouseId'
                ]);
            })
            ->where(function ($query) {
                $this->applyBaseFilters($query);
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'registradas') {
                    $query->where('status', 'REGISTRADO');
                } elseif ($this->statusFilter === 'alistamiento') {
                    $query->where('status', 'ALISTAMIENTO');
                } elseif ($this->statusFilter === 'sin_entregar') {
                    $query->where('status', '!=', 'ENTREGADO');
                } elseif ($this->statusFilter === 'sin_facturar') {
                    $query->whereDoesntHave('invoiceSale');
                } elseif ($this->statusFilter === 'sin_autorizacion') {
                    $query->where('status', '!=', 'ANULADO')
                        ->where('status', '!=', 'ENTREGADO')
                        ->whereDoesntHave('authorizations', function ($q) {
                            $q->where('auth_type', 'despacho')->where('status', 1);
                        });
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        // Log de datos para debug
        Log::info('📊 Remisiones cargadas y filtradas', [
            'total' => $remissions->total(),
            'count' => $remissions->count(),
            'per_page' => $this->perPage,
            'current_page' => $remissions->currentPage(),
            'filtered_by_store' => $storeId
        ]);

        // Log detallado de cada remisión
        foreach ($remissions as $remission) {
            $seller = $remission->getUser();
            Log::info('📦 Remisión #' . $remission->consecutive, [
                'id' => $remission->id,
                'warehouseId' => $remission->warehouseId,
                'store_name' => $remission->store?->name ?? 'N/A',
                'store_id' => $remission->store?->id ?? 'N/A',
                'quote_warehouse_name' => $remission->quote?->warehouse?->name ?? 'N/A',
                'customer_name' => $remission->quote?->customer_name ?? 'N/A',
                'status' => $remission->status,
                'userId' => $remission->userId,
                'seller_name' => $seller ? ($seller->name . ' ' . ($seller->lastName ?? '')) : 'N/A',
                'seller_email' => $seller?->email ?? 'N/A',
                'created_at' => $remission->created_at->format('Y-m-d H:i:s')
            ]);
        }

        Log::info('✅ Render completado - Todas las remisiones procesadas');

        return view('livewire.tenant.remissions.remissions', [
            'remissions' => $remissions
        ])->layout('layouts.app', ['header' => 'Pedidos']);
    }

    /**
     * Facturar una remisión individual
     * Proceso: 1) Crear factura SIN EMITIR, 2) Emitir factura (stamp)
     */
    private function facturarRemisionIndividual(InvRemissions $remission, Tenant $tenant): void
    {
        try {
            Log::info('🧾 Iniciando facturación individual de remisión', [
                'remission_id' => $remission->id,
                'consecutive' => $remission->consecutive,
                'tenant_id' => $tenant->id
            ]);

            // Cargar relaciones necesarias si no están cargadas
            if (!$remission->relationLoaded('quote') || !$remission->relationLoaded('details')) {
                $remission->load(['quote.customer', 'details.item']);
            }

            if (!$remission->quote || !$remission->quote->customer) {
                throw new \Exception('Remisión sin cotización o cliente asociado');
            }

            if ($remission->details->isEmpty()) {
                throw new \Exception('Remisión sin productos');
            }

            // Crear instancia del servicio de facturación
            $facturacionService = FacturacionService::forTenant($tenant);

            // Construir datos de la factura usando el InvoiceDataBuilder
            // Para remisiones: establecer como CRÉDITO (no CASH)
            $paymentMethods = [
                [
                    'descriptionFormaPago' => 'CREDITO',
                    'nombre' => 'CREDITO',
                    'valor' => $this->calculateRemissionTotal($remission) // Valor total de la remisión
                ]
            ];
            $retentions = [];     // Sin retenciones
            $termDays = 0;        // Sin términos

            $invoiceData = InvoiceDataBuilder::buildFromQuote(
                $remission->quote,  // Usamos la cotización asociada a la remisión
                $paymentMethods,
                $retentions,
                $termDays
            );

            Log::info('📡 Enviando factura a API de Alegra desde remisión', [
                'remission_id' => $remission->id,
                'quote_id' => $remission->quote->id,
                'invoice_data_summary' => [
                    'client_id' => $invoiceData['client']['id'] ?? null,
                    'items_count' => count($invoiceData['items'] ?? []),
                    'payment_form' => $invoiceData['paymentForm'] ?? null,
                    'has_payment_method' => isset($invoiceData['paymentMethod']),
                    'payment_method_value' => $invoiceData['paymentMethod'] ?? 'not_set'
                ],
                'full_invoice_data' => $invoiceData
            ]);

            // 1. CREAR FACTURA EN ALEGRA (Estado inicial: SIN EMITIR)
            $apiResponse = $facturacionService->createInvoice($invoiceData);

            if (!$apiResponse['success'] || !isset($apiResponse['data']['id'])) {
                $errorMessage = $apiResponse['message'] ?? 'Error desconocido en la API';
                throw new \Exception("Error creando factura en API: {$errorMessage}");
            }

            $invoiceId = $apiResponse['data']['id'];
            $invoiceNumber = $apiResponse['data']['fullNumber'] ?? $invoiceId;

            // 2. CREAR REGISTRO LOCAL DE FACTURA CON ESTADO "SIN EMITIR"
            $invoice = VntInvoices::create([
                'consecutive' => $invoiceId, // Usar ID de Alegra como consecutivo temporal
                'status' => 'SIN EMITIR', // Estado inicial
                'quoteId' => $remission->quote->id,
                'warehouseId' => $remission->warehouseId,
                'api_data_id' => $invoiceId,
                'invoiceNumber' => $invoiceNumber,
                'partialPayment' => 0.00,
                'retentionFuente' => 0.00,
                'retentionIca' => 0.00,
                'retentionIva' => 0.00,
                'creditNote' => null,
                'orderNumber' => null,
                'remission' => 0 // Valor por defecto para nueva arquitectura
            ]);

            // 3. CREAR REGISTRO EN vnt_invoicesXsales
            VntInvoicesXsales::create([
                'remissionId' => $remission->id, // Desde remisión
                'quoteId' => $remission->quote->id,
                'invoiceId' => $invoice->id
            ]);

            // 4. ACTUALIZAR EL CAMPO invoiceId EN LOS DETALLES DE LA REMISIÓN
            \App\Models\Tenant\Remissions\InvDetailRemissions::where('remissionId', $remission->id)
                ->update(['invoiceId' => $invoice->id]);

            Log::info('📄 Factura creada con estado SIN EMITIR, procediendo a emitir', [
                'invoice_local_id' => $invoice->id,
                'api_data_id' => $invoiceId,
                'remission_id' => $remission->id
            ]);

            // 5. INTENTAR EMITIR (STAMP) LA FACTURA
            $stampResponse = $facturacionService->stampInvoice($invoiceId);

            if ($stampResponse['success']) {
                // ✅ STAMP EXITOSO: Actualizar estado a FACTURADO
                $invoice->update(['status' => 'FACTURADO']);
                // La remisión mantiene su status original (REGISTRADO, etc.)

                // ✅ NUEVO: Actualizar status de la cotización asociada a FACTURADO
                if ($remission->quote) {
                    $remission->quote->update(['status' => 'FACTURADO']);
                    Log::info('📋 Status de cotización actualizado a FACTURADO', [
                        'quote_id' => $remission->quote->id,
                        'quote_consecutive' => $remission->quote->consecutive,
                        'remission_id' => $remission->id
                    ]);
                }

                Log::info('✅ Factura emitida exitosamente desde remisión', [
                    'remission_id' => $remission->id,
                    'invoice_id_alegra' => $invoiceId,
                    'invoice_number' => $invoiceNumber,
                    'final_status' => 'FACTURADO'
                ]);
            } else {
                // ❌ STAMP FALLÓ: Factura queda como SIN EMITIR
                Log::error('❌ Falló la emisión legal (stamp) de remisión', [
                    'remission_id' => $remission->id,
                    'invoice_id' => $invoiceId,
                    'stamp_response' => $stampResponse
                ]);

                // Extraer mensaje de error más específico
                $errorMessage = $this->extractStampErrorMessage($stampResponse);

                throw new \Exception("La factura #{$invoiceNumber} se creó exitosamente pero no se pudo emitir legalmente. Razón: {$errorMessage}. Puede intentar emitirla desde el módulo de facturas.");
            }
        } catch (\Exception $e) {
            Log::error('❌ Error en facturarRemisionIndividual', [
                'remission_id' => $remission->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Facturar múltiples remisiones agrupadas en una sola factura
     */
    private function facturarRemisionesAgrupadas($remisiones, Tenant $tenant): void
    {
        try {
            $remissionIds = $remisiones->pluck('id')->toArray();

            Log::info('🧾 Iniciando facturación agrupada de remisiones', [
                'remisiones_count' => $remisiones->count(),
                'remisiones_ids' => $remissionIds,
                'tenant_id' => $tenant->id
            ]);

            // Cargar todas las relaciones necesarias
            foreach ($remisiones as $remission) {
                if (!$remission->relationLoaded('quote') || !$remission->relationLoaded('details')) {
                    $remission->load(['quote.customer', 'details.item']);
                }

                if (!$remission->quote || !$remission->quote->customer) {
                    throw new \Exception("Remisión {$remission->id} sin cotización o cliente asociado");
                }
            }

            // Agrupar y validar items
            $groupedItems = $this->groupRemissionItems($remisiones);

            if (count($groupedItems) > 30) {
                throw new \Exception("La factura agrupada supera el límite de 30 items (" . count($groupedItems) . " items). Seleccione menos remisiones.");
            }

            // Crear instancia del servicio de facturación
            $facturacionService = FacturacionService::forTenant($tenant);

            // Construir datos de la factura agrupada
            $invoiceData = $this->buildGroupedInvoiceData($remisiones, $groupedItems);

            Log::info('📡 Enviando factura agrupada a API de Alegra', [
                'remisiones_ids' => $remissionIds,
                'items_count' => count($groupedItems)
            ]);

            // 1. CREAR FACTURA EN ALEGRA
            $apiResponse = $facturacionService->createInvoice($invoiceData);

            if (!$apiResponse['success'] || !isset($apiResponse['data']['id'])) {
                $errorMessage = $apiResponse['message'] ?? 'Error desconocido en la API';
                throw new \Exception("Error creando factura agrupada en API: {$errorMessage}");
            }

            $invoiceId = $apiResponse['data']['id'];
            $invoiceNumber = $apiResponse['data']['fullNumber'] ?? $invoiceId;

            // 2. CREAR REGISTRO LOCAL DE FACTURA
            $invoice = VntInvoices::create([
                'consecutive' => $invoiceId,
                'status' => 'SIN EMITIR',
                'quoteId' => $remisiones->first()->quote->id, // Usar la primera cotización como referencia
                'warehouseId' => $remisiones->first()->warehouseId,
                'api_data_id' => $invoiceId,
                'invoiceNumber' => $invoiceNumber,
                'partialPayment' => 0.00,
                'retentionFuente' => 0.00,
                'retentionIca' => 0.00,
                'retentionIva' => 0.00,
                'creditNote' => null,
                'orderNumber' => null,
                'remission' => 0 // Valor por defecto para facturas agrupadas
            ]);

            // 3. CREAR REGISTROS EN vnt_invoicesXsales para TODAS las remisiones
            foreach ($remisiones as $remission) {
                VntInvoicesXsales::create([
                    'remissionId' => $remission->id,
                    'quoteId' => $remission->quote->id,
                    'invoiceId' => $invoice->id
                ]);
            }

            // 4. ACTUALIZAR EL CAMPO invoiceId EN LOS DETALLES DE TODAS LAS REMISIONES
            foreach ($remisiones as $remission) {
                \App\Models\Tenant\Remissions\InvDetailRemissions::where('remissionId', $remission->id)
                    ->update(['invoiceId' => $invoice->id]);
            }

            Log::info('📄 Factura agrupada creada, procediendo a emitir', [
                'invoice_local_id' => $invoice->id,
                'api_data_id' => $invoiceId,
                'remisiones_asociadas' => $remissionIds
            ]);

            // 5. EMITIR (STAMP) LA FACTURA
            $stampResponse = $facturacionService->stampInvoice($invoiceId);

            if ($stampResponse['success']) {
                // ✅ STAMP EXITOSO
                $invoice->update(['status' => 'FACTURADO']);

                // ✅ NUEVO: Actualizar status de todas las cotizaciones asociadas a FACTURADO
                $quotesUpdated = 0;
                foreach ($remisiones as $remission) {
                    if ($remission->quote) {
                        $remission->quote->update(['status' => 'FACTURADO']);
                        $quotesUpdated++;

                        Log::info('📋 Status de cotización actualizado a FACTURADO (agrupada)', [
                            'quote_id' => $remission->quote->id,
                            'quote_consecutive' => $remission->quote->consecutive,
                            'remission_id' => $remission->id,
                            'invoice_number' => $invoiceNumber
                        ]);
                    }
                }

                Log::info('✅ Factura agrupada emitida exitosamente', [
                    'remisiones_ids' => $remissionIds,
                    'invoice_id_alegra' => $invoiceId,
                    'invoice_number' => $invoiceNumber,
                    'remisiones_count' => $remisiones->count(),
                    'quotes_updated' => $quotesUpdated
                ]);
            } else {
                // ❌ STAMP FALLÓ
                $errorMessage = $this->extractStampErrorMessage($stampResponse);
                throw new \Exception("La factura agrupada #{$invoiceNumber} se creó exitosamente pero no se pudo emitir legalmente. Razón: {$errorMessage}");
            }
        } catch (\Exception $e) {
            Log::error('❌ Error en facturarRemisionesAgrupadas', [
                'remisiones_ids' => $remisiones->pluck('id')->toArray(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Agrupar items de múltiples remisiones, sumando cantidades del mismo producto
     */
    private function groupRemissionItems($remisiones): array
    {
        $groupedItems = [];

        foreach ($remisiones as $remission) {
            foreach ($remission->details as $detail) {
                $product = $detail->item;

                if (!$product) {
                    Log::warning('⚠️ Producto no encontrado para detalle en remisión agrupada', [
                        'remission_id' => $remission->id,
                        'detail_id' => $detail->id
                    ]);
                    continue;
                }

                $productId = $product->id;
                $price = floatval($detail->value ?: 0);
                $tax = floatval($detail->tax ?: 0);

                // ✅ NUEVA VALIDACIÓN: Crear clave única por producto + precio + tax
                // Esto permite que el mismo producto con diferentes precios/tax sea tratado como items separados
                $uniqueKey = $productId . '_' . $price . '_' . $tax;

                if (!isset($groupedItems[$uniqueKey])) {
                    // Primera vez que vemos esta combinación producto + precio + tax
                    $groupedItems[$uniqueKey] = [
                        'product' => $product,
                        'product_id' => $productId,
                        'total_quantity' => 0,
                        'price' => $price,
                        'tax' => $tax,
                        'remissions' => [], // Para tracking
                        'unique_key' => $uniqueKey // Para debug
                    ];
                }

                // Validar que precio y tax coincidan (seguridad adicional)
                if ($groupedItems[$uniqueKey]['price'] !== $price || $groupedItems[$uniqueKey]['tax'] !== $tax) {
                    Log::error('❌ Inconsistencia en agrupación de items', [
                        'product_id' => $productId,
                        'product_name' => $product->name,
                        'existing_price' => $groupedItems[$uniqueKey]['price'],
                        'current_price' => $price,
                        'existing_tax' => $groupedItems[$uniqueKey]['tax'],
                        'current_tax' => $tax,
                        'remission_id' => $remission->id
                    ]);
                    continue; // Saltar este item por inconsistencia
                }

                // Sumar la cantidad para esta combinación exacta
                $groupedItems[$uniqueKey]['total_quantity'] += intval($detail->quantity ?: 1);
                $groupedItems[$uniqueKey]['remissions'][] = $remission->id;
            }
        }

        Log::info('📦 Items agrupados con validación precio/tax', [
            'total_combinaciones_unicas' => count($groupedItems),
            'detalle_agrupacion' => array_map(function ($item) {
                return [
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product']->name,
                    'unique_key' => $item['unique_key'],
                    'total_quantity' => $item['total_quantity'],
                    'price' => $item['price'],
                    'tax' => $item['tax'],
                    'from_remissions' => $item['remissions']
                ];
            }, $groupedItems)
        ]);

        return $groupedItems;
    }

    /**
     * Construir datos de factura para múltiples remisiones agrupadas
     */
    private function buildGroupedInvoiceData($remisiones, $groupedItems): array
    {
        // Usar la primera remisión como base para datos del cliente
        $firstRemission = $remisiones->first();
        $quote = $firstRemission->quote;

        // Calcular total de todas las remisiones
        $totalValue = 0;
        foreach ($remisiones as $remission) {
            $totalValue += $this->calculateRemissionTotal($remission);
        }

        // Construir array de métodos de pago para CRÉDITO
        $paymentMethods = [
            [
                'descriptionFormaPago' => 'CREDITO',
                'nombre' => 'CREDITO',
                'valor' => $totalValue
            ]
        ];

        // Construir la factura usando el InvoiceDataBuilder pero con items agrupados
        $invoiceData = InvoiceDataBuilder::buildFromQuote(
            $quote,
            $paymentMethods,
            [], // Sin retenciones
            0   // Sin términos
        );

        // REEMPLAZAR los items con nuestros items agrupados
        $invoiceData['items'] = $this->buildGroupedItemsForAlegra($groupedItems);

        // Agregar información de agrupación para tracking
        $invoiceData['remissionIds'] = $remisiones->pluck('id')->toArray();
        $invoiceData['es_agrupado'] = true;
        $invoiceData['total_remisiones'] = $remisiones->count();

        return $invoiceData;
    }

    /**
     * Construir items agrupados en formato para API de Alegra
     */
    private function buildGroupedItemsForAlegra($groupedItems): array
    {
        $itemsAlegra = [];

        foreach ($groupedItems as $uniqueKey => $itemData) {
            $product = $itemData['product'];
            $quantity = $itemData['total_quantity'];
            $price = $itemData['price'];
            $taxPercentage = $itemData['tax']; // ✅ CORREGIDO: Usar tax real del detalle de remisión

            // Obtener ID de Alegra del producto
            $idAlegra = $product->api_data_id ?? $product->id_alegra ?? $product->alegra_id ?? null;
            if (empty($idAlegra)) {
                Log::warning('⚠️ Producto agrupado sin ID de Alegra', [
                    'product_id' => $product->id,
                    'name' => $product->name
                ]);
                continue;
            }

            // Determinar tax ID basado en el porcentaje real del detalle
            if ($taxPercentage == 5) {
                $taxIdAlegra = '2'; // ID común para IVA 5% en Alegra
            } elseif ($taxPercentage == 19) {
                $taxIdAlegra = '3'; // ID común para IVA 19% en Alegra
            } else {
                $taxIdAlegra = '1'; // ID común para exento/0% en Alegra
            }

            // ✅ CORREGIDO: Calcular precio sin IVA usando el tax real (igual que en InvoiceDataBuilder)
            $originalPriceWithIva = floatval($price);

            if ($taxPercentage > 0) {
                // Producto con IVA - calcular precio base sin IVA
                $taxMultiplier = 1 + ($taxPercentage / 100);
                $targetSubtotal = round($originalPriceWithIva / $taxMultiplier, 2);
                $finalPrice = $targetSubtotal;

                // Verificación con el IVA correcto
                $verificationTotal = $finalPrice * $taxMultiplier;
            } else {
                // Producto exento de IVA
                $finalPrice = $originalPriceWithIva;
                $verificationTotal = $finalPrice;
                $taxMultiplier = 1;
            }

            Log::info('💰 FACTURACIÓN AGRUPADA - Cálculo precio para Alegra', [
                'product_id' => $product->id,
                'product_name' => $product->name ?? 'Sin nombre',
                'original_price_with_iva' => $originalPriceWithIva,
                'tax_percentage_from_remission' => $taxPercentage,
                'tax_multiplier' => $taxMultiplier ?? 1,
                'final_price_after_calculation' => $finalPrice,
                'verification_total' => $verificationTotal ?? $finalPrice,
                'difference' => ($verificationTotal ?? $finalPrice) - $originalPriceWithIva,
                'tax_id_alegra' => $taxIdAlegra,
                'quantity' => $quantity
            ]);

            $itemsAlegra[] = [
                'id' => (string)$idAlegra,
                'name' => (string)($product->name ?: 'Producto sin nombre'),
                'description' => (string)($product->description ?: $product->name ?: 'Producto agrupado'),
                'price' => $finalPrice,
                'quantity' => $quantity, // Cantidad agrupada
                'tax' => [['id' => (string)$taxIdAlegra]],
                'reference' => (string)($product->sku ?: $product->internal_code ?: $product->id)
            ];
        }

        return $itemsAlegra;
    }

    /**
     * Calcular el total de una remisión
     */
    private function calculateRemissionTotal(InvRemissions $remission): float
    {
        return $remission->details->sum(function ($detail) {
            return $detail->quantity * $detail->value;
        });
    }

    /**
     * Extraer mensaje de error más claro del response de stamp
     */
    private function extractStampErrorMessage(array $stampResponse): string
    {
        // Buscar mensaje en diferentes ubicaciones del response
        $message = '';

        if (isset($stampResponse['data']['error_details']['error'][0]['message'])) {
            $message = $stampResponse['data']['error_details']['error'][0]['message'];
        } elseif (isset($stampResponse['data']['message'])) {
            $message = $stampResponse['data']['message'];
        } elseif (isset($stampResponse['message'])) {
            $message = $stampResponse['message'];
        } else {
            return 'Error desconocido en la emisión';
        }

        // Limpiar el mensaje para hacerlo más entendible al usuario
        if (str_contains($message, 'La factura electrónica de venta no se ha podido emitir porque no cumple con las validaciones necesarias:')) {
            // Extraer solo las reglas de validación
            $message = str_replace('La factura electrónica de venta no se ha podido emitir porque no cumple con las validaciones necesarias:', '', $message);
            $message = strip_tags($message); // Quitar HTML
            $message = str_replace(['<ul>', '</ul>', '<li>', '</li>'], ['', '', '• ', "\n"], $message);
            $message = trim($message);
        }

        // Traducir algunos errores comunes
        if (str_contains($message, 'medio de pago informado es invalido')) {
            $message .= ' - Verifique la configuración del método de pago.';
        }

        if (str_contains($message, 'debe existir el grupo de información de identificación del bien o servicio')) {
            $message .= ' - Revise que los productos tengan toda la información requerida.';
        }

        if (str_contains($message, 'nombre informado no corresponde al registrado en el rut')) {
            $message .= ' - Verifique que los datos del cliente estén actualizados en el RUT.';
        }

        return $message;
    }

    /**
     * Validar y notificar sobre items con diferentes precios en facturación agrupada
     */
    private function validateItemPricingForGrouping($remisiones): void
    {
        $productPriceMap = [];
        $duplicatesWithDifferentPrices = [];

        // Analizar todos los items para detectar productos con precios diferentes
        foreach ($remisiones as $remission) {
            foreach ($remission->details as $detail) {
                if (!$detail->item) continue;

                $productId = $detail->item->id;
                $productName = $detail->item->name;
                $price = floatval($detail->value ?: 0);
                $tax = floatval($detail->tax ?: 0);

                $key = $productId;

                if (!isset($productPriceMap[$key])) {
                    $productPriceMap[$key] = [
                        'product_name' => $productName,
                        'prices' => [$price],
                        'taxes' => [$tax],
                        'price_tax_combinations' => ["{$price}_{$tax}"]
                    ];
                } else {
                    $combination = "{$price}_{$tax}";

                    if (!in_array($combination, $productPriceMap[$key]['price_tax_combinations'])) {
                        $productPriceMap[$key]['prices'][] = $price;
                        $productPriceMap[$key]['taxes'][] = $tax;
                        $productPriceMap[$key]['price_tax_combinations'][] = $combination;

                        if (!isset($duplicatesWithDifferentPrices[$key])) {
                            $duplicatesWithDifferentPrices[$key] = $productPriceMap[$key];
                        }
                    }
                }
            }
        }

        if (!empty($duplicatesWithDifferentPrices)) {
            Log::info('ℹ️ Items con diferentes precios/tax detectados en facturación agrupada', [
                'duplicates_count' => count($duplicatesWithDifferentPrices),
                'products' => array_map(function ($data, $productId) {
                    return [
                        'product_id' => $productId,
                        'product_name' => $data['product_name'],
                        'price_tax_combinations' => $data['price_tax_combinations'],
                        'total_variations' => count($data['price_tax_combinations'])
                    ];
                }, $duplicatesWithDifferentPrices, array_keys($duplicatesWithDifferentPrices))
            ]);

            // Crear mensaje informativo para el usuario
            $productNames = array_map(function ($data) {
                return $data['product_name'];
            }, $duplicatesWithDifferentPrices);

            $message = count($productNames) === 1
                ? "ℹ️ El producto '" . $productNames[0] . "' tiene diferentes precios/IVA entre remisiones y se facturará por separado."
                : "ℹ️ Los productos: " . implode(', ', array_slice($productNames, 0, 3)) .
                (count($productNames) > 3 ? ' y otros' : '') .
                " tienen diferentes precios/IVA entre remisiones y se facturarán por separado.";

            // Opcional: Mostrar notificación al usuario
            // $this->dispatch('show-toast', [
            //     'type' => 'info',
            //     'message' => $message
            // ]);
        }
    }

    /**
     * Cambiar el estado de una remisión
     */
    public function changeStatus($remissionId)
    {
        $this->ensureTenantConnection();

        try {
            $remission = InvRemissions::findOrFail($remissionId);

            $currentStatus = $remission->status;

            // No permitir cambios de estado si ya está ENTREGADO
            if ($currentStatus === 'ENTREGADO') {
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'No se puede cambiar el estado de una remisión ya entregada.'
                ]);
                return;
            }

            // Definir la secuencia de estados
            $statusSequence = [
                'REGISTRADO' => 'ALISTAMIENTO',
                'ALISTAMIENTO' => 'EMPACADO',
                'EMPACADO' => 'EN RECORRIDO',
                'EN RECORRIDO' => 'ENTREGADO'
            ];

            $nextStatus = $statusSequence[$currentStatus] ?? null;

            if (!$nextStatus) {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'Estado no válido para cambio.'
                ]);
                return;
            }

            // CARTERA: Validaciones de flujo dinámico entre Cartera y Pedidos
            $remission->load('authorizations');
            $hasEmpaque = $remission->authorizations->where('auth_type', 'empaque')->where('status', 1)->isNotEmpty();
            $hasDespacho = $remission->authorizations->where('auth_type', 'despacho')->where('status', 1)->isNotEmpty();
            $hasPago = $remission->authorizations->where('auth_type', 'pago')->where('status', 1)->isNotEmpty();

            // Si tiene pago confirmado, se libera todo el flujo (prioridad máxima)
            if (!$hasPago) {
                // 1. Si intenta pasar de ALISTAMIENTO a EMPACADO, requiere Despacho Autorizado
                if ($currentStatus === 'ALISTAMIENTO' && $nextStatus === 'EMPACADO') {
                    if (!$hasDespacho) {
                        $this->dispatch('show-toast', [
                            'type' => 'warning',
                            'message' => 'BLOQUEO DE CARTERA: No se puede avanzar a EMPACADO sin la autorización de DESPACHO.'
                        ]);
                        return;
                    }
                }

                // 2. Si intenta pasar de REGISTRADO a ALISTAMIENTO, requiere al menos Empaque o Despacho
                if ($currentStatus === 'REGISTRADO' && $nextStatus === 'ALISTAMIENTO') {
                    if (!$hasEmpaque && !$hasDespacho) {
                        $this->dispatch('show-toast', [
                            'type' => 'warning',
                            'message' => 'BLOQUEO DE CARTERA: Se requiere autorización de EMPAQUE para iniciar el alistamiento.'
                        ]);
                        return;
                    }
                }
            }

            // Actualizar el estado
            $remission->update(['status' => $nextStatus]);

            Log::info('🔄 Estado de remisión cambiado', [
                'remission_id' => $remissionId,
                'from_status' => $currentStatus,
                'to_status' => $nextStatus
            ]);

            // Si el nuevo estado es ALISTAMIENTO, imprimir la remisión
            if ($nextStatus === 'ALISTAMIENTO') {
                $this->printRemission($remissionId);
            }

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => "Estado cambiado de {$currentStatus} a {$nextStatus}" .
                    ($nextStatus === 'ALISTAMIENTO' ? '. Remisión enviada a impresión.' : '')
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error cambiando estado de remisión', [
                'remission_id' => $remissionId,
                'error' => $e->getMessage()
            ]);

            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al cambiar el estado de la remisión: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Propiedad computada para verificar si el módulo de facturación está activo
     */
    public function getIsInvoiceModuleActiveProperty()
    {
        try {
            $this->ensureTenantConnection();

            $result = DB::connection('tenant')
                ->table('cnf_company_options')
                ->where('option_id', 8) // ID para FACTURACION_ELECTRONICA o similar
                ->value('value');

            return (bool) $result;
        } catch (\Exception $e) {
            Log::error('❌ Error verificando módulo de facturación en Remissions', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function openConfirmationsModal()
    {
        $this->showConfirmationsModal = true;
    }

    public function closeConfirmationsModal()
    {
        $this->showConfirmationsModal = false;
    }

    #[On('confirmation-updated')]
    public function refreshCounts()
    {
        // El render se encargará de recalcular summaryCounts
    }

    /**
     * Solicitar una devolución para un item de remisión
     */
    public function solicitarDevolucion($detailId)
    {
        $this->ensureTenantConnection();

        try {
            $detail = \App\Models\Tenant\Remissions\InvDetailRemissions::findOrFail($detailId);

            // Verificar si ya existe una devolución para este item
            $exists = VntReturn::where('remission_id', $detail->doc)
                ->where('item_id', $detail->id_producto)
                ->where('status', '<', 4) // No finalizada
                ->exists();

            if ($exists) {
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'Ya existe una solicitud de devolución pendiente para este producto.'
                ]);
                return;
            }

            // Aquí podríamos disparar un SweetAlert para pedir la observación
            // Por simplicidad en este paso, crearemos una solicitud base y pediremos editarla en el módulo de devoluciones
            $vntReturn = VntReturn::create([
                'remission_id' => $detail->doc,
                'item_id' => $detail->id_producto,
                'user_id' => Auth::id(),
                'requested_at' => now(),
                'original_qty' => $detail->quantity,
                'commercial_qty' => $detail->quantity, // Por defecto toda la cantidad
                'status' => 1, // Comercial
                'obs_commercial' => 'Solicitud iniciada desde el panel de remisiones.',
            ]);

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Solicitud de devolución creada correctamente. Puede procesarla en el módulo de Devoluciones.'
            ]);

            // Redirigir al módulo de devoluciones (opcional)
            // return redirect()->route('tenant.returns');

        } catch (\Exception $e) {
            Log::error('❌ Error creando solicitud de devolución', [
                'detail_id' => $detailId,
                'error' => $e->getMessage()
            ]);

            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Abrir el modal de registro de devolución detallada
     */
    public function openReturnRegistration($remissionId)
    {
        $this->dispatch('openReturnRegistration', $remissionId);
    }
}
