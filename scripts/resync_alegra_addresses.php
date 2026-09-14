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
    ->get();

echo "Modo: " . ($dryRun ? "SIMULACIÓN (no se escribe nada en Alegra)" : "REAL (se va a actualizar en Alegra)") . PHP_EOL;
echo "Clientes con api_data_id a revisar: {$companies->count()}" . PHP_EOL . PHP_EOL;

$updated = 0;
$skippedAlreadySet = 0;
$skippedNoWarehouse = 0;
$errors = 0;

foreach ($companies as $company) {
    $warehouse = $company->mainWarehouse;

    if (!$warehouse) {
        $skippedNoWarehouse++;
        echo "⚠️  #{$company->id} ({$company->businessName}) — sin bodega/sucursal principal, no se puede calcular su dirección. Se omite." . PHP_EOL;
        continue;
    }

    // Solo se lee de Alegra para saber si el campo está vacío — el valor a
    // enviar (si hace falta) sale siempre del ERP, nunca de esta lectura.
    $current = $apiClient->getContact((int) $company->api_data_id);

    if (!($current['success'] ?? false)) {
        $errors++;
        echo "⚠️  #{$company->id} ({$company->businessName}) — no se pudo leer el contacto {$company->api_data_id} en Alegra: " . ($current['message'] ?? 'error desconocido') . PHP_EOL;
        continue;
    }

    $currentAddress = $current['data']['address'] ?? [];
    $cityIsEmpty = empty($currentAddress['city']);
    $deptIsEmpty = empty($currentAddress['department']);

    if (!$cityIsEmpty && !$deptIsEmpty) {
        $skippedAlreadySet++;
        continue; // Ya tiene algo cargado en Alegra, no se toca.
    }

    $cityId = $warehouse->cityId ?? null;
    $target = \App\Services\Facturacion\AlegraAddressResolver::resolve($cityId ? (int) $cityId : null);

    echo ($dryRun ? "[SIMULACIÓN] " : "") . "#{$company->id} ({$company->businessName}, api_data_id={$company->api_data_id}) — vacío en Alegra, se completa con: "
        . "city='{$target['cityName']}', department='{$target['departmentName']}'" . PHP_EOL;

    if (!$dryRun) {
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
            $errors++;
            echo "❌ Error actualizando #{$company->id}: " . ($result['message'] ?? 'desconocido') . PHP_EOL;
        }
    } else {
        $updated++; // Contabilizado como "se actualizaría" en modo simulación.
    }

    usleep(400000); // ~0.4s entre llamadas (lectura + escritura) para no saturar la API de Alegra
}

echo PHP_EOL . "Listo. " . ($dryRun ? "Se completarían" : "Completados") . ": {$updated}. Ya tenían algo cargado (sin tocar): {$skippedAlreadySet}. Sin bodega principal (omitidos): {$skippedNoWarehouse}. Errores: {$errors}." . PHP_EOL;
