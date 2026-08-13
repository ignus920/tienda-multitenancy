<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Auth\Tenant;
use App\Models\Tenant\Items\Items;
use App\Services\Tenant\TenantManager;
use App\Services\Tenant\WordPress\WordPressService;
use App\Exports\GenericExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Exception;

class CheckWordPressSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wp:check-sync {--tenant= : ID del tenant específico}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un reporte en Excel de las fotos de productos en el ERP y su sincronización en WooCommerce';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->option('tenant');

        if (!$tenantId) {
            $this->error('Error: Debe especificar un inquilino usando la opción --tenant= (ej. --tenant=fervicom)');
            return self::FAILURE;
        }

        $tenant = Tenant::where('id', $tenantId)->first();
        if (!$tenant) {
            $this->error("Error: No se encontró el inquilino '{$tenantId}'.");
            return self::FAILURE;
        }

        $this->info("📌 Inicializando inquilino: {$tenant->name} ({$tenant->id})");

        // Configurar conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);

        $wpService = app(WordPressService::class);

        if (!$wpService->isConfigured()) {
            $this->error("Error: WordPress no está configurado para este inquilino.");
            return self::FAILURE;
        }

        // Obtener configuración activa de WordPress
        $config = \App\Models\Tenant\WordPress\WordPressConfig::where('is_active', true)->first();
        if (!$config) {
            $this->error("Error: No se encontró configuración activa de WordPress en la base de datos del inquilino.");
            return self::FAILURE;
        }

        $baseUrl = rtrim($config->wp_url, '/') . '/wp-json/wc/v3/';
        $auth = [$config->wp_user, $config->wp_password];

        $this->info("📡 Descargando todos los productos de WooCommerce (lotes de 100)...");
        $wpSkus = [];
        $page = 1;
        $hasMore = true;
        
        while ($hasMore) {
            $this->line("   -> Descargando página {$page}...");
            try {
                $response = Http::withBasicAuth($auth[0], $auth[1])
                    ->timeout(45)
                    ->get($baseUrl . 'products', [
                        'page' => $page,
                        'per_page' => 100,
                        '_fields' => 'sku'
                    ]);

                if ($response->successful()) {
                    $products = $response->json();
                    if (empty($products)) {
                        $hasMore = false;
                    } else {
                        foreach ($products as $p) {
                            if (!empty($p['sku'])) {
                                $wpSkus[strtolower(trim($p['sku']))] = true;
                            }
                        }
                        $page++;
                    }
                } else {
                    $this->error("Error en respuesta de WooCommerce en página {$page}: " . $response->body());
                    $hasMore = false;
                }
            } catch (Exception $e) {
                $this->error("Excepción al consultar WooCommerce: " . $e->getMessage());
                $hasMore = false;
            }
        }

        $this->info("   ✅ Se obtuvieron " . count($wpSkus) . " SKUs únicos de WooCommerce.");

        $this->info("🔍 Analizando productos e imágenes en la base de datos del ERP...");
        
        $items = Items::withCount(['images' => function($q) {
            $q->whereNull('deleted_at');
        }])->get();

        $this->info("   ✅ Se encontraron " . $items->count() . " productos en el ERP.");

        $dataRows = [];
        foreach ($items as $item) {
            $skuClean = strtolower(trim($item->sku));
            
            $tieneFoto = ($item->images_count > 0) ? 'SI' : 'NO';
            $estaEnWp = isset($wpSkus[$skuClean]) ? 'SI' : 'NO';
            
            $dataRows[] = [
                'sku' => $item->sku,
                'name' => $item->name ?: $item->display_name,
                'tiene_foto' => $tieneFoto,
                'cantidad_fotos' => $item->images_count,
                'esta_en_wp' => $estaEnWp
            ];
        }

        $collection = collect($dataRows);
        $headings = ['SKU', 'Nombre del Producto', '¿Tiene Foto en ERP?', 'Cantidad Fotos ERP', '¿Está en WordPress?'];

        $fileName = 'Reporte_Sincronizacion_WordPress_' . date('Ymd_His') . '.xlsx';
        $filePath = 'reports/' . $fileName;

        $this->info("💾 Generando archivo Excel...");

        try {
            // Asegurar que la carpeta reports exista
            Storage::disk('local')->makeDirectory('reports');
            
            $export = new GenericExport($collection, $headings);
            Excel::store($export, $filePath, 'local');

            $fullPath = Storage::disk('local')->path($filePath);
            $this->info("🎉 Reporte generado con éxito!");
            $this->info("📍 Ruta del archivo: <comment>{$fullPath}</comment>");
        } catch (Exception $e) {
            $this->error("Error al generar el archivo de Excel: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
