<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Auth\Tenant;
use App\Models\Tenant\Items\Items;
use App\Services\Tenant\TenantManager;
use App\Services\Tenant\WordPress\WordPressService;
use Illuminate\Support\Facades\Log;

class SyncWordPressStock extends Command
{
    protected $signature   = 'wp:sync-stock {--tenant= : ID del tenant específico (opcional)} {--limit=0 : Limitar a N items para pruebas (0 = todos)} {--sku= : Sincronizar un único item por SKU}';
    protected $description = 'Sincroniza stock y precios del ERP hacia WooCommerce';

    public function handle(): int
    {
        $tenantId  = $this->option('tenant');
        $limit     = (int) $this->option('limit');
        $skuFiltro = $this->option('sku');

        Log::info('🚀 [WP-Stock] Iniciando comando SyncWordPressStock', [
            'tenant_id' => $tenantId ?? 'todos',
            'sku'       => $skuFiltro ?? 'todos',
            'limit'     => $limit ?: 'sin límite',
        ]);

        $this->info('🔄 Iniciando sincronización de stock con WordPress...');
        if ($skuFiltro) {
            $this->line("🎯 Modo prueba — solo SKU: <info>{$skuFiltro}</info>");
        }

        $tenantsQuery = Tenant::where('is_active', true);
        if ($tenantId) {
            $tenantsQuery->where('id', $tenantId);
        }
        $tenants = $tenantsQuery->get();

        if ($tenants->isEmpty()) {
            $this->error('No se encontraron tenants activos.');
            return self::FAILURE;
        }

        $totalOk    = 0;
        $totalFail  = 0;
        $totalSkip  = 0;

        foreach ($tenants as $tenant) {
            $this->line("\n📌 Tenant: <info>{$tenant->name}</info> (ID: {$tenant->id})");

            // Configurar conexión tenant
            $tenantManager = app(TenantManager::class);
            $tenantManager->setConnection($tenant);
            tenancy()->initialize($tenant);

            $wpService = app(WordPressService::class);

            if (!$wpService->isConfigured()) {
                $this->warn("  ⚠️  WordPress no configurado para este tenant — omitido");
                Log::warning('⚠️ [WP-Stock] Tenant sin WP configurado', ['tenant_id' => $tenant->id]);
                continue;
            }

            // Items activos con SKU
            $itemsQuery = Items::where('status', 1)
                ->whereNotNull('sku')
                ->where('sku', '!=', '')
                ->with(['tax', 'invItemsStore']);

            if ($skuFiltro) {
                $itemsQuery->where('sku', $skuFiltro);
            } elseif ($limit > 0) {
                $itemsQuery->limit($limit);
            }

            $items = $itemsQuery->get();

            $this->line("  📦 Items a sincronizar: <info>{$items->count()}</info>");

            $bar = $this->output->createProgressBar($items->count());
            $bar->start();

            foreach ($items as $item) {
                try {
                    $result = $wpService->syncItemStock($item);

                    if ($result['success']) {
                        $totalOk++;
                        if ($skuFiltro) {
                            $this->newLine();
                            $this->info("  ✅ Sincronizado: {$item->name}");
                            $this->table(
                                ['Campo', 'Valor'],
                                [
                                    ['SKU',           $result['sku']],
                                    ['WP Product ID', $result['wp_product_id'] ?? 'N/A'],
                                    ['Stock bruto',   $result['stock_bruto']],
                                    ['Reservas',      $result['reservas']],
                                    ['Stock → WP',    $result['stock_wp']],
                                    ['Precio → WP',   '$' . number_format($result['precio_wp'], 0, ',', '.')],
                                ]
                            );
                        }
                    } elseif (str_contains($result['message'], 'no encontrado en WP') || str_contains($result['message'], 'sin SKU') || str_contains($result['message'], 'Sin registro')) {
                        $totalSkip++;
                        if ($skuFiltro) {
                            $this->warn("  ⚠️  Omitido: " . $result['message']);
                        }
                    } else {
                        $totalFail++;
                        if ($skuFiltro) {
                            $this->error("  ❌ Error: " . $result['message']);
                        }
                    }
                } catch (\Exception $e) {
                    $totalFail++;
                    Log::error('❌ [WP-Stock] Error procesando item', [
                        'item_id' => $item->id,
                        'sku'     => $item->sku,
                        'error'   => $e->getMessage(),
                    ]);
                    if ($skuFiltro) {
                        $this->error("  ❌ Excepción: " . $e->getMessage());
                    }
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->newLine();
        $this->table(
            ['Resultado', 'Cantidad'],
            [
                ['✅ Sincronizados', $totalOk],
                ['⚠️  Omitidos (sin WP)', $totalSkip],
                ['❌ Fallidos', $totalFail],
            ]
        );

        Log::info('✅ [WP-Stock] Comando finalizado', [
            'ok'   => $totalOk,
            'skip' => $totalSkip,
            'fail' => $totalFail,
        ]);

        return self::SUCCESS;
    }
}
