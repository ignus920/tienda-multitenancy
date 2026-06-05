<?php

namespace App\Livewire\Tenant\Invoices;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Tenant\Invoices\VntInvoices;
use App\Models\Tenant\Invoices\VntInvoicesXsales;
use App\Models\Tenant\Remissions\InvRemissions;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\Facturacion\FacturacionService;
use App\Services\Facturacion\TenantConfigManager;
use App\Traits\HasCompanyConfiguration;
use App\Models\Tenant\Items\InvItemsStore;
use App\Traits\Livewire\HasDynamicButtons;

class Invoices extends Component
{
    use WithPagination, HasCompanyConfiguration, HasDynamicButtons, WithFileUploads;

    public $search = '';
    public $perPage = 12;
    public $sortField = 'vnt_invoices.invoiceNumber';
    public $sortDirection = 'desc';
    public $moduleKey = 'invoices';
    
    // Filtros
    public $filterDateFrom = '';
    public $filterDateTo = '';

    // ── Nota Crédito ──────────────────────────────────────────
    public bool  $showCreditNoteModal = false;
    public array $creditNoteInvoice   = [];
    public array $creditNoteItems     = [];
    public string $creditNoteReason   = '';
    public string $creditNotePayment  = 'Efectivo';
    public string $creditNoteObs      = '';
    public float  $creditNoteTotal    = 0;

    // ── Pago de Factura ───────────────────────────────────────
    public bool $showPaymentModal = false;
    public $paymentInvoiceId = null;
    public $paymentProofFile = null;
    public $paymentMethodId = null;
    public array $paymentMethodsList = [];
    public bool $useRemissionProof = false;
    public ?string $remissionProofPath = null;

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatingFilterDateFrom()
    {
        $this->resetPage();
    }

    public function updatingFilterDateTo()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search']);
        
        // Restablecer fechas por defecto al limpiar
        $this->filterDateFrom = now()->subDays(7)->format('Y-m-d');
        $this->filterDateTo = now()->addDays(7)->format('Y-m-d');
        
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
        $this->resetPage();
    }

    public function mount()
    {
        $this->ensureTenantConnection();
        $this->initializeCompanyConfiguration();

        // Inicializar fechas por defecto (±7 días)
        $this->filterDateFrom = now()->subDays(7)->format('Y-m-d');
        $this->filterDateTo = now()->addDays(7)->format('Y-m-d');
    }

    /**
     * Procesar pago de una factura enviando a Alegra
     */
    public function payInvoice($invoiceId)
    {
        $this->ensureTenantConnection();

        try {
            $invoice = VntInvoices::findOrFail($invoiceId);

            // Validar que la factura puede ser pagada
            if ($invoice->status_payment === 'PAGADO') {
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'Esta factura ya está pagada.'
                ]);
                return;
            }

            if ($invoice->status_payment === 'ANULADO') {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'No se puede pagar una factura anulada.'
                ]);
                return;
            }

            // Validar que tenga api_data_id (ID de Alegra)
            if (empty($invoice->api_data_id)) {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'Esta factura no tiene ID de Alegra. No se puede procesar el pago.'
                ]);
                return;
            }

            // Obtener configuración del tenant
            $tenant = session('tenant_id') ? Tenant::find(session('tenant_id')) : null;
            if (!$tenant) {
                throw new \Exception('No se pudo identificar el tenant');
            }

            $hasFacturacionConfig = TenantConfigManager::hasFacturacionConfig($tenant);
            if (!$hasFacturacionConfig) {
                throw new \Exception('No hay configuración de facturación para este tenant');
            }

            // Crear servicio de facturación
            $facturacionService = FacturacionService::forTenant($tenant);

            // Calcular monto total a pagar (con retenciones si aplica)
            $totalAmount = $this->calculateInvoiceTotalWithRetentions($invoice);

            // Construir datos de pago para Alegra
            $alegraPaymentData = $this->buildAlegraPaymentData($invoice, $totalAmount);

            Log::info('📤 Enviando pago a Alegra desde listado de facturas', [
                'invoice_id' => $invoiceId,
                'invoice_number' => $invoice->invoiceNumber,
                'alegra_invoice_id' => $invoice->api_data_id,
                'payment_amount' => $totalAmount,
                'payment_data' => $alegraPaymentData
            ]);

            // Enviar pago a Alegra
            $paymentResponse = $facturacionService->registerPayment($alegraPaymentData);

            if ($paymentResponse['success'] ?? false) {
                // Actualizar estado local de la factura
                $invoice->update([
                    'status_payment' => 'PAGADO',
                    'api_data_id_pay' => $paymentResponse['data']['id'] ?? null
                ]);

                Log::info('✅ Pago procesado exitosamente en Alegra', [
                    'invoice_id' => $invoiceId,
                    'alegra_payment_id' => $paymentResponse['data']['id'] ?? null,
                    'previous_status' => $invoice->getOriginal('status_payment'),
                    'new_status' => 'PAGADO'
                ]);

                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => "✅ Factura #{$invoice->invoiceNumber} pagada exitosamente en Alegra."
                ]);
            } else {
                // Error en la API de Alegra
                $errorMessage = $paymentResponse['message'] ?? 'Error desconocido al procesar el pago';
                throw new \Exception("Error en Alegra: {$errorMessage}");
            }
        } catch (\Exception $e) {
            Log::error('❌ Error procesando pago de factura', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al procesar el pago: ' . $e->getMessage()
            ]);
        }
    }

    public function openPaymentModal($invoiceId)
    {
        $this->ensureTenantConnection();
        $this->paymentInvoiceId = $invoiceId;
        $this->paymentProofFile = null;
        $this->remissionProofPath = null;
        $this->useRemissionProof = false;

        // Intentar obtener la forma de pago original seleccionada en el pedido/remisión
        $defaultMethodId = null;
        $invoiceSale = VntInvoicesXsales::where('invoiceId', $invoiceId)->with('remission')->first();
        if ($invoiceSale && $invoiceSale->remission) {
            $defaultMethodId = $invoiceSale->remission->methodPaymentId;
            if ($invoiceSale->remission->proof_payment) {
                $this->remissionProofPath = $invoiceSale->remission->proof_payment;
                $this->useRemissionProof = true;
            }
            Log::info('💳 Forma de pago preseleccionada del pedido/remisión', [
                'remission_id' => $invoiceSale->remissionId,
                'method_payment_id' => $defaultMethodId,
                'has_proof_payment' => !empty($this->remissionProofPath)
            ]);
        }

        // Fetch active payment methods
        $this->paymentMethodsList = \App\Models\Tenant\MethodPayments\VntMethodPayMents::where('status', 1)->get()->toArray();
        
        if ($defaultMethodId && collect($this->paymentMethodsList)->contains('id', $defaultMethodId)) {
            $this->paymentMethodId = $defaultMethodId;
        } elseif (!empty($this->paymentMethodsList)) {
            $this->paymentMethodId = $this->paymentMethodsList[0]['id'];
        } else {
            $this->paymentMethodId = null;
        }

        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->paymentInvoiceId = null;
        $this->paymentProofFile = null;
        $this->paymentMethodId = null;
        $this->remissionProofPath = null;
        $this->useRemissionProof = false;
    }

    public function isRemissionProofImage()
    {
        if (empty($this->remissionProofPath)) {
            return false;
        }
        $extension = strtolower(pathinfo($this->remissionProofPath, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    public function submitPayment()
    {
        $this->ensureTenantConnection();

        $rules = [
            'paymentMethodId' => 'required',
        ];

        if (!$this->useRemissionProof) {
            $rules['paymentProofFile'] = 'required|image|max:2048'; // 2MB max, proof file is required for payment confirmation
        }

        $this->validate($rules);

        try {
            $invoice = VntInvoices::findOrFail($this->paymentInvoiceId);
            
            // Proceed to pay invoice first (Alegra and local update to PAGADO)
            $this->payInvoice($this->paymentInvoiceId);

            // Re-fetch to check if status indeed changed to PAGADO
            $invoice->refresh();

            if ($invoice->status_payment === 'PAGADO') {
                $filePath = null;
                if ($this->useRemissionProof && $this->remissionProofPath) {
                    $filePath = $this->remissionProofPath;
                } elseif ($this->paymentProofFile) {
                    $tenantId = session('tenant_id') ?? 'default';
                    $filePath = $this->paymentProofFile->store("tenants/{$tenantId}/payments", 'public');
                }

                $totalAmount = $this->calculateInvoiceTotalWithRetentions($invoice);

                \App\Models\Tenant\Invoices\VntInvoicePayments::create([
                    'value' => $totalAmount,
                    'invoiceId' => $invoice->id,
                    'methodPaymentId' => $this->paymentMethodId,
                    'proof_payment' => $filePath
                ]);
            }

            $this->closePaymentModal();
        } catch (\Exception $e) {
            Log::error('❌ Error guardando pago de factura', [
                'invoice_id' => $this->paymentInvoiceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al procesar el pago: ' . $e->getMessage()
            ]);
        }
    }


    /**
     * Emitir una factura que quedó con status "SIN EMITIR"
     */
    public function emitirFactura($invoiceId)
    {
        $this->ensureTenantConnection();

        try {
            $invoice = VntInvoices::findOrFail($invoiceId);

            // Validar que la factura puede ser emitida
            if ($invoice->status === 'FACTURADO') {
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'Esta factura ya está emitida.'
                ]);
                return;
            }

            if ($invoice->status !== 'SIN EMITIR') {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'Solo se pueden emitir facturas con estado "SIN EMITIR".'
                ]);
                return;
            }

            // Validar que tenga api_data_id (ID de Alegra)
            if (empty($invoice->api_data_id)) {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'Esta factura no tiene ID de Alegra. No se puede emitir.'
                ]);
                return;
            }

            // Obtener configuración del tenant
            $tenant = session('tenant_id') ? Tenant::find(session('tenant_id')) : null;
            if (!$tenant) {
                throw new \Exception('No se pudo identificar el tenant');
            }

            $hasFacturacionConfig = TenantConfigManager::hasFacturacionConfig($tenant);
            if (!$hasFacturacionConfig) {
                throw new \Exception('No hay configuración de facturación para este tenant');
            }

            // Crear servicio de facturación
            $facturacionService = FacturacionService::forTenant($tenant);

            Log::info('📤 Intentando emitir factura desde listado de facturas', [
                'invoice_id' => $invoiceId,
                'invoice_number' => $invoice->invoiceNumber,
                'alegra_invoice_id' => $invoice->api_data_id,
                'current_status' => $invoice->status
            ]);

            // Intentar emitir (stamp) la factura en Alegra
            $stampResponse = $facturacionService->stampInvoice($invoice->api_data_id);

            if ($stampResponse['success']) {
                // ✅ STAMP EXITOSO: Actualizar estado a FACTURADO
                $invoice->update(['status' => 'FACTURADO']);

                // ✅ Actualizar status de cotizaciones asociadas a FACTURADO
                $this->updateRelatedQuotesToFacturado($invoice);

                Log::info('✅ Factura emitida exitosamente desde listado', [
                    'invoice_id' => $invoiceId,
                    'invoice_number' => $invoice->invoiceNumber,
                    'alegra_invoice_id' => $invoice->api_data_id,
                    'previous_status' => 'SIN EMITIR',
                    'new_status' => 'FACTURADO'
                ]);

                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => "✅ Factura #{$invoice->invoiceNumber} emitida exitosamente."
                ]);
            } else {
                // ❌ STAMP FALLÓ
                $errorMessage = $this->extractStampErrorMessage($stampResponse);
                throw new \Exception("Error al emitir la factura: {$errorMessage}");
            }
        } catch (\Exception $e) {
            Log::error('❌ Error emitiendo factura desde listado', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al emitir la factura: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Método para imprimir factura (basado en una factura emitida)
     * Utiliza el api_data_id para obtener el PDF oficial de Alegra.
     */
    public function printInvoice($invoiceId)
    {
        Log::info('🖨️ Invoices.printInvoice llamado', ['invoice_id' => $invoiceId]);

        $this->ensureTenantConnection();
        $this->initializeCompanyConfiguration();

        try {
            // 1. Buscar la factura
            $invoice = VntInvoices::findOrFail($invoiceId);

            Log::info('🔍 Factura encontrada', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoiceNumber,
                'status' => $invoice->status,
                'api_data_id' => $invoice->api_data_id
            ]);

            // 2. Validar que la factura esté emitida
            if ($invoice->status !== 'FACTURADO') {
                Log::warning('⚠️ Factura no está emitida', [
                    'invoice_id' => $invoiceId,
                    'status' => $invoice->status
                ]);
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'Solo se pueden imprimir facturas emitidas (estado: FACTURADO).'
                ]);
                return;
            }

            // 3. Validar que tenga api_data_id
            if (empty($invoice->api_data_id)) {
                Log::warning('⚠️ Factura sin api_data_id', ['invoice_id' => $invoiceId]);
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'Esta factura no tiene ID de Alegra. No se puede obtener el PDF oficial.'
                ]);
                return;
            }

            // 4. Obtener configuración de facturación
            $tenant = session('tenant_id') ? Tenant::find(session('tenant_id')) : null;
            if (!$tenant) {
                throw new \Exception('No se pudo identificar el tenant');
            }

            $facturacionService = new FacturacionService($tenant);
            $hasFacturacionConfig = TenantConfigManager::hasFacturacionConfig($tenant);

            // 5. Obtener PDF de Alegra
            if ($hasFacturacionConfig && $invoice->api_data_id) {
                Log::info('🔗 Obteniendo PDF de factura desde Alegra', ['api_id' => $invoice->api_data_id]);

                $apiResponse = $facturacionService->getInvoicePdf($invoice->api_data_id);

                // La respuesta de Alegra viene en $apiResponse['data'] (makeRequest la envuelve así)
                // Alegra devuelve: { "pdf": "URL", "id": ..., ... }
                // El proxy puede devolver: { "success": true, "pdf": "URL", "data": {...} }
                $respData = $apiResponse['data'] ?? [];

                // Buscar la URL del PDF en los distintos posibles campos de la respuesta
                $printUrl = $respData['pdf']
                    ?? $respData['publicUrl']
                    ?? $respData['url']
                    ?? ($respData['data']['pdf'] ?? null)
                    ?? ($respData['data']['publicUrl'] ?? null)
                    ?? null;

                Log::info('📄 Respuesta PDF de Alegra', [
                    'api_id'        => $invoice->api_data_id,
                    'http_success'  => $apiResponse['success'] ?? false,
                    'http_status'   => $apiResponse['status'] ?? null,
                    'pdf_url_found' => !empty($printUrl),
                    'print_url'     => $printUrl,
                    'response_keys' => array_keys($respData),
                    'full_response' => $apiResponse,
                ]);

                if (!empty($printUrl)) {
                    Log::info('✅ URL de PDF de factura obtenida', ['url' => $printUrl]);

                    $this->dispatch('open-print-window', [
                        'url'    => $printUrl,
                        'format' => 'carta'
                    ]);

                    $this->dispatch('show-toast', [
                        'type'    => 'success',
                        'message' => 'Factura #' . $invoice->invoiceNumber . ' preparada para impresión.'
                    ]);
                    return;
                } else {
                    $this->dispatch('show-toast', [
                        'type'    => 'warning',
                        'message' => 'No se pudo obtener el PDF desde Alegra. Revise los logs o verifique que la factura esté emitida.'
                    ]);
                }
            } else {
                Log::info('ℹ️ Facturación no configurada para obtener PDF', [
                    'has_config' => $hasFacturacionConfig,
                    'has_api_id' => !empty($invoice->api_data_id)
                ]);
                $this->dispatch('show-toast', [
                    'type'    => 'info',
                    'message' => 'No se puede obtener el PDF de Alegra. Verifique la configuración de facturación.'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('❌ Error en Invoices.printInvoice: ' . $e->getMessage(), [
                'invoice_id' => $invoiceId,
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al procesar impresión de factura: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Actualizar status de cotizaciones relacionadas con la factura a FACTURADO
     */
    private function updateRelatedQuotesToFacturado(VntInvoices $invoice): void
    {
        try {
            // Buscar todas las remisiones asociadas a esta factura
            $remissionIds = VntInvoicesXsales::where('invoiceId', $invoice->id)
                ->pluck('remissionId')
                ->toArray();

            if (empty($remissionIds)) {
                Log::info('ℹ️ No se encontraron remisiones asociadas a la factura', [
                    'invoice_id' => $invoice->id
                ]);
                return;
            }

            // Buscar las cotizaciones de esas remisiones
            $quotesToUpdate = DB::connection('tenant')
                ->table('inv_remissions')
                ->join('vnt_quotes', 'inv_remissions.quoteId', '=', 'vnt_quotes.id')
                ->whereIn('inv_remissions.id', $remissionIds)
                ->select('vnt_quotes.id', 'vnt_quotes.consecutive')
                ->get();

            $quotesUpdated = 0;
            foreach ($quotesToUpdate as $quote) {
                DB::connection('tenant')
                    ->table('vnt_quotes')
                    ->where('id', $quote->id)
                    ->update(['status' => 'FACTURADO']);

                $quotesUpdated++;

                Log::info('📋 Status de cotización actualizado a FACTURADO (desde emisión)', [
                    'quote_id' => $quote->id,
                    'quote_consecutive' => $quote->consecutive,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoiceNumber
                ]);
            }

            Log::info('✅ Cotizaciones actualizadas tras emisión de factura', [
                'invoice_id' => $invoice->id,
                'quotes_updated' => $quotesUpdated
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error actualizando cotizaciones tras emisión de factura', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function extractStampErrorMessage(array $stampResponse): string
    {
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

        // Extraer solo las validaciones cuando viene el mensaje largo de la DIAN
        if (str_contains($message, 'La factura electrónica de venta no se ha podido emitir porque no cumple con las validaciones necesarias:')) {
            $message = str_replace('La factura electrónica de venta no se ha podido emitir porque no cumple con las validaciones necesarias:', '', $message);
            $message = str_replace(['<ul>', '</ul>', '<li>', '</li>'], ['', '', '• ', "\n"], $message);
            $message = strip_tags($message);
            $message = trim($message);
        }

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
     * Calcular el total de la factura considerando retenciones - SIN REDONDEAR
     */
    private function calculateInvoiceTotalWithRetentions(VntInvoices $invoice): float
    {
        // Obtener el total con impuestos desde la query usando ROUND en SQL para precisión exacta
        $query = VntInvoices::query()
            ->select([
                DB::raw("MAX(COALESCE(dr_totals.total_con_impuestos, dq_totals.total_con_impuestos, 0)) AS total_con_impuestos")
            ])
            ->leftJoinSub($this->getRemissionTotalsSubquery(), "dr_totals", "vnt_invoices.id", "=", "dr_totals.invoiceId")
            ->leftJoinSub($this->getQuoteTotalsSubquery(), "dq_totals", "vnt_invoices.id", "=", "dq_totals.invoiceId")
            ->where('vnt_invoices.id', $invoice->id)
            ->first();

        // Usar números decimales exactos sin redondeo intermedio
        $totalConImpuestos = (float)($query->total_con_impuestos ?? 0);
        $retentionFuente = (float)($invoice->retentionFuente ?? 0);
        $retentionIca = (float)($invoice->retentionIca ?? 0);
        $retentionIva = (float)($invoice->retentionIva ?? 0);

        // Calcular el total EXACTO sin redondeo
        $totalAPagar = $totalConImpuestos - $retentionFuente - $retentionIca - $retentionIva;

        Log::info('💰 Cálculo EXACTO de monto de pago (SIN REDONDEO)', [
            'invoice_id' => $invoice->id,
            'total_con_impuestos_raw' => $query->total_con_impuestos,
            'total_con_impuestos_float' => $totalConImpuestos,
            'retention_fuente' => $retentionFuente,
            'retention_ica' => $retentionIca,
            'retention_iva' => $retentionIva,
            'total_a_pagar_exacto' => $totalAPagar,
            'total_a_pagar_formatted' => number_format($totalAPagar, 2, '.', '')
        ]);

        // Retornar el valor EXACTO sin redondear
        return $totalAPagar;
    }

    /**
     * Construir datos de pago para la API de Alegra - VALORES EXACTOS
     */
    private function buildAlegraPaymentData(VntInvoices $invoice, float $totalAmount): array
    {
        // Obtener retenciones EXACTAS sin redondear
        $retentions = [];
        $totalRetentions = 0;

        if ($invoice->retentionFuente > 0) {
            $retentionAmount = (float)$invoice->retentionFuente;
            $retentions[] = [
                'id' => config('facturacion.retentions.alegra_ids.fuente', '14'),
                'amount' => $retentionAmount  // SIN REDONDEAR
            ];
            $totalRetentions += $retentionAmount;
        }
        if ($invoice->retentionIca > 0) {
            $retentionAmount = (float)$invoice->retentionIca;
            $retentions[] = [
                'id' => config('facturacion.retentions.alegra_ids.ica', '11'),
                'amount' => $retentionAmount  // SIN REDONDEAR
            ];
            $totalRetentions += $retentionAmount;
        }
        if ($invoice->retentionIva > 0) {
            $retentionAmount = (float)$invoice->retentionIva;
            $retentions[] = [
                'id' => config('facturacion.retentions.alegra_ids.iva', '12'),
                'amount' => $retentionAmount  // SIN REDONDEAR
            ];
            $totalRetentions += $retentionAmount;
        }

        // Construir objeto de factura con valor EXACTO
        $totalBruto = $totalAmount + $totalRetentions; // Total antes de retenciones

        $invoiceObject = [
            'id' => $invoice->api_data_id,
            'amount' => $totalBruto // Total bruto EXACTO
        ];

        // Agregar retenciones si existen
        if (!empty($retentions)) {
            $invoiceObject['retentions'] = $retentions;
        }

        // Usar cuenta bancaria por defecto (puede configurarse según necesidades)
        $paymentData = [
            'bankAccount' => [
                'id' => '1' // ID de cuenta bancaria por defecto en Alegra
            ],
            'type' => 'in', // Pago entrante
            'date' => now()->format('Y-m-d'),
            'invoices' => [$invoiceObject]
        ];

        Log::info('🔧 Datos de pago construidos para Alegra - VALORES EXACTOS', [
            'payment_data' => $paymentData,
            'retentions_count' => count($retentions),
            'total_retentions_exacto' => $totalRetentions,
            'net_amount_exacto' => $totalAmount,
            'total_bruto_exacto' => $totalBruto,
            'retentions_detalle' => $retentions,
            'json_exacto' => json_encode($paymentData, JSON_PRESERVE_ZERO_FRACTION)
        ]);

        return $paymentData;
    }

    /**
     * Obtener subconsulta de totales de remisiones - PRECISIÓN EXACTA
     */
    private function getRemissionTotalsSubquery()
    {
        return DB::connection('tenant')->table("inv_detail_remissions")
            ->select(
                "invoiceId",
                DB::raw("CAST(SUM(value * quantity) AS DECIMAL(15,6)) as total_con_impuestos"),
                DB::raw("CAST(SUM((value / (1 + tax / 100)) * quantity) AS DECIMAL(15,6)) as total_sin_impuestos")
            )
            ->whereNotNull("invoiceId")
            ->groupBy("invoiceId");
    }

    /**
     * Obtener subconsulta de totales de cotizaciones - PRECISIÓN EXACTA
     */
    private function getQuoteTotalsSubquery()
    {
        return DB::connection('tenant')->table("vnt_detail_quotes as dq")
            ->select(
                "ixs.invoiceId",
                DB::raw("CAST(SUM(dq.value * dq.quantity) AS DECIMAL(15,6)) as total_con_impuestos"),
                DB::raw("CAST(SUM((dq.value / (1 + dq.tax / 100)) * dq.quantity) AS DECIMAL(15,6)) as total_sin_impuestos")
            )
            ->join("vnt_invoicesXsales as ixs", "dq.quoteId", "=", "ixs.quoteId")
            ->whereNotNull("ixs.invoiceId")
            ->groupBy("ixs.invoiceId");
    }

    public function render()
    {
        try {
            $centralDbName = config('database.connections.central.database');
            $this->ensureTenantConnection();

            // Subconsulta para totales de detalles de remisión - PRECISIÓN EXACTA con DEBUG
            // CORRECCIÓN: El campo 'value' en inv_detail_remissions en realidad YA incluye impuestos
            $remissionTotals = DB::connection('tenant')->table("inv_detail_remissions")
                ->select(
                    "invoiceId",
                    DB::raw("CAST(SUM(value * quantity) AS DECIMAL(15,6)) as total_con_impuestos"), // El 'value' YA tiene impuestos
                    DB::raw("CAST(SUM((value / (1 + tax / 100)) * quantity) AS DECIMAL(15,6)) as total_sin_impuestos"), // Quitamos impuestos INDIVIDUALMENTE
                    DB::raw("COUNT(DISTINCT remissionId) as remissions_count"), // Para debug: cuántas remisiones
                    DB::raw("GROUP_CONCAT(CONCAT('R', remissionId, ':V', value, ':T', tax, ':Q', quantity) SEPARATOR '|') as debug_items") // Para debug
                )
                ->whereNotNull("invoiceId")
                ->groupBy("invoiceId");

            // Subconsulta para totales de detalles de cotización - PRECISIÓN EXACTA
            // NOTA: Los valores en vnt_detail_quotes YA incluyen impuestos
            $quoteTotals = DB::connection('tenant')->table("vnt_detail_quotes as dq")
                ->select(
                    "ixs.invoiceId",
                    DB::raw("CAST(SUM(dq.value * dq.quantity) AS DECIMAL(15,6)) as total_con_impuestos"),
                    DB::raw("CAST(SUM((dq.value / (1 + dq.tax / 100)) * dq.quantity) AS DECIMAL(15,6)) as total_sin_impuestos")
                )
                ->join("vnt_invoicesXsales as ixs", "dq.quoteId", "=", "ixs.quoteId")
                ->whereNotNull("ixs.invoiceId")
                ->groupBy("ixs.invoiceId");

            // Subconsulta para GROUP_CONCAT de consecutivos de remisión
            $remissionConsecutives = DB::connection('tenant')->table("vnt_invoicesXsales as s_sub")
                ->select(
                    "s_sub.invoiceId",
                    DB::raw("GROUP_CONCAT(r_sub.consecutive ORDER BY r_sub.consecutive SEPARATOR ', ') as remission_consecutive")
                )
                ->join("inv_remissions as r_sub", "s_sub.remissionId", "=", "r_sub.id")
                ->groupBy("s_sub.invoiceId");

            $query = VntInvoices::query()
                ->select([
                    'vnt_invoices.id',
                    'vnt_invoices.consecutive',
                    'vnt_invoices.status',
                    'vnt_invoices.status_payment',
                    'vnt_invoices.api_data_id',
                    'vnt_invoices.api_data_id_pay',
                    'vnt_invoices.partialPayment',
                    'vnt_invoices.quoteId',
                    'vnt_invoices.warehouseId',
                    'vnt_invoices.remission',
                    'vnt_invoices.creditNoteId',
                    'vnt_invoices.invoiceNumber',
                    'vnt_invoices.retentionFuente',
                    'vnt_invoices.retentionIca',
                    'vnt_invoices.retentionIva',
                    'vnt_invoices.creditNote',
                    'vnt_invoices.orderNumber',
                    'vnt_invoices.created_at',
                    'vnt_invoices.updated_at',
                    'vnt_invoices.deleted_at',
                    DB::raw("MAX(remission_consecutives.remission_consecutive) as remission_consecutive"),
                    DB::raw("MAX(COALESCE(wr.name, wd.name)) as warehouse_name"),
                    DB::raw("MAX(CONCAT(COALESCE(u.name, ''), ' ', COALESCE(u.name, ''))) AS seller"),
                    DB::raw("MAX(CONCAT(COALESCE(c.firstName, ''), ' ', COALESCE(c.secondName, ''), ' ', COALESCE(c.lastName, ''), ' ', COALESCE(c.secondLastName, ''))) AS client_name"),
                    DB::raw("MAX(COALESCE(dr_totals.total_sin_impuestos, dq_totals.total_sin_impuestos, 0)) AS total_sin_impuestos"),
                    DB::raw("MAX(COALESCE(dr_totals.total_con_impuestos, dq_totals.total_con_impuestos, 0)) AS total_con_impuestos"),
                    DB::raw("MAX(IF(s.remissionId IS NOT NULL, 'REMISIONADA', 'COTIZADA')) as tipo_factura"),
                    // Campos DEBUG para facturas agrupadas
                    DB::raw("MAX(dr_totals.remissions_count) as debug_remissions_count"),
                    DB::raw("MAX(dr_totals.debug_items) as debug_items_detail"),
                ])
                ->join("vnt_invoicesXsales as s", "s.invoiceId", "=", "vnt_invoices.id")
                // Joins para la ruta de Remisión
                ->leftJoin("inv_remissions as r", "s.remissionId", "=", "r.id")
                ->leftJoin("inv_store as wr", "r.warehouseId", "=", "wr.id")
                // Joins para la ruta de Cotización (puede venir de una remisión o directa)
                ->leftJoin("vnt_quotes as qr", "r.quoteId", "=", "qr.id") // Cotización vía Remisión
                ->leftJoin("vnt_quotes as qd", "s.quoteId", "=", "qd.id") // Cotización Directa
                ->leftJoin("inv_store as wd", "qd.warehouseId", "=", "wd.id")
                // Joins para datos de Cliente y Vendedor (usando COALESCE para tomar de cualquiera de las dos rutas)
                // customerId guarda el warehouseId del contacto (vnt_contacts.warehouseId = vnt_quotes.customerId)
                ->leftJoin("vnt_contacts as c", "c.warehouseId", "=", DB::raw("COALESCE(qr.customerId, qd.customerId)"))
                ->leftJoin(DB::raw("{$centralDbName}.users as u"), "u.id", "=", DB::raw("COALESCE(qr.userId, qd.userId)"))
                // Joins con las subconsultas
                ->leftJoinSub($remissionTotals, "dr_totals", "vnt_invoices.id", "=", "dr_totals.invoiceId")
                ->leftJoinSub($quoteTotals, "dq_totals", "vnt_invoices.id", "=", "dq_totals.invoiceId")
                ->leftJoinSub($remissionConsecutives, "remission_consecutives", "vnt_invoices.id", "=", "remission_consecutives.invoiceId")
                ->groupBy([
                    "vnt_invoices.id",
                    "vnt_invoices.consecutive",
                    "vnt_invoices.status",
                    "vnt_invoices.status_payment",
                    "vnt_invoices.api_data_id",
                    "vnt_invoices.api_data_id_pay",
                    "vnt_invoices.partialPayment",
                    "vnt_invoices.quoteId",
                    "vnt_invoices.warehouseId",
                    "vnt_invoices.remission",
                    "vnt_invoices.creditNoteId",
                    "vnt_invoices.invoiceNumber",
                    "vnt_invoices.retentionFuente",
                    "vnt_invoices.retentionIca",
                    "vnt_invoices.retentionIva",
                    "vnt_invoices.creditNote",
                    "vnt_invoices.orderNumber",
                    "vnt_invoices.created_at",
                    "vnt_invoices.updated_at",
                    "vnt_invoices.deleted_at"
                ]);

            // Aplicar búsqueda — todo en HAVING para compatibilidad con GROUP BY
            $query->when($this->search, function ($q) {
                $search = '%' . $this->search . '%';
                $q->havingRaw("
                    vnt_invoices.invoiceNumber LIKE ?
                    OR MAX(CONCAT(COALESCE(c.firstName, ''), ' ', COALESCE(c.lastName, ''))) LIKE ?
                    OR MAX(COALESCE(u.name, '')) LIKE ?
                    OR MAX(remission_consecutives.remission_consecutive) LIKE ?
                    OR vnt_invoices.consecutive LIKE ?
                ", [$search, $search, $search, $search, $search]);
            })
            ->when($this->filterDateFrom, function ($q) {
                $q->whereDate('vnt_invoices.created_at', '>=', $this->filterDateFrom);
            })
            ->when($this->filterDateTo, function ($q) {
                $q->whereDate('vnt_invoices.created_at', '<=', $this->filterDateTo);
            });

            // Aplicar ordenamiento
            $query->orderBy($this->sortField, $this->sortDirection);

            $invoices = $query->paginate($this->perPage);

            // LOG DEBUG: Analizar facturas con múltiples remisiones
            foreach ($invoices as $invoice) {
                if ($invoice->debug_remissions_count > 1) {
                    Log::info('🔍 DEBUG: Factura con múltiples remisiones detectada', [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoiceNumber,
                        'remissions_count' => $invoice->debug_remissions_count,
                        'total_con_impuestos' => $invoice->total_con_impuestos,
                        'total_sin_impuestos' => $invoice->total_sin_impuestos,
                        'items_detail' => $invoice->debug_items_detail,
                        'diferencia_iva' => $invoice->total_con_impuestos - $invoice->total_sin_impuestos
                    ]);
                }
            }

            return view('livewire.tenant.invoices.invoices', [
                'invoices' => $invoices
            ])->layout('layouts.app', ['header' => 'Gestión de Facturas']);
        } catch (\Exception $e) {
            Log::error('❌ Error en la consulta de facturas: ' . $e->getMessage() . ' en la línea ' . $e->getLine());
            // Opcional: retornar una vista de error o una colección vacía para no romper la UI
            return view('livewire.tenant.invoices.invoices', [
                'invoices' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage)
            ])->layout('layouts.app', ['header' => 'Gestión de Facturas']);
        }
    }

    // ── Nota Crédito ─────────────────────────────────────────────────────────

    public function openCreditNoteModal(int $invoiceId): void
    {
        $this->ensureTenantConnection();

        $invoice = VntInvoices::findOrFail($invoiceId);

        if ($invoice->status_payment === 'PAGADO') {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No se puede crear una nota crédito a una factura ya pagada.']);
            return;
        }

        if ($invoice->creditNote) {
            $this->dispatch('show-toast', ['type' => 'warning', 'message' => 'Esta factura ya tiene una nota crédito asociada.']);
            return;
        }

        // Cargar TODOS los registros de ventas vinculados a esta factura
        $sales           = VntInvoicesXsales::where('invoiceId', $invoiceId)->get();
        $clientName      = '—';
        $customerApiId   = null;
        $total           = 0;
        // Usamos item_id como clave para agrupar productos iguales de distintas remisiones
        $grouped         = [];

        foreach ($sales as $sale) {
            if ($sale->remissionId) {
                $remission = InvRemissions::with(['quote.customer.company', 'details.item.tax'])->find($sale->remissionId);

                if ($clientName === '—' && $remission?->quote?->customer) {
                    $contact    = $remission->quote->customer;
                    $clientName = trim(collect([
                        $contact->firstName,
                        $contact->secondName,
                        $contact->lastName,
                        $contact->secondLastName,
                    ])->filter()->implode(' '));
                    // Misma prioridad que InvoiceDataBuilder: company->api_data_id primero, luego contact
                    $customerApiId = $contact->company?->api_data_id ?? $contact->api_data_id ?? null;
                }

                foreach ($remission?->details ?? [] as $detail) {
                    $unitPrice  = $detail->tax > 0
                        ? $detail->value / (1 + $detail->tax / 100)
                        : $detail->value;
                    $unitPriceRounded = round($unitPrice, 2);
                    $qty        = (float) $detail->quantity;
                    $key        = 'item_' . ($detail->itemId ?? 'x_' . $detail->id);
                    $taxPercent = (float) $detail->tax;
                    $taxApiId   = $detail->item?->tax?->api_data_id
                        ?? ($taxPercent == 19 ? '3' : ($taxPercent == 5 ? '2' : '1'));

                    if (isset($grouped[$key])) {
                        $grouped[$key]['quantity'] += $qty;
                        $grouped[$key]['max_qty']  += $qty;
                        $newSubtotal = round($grouped[$key]['unit_price'] * $grouped[$key]['quantity'], 2);
                        $grouped[$key]['subtotal'] = $newSubtotal;
                        $grouped[$key]['total']    = $newSubtotal + round($newSubtotal * $grouped[$key]['tax'] / 100, 2);
                    } else {
                        $subtotal = round($unitPriceRounded * $qty, 2);
                        $grouped[$key] = [
                            'selected'    => true,
                            'code'        => $detail->item?->sku ?? '—',
                            'name'        => $detail->item?->name ?? 'Producto no encontrado',
                            'quantity'    => $qty,
                            'max_qty'     => $qty,
                            'unit_price'  => $unitPriceRounded,
                            'tax'         => $taxPercent,
                            'subtotal'    => $subtotal,
                            'total'       => $subtotal + round($subtotal * $taxPercent / 100, 2),
                            'type'        => 'PRINCIPAL',
                            'detail_type' => 'remission',
                            'detail_id'   => $detail->id,
                            'item_id'     => $detail->itemId,
                            'item_api_id' => $detail->item?->api_data_id ?? null,
                            'tax_api_id'  => $taxApiId,
                        ];
                    }
                }
            } elseif ($sale->quoteId) {
                $quote = \App\Models\Tenant\Quoter\VntQuote::with(['customer.company', 'detalles.item.tax'])->find($sale->quoteId);

                if ($clientName === '—' && $quote?->customer) {
                    $contact    = $quote->customer;
                    $clientName = trim(collect([
                        $contact->firstName,
                        $contact->secondName,
                        $contact->lastName,
                        $contact->secondLastName,
                    ])->filter()->implode(' '));
                    // Misma prioridad que InvoiceDataBuilder: company->api_data_id primero, luego contact
                    $customerApiId = $customerApiId ?? ($contact->company?->api_data_id ?? $contact->api_data_id ?? null);
                }

                foreach ($quote?->detalles ?? [] as $detalle) {
                    $unitPrice  = $detalle->tax > 0
                        ? $detalle->value / (1 + $detalle->tax / 100)
                        : $detalle->value;
                    $unitPriceRounded = round($unitPrice, 2);
                    $qty        = (float) $detalle->quantity;
                    $key        = 'item_' . ($detalle->itemId ?? 'x_' . $detalle->id);
                    $taxPercent = (float) $detalle->tax;
                    $taxApiId   = $detalle->item?->tax?->api_data_id
                        ?? ($taxPercent == 19 ? '3' : ($taxPercent == 5 ? '2' : '1'));

                    if (isset($grouped[$key])) {
                        $grouped[$key]['quantity'] += $qty;
                        $grouped[$key]['max_qty']  += $qty;
                        $newSubtotal = round($grouped[$key]['unit_price'] * $grouped[$key]['quantity'], 2);
                        $grouped[$key]['subtotal'] = $newSubtotal;
                        $grouped[$key]['total']    = $newSubtotal + round($newSubtotal * $grouped[$key]['tax'] / 100, 2);
                    } else {
                        $subtotal = round($unitPriceRounded * $qty, 2);
                        $grouped[$key] = [
                            'selected'    => true,
                            'code'        => $detalle->item?->sku ?? '—',
                            'name'        => $detalle->item?->name ?? 'Producto no encontrado',
                            'quantity'    => $qty,
                            'max_qty'     => $qty,
                            'unit_price'  => $unitPriceRounded,
                            'tax'         => $taxPercent,
                            'subtotal'    => $subtotal,
                            'total'       => $subtotal + round($subtotal * $taxPercent / 100, 2),
                            'type'        => 'PRINCIPAL',
                            'detail_type' => 'quote',
                            'detail_id'   => $detalle->id,
                            'item_id'     => $detalle->itemId,
                            'item_api_id' => $detalle->item?->api_data_id ?? null,
                            'tax_api_id'  => $taxApiId,
                        ];
                    }
                }
            }
        }

        // Convertir el array agrupado a lista plana y calcular total general
        $this->creditNoteItems = array_values($grouped);
        foreach ($this->creditNoteItems as $item) {
            $total += $item['total'];
        }

        $this->creditNoteInvoice = [
            'id'                  => $invoice->id,
            'invoiceNumber'       => $invoice->invoiceNumber,
            'status'              => $invoice->status,
            'status_payment'      => $invoice->status_payment,
            'client_name'         => $clientName,
            'total'               => $total,
            'invoice_api_data_id' => $invoice->api_data_id,
            'customer_api_id'     => $customerApiId,
        ];

        $this->creditNoteTotal   = $total;
        $this->creditNoteReason  = 'VOID_ELECTRONIC_INVOICE';
        $this->creditNotePayment = 'Efectivo';
        $this->creditNoteObs     = '';
        $this->showCreditNoteModal = true;
    }

    public function closeCreditNoteModal(): void
    {
        $this->showCreditNoteModal = false;
        $this->creditNoteInvoice  = [];
        $this->creditNoteItems    = [];
        $this->creditNoteTotal    = 0;
        $this->creditNoteReason   = '';
        $this->creditNotePayment  = 'Efectivo';
        $this->creditNoteObs      = '';
    }

    public function toggleCreditNoteItem(int $index): void
    {
        $this->creditNoteItems[$index]['selected'] = !$this->creditNoteItems[$index]['selected'];
        $this->recalcCreditNoteTotal();
    }

    public function selectAllCreditNoteItems(): void
    {
        foreach ($this->creditNoteItems as &$item) {
            $item['selected'] = true;
        }
        $this->recalcCreditNoteTotal();
    }

    public function deselectAllCreditNoteItems(): void
    {
        foreach ($this->creditNoteItems as &$item) {
            $item['selected'] = false;
        }
        $this->creditNoteTotal = 0;
    }

    public function updatedCreditNoteReason(): void
    {
        if ($this->creditNoteReason === 'VOID_ELECTRONIC_INVOICE') {
            foreach ($this->creditNoteItems as &$item) {
                $item['selected'] = true;
                $item['quantity'] = $item['max_qty'];
                $subtotal = round($item['unit_price'] * $item['quantity'], 2);
                $item['subtotal'] = $subtotal;
                $item['total']    = $subtotal + round($subtotal * $item['tax'] / 100, 2);
            }
        }
        $this->recalcCreditNoteTotal();
    }

    public function updatedCreditNoteItems(): void
    {
        foreach ($this->creditNoteItems as &$item) {
            $qty = max(0, min((float)($item['quantity'] ?? 0), $item['max_qty']));
            $item['quantity'] = $qty;
            $subtotal = round($item['unit_price'] * $qty, 2);
            $item['subtotal'] = $subtotal;
            $item['total']    = $subtotal + round($subtotal * $item['tax'] / 100, 2);
        }
        $this->recalcCreditNoteTotal();
    }

    private function recalcCreditNoteTotal(): void
    {
        $this->creditNoteTotal = collect($this->creditNoteItems)
            ->where('selected', true)
            ->sum('total');
    }

    public function submitCreditNote(): void
    {
        $this->ensureTenantConnection();

        $invoice = VntInvoices::find($this->creditNoteInvoice['id'] ?? 0);
        if (!$invoice || $invoice->status_payment === 'PAGADO') {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No se puede crear una nota crédito a una factura ya pagada.']);
            $this->closeCreditNoteModal();
            return;
        }

        $this->validate([
            'creditNoteReason' => 'required|string',
            'creditNoteItems'  => 'required|array|min:1',
        ]);

        $selectedItems = collect($this->creditNoteItems)->where('selected', true);

        if ($selectedItems->isEmpty()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Debe seleccionar al menos un ítem.']);
            return;
        }

        try {
            $invoiceApiId  = $this->creditNoteInvoice['invoice_api_data_id'] ?? null;
            $customerApiId = $this->creditNoteInvoice['customer_api_id'] ?? null;

            if (!$invoiceApiId) {
                $this->dispatch('show-toast', ['type' => 'error', 'message' => 'La factura no tiene ID de Alegra. No se puede crear la nota crédito.']);
                return;
            }

            $tenant = session('tenant_id') ? Tenant::find(session('tenant_id')) : null;
            if (!$tenant || !TenantConfigManager::hasFacturacionConfig($tenant)) {
                $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No hay configuración de facturación para este tenant.']);
                return;
            }

            $facturacionService = FacturacionService::forTenant($tenant);

            // Si no tenemos el cliente, lo consultamos desde la factura en Alegra
            if (!$customerApiId) {
                $alegraInvoice = $facturacionService->getApiClient()->getInvoice($invoiceApiId);
                $customerApiId = $alegraInvoice['data']['client']['id']
                    ?? $alegraInvoice['data']['contact']['id']
                    ?? null;
            }

            if (!$customerApiId) {
                $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No se pudo obtener el cliente de la factura en Alegra.']);
                return;
            }

            $alegraItems = $selectedItems
                ->filter(fn($item) => !empty($item['item_api_id']))
                ->map(fn($item) => [
                    'id'       => (string) $item['item_api_id'],
                    'name'     => $item['name'],
                    'tax'      => !empty($item['tax_api_id']) ? [['id' => (string) $item['tax_api_id']]] : [],
                    'price'    => (float) $item['unit_price'],
                    'quantity' => (float) $item['quantity'],
                ])
                ->values()
                ->toArray();

            if (empty($alegraItems)) {
                $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Ningún ítem seleccionado tiene ID de Alegra.']);
                return;
            }

            $creditNoteTotal       = (int) round($selectedItems->sum('total'));
            $creditNoteTotalAlegra = round($selectedItems->sum('total'), 2);

            $alegraPayload = [
                'client'   => ['id' => (string) $customerApiId],
                'date'     => now()->format('Y-m-d'),
                'items'    => $alegraItems,
                'type'     => $this->creditNoteReason,
                'invoices' => [
                    ['id' => (int) $invoiceApiId, 'amount' => $creditNoteTotalAlegra],
                ],
            ];

            Log::info('📤 Enviando nota crédito a Alegra', [
                'json_enviado' => json_encode($alegraPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            ]);

            $alegraResponse = $facturacionService->createCreditNote($invoiceApiId, $alegraPayload);

            Log::info('📥 Respuesta completa de Alegra (nota crédito)', [
                'success'       => $alegraResponse['success'] ?? false,
                'status'        => $alegraResponse['status'] ?? null,
                'message'       => $alegraResponse['message'] ?? null,
                'data_completa' => $alegraResponse['data'] ?? null,
            ]);

            if (!($alegraResponse['success'] ?? false)) {
                $alegraError = $alegraResponse['message']
                    ?? $alegraResponse['data']['message']
                    ?? 'Error desconocido de Alegra';

                Log::warning('⚠️ No se pudo crear la nota crédito en Alegra', [
                    'error_msg'   => $alegraError,
                    'status_code' => $alegraResponse['status'] ?? null,
                ]);

                $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error en Alegra: ' . $alegraError]);
                return;
            }

            // ── Alegra OK: guardar localmente ───────────────────────────────
            $alegraId = $alegraResponse['data']['id']
                ?? $alegraResponse['data']['data']['id']
                ?? $alegraResponse['data']['creditNote']['id']
                ?? $alegraResponse['data']['consecutivo']
                ?? null;

            VntInvoices::where('id', $this->creditNoteInvoice['id'])
                ->update([
                    'creditNote'   => $creditNoteTotal,
                    'creditNoteId' => $alegraId,
                ]);

            Log::info('✅ Nota crédito creada en Alegra', [
                'alegra_credit_note_id' => $alegraId,
                'invoice_local_id'      => $this->creditNoteInvoice['id'],
            ]);

            // ── Reintegrar stock al inventario ──────────────────────────────
            $storeId = $invoice->warehouseId;
            foreach ($selectedItems as $item) {
                if (empty($item['item_id'])) {
                    continue;
                }
                $itemStore = InvItemsStore::where('itemId', $item['item_id'])
                    ->where('storeId', $storeId)
                    ->first();
                if ($itemStore) {
                    $newStock = $itemStore->stock_items_store + (float) $item['quantity'];
                    $itemStore->update(['stock_items_store' => $newStock]);
                    Log::info('📦 Stock reintegrado', [
                        'item_id'      => $item['item_id'],
                        'store_id'     => $storeId,
                        'qty_returned' => $item['quantity'],
                        'new_stock'    => $newStock,
                    ]);
                }
            }

            $this->closeCreditNoteModal();
            $this->dispatch('show-toast', [
                'type'    => 'success',
                'message' => 'Nota crédito creada exitosamente' . ($alegraId ? " (ID Alegra: {$alegraId})" : '') . '.',
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error creando nota crédito: ' . $e->getMessage());
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error al crear la nota crédito: ' . $e->getMessage()]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

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
}
