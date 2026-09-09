<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenant = App\Models\Central\Tenant::first();
tenancy()->initialize($tenant);
$company = App\Models\Tenant\Customer\VntCompany::find(4205);
file_put_contents('customer_dump.json', json_encode($company->toArray(), JSON_PRETTY_PRINT));
echo "Dumped to customer_dump.json\n";
