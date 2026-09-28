<?php

namespace App\Jobs\Tenant\WordPress;

use App\Models\Auth\Tenant;
use App\Models\Tenant\Items\Items;
use App\Services\Tenant\TenantManager;
use App\Services\Tenant\WordPress\WordPressService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncWordPressPricingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tenantId;
    public $itemIds;

    public function __construct($tenantId, array $itemIds)
    {
        $this->tenantId = $tenantId;
        $this->itemIds = $itemIds;
    }

    public function handle(WordPressService $wpService, TenantManager $tenantManager)
    {
        $tenant = Tenant::find($this->tenantId);
        if (!$tenant) {
            Log::error('SyncWordPressPricingJob: Tenant no encontrado.', ['tenant_id' => $this->tenantId]);
            return;
        }

        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);

        if (!$wpService->isConfigured()) {
            Log::warning('SyncWordPressPricingJob: WordPress no está configurado para el tenant.', ['tenant_id' => $this->tenantId]);
            return;
        }

        $items = Items::whereIn('id', $this->itemIds)->with('pricingParams')->get();

        foreach ($items as $item) {
            $params = $item->pricingParams;
            if (!$params) continue;

            $wpProduct = $wpService->findProductBySku($item->sku);
            if (!$wpProduct) {
                Log::warning("SyncWordPressPricingJob: Producto SKU {$item->sku} no encontrado en WP.");
                continue;
            }

            // Construir reglas de descuento
            $rules = [];
            if ($params->scale_1_qty > 0 && $params->scale_1_discount > 0) $rules[(int)$params->scale_1_qty] = (float)$params->scale_1_discount;
            if ($params->scale_2_qty > 0 && $params->scale_2_discount > 0) $rules[(int)$params->scale_2_qty] = (float)$params->scale_2_discount;
            if ($params->scale_3_qty > 0 && $params->scale_3_discount > 0) $rules[(int)$params->scale_3_qty] = (float)$params->scale_3_discount;
            if ($params->scale_4_qty > 0 && $params->scale_4_discount > 0) $rules[(int)$params->scale_4_qty] = (float)$params->scale_4_discount;

            // Por defecto
            $min = 1; 
            $step = 1; 

            // Determinar si es variación
            $parentId = $wpProduct['is_variation'] ? $wpProduct['parent_id'] : null;

            // Actualizar el regular_price en WP (Precio Página Web)
            if ($params->web_price > 0) {
                $wpService->updateProductPrice($wpProduct['id'], $params->web_price);
            }

            if (!empty($rules)) {
                // Actualizar escalas
                $result = $wpService->updateTieredPricing($wpProduct['id'], $rules, $min, $step, $parentId, null);
                if (!$result['success']) {
                    Log::error("SyncWordPressPricingJob: Error al actualizar escalas para SKU {$item->sku}", ['error' => $result]);
                } else {
                    Log::info("SyncWordPressPricingJob: Escalas actualizadas para SKU {$item->sku}");
                }
            }
        }
    }
}
