<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenant = App\Models\Auth\Tenant::find("82527a3d-e479-4eba-91e2-4067d412c2e4");

if (!$tenant) {
    echo "Tenant company_131 not found.\n";
    exit;
}

echo "Found Tenant: {$tenant->id} - {$tenant->name} - {$tenant->db_name}\n";
try {
    app(App\Services\Tenant\TenantManager::class)->setConnection($tenant);
} catch (\Exception $e) {
    echo "Error setting connection: " . $e->getMessage() . "\n";
    exit;
}

echo "--- VNT_WAREHOUSES (from tenant connection) ---\n";
try {
    print_r(App\Models\Tenant\Customer\VntWarehouse::all()->toArray());
} catch (\Exception $e) {
    echo "Error reading vnt_warehouses: " . $e->getMessage() . "\n";
}

echo "--- INV_STORE (from tenant connection) ---\n";
try {
    print_r(App\Models\Tenant\Items\InvStore::all()->toArray());
} catch (\Exception $e) {
    echo "Error reading inv_store: " . $e->getMessage() . "\n";
}
