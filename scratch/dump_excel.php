<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $inputFileType = IOFactory::identify(__DIR__ . '/../inv_porcentaje.xlsx');
    $reader = IOFactory::createReader($inputFileType);
    $spreadsheet = $reader->load(__DIR__ . '/../inv_porcentaje.xlsx');
    $worksheet = $spreadsheet->getActiveSheet();
    
    $rows = $worksheet->toArray();
    
    // Imprimir las primeras 5 filas para ver la estructura
    for ($i = 0; $i < min(10, count($rows)); $i++) {
        print_r($rows[$i]);
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
