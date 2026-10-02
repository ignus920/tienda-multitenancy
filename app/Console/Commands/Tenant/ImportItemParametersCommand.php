<?php

namespace App\Console\Commands\Tenant;

use Illuminate\Console\Command;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvItemsDimensions;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportItemParametersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:import-item-parameters 
                            {tenant_id? : ID o UUID del Tenant (ej. 131 para local/test, 132 para produccion)}
                            {--file= : Archivo específico a importar (csv, fuentes o ruta completa)}
                            {--dry-run : Ejecuta en modo simulación sin guardar cambios en la base de datos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa y actualiza los parámetros (Grupo Comercial, Voltaje, Potencia, Alimentación, Lúmenes y Corte) desde los archivos de data_imports';

    public function handle(TenantManager $tenantManager)
    {
        $tenantId = $this->argument('tenant_id');

        if (!$tenantId) {
            $tenantId = $this->ask('Por favor, ingresa el ID del tenant (ej. 131 para staging/test, 132 para produccion)');
        }

        // Buscar tenant por id numérico, uuid o nombre de bd
        $tenant = Tenant::where('id', $tenantId)
            ->orWhere('db_name', 'like', "%{$tenantId}%")
            ->first();

        if (!$tenant) {
            $this->error("❌ Tenant con identificador '{$tenantId}' no fue encontrado en la base de datos central.");
            return 1;
        }

        // Conectar a la base de datos del Tenant
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);

        $dryRun = (bool) $this->option('dry-run');

        $this->info("==========================================================");
        $this->info("Conectado a la base de datos Tenant: {$tenant->db_name}");
        if ($dryRun) {
            $this->warn("⚠️  MODO SIMULACIÓN (--dry-run) ACTIVADO: Ningún cambio será guardado.");
        }
        $this->info("==========================================================");

        $fileFilter = strtolower($this->option('file') ?? 'all');

        $filesToProcess = [];

        // 1. Archivo CSV: Cinta_modulo_regleta_actualizado.csv
        $csvPath = base_path('database/data_imports/Cinta_modulo_regleta_actualizado.csv');
        if (file_exists($csvPath) && in_array($fileFilter, ['all', 'csv', 'cintas', 'regletas', 'modulos', basename($csvPath)])) {
            $filesToProcess[] = [
                'type' => 'csv',
                'name' => basename($csvPath),
                'path' => $csvPath,
            ];
        }

        // 2. Archivo Excel: Parametros fuentes.xlsx
        $fuentesPath = base_path('database/data_imports/Parametros fuentes.xlsx');
        if (file_exists($fuentesPath) && in_array($fileFilter, ['all', 'fuentes', 'excel', 'xlsx', basename($fuentesPath)])) {
            $filesToProcess[] = [
                'type' => 'excel',
                'name' => basename($fuentesPath),
                'path' => $fuentesPath,
            ];
        }

        if (empty($filesToProcess)) {
            $this->error("❌ No se encontraron archivos para procesar en database/data_imports/");
            return 1;
        }

        $totalProcessed = 0;
        $totalUpdated = 0;
        $totalNotFound = 0;
        $notFoundSkus = [];

        foreach ($filesToProcess as $fileInfo) {
            $this->line("");
            $this->info("📂 Procesando archivo: {$fileInfo['name']}");

            $rows = $this->loadRows($fileInfo);

            if (empty($rows)) {
                $this->warn("⚠️  El archivo {$fileInfo['name']} no contiene filas de datos.");
                continue;
            }

            $bar = $this->output->createProgressBar(count($rows));
            $bar->start();

            DB::connection('tenant')->beginTransaction();

            try {
                foreach ($rows as $row) {
                    $sku = trim((string) ($row['SKU'] ?? $row['Codigo_Interno'] ?? ''));
                    $internalCode = trim((string) ($row['Codigo_Interno'] ?? ''));

                    if ($sku === '' && $internalCode === '') {
                        $bar->advance();
                        continue;
                    }

                    $totalProcessed++;

                    // Buscar el ítem en la base de datos
                    $item = Items::where('sku', $sku)
                        ->orWhere('internal_code', $sku)
                        ->orWhere('internal_code', $internalCode)
                        ->first();

                    if (!$item) {
                        $totalNotFound++;
                        $notFoundSkus[] = "SKU: {$sku} | Cod: {$internalCode} | " . ($row['Nombre'] ?? '');
                        $bar->advance();
                        continue;
                    }

                    // 1. Grupo Comercial
                    $rawGroup = trim((string) ($row['Grupo_Comercial'] ?? ''));
                    if ($rawGroup !== '' && strtoupper($rawGroup) !== 'NULL') {
                        $groupId = $this->getOrCreateCommercialGroup($rawGroup, $dryRun);
                        if ($groupId && $item->commercial_group_id != $groupId) {
                            $item->commercial_group_id = $groupId;
                        }
                    }

                    // 2. Dimensiones y Parámetros Eléctricos
                    $dimensions = InvItemsDimensions::firstOrNew(['item_id' => $item->id]);

                    // Voltaje
                    $rawVolt = trim((string) ($row['Voltaje'] ?? ''));
                    if ($rawVolt !== '' && strtoupper($rawVolt) !== 'NULL') {
                        $cleanVolt = $this->parseNumber($rawVolt);
                        if ($cleanVolt !== null) {
                            $dimensions->voltage = $cleanVolt;
                        }
                    }

                    // Potencia
                    $rawPower = trim((string) ($row['Potencia'] ?? ''));
                    if ($rawPower !== '' && strtoupper($rawPower) !== 'NULL') {
                        $cleanPower = $this->parseNumber($rawPower);
                        if ($cleanPower !== null) {
                            $dimensions->power = $cleanPower;
                        }
                    }

                    // Tipo de Alimentación (CV / CC)
                    $rawAlim = trim((string) ($row['Tipo_Alimentacion'] ?? ''));
                    if ($rawAlim !== '' && strtoupper($rawAlim) !== 'NULL') {
                        $cleanAlim = strtoupper($rawAlim);
                        if (in_array($cleanAlim, ['CV', 'CC'])) {
                            $dimensions->electrical_type = $cleanAlim;
                        }
                    }

                    // Intensidad Lumínica (Lm/m)
                    $rawLumens = trim((string) ($row['Intensidad_Luminica'] ?? ''));
                    if ($rawLumens !== '' && strtoupper($rawLumens) !== 'NULL') {
                        $cleanLumens = $this->parseNumber($rawLumens);
                        if ($cleanLumens !== null) {
                            $dimensions->lumens_per_meter = $cleanLumens;
                        }
                    }

                    // Unidad Mínima de Corte
                    $rawCut = trim((string) ($row['Unidad_Minima_Corte'] ?? ''));
                    if ($rawCut !== '' && strtoupper($rawCut) !== 'NULL') {
                        $cleanCut = $this->parseNumber($rawCut);
                        if ($cleanCut !== null) {
                            $dimensions->min_cut_length = $cleanCut;
                        }
                    }

                    if (!$dryRun) {
                        $item->save();
                        $dimensions->save();
                    }

                    $totalUpdated++;
                    $bar->advance();
                }

                $bar->finish();
                $this->line("");

                if ($dryRun) {
                    DB::connection('tenant')->rollBack();
                } else {
                    DB::connection('tenant')->commit();
                }

            } catch (\Throwable $e) {
                DB::connection('tenant')->rollBack();
                $this->error("❌ Error al procesar archivo {$fileInfo['name']}: " . $e->getMessage());
                return 1;
            }
        }

        $this->line("");
        $this->info("==========================================================");
        $this->info("                    RESUMEN DE IMPORTACIÓN                 ");
        $this->info("==========================================================");
        $this->line("• Total de filas procesadas: <comment>{$totalProcessed}</comment>");
        $this->line("• Total de productos actualizados: <info>{$totalUpdated}</info>");
        $this->line("• Total de productos no encontrados: <comment>{$totalNotFound}</comment>");

        if (!empty($notFoundSkus)) {
            $this->line("");
            $this->warn("⚠️  Los siguientes SKUs / Códigos no se encontraron en la tabla 'inv_items':");
            foreach (array_slice($notFoundSkus, 0, 15) as $miss) {
                $this->line("   - {$miss}");
            }
            if (count($notFoundSkus) > 15) {
                $this->line("   ... y " . (count($notFoundSkus) - 15) . " más.");
            }
        }

        $this->line("");
        if ($dryRun) {
            $this->warn("✅ Simulación completada con éxito. Para aplicar los cambios reales, ejecuta el comando sin el flag --dry-run.");
        } else {
            $this->info("✅ ¡Todos los parámetros fueron actualizados exitosamente en la base de datos!");
        }

        return 0;
    }

    /**
     * Cargar filas estructuradas de un archivo CSV o Excel
     */
    private function loadRows(array $fileInfo): array
    {
        $rows = [];

        if ($fileInfo['type'] === 'csv') {
            if (($handle = fopen($fileInfo['path'], 'r')) !== false) {
                $headers = fgetcsv($handle, 2000, ';');
                if ($headers) {
                    $headers = array_map(fn($h) => trim(str_replace("\xEF\xBB\xBF", '', $h)), $headers);
                    while (($data = fgetcsv($handle, 2000, ';')) !== false) {
                        if (count($data) === count($headers)) {
                            $rows[] = array_combine($headers, $data);
                        }
                    }
                }
                fclose($handle);
            }
        } elseif ($fileInfo['type'] === 'excel') {
            $sheets = Excel::toArray([], $fileInfo['path']);
            if (!empty($sheets) && !empty($sheets[0])) {
                $rawSheet = $sheets[0];
                $headers = array_shift($rawSheet);
                $headers = array_map(fn($h) => trim((string) $h), $headers);

                foreach ($rawSheet as $row) {
                    if (count($row) === count($headers)) {
                        $rows[] = array_combine($headers, $row);
                    }
                }
            }
        }

        return $rows;
    }

    /**
     * Obtener o crear grupo comercial en inv_commercial_groups
     */
    private function getOrCreateCommercialGroup(string $groupName, bool $dryRun): ?int
    {
        $existing = DB::connection('tenant')->table('inv_commercial_groups')
            ->where('name', $groupName)
            ->first();

        if ($existing) {
            return $existing->id;
        }

        if ($dryRun) {
            return 999999; // ID ficticio en simulación
        }

        return DB::connection('tenant')->table('inv_commercial_groups')->insertGetId([
            'name' => $groupName,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Extrae un número float limpio soportando formatos con texto (ej: "15 CV", "1.44W", comas o puntos)
     */
    private function parseNumber($val): ?float
    {
        if ($val === null) return null;
        $str = trim((string) $val);
        if ($str === '' || strtoupper($str) === 'NULL') return null;

        // Reemplazar coma por punto
        $str = str_replace(',', '.', $str);

        // Extraer el primer patrón numérico con o sin decimales
        if (preg_match('/[0-9]+(?:\.[0-9]+)?/', $str, $matches)) {
            return (float) $matches[0];
        }

        return null;
    }
}
