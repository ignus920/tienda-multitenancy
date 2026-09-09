<?php

namespace App\Http\Controllers\Tenant\Portal;

use App\Http\Controllers\Controller;
use App\Models\Auth\Tenant;
use App\Services\Facturacion\FacturacionService;
use App\Services\Facturacion\TenantConfigManager;
use App\Traits\InteractsWithClientPortal;
use Illuminate\Support\Facades\Log;

class ClientPortalController extends Controller
{
    use InteractsWithClientPortal;

    /**
     * Redirige al PDF oficial (Alegra) de una factura del cliente.
     */
    public function invoicePdf(int $invoice)
    {
        $this->ensureTenantConnection();

        // Verifica que la factura pertenezca a la empresa del usuario del portal (aborta 403)
        $invoiceModel = $this->findClientInvoiceOrFail($invoice);

        if ($invoiceModel->status !== 'FACTURADO' || empty($invoiceModel->api_data_id)) {
            abort(404, 'Esta factura todavía no está emitida electrónicamente.');
        }

        $tenant = Tenant::find(session('tenant_id'));
        abort_if(!$tenant, 404);

        if (!TenantConfigManager::hasFacturacionConfig($tenant)) {
            abort(404, 'La facturación electrónica no está configurada.');
        }

        try {
            $response = (new FacturacionService($tenant))->getInvoicePdf((int) $invoiceModel->api_data_id);
            $data = $response['data'] ?? [];

            $url = $data['pdf']
                ?? $data['publicUrl']
                ?? $data['url']
                ?? ($data['data']['pdf'] ?? null)
                ?? ($data['data']['publicUrl'] ?? null);

            if (empty($url)) {
                Log::warning('Portal cliente: PDF de factura sin URL', [
                    'invoice_id' => $invoiceModel->id,
                    'api_data_id' => $invoiceModel->api_data_id,
                    'response_keys' => array_keys($data),
                ]);
                abort(502, 'No se pudo obtener el PDF de la factura en este momento. Intenta más tarde.');
            }

            return redirect()->away($url);
        } catch (\Throwable $e) {
            Log::error('Portal cliente: error obteniendo PDF de factura', [
                'invoice_id' => $invoiceModel->id,
                'error' => $e->getMessage(),
            ]);
            abort(502, 'No se pudo obtener el PDF de la factura en este momento.');
        }
    }
}
