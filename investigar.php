<?php

// Asegurar que el script se corra desde la consola
if (php_sapi_name() !== 'cli') {
    die("Solo ejecutable desde CLI");
}

// Cargar y arrancar Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Auth\Tenant;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvItemsStore;
use App\Models\Tenant\Movements\InvDetailInventoryAdjustment;
use App\Services\Tenant\TenantManager;
use App\Services\Facturacion\ApiClient;

$dbName = 'company_132_d46d7c90_bf7d_4b06_8fff_cda14803c4f2';
$ref = '7800250';

echo "=========================================================\n";
echo "🔍 INICIANDO INVESTIGACIÓN DE INVENTARIO PARA SKU: {$ref}\n";
echo "=========================================================\n\n";

// 1. Buscar y activar el tenant
$tenant = Tenant::where('db_name', $dbName)->first();
if (!$tenant) {
    die("❌ Error: No se encontró el tenant con BD: {$dbName}\n");
}

app(TenantManager::class)->setConnection($tenant);
echo "🔗 Conexión establecida con el tenant: {$tenant->name} ({$tenant->db_name})\n\n";

// 2. Buscar producto localmente
$item = Items::where('sku', $ref)->orWhere('internal_code', $ref)->first();

if (!$item) {
    echo "❌ No se encontró ningún item local con referencia/sku: {$ref}\n\n";
} else {
    echo "=== DATOS LOCALES DEL PRODUCTO (ERP) ===\n";
    echo "ID Local: {$item->id}\n";
    echo "Alegra ID (api_data_id): " . ($item->api_data_id ?? 'N/A') . "\n";
    echo "Nombre: {$item->name}\n";
    echo "SKU: {$item->sku}\n";
    echo "Código Interno: {$item->internal_code}\n";
    echo "Tipo: {$item->type}\n\n";

    // Obtener stock local
    $stocks = InvItemsStore::where('itemId', $item->id)->with('store')->get();
    echo "=== STOCK EN BODEGAS LOCALES ===\n";
    foreach ($stocks as $stock) {
        echo "Bodega: " . ($stock->store->name ?? 'Desconocida') . " (ID: {$stock->storeId}) -> Stock: {$stock->stock_items_store} (Inicial: {$stock->initial_stock})\n";
    }
    echo "\n";

    // Obtener historial de ajustes
    $adjustments = InvDetailInventoryAdjustment::where('itemId', $item->id)
        ->with(['inventoryAdjustment.store', 'inventoryAdjustment.reason'])
        ->get();

    echo "=== HISTORIAL DE AJUSTES EN EL ERP ===\n";
    if ($adjustments->isEmpty()) {
        echo "No hay ajustes de inventario para este producto.\n";
    } else {
        $sinSincronizar = 0;
        foreach ($adjustments as $detail) {
            $adj = $detail->inventoryAdjustment;
            if (!$adj) continue;
            
            $sincronizado = !empty($adj->api_data_id);
            if (!$sincronizado) {
                $sinSincronizar++;
            }
            
            echo "Ajuste ID: {$adj->id} | Consecutivo: " . ($adj->consecutive ?? 'N/A') . "\n";
            echo "  - Tipo: " . ($adj->type ?? 'N/A') . " | Cantidad: {$detail->quantity} | Fecha: " . ($adj->date ? $adj->date->format('Y-m-d H:i') : 'N/A') . "\n";
            echo "  - Sincronizado a Alegra: " . ($sincronizado ? "✅ SÍ (ID: {$adj->api_data_id})" : "❌ NO") . "\n";
        }
        echo "Total de ajustes locales sin sincronizar: {$sinSincronizar}\n\n";
    }
}

// 3. Consultar la API de Alegra
try {
    $apiClient = ApiClient::forTenant($tenant);
    echo "=== CONSULTANDO EN ALEGRA API ===\n";
    $response = $apiClient->getItems(['query' => $ref]);
    if ($response['success'] && !empty($response['data'])) {
        foreach ($response['data'] as $alegraItem) {
            echo "Alegra ID: {$alegraItem['id']}\n";
            echo "Nombre en Alegra: {$alegraItem['name']}\n";
            echo "Referencia en Alegra: " . ($alegraItem['reference'] ?? 'N/A') . "\n";
            if (isset($alegraItem['inventory'])) {
                $inv = $alegraItem['inventory'];
                echo "Inventario Total en Alegra: " . ($inv['quantity'] ?? '0') . "\n";
                if (!empty($inv['warehouses'])) {
                    echo "Inventario por Bodega en Alegra:\n";
                    foreach ($inv['warehouses'] as $wh) {
                        echo "  - Bodega: {$wh['name']} (ID: {$wh['id']}) -> Cantidad: {$wh['quantity']}\n";
                    }
                }
            } else {
                echo "Este item no es inventariable en Alegra.\n";
            }
            echo "----------------------------------------\n";
        }
    } else {
        echo "No se encontraron resultados en Alegra para la consulta: {$ref}\n";
        if (!$response['success']) {
            echo "Error de API: " . json_encode($response['error_details'] ?? $response['message']) . "\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Error al conectar con la API de Alegra: " . $e->getMessage() . "\n";
}

echo "\n🔍 INVESTIGACIÓN FINALIZADA.\n";
echo "=========================================================\n";
