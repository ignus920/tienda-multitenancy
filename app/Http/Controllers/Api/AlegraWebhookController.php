<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\Tenant\TenantManager;
use App\Models\Tenant\Invoices\VntInvoices;
use App\Models\Tenant\Invoices\VntInvoicesXsales;

class AlegraWebhookController extends Controller
{
    public function __construct(private TenantManager $tenantManager) {}

    public function handle(Request $request, $tenantId)
    {
        Log::info("🔔 Webhook de Alegra recibido para tenant {$tenantId}", $request->all());

        try {
            // 1. Configurar conexión del tenant correspondiente
            $this->tenantManager->setConnectionByTenantId($tenantId);

            $payload = $request->all();

            // 2. Extraer los datos del webhook de manera flexible
            $event = $payload['event'] ?? '';
            $data = $payload['data'] ?? [];
            
            $apiDataId = $data['id'] ?? $data['invoice']['id'] ?? $payload['id'] ?? null;
            $status = $data['status'] ?? $data['invoice']['status'] ?? $payload['status'] ?? null;

            if (!$apiDataId) {
                Log::warning('⚠️ Webhook de Alegra sin ID de factura válido', ['payload' => $payload]);
                return response()->json(['message' => 'Missing ID'], 400);
            }

            // 3. Buscar la factura en el ERP
            $invoice = VntInvoices::where('api_data_id', (string)$apiDataId)->first();

            if (!$invoice) {
                Log::warning('⚠️ Factura no encontrada para webhook de Alegra', ['api_data_id' => $apiDataId]);
                return response()->json(['message' => 'Invoice not found'], 404);
            }

            // 4. Evaluar el estado que reporta Alegra
            // En Alegra, una factura emitida suele tener estado 'open' o si es un evento es 'invoice.stamped'
            $isSuccess = ($event === 'invoice.stamped' || $event === 'invoice.created' || $status === 'open' || $status === 'stamped');
            $isFailure = ($event === 'invoice.failed' || $status === 'draft' || $status === 'failed');

            if ($isSuccess) {
                if ($invoice->status !== 'FACTURADO') {
                    $invoice->update(['status' => 'FACTURADO']);
                    $this->updateRelatedQuotesToFacturado($invoice);
                    Log::info("✅ Factura {$invoice->id} marcada como FACTURADO desde Webhook");
                }
            } elseif ($isFailure) {
                if ($invoice->status === 'EN PROCESO DIAN') {
                    $invoice->update(['status' => 'SIN EMITIR']);
                    Log::info("❌ Factura {$invoice->id} rechazada por la DIAN. Retornada a SIN EMITIR desde Webhook.");
                }
            } else {
                Log::info("ℹ️ Evento de factura ignorado por Webhook", ['event' => $event, 'status' => $status]);
            }

            return response()->json(['message' => 'Webhook procesado exitosamente']);

        } catch (\Exception $e) {
            Log::error("❌ Error procesando webhook de Alegra", [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Error interno'], 500);
        }
    }

    private function updateRelatedQuotesToFacturado(VntInvoices $invoice): void
    {
        try {
            $remissionIds = VntInvoicesXsales::where('invoiceId', $invoice->id)
                ->pluck('remissionId')
                ->toArray();

            if (empty($remissionIds)) {
                return;
            }

            $quotesToUpdate = DB::connection('tenant')
                ->table('inv_remissions')
                ->join('vnt_quotes', 'inv_remissions.quoteId', '=', 'vnt_quotes.id')
                ->whereIn('inv_remissions.id', $remissionIds)
                ->select('vnt_quotes.id', 'vnt_quotes.consecutive')
                ->get();

            foreach ($quotesToUpdate as $quote) {
                DB::connection('tenant')
                    ->table('vnt_quotes')
                    ->where('id', $quote->id)
                    ->update(['status' => 'FACTURADO']);
                
                Log::info('📋 Status de cotización actualizado a FACTURADO (desde Webhook)', [
                    'quote_id' => $quote->id,
                    'invoice_id' => $invoice->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('❌ Error actualizando cotizaciones desde Webhook', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
