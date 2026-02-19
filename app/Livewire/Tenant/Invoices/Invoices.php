<?php

namespace App\Livewire\Tenant\Invoices;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Invoices\VntInvoices;
use App\Models\Tenant\Invoices\VntInvoicesXsales;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\Facturacion\FacturacionService;
use App\Services\Facturacion\TenantConfigManager;
use App\Traits\HasCompanyConfiguration;

class Invoices extends Component
{
    use WithPagination, HasCompanyConfiguration;

    public $search = '';
    public $perPage = 12;
    public $sortField = 'vnt_invoices.invoiceNumber';
    public $sortDirection = 'desc';

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

                // Analizar estructura de respuesta
                $respData = $apiResponse['data'] ?? [];

                // Intentar obtener URL de varios posibles campos
                $printUrl = $respData['pdf'] ??
                            $respData['publicUrl'] ??
                            ($respData['data']['publicUrl'] ?? null);

                if ($apiResponse['success'] && !empty($printUrl)) {
                    Log::info('✅ URL de PDF de factura obtenida', ['url' => $printUrl]);

                    $this->dispatch('open-print-window', [
                        'url' => $printUrl,
                        'format' => 'carta'
                    ]);

                    $this->dispatch('show-toast', [
                        'type' => 'success',
                        'message' => 'Factura #' . $invoice->invoiceNumber . ' preparada para impresión.'
                    ]);
                    return;
                } else {
                    Log::warning('⚠️ No se obtuvo URL válida de Alegra para factura', [
                        'response' => $apiResponse,
                        'invoice_id' => $invoiceId
                    ]);
                    $this->dispatch('show-toast', [
                        'type' => 'warning',
                        'message' => 'No se pudo obtener el PDF de la factura desde Alegra. Verifique que esté correctamente emitida.'
                    ]);
                }
            } else {
                Log::info('ℹ️ Facturación no configurada para obtener PDF', [
                    'has_config' => $hasFacturacionConfig,
                    'has_api_id' => !empty($invoice->api_data_id)
                ]);
                $this->dispatch('show-toast', [
                    'type' => 'info',
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

    /**
     * Extraer mensaje de error más legible del response de stamp
     */
    private function extractStampErrorMessage(array $stampResponse): string
    {
        $message = $stampResponse['message'] ?? 'Error desconocido';

        // Mejorar mensajes comunes de la DIAN
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
                ->leftJoin("vnt_contacts as c", "c.id", "=", DB::raw("COALESCE(qr.customerId, qd.customerId)"))
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

            // Aplicar búsqueda
            $query->when($this->search, function ($q) {
                $search = '%' . $this->search . '%';
                $q->where(function ($subQ) use ($search) {
                    $subQ->where('vnt_invoices.invoiceNumber', 'like', $search)
                        ->orWhere(DB::raw("CONCAT(COALESCE(c.firstName, ''), ' ', COALESCE(c.lastName, ''))"), 'like', $search)
                        ->orWhere(DB::raw("CONCAT(COALESCE(u.name, ''), ' ', COALESCE(u.name, ''))"), 'like', $search);
                });
                // Usar HAVING para campos agregados
                $q->havingRaw("MAX(remission_consecutives.remission_consecutive) LIKE ?", [$search]);
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
