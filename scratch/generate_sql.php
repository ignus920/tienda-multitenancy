<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $inputFileType = IOFactory::identify(__DIR__ . '/../inv_porcentaje.xlsx');
    $reader = IOFactory::createReader($inputFileType);
    $spreadsheet = $reader->load(__DIR__ . '/../inv_porcentaje.xlsx');
    $worksheet = $spreadsheet->getActiveSheet();
    
    $rows = $worksheet->toArray();
    
    $sqlStatements = [];
    $sqlStatements[] = "-- SQL para actualizar wp_stock_percentage en inv_items_store (storeId = 2) basado en internal_code";
    
    // Saltamos la cabecera (i = 0)
    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];
        
        $internalCode = trim($row[1] ?? '');
        $porcRaw = trim($row[3] ?? '');
        
        if (empty($internalCode)) {
            continue;
        }
        
        // Limpiar el porcentaje, ej: "50%" -> 50, o "40" -> 40
        $percentage = (int)str_replace('%', '', $porcRaw);
        
        // Generar SQL
        $sqlStatements[] = "UPDATE inv_items_store SET wp_stock_percentage = {$percentage} WHERE itemId = (SELECT id FROM inv_items WHERE internal_code = '{$internalCode}' LIMIT 1) AND storeId = 2;";
    }
    
    // Guardar a un archivo sql
    file_put_contents(__DIR__ . '/update_stock_percentages.sql', implode("\n", $sqlStatements));
    echo "¡SQL generado exitosamente en scratch/update_stock_percentages.sql! Total registros: " . (count($sqlStatements) - 1) . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
