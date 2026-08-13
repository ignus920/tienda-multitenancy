<?php

namespace App\Jobs\Tenant\WordPress;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Auth\Tenant;
use App\Models\Tenant\Items\Items;
use App\Services\Tenant\TenantManager;
use App\Services\Tenant\WordPress\WordPressService;
use Illuminate\Support\Facades\Log;

class SyncSingleItemStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenant;
    protected $itemId;

    /**
     * Create a new job instance.
     */
    public function __construct(Tenant $tenant, $itemId)
    {
        $this->tenant = $tenant;
        $this->itemId = $itemId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("🚀 [WP-Single-Stock-Job] Iniciando sincronización para el Tenant: {$this->tenant->name} y el Item ID: {$this->itemId}");

        try {
            // Inicializar la conexión de base de datos del tenant
            $tenantManager = app(TenantManager::class);
            $tenantManager->setConnection($this->tenant);
            
            if (!tenancy()->initialized) {
                tenancy()->initialize($this->tenant);
            }

            $wpService = app(WordPressService::class);

            if (!$wpService->isConfigured()) {
                Log::warning("⚠️ [WP-Single-Stock-Job] WordPress no está configurado para el Tenant: {$this->tenant->name} — Omitido");
                return;
            }

            $item = Items::with(['tax', 'invItemsStore'])->find($this->itemId);

            if (!$item) {
                Log::warning("⚠️ [WP-Single-Stock-Job] Item ID {$this->itemId} no encontrado en el tenant.");
                return;
            }

            if (empty($item->sku)) {
                Log::warning("⚠️ [WP-Single-Stock-Job] Item ID {$this->itemId} no tiene SKU.");
                return;
            }

            $result = $wpService->syncItemStock($item);

            if ($result['success']) {
                Log::info("✅ [WP-Single-Stock-Job] Sincronización exitosa para SKU: {$item->sku}");
            } else {
                Log::warning("⚠️ [WP-Single-Stock-Job] Error al sincronizar SKU {$item->sku}: {$result['message']}");
            }

        } catch (\Exception $e) {
            Log::error("❌ [WP-Single-Stock-Job] Fallo crítico al sincronizar Item ID {$this->itemId}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
