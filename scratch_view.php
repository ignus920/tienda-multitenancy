<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$tenant = \App\Models\Auth\Tenant::find(131) ?? \App\Models\Auth\Tenant::first();
app(\App\Services\Tenant\TenantManager::class)->setConnection($tenant);
tenancy()->initialize($tenant);

$view = \Illuminate\Support\Facades\DB::connection('tenant')->select("SHOW CREATE VIEW v_mov_pt");
file_put_contents('scratch_view.json', json_encode($view));
echo "Done\n";
