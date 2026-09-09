<?php

namespace App\Jobs\Tenant\WordPress;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use App\Services\Tenant\WordPress\WordPressService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WarmUpWpSkusCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenantId;

    /**
     * Create a new job instance.
     */
    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("🚀 [Job] Iniciando carga de caché de SKUs de WooCommerce para tenant: {$this->tenantId}");

        $tenant = Tenant::find($this->tenantId);
        if (!$tenant) {
            Log::error("❌ [Job] Tenant no encontrado: {$this->tenantId}");
            return;
        }

        // Configurar conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        
        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }

        $wpService = app(WordPressService::class);
        if (!$wpService->isConfigured()) {
            Log::warning("⚠️ [Job] WordPress no configurado para el tenant: {$this->tenantId}");
            return;
        }

        // Obtener todos los SKUs de WooCommerce de forma optimizada y concurrente
        $skus = $wpService->getAllProductSkus();

        if (!empty($skus)) {
            // Guardar en la caché persistente por 12 horas
            Cache::put('wp_active_skus_' . $this->tenantId, $skus, 43200);
            Log::info("✅ [Job] Caché de SKUs de WooCommerce actualizada con " . count($skus) . " SKUs para tenant: {$this->tenantId}");
        } else {
            Log::warning("⚠️ [Job] No se obtuvieron SKUs de WooCommerce para el tenant: {$this->tenantId}");
        }
    }
}
