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

class SyncWordPressStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenant;

    /**
     * Create a new job instance.
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("🚀 [WP-Stock-Job] Iniciando sincronización automática para el Tenant: {$this->tenant->name} (ID: {$this->tenant->id})");

        try {
            // Inicializar la conexión de base de datos del tenant
            $tenantManager = app(TenantManager::class);
            $tenantManager->setConnection($this->tenant);
            
            if (!tenancy()->initialized) {
                tenancy()->initialize($this->tenant);
            }

            $wpService = app(WordPressService::class);

            if (!$wpService->isConfigured()) {
                Log::warning("⚠️ [WP-Stock-Job] WordPress no está configurado para el Tenant: {$this->tenant->name} — Omitido");
                return;
            }

            // Obtener todos los items activos con SKU (filtrado temporalmente para el test del item 6700137)
            $items = Items::where('status', 1)
                ->where(function($query) {
                    $query->where('internal_code', '6700137')
                          ->orWhere('sku', '6700137');
                })
                ->whereNotNull('sku')
                ->where('sku', '!=', '')
                ->with(['tax', 'invItemsStore'])
                ->get();

            Log::info("📦 [WP-Stock-Job] Sincronizando {$items->count()} items para el Tenant: {$this->tenant->name}");

            $ok = 0;
            $fail = 0;
            $skip = 0;

            foreach ($items as $item) {
                try {
                    $result = $wpService->syncItemStock($item);

                    if ($result['success']) {
                        $ok++;
                    } elseif (
                        str_contains($result['message'], 'no encontrado en WP') || 
                        str_contains($result['message'], 'sin SKU') || 
                        str_contains($result['message'], 'Sin registro')
                    ) {
                        $skip++;
                    } else {
                        $fail++;
                        Log::warning("⚠️ [WP-Stock-Job] Error al sincronizar item ID {$item->id} (SKU: {$item->sku}): {$result['message']}");
                    }
                } catch (\Exception $e) {
                    $fail++;
                    Log::error("❌ [WP-Stock-Job] Excepción sincronizando item ID {$item->id} (SKU: {$item->sku}): " . $e->getMessage());
                }
            }

            Log::info("✅ [WP-Stock-Job] Sincronización finalizada para el Tenant: {$this->tenant->name}. Resultados -> Exitosos: {$ok}, Omitidos: {$skip}, Fallidos: {$fail}");

        } catch (\Exception $e) {
            Log::error("❌ [WP-Stock-Job] Fallo crítico al procesar el Tenant ID {$this->tenant->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
