<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Auth\Tenant;
use App\Models\Tenant\Items\InvItemsLocations;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportItemLocations extends Command
{
    protected $signature   = 'import:item-locations
                                {--tenant= : ID del tenant (requerido)}
                                {--file=archivos/ubicaciones.xlsx : Ruta del archivo xlsx}
                                {--dry-run : Solo muestra qué haría sin insertar}';

    protected $description = 'Importa ubicaciones de items desde el archivo ubicaciones.xlsx';

    public function handle(): int
    {
        $tenantId = $this->option('tenant');
        $filePath = base_path($this->option('file'));
        $dryRun   = $this->option('dry-run');

        if (!$tenantId) {
            $this->error('Debes indicar el tenant con --tenant=ID');
            return self::FAILURE;
        }

        if (!file_exists($filePath)) {
            $this->error("Archivo no encontrado: {$filePath}");
            return self::FAILURE;
        }

        // Configurar conexión tenant
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            $this->error("Tenant {$tenantId} no encontrado.");
            return self::FAILURE;
        }

        app(TenantManager::class)->setConnection($tenant);
        tenancy()->initialize($tenant);

        $this->info("📂 Leyendo: {$filePath}");
        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getSheetByName('Hoja1');
        $rows        = $sheet->toArray();

        // Filtrar filas con datos reales (excluir header y filas NULL)
        $dataRows = array_filter($rows, function ($row) {
            return !empty($row[0])
                && $row[0] !== 'id'
                && !empty($row[2]) // internal_code
                && !empty($row[3]) && $row[3] !== 'NULL'   // storeId
                && !empty($row[4]) && $row[4] !== 'NULL';  // ubicacion
        });

        $this->info("📊 Filas con ubicación: " . count($dataRows));

        if ($dryRun) {
            $this->warn('⚠️  Modo DRY RUN — no se insertará nada');
        }

        $created  = 0;
        $updated  = 0;
        $skipped  = 0;

        $rows_table = [];

        foreach ($dataRows as $row) {
            $itemId    = (int) $row[0];
            $itemName  = $row[1];
            $storeId   = (int) $row[3];
            $ubicacion = trim($row[4]);

            // Verificar si ya existe
            $existing = InvItemsLocations::where('itemId', $itemId)
                ->where('storeId', $storeId)
                ->first();

            if ($existing) {
                if ($existing->locationId === $ubicacion) {
                    $skipped++;
                    $rows_table[] = ['⏭️  Igual', $itemId, $storeId, $ubicacion, $itemName];
                    continue;
                }

                if (!$dryRun) {
                    $existing->update(['locationId' => $ubicacion]);
                }
                $updated++;
                $rows_table[] = ['✏️  Actualizado', $itemId, $storeId, $ubicacion, $itemName];
            } else {
                if (!$dryRun) {
                    InvItemsLocations::create([
                        'itemId'              => $itemId,
                        'storeId'             => $storeId,
                        'locationId'          => $ubicacion,
                        'stock_item_location' => 0,
                    ]);
                }
                $created++;
                $rows_table[] = ['✅ Creado', $itemId, $storeId, $ubicacion, $itemName];
            }
        }

        $this->table(
            ['Acción', 'Item ID', 'Store ID', 'Ubicación', 'Nombre'],
            $rows_table
        );

        $this->newLine();
        $this->info("✅ Creados  : {$created}");
        $this->info("✏️  Actualizados: {$updated}");
        $this->info("⏭️  Sin cambios : {$skipped}");

        Log::info('✅ [ImportItemLocations] Importación finalizada', [
            'tenant_id' => $tenantId,
            'created'   => $created,
            'updated'   => $updated,
            'skipped'   => $skipped,
            'dry_run'   => $dryRun,
        ]);

        return self::SUCCESS;
    }
}
