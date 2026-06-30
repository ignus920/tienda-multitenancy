<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcessExwExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:process-exw-excel {database? : El nombre de la base de datos del tenant}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Procesa el archivo Excel inv_EXW.xlsx and genera las sentencias SQL correspondientes para el tenant seleccionado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dbName = $this->argument('database');

        if (!$dbName) {
            // Listar bases de datos locales que comiencen con tenant_ o company_
            try {
                // Conectarse a MySQL local para listar las bases de datos reales locales
                $pdo = new \PDO("mysql:host=127.0.0.1;port=3306;charset=utf8", "root", "");
                $stmt = $pdo->query("SHOW DATABASES");
                $databases = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                
                $dbList = [];
                foreach ($databases as $name) {
                    if (
                        str_starts_with($name, 'tenant_') || 
                        str_starts_with($name, 'company_') || 
                        $name === 'fervicom' || 
                        $name === 'fervicom_productivo' || 
                        $name === 'rap' ||
                        preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $name)
                    ) {
                        $dbList[] = $name;
                    }
                }
                
                if (empty($dbList)) {
                    $this->error("No se encontraron bases de datos de tipo tenant en tu MySQL local.");
                    $dbName = $this->ask("Por favor escribe el nombre exacto de la base de datos del tenant a utilizar:");
                } else {
                    $dbName = $this->choice(
                        "Selecciona la base de datos del tenant a utilizar:",
                        array_merge($dbList, ['Escribir otro nombre...']),
                        0
                    );

                    if ($dbName === 'Escribir otro nombre...') {
                        $dbName = $this->ask("Por favor escribe el nombre exacto de la base de datos:");
                    }
                }
            } catch (\Exception $e) {
                $this->error("Error al listar bases de datos: " . $e->getMessage());
                $dbName = $this->ask("Por favor escribe el nombre exacto de la base de datos del tenant a utilizar:");
            }
        }

        if (!$dbName) {
            $this->error("Se requiere una base de datos para continuar.");
            return 1;
        }

        $this->info("Conectándose a la base de datos: $dbName...");

        // Configurar la conexión del tenant
        config([
            'database.connections.tenant.host' => '127.0.0.1',
            'database.connections.tenant.port' => '3306',
            'database.connections.tenant.database' => $dbName,
            'database.connections.tenant.username' => 'root',
            'database.connections.tenant.password' => '',
        ]);
        
        DB::purge('tenant');
        
        try {
            DB::connection('tenant')->getPdo();
        } catch (\Exception $e) {
            $this->error("No se pudo conectar a la base de datos '$dbName': " . $e->getMessage());
            return 1;
        }

        $filePath = base_path('inv_EXW.xlsx');
        if (!file_exists($filePath)) {
            $this->error("No se encontró el archivo inv_EXW.xlsx en la raíz del proyecto ($filePath).");
            return 1;
        }

        $this->info("Cargando archivo Excel...");
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
        } catch (\Exception $e) {
            $this->error("Error al leer el archivo Excel: " . $e->getMessage());
            return 1;
        }

        $headers = array_shift($rows); // Cabeceras
        $this->info("Estructura de columnas detectada: " . implode(" | ", $headers));

        $sqlStatements = [];
        $notFound = [];
        $processed = 0;

        $this->info("Procesando registros...");
        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        foreach ($rows as $index => $row) {
            $bar->advance();
            if (empty($row[1])) {
                continue;
            }

            $internalCode = trim($row[1]);
            $exwValue = floatval($row[3] ?? 0);

            // Buscar el id en inv_items
            $item = DB::connection('tenant')
                ->table('inv_items')
                ->where('sku', $internalCode)
                ->orWhere('internal_code', $internalCode)
                ->first();

            if ($item) {
                $itemId = $item->id;
                // Generar sentencia SQL
                $sqlStatements[] = "INSERT INTO imp_items_setup (item_id, exw, created_at, updated_at) VALUES ($itemId, $exwValue, NOW(), NOW()) ON DUPLICATE KEY UPDATE exw = $exwValue, updated_at = NOW();";
                $processed++;
            } else {
                $notFound[] = "Fila " . ($index + 2) . ": Código '$internalCode' no encontrado en inv_items. (Nombre: {$row[2]})";
            }
        }
        $bar->finish();
        $this->newLine(2);

        // Guardar archivo SQL
        $outputSqlPath = base_path('update_exw.sql');
        file_put_contents($outputSqlPath, implode("\n", $sqlStatements));

        // Guardar archivo de reporte
        $reportPath = base_path('reporte_exw_procesamiento.txt');
        $reportContent = "Reporte de Procesamiento de inv_EXW.xlsx\n";
        $reportContent .= "=========================================\n";
        $reportContent .= "Base de Datos consultada: $dbName\n";
        $reportContent .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
        $reportContent .= "Registros procesados y generados: $processed\n";
        $reportContent .= "Registros no encontrados: " . count($notFound) . "\n\n";
        if (count($notFound) > 0) {
            $reportContent .= "Detalle de no encontrados:\n";
            $reportContent .= implode("\n", $notFound) . "\n";
        }
        file_put_contents($reportPath, $reportContent);

        $this->info("¡Proceso completado exitosamente!");
        $this->info("- Consultas SQL generadas: $processed");
        $this->warn("- Registros no encontrados en DB: " . count($notFound));
        $this->info("- Archivo SQL guardado en: $outputSqlPath");
        $this->info("- Reporte de inconsistencias guardado en: $reportPath");

        return 0;
    }
}
