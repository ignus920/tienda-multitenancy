<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$tenant = \App\Models\Auth\Tenant::first();
app(\App\Services\Tenant\TenantManager::class)->setConnection($tenant);
tenancy()->initialize($tenant);
$columns = Illuminate\Support\Facades\Schema::connection('tenant')->getColumnListing('inv_detail_remissions');
file_put_contents('scratch_cols.json', json_encode($columns));
echo "Done\n";
