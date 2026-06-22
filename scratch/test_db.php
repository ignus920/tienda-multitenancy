<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sqlFilePath = 'db rap/rap (2).sql';

if (!file_exists($sqlFilePath)) {
    echo "SQL backup file not found at: $sqlFilePath\n";
    exit(1);
}

echo "Reading SQL backup file...\n";
$handle = fopen($sqlFilePath, "r");
if (!$handle) {
    echo "Failed to open SQL file.\n";
    exit(1);
}

$inInsertStates = false;
$statesSql = "";
$countStatements = 0;

// Enable foreign key checks bypass if needed, but states has no dependencies
DB::connection('central')->statement("SET FOREIGN_KEY_CHECKS=0;");
DB::connection('central')->statement("TRUNCATE TABLE states;"); // Clear empty/partial records just in case

while (($line = fgets($handle)) !== false) {
    if (strpos($line, "INSERT INTO `states`") !== false) {
        $inInsertStates = true;
        $statesSql = $line;
        continue;
    }
    
    if ($inInsertStates) {
        $statesSql .= $line;
        // Check if statement ends with semicolon
        if (trim($line) !== '' && substr(trim($line), -1) === ';') {
            $inInsertStates = false;
            try {
                DB::connection('central')->statement($statesSql);
                $countStatements++;
                echo "Executed states INSERT batch $countStatements\n";
            } catch (\Exception $e) {
                echo "Error executing INSERT batch: " . $e->getMessage() . "\n";
            }
            $statesSql = "";
        }
    }
}

fclose($handle);
DB::connection('central')->statement("SET FOREIGN_KEY_CHECKS=1;");

$finalCount = DB::connection('central')->table('states')->count();
echo "Import finished! Total states in database: $finalCount\n";
