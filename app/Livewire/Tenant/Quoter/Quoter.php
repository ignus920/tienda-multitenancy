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
use App\Models\Tenant\Invoices\VntInvoices;
use App\Services\Facturacion\FacturacionService;
use App\Services\Facturacion\InvoiceDataBuilder;
use App\Services\Facturacion\TenantConfigManager;
use Illuminate\Support\Facades\DB;
use App\Traits\Livewire\HasDynamicButtons;
use App\Traits\Livewire\WithExport;

class Quoter extends Component
{
    use WithPagination, HasCompanyConfiguration, HasDynamicButtons, WithExport;

    public $search = '';
    public $filterNit = '';
    public $filterName = '';
    public $filterConsecutive = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $viewType = 'desktop'; // 'desktop' o 'mobile'
    public $perPage = 10; // Registros por página
    public $showDetailModal = false;
    public $selectedQuoteId = null;
    public $moduleKey = 'quoter';



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

        // Inicializar fechas por defecto (±7 días como en Pedidos)
        $this->filterDateFrom = now()->subDays(7)->format('Y-m-d');
        $this->filterDateTo = now()->addDays(7)->format('Y-m-d');

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

    /**
     * Computed property para acceder al modelo selectedQuote
     * Carga el modelo con la conexión tenant correcta establecida
     */
    public function getSelectedQuoteProperty()
    {
        if (!$this->selectedQuoteId) {
            return null;
        }

        $this->ensureTenantConnection();

        $quote = VntQuote::with(['customer', 'detalles.item', 'warehouse', 'branch.company'])
            ->find($this->selectedQuoteId);

        // Agregar el storage_name al modelo
        if ($quote) {
            $quote->storage_name = $quote->getStorageName();
        }

        return $quote;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['filterNit', 'filterName', 'filterConsecutive', 'filterDateFrom', 'filterDateTo'])) {
            $this->resetPage();
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterNit', 'filterName', 'filterConsecutive']);
        
        // Restablecer fechas por defecto al limpiar
        $this->filterDateFrom = now()->subDays(7)->format('Y-m-d');
        $this->filterDateTo = now()->addDays(7)->format('Y-m-d');
        
        $this->resetPage();
    }

    public function nuevaCotizacion()
    {
        // Limpiar el carrito de la sesión para que el cotizador empiece vacío
        session()->forget('quoter_items');

        // Determinar la ruta correcta según el tipo de vista actual para evitar fallos de detección
        $routeName = $this->viewType === 'mobile'
            ? 'tenant.quoter.products.mobile'
            : 'tenant.quoter.products.desktop';

        return redirect()->route($routeName);
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
        $this->selectedQuoteId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedQuoteId = null;
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
                $quote->load(['customer.company', 'customer.warehouse.city']);
                Log::info('👤 Cliente cargado', [
                    'customer_id' => $quote->customerId,
                    'has_company' => $quote->customer && $quote->customer->company ? 'YES' : 'NO',
                    'has_warehouse' => $quote->customer && $quote->customer->warehouse ? 'YES' : 'NO',
                    'has_city' => $quote->customer && $quote->customer->warehouse && $quote->customer->warehouse->city ? 'YES' : 'NO'
                ]);
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

            $tableName = ($quote->status === 'REMISIÓN') ? 'inv_detail_remissions' : 'vnt_detail_quotes';
            $tableNameId = ($quote->status === 'REMISIÓN') ? 'remissionId' : 'quoteId';
            // Calcular el peso total de los items
            $totalWeight = DB::connection('tenant')->table($tableName)
                ->join('inv_items_dimensions', $tableName . '.itemId', '=', 'inv_items_dimensions.item_id')
                ->where($tableName . '.' . $tableNameId, $id)
                ->sum('inv_items_dimensions.weight');


            Log::info('⚖️ Peso total calculado:', ['totalWeight' => $totalWeight]);

            $observations = DB::connection('tenant')->table('inv_remissions')
                ->where('quoteId', $id)
                ->select('observations_delivery', 'obs')->first();

            // Determinar el formato de impresión según configuración
            $printFormat = $this->getPrintCopiesLimit(); // 0 = POS Simple, 1 = Institucional
            Log::info('🎯 Formato determinado desde configuración', ['printFormat' => $printFormat]);

            // Determinar el título del documento (COTIZACIÓN o REMISIÓN)
            $documentTitle = ($quote->status === 'REMISIÓN') ? 'REMISIÓN' : 'COTIZACIÓN';
            Log::info('📄 Título del documento:', ['title' => $documentTitle]);

            // Datos para la vista
            $data = [
                'quote' => $quote,
                'customer' => $quote->customer,
                'company' => $company,
                'documentTitle' => $documentTitle,
                'showQR' => true, // Opcional: mostrar código QR
                'defaultObservations' => 'Observaciones por defecto',
                'totalWeight' => $totalWeight,
                'observations_delivery' => $observations->observations_delivery ?? null,
                'obs' => $observations->obs ?? null,
                'showValues' => true,
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
     * Método para imprimir factura (basado en una cotización facturada)
     * Utiliza el api_data_id si existe, o el endpoint de Vista Previa (Preview).
     */
    public function printInvoice($id)
    {
        Log::info('🖨️ printInvoice llamado', ['quote_id' => $id]);

        $this->ensureTenantConnection();
        $this->initializeCompanyConfiguration();

        try {
            $quote = VntQuote::with(['detalles.item', 'customer', 'warehouse'])->findOrFail($id);

            // 🔍 Buscar factura local para obtener el ID de Alegra (api_data_id)
            $invoice = VntInvoices::where('quoteId', $id)->first();

            Log::info('🔍 Datos de factura local encontrados', [
                'has_invoice' => !is_null($invoice),
                'invoice_id' => $invoice ? $invoice->id : null,
                'api_data_id' => $invoice ? $invoice->api_data_id : null
            ]);

            // 1. Obtener configuración del tenant
            $tenant = session('tenant_id') ? Tenant::find(session('tenant_id')) : null;
            $hasConfig = $tenant && TenantConfigManager::hasFacturacionConfig($tenant);

            Log::info('⚙️ Verificación de configuración de facturación', [
                'has_config' => $hasConfig,
                'tenant_id' => $tenant ? $tenant->id : 'No encontrado en sesión'
            ]);

            if ($hasConfig) {
                $facturacionService = FacturacionService::forTenant($tenant);

                // 🚀 PRIORIDAD 1: Si ya tiene api_data_id (Factura ya creada en Alegra)
                if ($invoice && $invoice->api_data_id) {
                    Log::info('🔗 Usando api_data_id existente para obtener PDF', ['api_id' => $invoice->api_data_id]);

                    $apiResponse = $facturacionService->getInvoicePdf($invoice->api_data_id);

                    // 🔍 Analizar estructura de respuesta para depurar
                    $respData = $apiResponse['data'] ?? [];
                    Log::info('📦 Estructura de respuesta PDF recibida', [
                        'success' => $apiResponse['success'] ?? false,
                        'keys_root' => array_keys($apiResponse),
                        'keys_data' => is_array($respData) ? array_keys($respData) : 'not_array',
                        'has_pdf_in_data' => isset($respData['pdf']),
                        'has_publicUrl_in_data' => isset($respData['publicUrl']),
                        'has_publicUrl_in_nested_data' => isset($respData['data']['publicUrl'])
                    ]);

                    // Intentar obtener URL de varios posibles campos
                    $printUrl = $respData['pdf'] ?? // Según snippet del usuario
                        $respData['publicUrl'] ?? // Estándar Alegra
                        ($respData['data']['publicUrl'] ?? null); // Anidado

                    if ($apiResponse['success'] && !empty($printUrl)) {
                        Log::info('✅ URL de documento encontrada', ['url' => $printUrl]);

                        $this->dispatch('open-print-window', [
                            'url' => $printUrl,
                            'format' => 'carta'
                        ]);
                        return;
                    } else {
                        Log::warning('⚠️ No se obtuvo URL válida vía endpoint de PDF.', [
                            'response' => $apiResponse
                        ]);
                    }
                }

                // 🚀 PRIORIDAD 2: Vista Previa (Preview) de Alegra
                Log::info('📝 Intentando obtener vista previa (API Preview)');

                // Obtener pagos si existen
                $paymentMethods = [];
                if ($invoice) {
                    $payments = DB::connection('tenant')->table('vnt_invoice_payments')
                        ->join('vnt_method_payments', 'vnt_invoice_payments.methodPaymentId', '=', 'vnt_method_payments.id')
                        ->where('vnt_invoice_payments.invoiceId', $invoice->id)
                        ->select('vnt_method_payments.name as method', 'vnt_invoice_payments.value as value')
                        ->get();

                    foreach ($payments as $p) {
                        $paymentMethods[] = [
                            'descriptionFormaPago' => $p->method,
                            'nombre' => $p->method,
                            'valor' => $p->value,
                            'method' => $p->method
                        ];
                    }
                }

                $invoiceData = InvoiceDataBuilder::buildFromQuote($quote, $paymentMethods);
                $apiResponse = $facturacionService->getInvoicePreview($invoiceData);

                if ($apiResponse['success'] && isset($apiResponse['data']['publicUrl'])) {
                    $printUrl = $apiResponse['data']['publicUrl'];
                    Log::info('✅ Vista previa obtenida correctamente', ['url' => $printUrl]);

                    $this->dispatch('open-print-window', [
                        'url' => $printUrl,
                        'format' => 'carta'
                    ]);
                    return;
                } else {
                    Log::warning('❌ Falló la vista previa de Alegra', ['response' => $apiResponse]);
                }
            }

            // 🚀 FALLBACK: Impresión local
            Log::warning('🏠 Ejecutando caída a impresión local');
            return $this->printInvoiceLocal($id);
        } catch (\Exception $e) {
            Log::error('❌ Error crítico en printInvoice: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->printInvoiceLocal($id);
        }
    }

    /**
     * Respaldo de impresión local (Generación de HTML interno)
     */
    private function printInvoiceLocal($id)
    {
        Log::info('🏠 Ejecutando impresión local de factura', ['quote_id' => $id]);

        try {
            $quote = VntQuote::findOrFail($id);
            $quote->load(['detalles.item', 'customer']);

            // Intentar obtener el número de factura real
            $invoice = VntInvoices::where('quoteId', $id)->first();
            if ($invoice && $invoice->invoiceNumber) {
                $quote->consecutive = $invoice->invoiceNumber;
            }

            $company = $this->getCompanyInfo($quote);
            $printFormat = $this->getPrintCopiesLimit();

            $data = [
                'quote' => $quote,
                'customer' => $quote->customer,
                'company' => $company,
                'documentTitle' => 'FACTURA',
                'showQR' => true,
                'defaultObservations' => 'Factura electrónica (Copia Local)',
                'showValues' => true
            ];

            $viewName = ($printFormat === 1)
                ? 'livewire.tenant.quoter.print.print-carta'
                : 'livewire.tenant.quoter.print.print-pos';

            $html = view($viewName, $data)->render();

            $tempFileName = 'quote_' . $id . '_' . time() . '.html';
            $tempPath = storage_path('app/temp/' . $tempFileName);

            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }

            file_put_contents($tempPath, $html);

            $printUrl = route('quoter.print.temp', ['file' => $tempFileName]);

            $this->dispatch('open-print-window', [
                'url' => $printUrl,
                'format' => $printFormat === 1 ? 'carta' : 'pos'
            ]);

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Factura local preparada'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error en printInvoiceLocal: ' . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al preparar impresión local: ' . $e->getMessage()
            ]);
        }
    }

    private function getCompanyInfo($quote = null)
    {
        Log::info('🏢 getCompanyInfo llamado con consulta optimizada');

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
        $this->ensureTenantConnection();

        $quotes = $this->getQuotesQuery()->paginate($this->perPage);

        // Agregar el nombre de la bodega a cada cotización
        $quotes->getCollection()->transform(function ($quote) {
            Log::info('🔄 Procesando cotización para obtener storage_name', [
                'quote_id' => $quote->id,
                'consecutive' => $quote->consecutive,
                'warehouseId' => $quote->warehouseId
            ]);

            $storageName = $quote->getStorageName();

            Log::info('✅ Storage name obtenido', [
                'quote_id' => $quote->id,
                'storage_name' => $storageName
            ]);

            $quote->storage_name = $storageName;
            return $quote;
        });

        Log::info('✅ Render completado - Todas las cotizaciones procesadas');

        $viewName = $this->viewType === 'mobile'
            ? 'livewire.tenant.quoter.components.quoter-mobile'
            : 'livewire.tenant.quoter.components.quoter-desktop';

        return view($viewName, [
            'quotes' => $quotes
        ]);
    }

    /**
     * Obtiene la consulta filtrada de cotizaciones
     */
    private function getQuotesQuery()
    {
        $this->ensureTenantConnection();

        $user = auth()->user();
        $storeId = null;

        if ($user && $user->contact_id) {
            $contact = \App\Models\Central\VntContact::on('central')->find($user->contact_id);
            if ($contact) {
                $storeId = $contact->store;
            }
        }

        Log::info('📋 Quoter getQuotesQuery() - Iniciando carga de cotizaciones', [
            'search' => $this->search,
            'perPage' => $this->perPage,
            'viewType' => $this->viewType,
            'user_id' => $user?->id,
            'contact_id' => $user?->contact_id,
            'store_id' => $storeId
        ]);

        return VntQuote::with(['customer', 'branch.company', 'detalles'])
            ->when($storeId, function ($query) use ($storeId) {
                $query->where('warehouseId', $storeId);
            })
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('consecutive', 'like', '%' . $this->search . '%')
                        ->orWhere('status', 'like', '%' . $this->search . '%')
                        ->orWhere('typeQuote', 'like', '%' . $this->search . '%')
                        ->orWhere('observations', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', function ($subQ) {
                            $subQ->where('firstName', 'like', '%' . $this->search . '%')
                                ->orWhere('secondName', 'like', '%' . $this->search . '%')
                                ->orWhere('lastName', 'like', '%' . $this->search . '%')
                                ->orWhere('secondLastName', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%')
                                ->orWhere('business_phone', 'like', '%' . $this->search . '%')
                                ->orWhere('personal_phone', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->filterNit, function ($query) {
                $query->whereHas('customer', function ($q) {
                    $q->where('identification', 'like', '%' . $this->filterNit . '%');
                });
            })
            ->when($this->filterName, function ($query) {
                $query->whereHas('customer', function ($q) {
                    $q->where(DB::raw("CONCAT(COALESCE(firstName,''), ' ', COALESCE(secondName,''), ' ', COALESCE(lastName,''), ' ', COALESCE(secondLastName,''))"), 'like', '%' . $this->filterName . '%');
                });
            })
            ->when($this->filterConsecutive, function ($query) {
                $query->where('consecutive', 'like', '%' . $this->filterConsecutive . '%');
            })
            ->when($this->filterDateFrom, function ($query) {
                $query->whereDate('created_at', '>=', $this->filterDateFrom);
            })
            ->when($this->filterDateTo, function ($query) {
                $query->whereDate('created_at', '<=', $this->filterDateTo);
            })
            ->orderBy('created_at', 'desc');
    }

    /**
     * Métodos de soporte para exportación
     */
    protected function getExportData()
    {
        $this->ensureTenantConnection();
        return $this->getQuotesQuery()->get();
    }

    protected function getExportHeadings(): array
    {
        return [
            'COTIZACIÓN #',
            'CLIENTE',
            'TIPO',
            'ESTADO',
            'BODEGA',
            'VENDEDOR',
            'TELÉFONO',
            'FECHA'
        ];
    }

    protected function getExportMapping()
    {
        return function ($quote) {
            return [
                '#' . $quote->consecutive,
                $quote->customer_name,
                $quote->typeQuote,
                $quote->status,
                $quote->getStorageName(),
                $quote->seller_name,
                ($quote->customer && $quote->customer->primary_phone) ? $quote->customer->primary_phone : 'Sin teléfono',
                $quote->created_at ? $quote->created_at->format('d/m/Y H:i') : 'N/A'
            ];
        };
    }

    protected function getExportFilename(): string
    {
        return 'cotizaciones_' . now()->format('Y-m-d_His');
    }
}
