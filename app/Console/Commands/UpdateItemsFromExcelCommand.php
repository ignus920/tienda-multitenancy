<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvItemsDimensions;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

class UpdateItemsFromExcelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'items:update-from-excel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza el campo is_cuttable y long de los productos basados en los 3 archivos Excel (Cables, Cintas, Perfiles).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Iniciando actualización de items desde archivos Excel...");

        $tenant = tenant();
        if (!$tenant) {
            $this->error("No se detectó un entorno de cliente (tenant). Ejecuta este comando a través de tenants:run.");
            return;
        }

        // Obtener de forma segura el nombre de la BD del cliente actual
        // Según la estructura de la base de datos, la columna se llama 'db_name'
        $currentDb = $tenant->db_name;
        
        if (empty($currentDb)) {
            $this->error("No se pudo obtener el nombre de la base de datos (db_name) para este tenant.");
            return;
        }

        config(['database.connections.tenant.database' => $currentDb]);
        \DB::purge('tenant');
        $this->info("Usando base de datos del cliente: " . $currentDb);

        // Archivos a procesar
        $files = [
            database_path('data_imports/Cables por Cm.xlsx'),
            database_path('data_imports/Cintas por cm.xlsx'),
            database_path('data_imports/Perfiles por Cm.xlsx')
        ];

        $totalUpdated = 0;
        $totalNotFound = 0;

        foreach ($files as $file) {
            if (!file_exists($file)) {
                $this->error("No se encontró el archivo: " . basename($file));
                continue;
            }

            $this->info("Procesando archivo: " . basename($file));
            
            try {
                $spreadsheet = IOFactory::load($file);
                $worksheet = $spreadsheet->getActiveSheet();
                
                $rowNumber = 1;
                foreach ($worksheet->getRowIterator() as $row) {
                    // Saltamos las dos primeras filas (título y encabezados)
                    if ($rowNumber <= 2) {
                        $rowNumber++;
                        continue;
                    }

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    
                    $rowData = [];
                    foreach ($cellIterator as $cell) {
                        $rowData[] = $cell->getValue();
                    }

                    // Columna 0: SKU
                    // Columna 1: Código Interno
                    // Columna 3: MEDIDAS (LARGO)
                    $sku = isset($rowData[0]) ? trim($rowData[0]) : null;
                    $internalCode = isset($rowData[1]) ? trim($rowData[1]) : null;
                    $largo = isset($rowData[3]) ? trim($rowData[3]) : null;

                    if (empty($sku) && empty($internalCode)) {
                        $rowNumber++;
                        continue;
                    }

                    // Buscar el item
                    $item = Items::where(function($query) use ($sku, $internalCode) {
                        if (!empty($sku)) {
                            $query->where('sku', $sku);
                        }
                        if (!empty($internalCode)) {
                            $query->orWhere('internal_code', $internalCode);
                        }
                    })->first();

                    if ($item) {
                        // Actualizar is_cuttable
                        $item->is_cuttable = 1;
                        $item->save();

                        // Actualizar o crear dimensiones
                        $dimensions = InvItemsDimensions::firstOrNew(['item_id' => $item->id]);
                        if ($largo !== null && $largo !== '') {
                            $dimensions->long = $largo;
                        }
                        $dimensions->save();

                        $totalUpdated++;
                    } else {
                        $this->warn("Producto no encontrado - SKU: {$sku} / Código: {$internalCode}");
                        $totalNotFound++;
                    }

                    $rowNumber++;
                }

            } catch (\Exception $e) {
                $this->error("Error procesando el archivo " . basename($file) . ": " . $e->getMessage());
            }
        }

        $this->info("Proceso finalizado.");
        $this->info("Productos actualizados: {$totalUpdated}");
        if ($totalNotFound > 0) {
            $this->warn("Productos no encontrados: {$totalNotFound}");
        }
    }
}
