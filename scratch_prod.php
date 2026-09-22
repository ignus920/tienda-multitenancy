<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$tenant = \App\Models\Auth\Tenant::find(131) ?? \App\Models\Auth\Tenant::first();
app(\App\Services\Tenant\TenantManager::class)->setConnection($tenant);
tenancy()->initialize($tenant);

$item = \Illuminate\Support\Facades\DB::connection('tenant')->select("SELECT codigo FROM c_productos WHERE codigo = '9999999' OR id = (SELECT id FROM inv_items WHERE sku = '9999999')");
file_put_contents('scratch_prod.json', json_encode($item));
echo "Done\n";
