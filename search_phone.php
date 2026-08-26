<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$databases = DB::select('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME LIKE "%company_%"');
$found = false;

foreach ($databases as $db) {
    config(['database.connections.tenant.database' => $db->SCHEMA_NAME]);
    DB::purge('tenant');
    try {
        $contacts = DB::connection('tenant')->select('SELECT phone, cellphone FROM vnt_contacts WHERE identification = "900500298" LIMIT 1');
        if (!empty($contacts)) {
            echo "En DB: " . $db->SCHEMA_NAME . "\n";
            echo "Teléfono: " . ($contacts[0]->phone ?? 'VACIO') . "\n";
            echo "Celular: " . ($contacts[0]->cellphone ?? 'VACIO') . "\n";
            $found = true;
        }
        
        $logs = DB::connection('tenant')->select('SELECT old_values, new_values FROM cnf_model_logs WHERE old_values LIKE "%900500298%" OR new_values LIKE "%900500298%"');
        foreach($logs as $log) {
            $old = json_decode($log->old_values, true);
            if (isset($old['phone']) && $old['phone']) {
                echo "Log Teléfono: " . $old['phone'] . "\n";
                $found = true;
            }
            if (isset($old['cellphone']) && $old['cellphone']) {
                echo "Log Celular: " . $old['cellphone'] . "\n";
                $found = true;
            }
        }
        
        $invoices = DB::connection('tenant')->select('SELECT * FROM vnt_invoices WHERE (id = 57139 OR invoice_number = "113098" OR customer_data LIKE "%900500298%") LIMIT 1');
        foreach($invoices as $inv) {
            echo "Factura encontrada en: " . $db->SCHEMA_NAME . "\n";
            $data = json_decode($inv->customer_data ?? '{}', true);
            if(isset($data['phone'])) {
                echo "Factura Teléfono: " . $data['phone'] . "\n";
                $found = true;
            }
            if(isset($data['cellphone'])) {
                echo "Factura Celular: " . $data['cellphone'] . "\n";
                $found = true;
            }
        }
    } catch (\Exception $e) {
        // Ignorar si la tabla no existe en alguna DB
    }
}

if (!$found) {
    echo "No se encontró ningún teléfono en ninguna base de datos para este NIT.\n";
}
