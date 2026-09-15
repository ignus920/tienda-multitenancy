<?php

/**
 * Script de una sola vez para RE-SINCRONIZAR con Alegra el campo
 * Ciudad/Departamento de los clientes (vnt_companies) que ya tienen
 * api_data_id (ya existen como contacto en Alegra), aplicando el mismo
 * ajuste que ya corrige VntCompanyForm/CustomerForm para clientes nuevos.
 *
 * El ERP es quien manda: el valor correcto siempre se calcula desde la
 * ciudad registrada en el ERP (bodega principal del cliente), NUNCA desde
 * lo que ya tenga Alegra. Antes de escribir, solo se revisa si Alegra ya
 * tiene algo cargado en Municipio/Departamento:
 *   - Si está VACÍO en Alegra -> se envía el valor del ERP.
 *   - Si ya tiene algo (lo que sea) -> se deja intacto, no se toca.
 * Solo se envía el objeto "address" (ciudad, departamento, dirección, país,
 * código postal) — nunca nombre, identificación, régimen ni el resto del
 * contacto.
 *
 * VELOCIDAD: cada consulta a Alegra (vía el proxy) puede tardar varios
 * segundos. Con miles de contactos, revisarlos uno por uno tomaría horas.
 * Por eso la FASE DE REVISIÓN consulta Alegra EN PARALELO, en tandas de
 * $concurrency contactos a la vez (ver más abajo). La fase de ESCRITURA
 * (cuando $dryRun = false) sí es secuencial y más lenta a propósito —
 * solo aplica a los que de verdad hace falta corregir, que normalmente
 * son muchos menos que el total, y así es más fácil ver error por error
 * si algo falla.
 *
 * USO (en la terminal del VPS, dentro de la carpeta del proyecto):
 *
 *   1) Primero en modo simulación (no escribe nada en Alegra, solo muestra
 *      qué haría):
 *      php artisan tinker --execute="require base_path('scripts/resync_alegra_addresses.php');"
 *
 *   2) Revisa la salida. Si se ve bien, edita este archivo, cambia
 *      $dryRun = true;  por  $dryRun = false;
 *      y vuelve a correr el mismo comando para aplicarlo de verdad.
 *
 * Ajusta $tenantName más abajo si tu tenant no se llama "produccion".
 */

$dryRun = true; // <-- cambiar a false para aplicar los cambios reales
$tenantName = 'produccion'; // <-- ajustar si el tenant tiene otro nombre
$concurrency = 10; // <-- cuántas consultas a Alegra en paralelo durante la revisión (bajar si empiezan a salir errores)

$tenant = \App\Models\Auth\Tenant::where('name', $tenantName)->first();

if (!$tenant) {
    echo "❌ No se encontró el tenant '{$tenantName}'. Ajusta \$tenantName en el script." . PHP_EOL;
    return;
}

app(\App\Services\Tenant\TenantManager::class)->setConnection($tenant);
if (!tenancy()->initialized) {
    tenancy()->initialize($tenant);
}
\Illuminate\Support\Facades\DB::purge('tenant');

$config = \App\Services\Facturacion\DatabaseConfigService::getFacturacionConfigFromDatabase($tenant);

if (!$config) {
    echo "❌ No se pudo obtener la configuración de facturación para el tenant '{$tenantName}'." . PHP_EOL;
    return;
}

$apiClient = \App\Services\Facturacion\ApiClient::forConfig($config);

$companies = \App\Models\Tenant\Customer\VntCompany::whereNotNull('api_data_id')
    ->with('mainWarehouse')
    ->get()
    ->filter(function ($company) {
        return (bool) $company->mainWarehouse;
    });

$skippedNoWarehouse = \App\Models\Tenant\Customer\VntCompany::whereNotNull('api_data_id')->count() - $companies->count();

echo "Modo: " . ($dryRun ? "SIMULACIÓN (no se escribe nada en Alegra)" : "REAL (se va a actualizar en Alegra)") . PHP_EOL;
echo "Clientes con api_data_id y bodega principal a revisar: {$companies->count()} (concurrencia: {$concurrency})" . PHP_EOL;
if ($skippedNoWarehouse > 0) {
    echo "⚠️  {$skippedNoWarehouse} cliente(s) con api_data_id pero SIN bodega principal — se omiten." . PHP_EOL;
}
echo PHP_EOL;

// -----------------------------------------------------------------
// FASE 1: revisión en paralelo — quiénes tienen el campo vacío en Alegra
// -----------------------------------------------------------------
$startedAt = time();
$total = $companies->count();
$processed = 0;
$errors = 0;
$needsUpdate = []; // [company, target]

foreach ($companies->chunk($concurrency) as $chunk) {
    $ids = $chunk->pluck('api_data_id')->map(fn($id) => (int) $id)->all();
    $results = $apiClient->poolGetContacts($ids, $concurrency);

    foreach ($chunk as $company) {
        $processed++;
        $result = $results[(int) $company->api_data_id] ?? null;

        if (!$result || !$result['success']) {
            $errors++;
            echo "⚠️  #{$company->id} ({$company->businessName}) — no se pudo leer el contacto {$company->api_data_id} en Alegra: " . ($result['message'] ?? 'error desconocido') . PHP_EOL;
            continue;
        }

        $currentAddress = $result['data']['address'] ?? [];
        if (!empty($currentAddress['city']) && !empty($currentAddress['department'])) {
            continue; // Ya tiene algo cargado, no se toca.
        }

        $warehouse = $company->mainWarehouse;
        $cityId = $warehouse->cityId ?? null;
        $target = \App\Services\Facturacion\AlegraAddressResolver::resolve($cityId ? (int) $cityId : null);

        $needsUpdate[] = [
            'company' => $company,
            'warehouse' => $warehouse,
            'currentAddress' => $currentAddress,
            'target' => $target,
        ];
    }

    $elapsedMin = round((time() - $startedAt) / 60, 1);
    $needCount = count($needsUpdate);
    echo "… revisados {$processed}/{$total} ({$elapsedMin} min transcurridos) — {$needCount} necesitan completarse hasta ahora" . PHP_EOL;
}

echo PHP_EOL . "Revisión terminada. Clientes que necesitan completarse: " . count($needsUpdate) . ". Errores de lectura: {$errors}." . PHP_EOL . PHP_EOL;

// -----------------------------------------------------------------
// FASE 2: escritura (secuencial, solo sobre los que hacen falta)
// -----------------------------------------------------------------
$updated = 0;
$writeErrors = 0;

foreach ($needsUpdate as $item) {
    $company = $item['company'];
    $warehouse = $item['warehouse'];
    $currentAddress = $item['currentAddress'];
    $target = $item['target'];

    echo ($dryRun ? "[SIMULACIÓN] " : "") . "#{$company->id} ({$company->businessName}, api_data_id={$company->api_data_id}) — vacío en Alegra, se completa con: "
        . "city='{$target['cityName']}', department='{$target['departmentName']}'" . PHP_EOL;

    if ($dryRun) {
        $updated++;
        continue;
    }

    $payload = [
        'address' => [
            'address' => $currentAddress['address'] ?? ($warehouse->address ?? ''),
            'city' => $target['cityName'],
            'department' => $target['departmentName'],
            'country' => 'Colombia',
            'zipCode' => $currentAddress['zipCode'] ?? ($warehouse->postcode ?? ''),
        ],
    ];

    $result = $apiClient->updateContact((int) $company->api_data_id, $payload);

    if ($result['success'] ?? false) {
        $updated++;
    } else {
        $writeErrors++;
        echo "❌ Error actualizando #{$company->id}: " . ($result['message'] ?? 'desconocido') . PHP_EOL;
    }

    usleep(300000); // ~0.3s entre escrituras para no saturar la API de Alegra
}

echo PHP_EOL . "Listo. " . ($dryRun ? "Se completarían" : "Completados") . ": {$updated}. Sin bodega principal (omitidos): {$skippedNoWarehouse}. Errores de lectura: {$errors}. Errores de escritura: {$writeErrors}." . PHP_EOL;
