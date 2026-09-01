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
        
        // El webhook no tiene sesión, así que debemos identificar el tenant por parámetro
        if (!$tenantId) {
            return response()->json(['error' => 'Falta el parámetro tenant_id en la URL.'], 400);
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return response()->json(['error' => 'tenant_id no válido o inactivo.'], 404);
        }

        // Inicializar la base de datos de este tenant específico
        tenancy()->initialize($tenant);
        $tenantManager->setConnection($tenant);

        $data = $request->all();
        
        try {
            $warrantyRequest = VntChatbotWarrantyRequest::create([
                'company_name' => $data['company_name'] ?? 'No especificada',
                'reference_number' => $data['op_number'] ?? ($data['reference_number'] ?? 'Sin referencia'),
                'advisor_name' => $data['advisor_name'] ?? 'Autogestionado',
                'product_details' => $data['product_details'] ?? 'No especificado',
                'description' => $data['description'] ?? 'Sin descripción',
                'media_urls' => $data['media_urls'] ?? [],
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Solicitud de garantía recibida y guardada en el ERP.',
                'id' => $warrantyRequest->id
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error guardando solicitud de garantía del chatbot: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }
}
