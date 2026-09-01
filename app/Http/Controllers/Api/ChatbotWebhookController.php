<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\Sales\VntChatbotWarrantyRequest;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Log;

class ChatbotWebhookController extends Controller
{
    /**
     * Endpoint para recibir solicitudes de garantía desde el Chatbot (DosilBot)
     * URL esperada: POST /api/webhooks/chatbot/warranties?tenant_id={ID}
     */
    public function receiveWarranty(Request $request, TenantManager $tenantManager)
    {
        $tenantId = $request->query('tenant_id');
        
        if (!$tenantId) {
            return response()->json(['error' => 'Falta el parámetro tenant_id en la URL.'], 400);
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return response()->json(['error' => 'tenant_id no válido o inactivo.'], 404);
        }

        tenancy()->initialize($tenant);
        $tenantManager->setConnection($tenant);

        $data = $request->all();
        $opNumber = $data['op_number'] ?? ($data['reference_number'] ?? null);
        $companyName = $data['company_name'] ?? null;
        $productDetails = $data['product_details'] ?? null;

        if (!$opNumber || !$companyName || !$productDetails) {
            return response()->json(['error' => 'Faltan parámetros obligatorios (op_number, company_name, product_details).'], 400);
        }

        try {
            // 1. Validar que la OP (Remisión) exista
            $remission = \App\Models\Tenant\Remissions\InvRemissions::with(['quote.customer', 'details.item'])
                ->where('consecutive', $opNumber)
                ->first();

            if (!$remission) {
                return response()->json(['error' => "La OP {$opNumber} no existe en nuestros registros."], 400);
            }

            // 2. Validar que la OP corresponda al cliente (búsqueda por coincidencias parciales)
            $customer = $remission->quote->customer ?? null;
            if (!$customer) {
                return response()->json(['error' => "La OP {$opNumber} no tiene un cliente asociado."], 400);
            }

            $customerNames = strtolower(($customer->businessName ?? '') . ' ' . ($customer->firstName ?? '') . ' ' . ($customer->lastName ?? ''));
            $inputCompanyName = strtolower($companyName);
            
            // Validar si alguna parte del nombre dado por el chatbot coincide con el nombre real
            $words = explode(' ', $inputCompanyName);
            $matchFound = false;
            foreach ($words as $word) {
                if (strlen($word) > 3 && strpos($customerNames, $word) !== false) {
                    $matchFound = true;
                    break;
                }
            }

            if (!$matchFound && strpos($customerNames, $inputCompanyName) === false && strpos($inputCompanyName, trim($customerNames)) === false) {
                return response()->json(['error' => "La OP {$opNumber} no pertenece al cliente indicado ('{$companyName}')."], 400);
            }

            // 3. Validar que la OP contenga el producto (por Código Interno)
            $productFound = false;
            foreach ($remission->details as $detail) {
                $itemCode = strtolower($detail->item->internal_code ?? '');
                
                // Si el chatbot envía el código o un texto que incluye el código, lo aceptamos
                if ($itemCode && strpos(strtolower($productDetails), $itemCode) !== false) {
                    $productFound = true;
                    break;
                }
            }

            if (!$productFound) {
                return response()->json(['error' => "El producto con código indicado no se encontró dentro de la OP {$opNumber}."], 400);
            }

            // Si pasa todas las validaciones, procedemos a guardar la solicitud
            $warrantyRequest = VntChatbotWarrantyRequest::create([
                'company_name' => $companyName,
                'reference_number' => $opNumber,
                'advisor_name' => $data['advisor_name'] ?? 'Autogestionado',
                'product_details' => $productDetails,
                'description' => $data['description'] ?? 'Sin descripción',
                'media_urls' => $data['media_urls'] ?? [],
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Solicitud de garantía recibida, validada y guardada en el ERP.',
                'id' => $warrantyRequest->id
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error guardando solicitud de garantía del chatbot: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }
}
