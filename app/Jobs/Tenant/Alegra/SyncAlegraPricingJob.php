<?php

namespace App\Jobs\Tenant\Alegra;

use App\Models\Auth\Tenant;
use App\Models\Tenant\Items\Items;
use App\Services\Facturacion\FacturacionService;
use App\Services\Tenant\TenantManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncAlegraPricingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tenantId;
    public $itemIds;

    public function __construct($tenantId, array $itemIds)
    {
        $this->tenantId = $tenantId;
        $this->itemIds = $itemIds;
    }

    public function handle(FacturacionService $facturacionService, TenantManager $tenantManager)
    {
        $tenant = Tenant::find($this->tenantId);
        if (!$tenant) {
            Log::error('SyncAlegraPricingJob: Tenant no encontrado.', ['tenant_id' => $this->tenantId]);
            return;
        }

        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);

        // Fetch the items updated
        $items = Items::whereIn('id', $this->itemIds)->with(['pricingParams', 'values'])->get();

        foreach ($items as $item) {
            // Check if item has remote_id (Alegra ID)
            $remoteId = $item->remote_id ?? null;

            if (!$remoteId) {
                Log::warning("SyncAlegraPricingJob: Producto SKU {$item->sku} no tiene ID de Alegra (remote_id).");
                continue;
            }

            // Get standard prices
            $precioRegular = $item->values()->where('type', 'precio')->where('label', 'Precio Regular')->first();
            $precioBase = $item->values()->where('type', 'precio')->where('label', 'Precio Base')->first();

            // Construct payload to update in Alegra
            $productData = [
                'name' => $item->name,
                'reference' => $item->sku,
                'price' => [
                    [
                        'idPriceList' => 1, // Main price list (default in Alegra)
                        'price' => $precioRegular ? $precioRegular->values : 0
                    ]
                ]
            ];

            // Some businesses map Precio Base to a different price list. For now we update the main price.
            $result = $facturacionService->syncProduct($productData, $remoteId);

            if (!$result['success']) {
                Log::error("SyncAlegraPricingJob: Error al actualizar precio en Alegra para SKU {$item->sku}", ['error' => $result]);
            } else {
                Log::info("SyncAlegraPricingJob: Precio actualizado en Alegra para SKU {$item->sku}");
            }
        }
    }
}
