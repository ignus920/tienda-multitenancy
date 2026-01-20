<?php

namespace App\Livewire\Tenant\Quoter;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use App\Models\Tenant\Quoter\VntQuote;
use App\Models\Central\VntWarehouse;
use App\Traits\HasCompanyConfiguration;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Remissions\InvRemissions;


class Quoter extends Component
{
    use WithPagination, HasCompanyConfiguration;

    public $search = '';
    public $viewType = 'desktop'; // 'desktop' o 'mobile'
    public $perPage = 10; // Registros por página
    public $showDetailModal = false;
    public $selectedQuote = null;




    protected $paginationTheme = 'tailwind';

    public function boot()
    {
        // Establecer conexión tenant lo más pronto posible (antes de la hidratación de modelos)
        $this->ensureTenantConnection();
    }

    public function mount($viewType = null)
    {
        // Obtener viewType desde parámetro, ruta o usar desktop por defecto
        $this->viewType = $viewType ?? request()->route('viewType', 'desktop');

        // Establecer conexión tenant antes de cualquier consulta
        $this->ensureTenantConnection();

        // Inicializar configuración de empresa
        $this->initializeCompanyConfiguration();

        // DEBUG: Limpiar caché para testing
        $this->clearConfigurationCache();

        // DEBUG: Log para verificar inicialización
        Log::info('🔍 Quoter mount() ejecutado', [
            'viewType' => $this->viewType,
            'currentCompanyId' => $this->currentCompanyId,
            'currentPlainId' => $this->currentPlainId,
            'configService_exists' => $this->configService ? 'YES' : 'NO'
        ]);
    }

    /**
     * Método que se ejecuta cuando el componente se hidrata (después de navegación)
     */
    public function hydrate()
    {
        Log::info('💧 Quoter hydrate() ejecutado - Re-estableciendo conexiones');

        // Re-establecer conexión tenant
        $this->ensureTenantConnection();

        // Re-inicializar configuración de empresa
        $this->initializeCompanyConfiguration();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function nuevaCotizacion()
    {
        // Limpiar el carrito de la sesión para que el cotizador empiece vacío
        session()->forget('quoter_items');
        
        return redirect('/tenant/quoter/products');
    }

    public function eliminar($id)
    {
        $this->ensureTenantConnection();
        $quote = VntQuote::find($id);
        if ($quote) {
            $quote->delete();
            session()->flash('message', 'Cotización eliminada correctamente.');
        }
    }

    /**
     * Redirige al cotizador para editar una cotización existente
     * Este método se ejecuta cuando el usuario hace clic en el botón "Editar"
     *
     * @param int $id ID de la cotización a editar
     * @return \Illuminate\Http\RedirectResponse
     */
    public function editarCotizacion($id)
    {
        // Determinar la ruta correcta según el tipo de vista (móvil o escritorio)
        $routeName = $this->viewType === 'mobile'
            ? 'tenant.quoter.products.mobile.edit'    // Ruta para vista móvil
            : 'tenant.quoter.products.desktop.edit';  // Ruta para vista escritorio

        // Redirigir al cotizador con el ID de la cotización para cargarla y editarla
        return redirect()->route($routeName, ['quoteId' => $id]);
    }

    /**
     * Redirige directamente al carrito de compras (ProductQuoter) para editar una cotización
     * Este método se usa ÚNICAMENTE en vista móvil para ir directo al carrito
     *
     * @param int $id ID de la cotización a editar
     * @return \Illuminate\Http\RedirectResponse
     */
    public function irAlCarrito($id)
    {
        // Solo funciona en vista móvil
        if ($this->viewType !== 'mobile') {
            return $this->editarCotizacion($id);
        }

        // Redirigir directamente al carrito móvil con la cotización cargada
        return redirect()->route('tenant.quoter.products.mobile.edit', ['quoteId' => $id]);
    }

    /**
     * Verifica tipo de impresion (opción 3)
     */
    // public function canPrint(): bool
    //  {
    //     $result = $this->isOptionEnabled(3);
    //      $value = $this->getOptionValue(3);

    //      //DEBUG: Log detallado de verificación
    //      Log::info('🔍 canPrint() verificación', [
    //          'companyId' => $this->currentCompanyId,
    //          'option_id' => 3,
    //          'result' => $result ? 'TRUE' : 'FALSE',
    //         'option_value' => $value,
    //         'configService_exists' => $this->configService ? 'YES' : 'NO',
    //         'method_called' => 'isOptionEnabled(3) y getOptionValue(3)'
    //     ]);
    //     return $result;
    //  }

    /**
     * Obtiene el tipo de impresion
     */
    public function getPrintCopiesLimit(): int
    {
        $this->ensureTenantConnection();
        Log::info('🔍 getPrintCopiesLimit() - Inicio del debug', [
            'companyId' => $this->currentCompanyId ?? 'NULL',
            'configService_exists' => isset($this->configService) ? 'YES' : 'NO',
            'method' => 'getPrintCopiesLimit()'
        ]);

        try {
            $value = $this->getOptionValue(3);

            Log::info('📊 getPrintCopiesLimit() - Valor obtenido', [
                'raw_value' => $value,
                'value_type' => gettype($value),
                'is_null' => $value === null ? 'YES' : 'NO',
                'final_return' => $value ?? 0
            ]);

            $finalValue = $value ?? 0;

            Log::info('✅ getPrintCopiesLimit() - Resultado final', [
                'final_value' => $finalValue,
                'format_description' => $finalValue == 0 ? 'POS (térmica 80mm)' : 'Carta (institucional)',
                'option_3_explanation' => '0=POS, 1=Institucional'
            ]);

            return $finalValue;

        } catch (\Exception $e) {
            Log::error('❌ getPrintCopiesLimit() - Error al obtener valor', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 0; // Default a POS en caso de error
        }
     }

    public function viewDetails($id)
    {
        $this->ensureTenantConnection();
        $this->selectedQuote = VntQuote::with(['customer', 'detalles.item', 'warehouse'])->find($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedQuote = null;
    }




     

    /**
     * Método para imprimir cotización
     * Determina el formato según la configuración:
     * - Valor 0: POS Simple (Tirilla 80mm)
     * - Valor 1: POS Institucional (Carta)
     */
    public function printQuote($id)
    {
        // Debug: Log para verificar que el método se está llamando
        Log::info('🖨️ printQuote llamado', ['quote_id' => $id]);

        // Asegurar que todas las conexiones estén establecidas
        $this->ensureTenantConnection();
        $this->initializeCompanyConfiguration();

        try {
            Log::info('🔄 Iniciando carga de cotización...');

            // Cargar la cotización paso a paso para debug
            Log::info('🔄 Cargando cotización básica...');
            $quote = VntQuote::findOrFail($id);
            Log::info('📄 Cotización básica cargada', ['consecutive' => $quote->consecutive]);

            Log::info('🔄 Cargando detalles...');
            try {
                $quote->load('detalles');
                Log::info('📋 Detalles cargados', ['count' => $quote->detalles->count()]);
            } catch (\Exception $detailError) {
                Log::error('❌ Error cargando detalles', ['error' => $detailError->getMessage()]);
                throw $detailError;
            }

            Log::info('🔄 Cargando cliente...');
            try {
                $quote->load('customer');
                Log::info('👤 Cliente cargado', ['customer_id' => $quote->customerId]);
            } catch (\Exception $customerError) {
                Log::error('❌ Error cargando cliente', ['error' => $customerError->getMessage()]);
                // Continuar sin cliente para debug
                $quote->customer = null;
            }

            // Nota: No cargamos warehouse aquí porque se consultará directamente desde central en getCompanyInfo()
            Log::info('🔄 WarehouseId de la cotización: ' . $quote->warehouseId);

            Log::info('🔄 Cargando items de los detalles...');
            try {
                $quote->load('detalles.item');
                Log::info('📦 Items cargados');

                // Debug: verificar si hay items null
                $nullItems = $quote->detalles->whereNull('item')->count();
                if ($nullItems > 0) {
                    Log::warning('⚠️ Hay items null', ['null_count' => $nullItems]);
                }
            } catch (\Exception $itemError) {
                Log::error('❌ Error cargando items', ['error' => $itemError->getMessage()]);
            }

            // Obtener información de la empresa
            $company = $this->getCompanyInfo($quote);
            Log::info('🏢 Empresa cargada', ['company' => $company->businessName ?? 'N/A']);

            // Determinar el formato de impresión según configuración
            $printFormat = $this->getPrintCopiesLimit(); // 0 = POS Simple, 1 = Institucional
            Log::info('🎯 Formato determinado desde configuración', ['printFormat' => $printFormat]);

            // Datos para la vista
            $data = [
                'quote' => $quote,
                'customer' => $quote->customer,
                'company' => $company,
                'showQR' => true, // Opcional: mostrar código QR
                'defaultObservations' => 'Observaciones por defecto'
            ];
            Log::info('📝 Datos preparados para la vista');

            // Seleccionar la vista según el formato
            $viewName = ($printFormat === 1)
                ? 'livewire.tenant.quoter.print.print-carta'
                : 'livewire.tenant.quoter.print.print-pos';
            Log::info('🎨 Vista seleccionada', ['viewName' => $viewName]);

            // Generar el HTML y redirigir a nueva ventana para impresión
            Log::info('🔄 Iniciando generación de HTML...');

            try {
                $html = view($viewName, $data)->render();
                Log::info('✅ HTML generado exitosamente', ['length' => strlen($html)]);
            } catch (\Exception $viewError) {
                Log::error('❌ Error generando vista', ['error' => $viewError->getMessage()]);
                throw $viewError;
            }

            // Guardar temporalmente el HTML para la impresión
            $tempFileName = 'quote_' . $id . '_' . time() . '.html';
            $tempPath = storage_path('app/temp/' . $tempFileName);
            Log::info('📁 Archivo temporal', ['fileName' => $tempFileName, 'path' => $tempPath]);

            // Crear directorio si no existe
            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
                Log::info('📂 Directorio temp creado');
            }

            file_put_contents($tempPath, $html);
            Log::info('💾 Archivo guardado', ['size' => filesize($tempPath) . ' bytes']);

            // Generar la URL del archivo
            $printUrl = route('quoter.print.temp', ['file' => $tempFileName]);
            Log::info('🔗 URL generada', ['url' => $printUrl]);

            // Dispatch evento para abrir ventana de impresión
            $this->dispatch('open-print-window', [
                'url' => $printUrl,
                'format' => $printFormat === 1 ? 'carta' : 'pos'
            ]);
            Log::info('🚀 Evento dispatch enviado');

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Cotización #' . $quote->consecutive . ' preparada para impresión (' . ($printFormat === 1 ? 'Formato Carta' : 'Formato POS') . ')'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al preparar impresión: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener información de la empresa para los documentos
     */
    private function getCompanyInfo($quote = null)
    {
        Log::info('🏢 getCompanyInfo llamado');

        // Intentar obtener información del warehouse desde la base central
        if ($quote && $quote->warehouseId) {
            Log::info('🏢 Obteniendo warehouse desde base central', ['warehouse_id' => $quote->warehouseId]);

            try {
                // Consultar directamente desde la base central usando el modelo VntWarehouse
                $warehouse = VntWarehouse::find($quote->warehouseId);

                if ($warehouse) {
                    Log::info('🏢 Warehouse encontrado en central', [
                        'id' => $warehouse->id,
                        'name' => $warehouse->name,
                        'address' => $warehouse->address
                    ]);

                    $companyData = [
                        'businessName' => $warehouse->name ?? 'EMPRESA DE PRUEBA',
                        'firstName' => 'Admin',
                        'lastName' => 'Sistema',
                        'identification' => '123456789',
                        'billingAddress' => $warehouse->address ?? 'Dirección de prueba',
                        'phone' => '1234567890',
                        'billingEmail' => 'test@empresa.com'
                    ];

                    Log::info('🏢 Datos empresa obtenidos del warehouse central', $companyData);
                } else {
                    Log::warning('⚠️ Warehouse no encontrado en central con ID: ' . $quote->warehouseId);
                    throw new \Exception('Warehouse no encontrado');
                }
            } catch (\Exception $e) {
                Log::error('❌ Error consultando warehouse central: ' . $e->getMessage());

                // Datos por defecto si hay error
                $companyData = [
                    'businessName' => 'EMPRESA DE PRUEBA',
                    'firstName' => 'Admin',
                    'lastName' => 'Sistema',
                    'identification' => '123456789',
                    'billingAddress' => 'Dirección de prueba',
                    'phone' => '1234567890',
                    'billingEmail' => 'test@empresa.com'
                ];
            }
        } else {
            Log::warning('⚠️ No se encontró warehouseId en la cotización, usando datos por defecto');

            // Datos por defecto si no hay warehouse
            $companyData = [
                'businessName' => 'EMPRESA DE PRUEBA',
                'firstName' => 'Admin',
                'lastName' => 'Sistema',
                'identification' => '123456789',
                'billingAddress' => 'Dirección de prueba',
                'phone' => '1234567890',
                'billingEmail' => 'test@empresa.com'
            ];
        }

        Log::info('🏢 Datos empresa preparados', $companyData);

        return (object) $companyData;
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

    public function render()
    {
        // Asegurar conexión tenant activa
        $this->ensureTenantConnection();

        // Cargar cotizaciones con sus relaciones
        $quotes = VntQuote::with(['customer', 'warehouse.contacts', 'branch', 'detalles'])
            ->when($this->search, function ($query) {
                $query->where('consecutive', 'like', '%' . $this->search . '%')
                    ->orWhere('status', 'like', '%' . $this->search . '%')
                    ->orWhere('typeQuote', 'like', '%' . $this->search . '%')
                    ->orWhere('observations', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('businessName', 'like', '%' . $this->search . '%')
                          ->orWhere('firstName', 'like', '%' . $this->search . '%')
                          ->orWhere('lastName', 'like', '%' . $this->search . '%')
                          ->orWhere('identification', 'like', '%' . $this->search . '%')
                          ->orWhere('billingEmail', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('warehouse', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('address', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        $viewName = $this->viewType === 'mobile'
            ? 'livewire.tenant.quoter.components.quoter-mobile'
            : 'livewire.tenant.quoter.components.quoter-desktop';

        return view($viewName, [
            'quotes' => $quotes
        ]);
    }
}