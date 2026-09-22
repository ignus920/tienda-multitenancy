<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$tenant = \App\Models\Auth\Tenant::first();
app(\App\Services\Tenant\TenantManager::class)->setConnection($tenant);
tenancy()->initialize($tenant);

$item = \App\Models\Tenant\Inventory\Items::where('sku', '9999999')->first();
if ($item) {
    $values = \App\Models\Tenant\Inventory\InvValues::where('itemId', $item->id)->get();
    file_put_contents('scratch_values.json', json_encode($values));
}
echo "Done\n";
