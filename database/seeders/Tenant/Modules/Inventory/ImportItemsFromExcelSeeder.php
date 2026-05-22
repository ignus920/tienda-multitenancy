<?php

namespace Database\Seeders\Tenant\Modules\Inventory;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

/**
 * Importa los ítems desde el Excel: archivos/Copia de inv_items nuevo ERP.XLSX
 *
 * Tablas afectadas:
 *   - inv_items          (columnas A–R del Excel)
 *   - inv_items_store    (columnas V–Z del Excel)
 *   - inv_values         (columnas AA–AT del Excel, hasta 4 bloques por fila)
 *
 * Mapeo de tipos (Excel → DB enum):
 *   Ensamblado      → ENSAMBLADO
 *   Compra Nacional → COMPRA NACIONAL
 *   Importados      → IMPORTADO
 *   PROYECTADOS     → PROYECTADOS
 *   CZCL            → CZCL
 *   #N/A            → se omite la fila
 *
 * Ejecución:
 *   php artisan db:seed --class="Database\Seeders\Tenant\Modules\Inventory\ImportItemsFromExcelSeeder"
 */
class ImportItemsFromExcelSeeder extends Seeder
{
    private const EXCEL_PATH  = 'archivos/Copia de inv_items nuevo ERP.XLSX';
    private const DEFAULT_TAX = 2;
    private const CHUNK_SIZE  = 200;

    // Columnas del Excel → nombre semántico
    private const COL = [
        'id'               => 'A',
        'api_data_id'      => 'B',
        'categoryId'       => 'C',
        'name'             => 'D',
        'internal_code'    => 'E',
        'sku'              => 'F',
        'description'      => 'G',
        'type'             => 'H',
        'taxId'            => 'I',
        'commandId'        => 'J',
        'brandId'          => 'K',
        'houseId'          => 'L',
        'inventoriable'    => 'M',
        'purchasing_unit'  => 'N',
        'consumption_unit' => 'O',
        'handles_serial'   => 'P',
        'status'           => 'Q',
        'generic'          => 'R',
        'created_at'       => 'S',
        'updated_at'       => 'T',
        'deleted_at'       => 'U',
        // inv_items_store
        'storeId'          => 'V',
        'initial_stock'    => 'W',
        'stock'            => 'X',
        'stock_min'        => 'Y',
        'stock_max'        => 'Z',
        // inv_values: 4 bloques (cada uno con values, type, itemId, warehouseId, label)
        'v1_values'        => 'AA',
        'v1_type'          => 'AB',
        'v1_itemId'        => 'AC',
        'v1_warehouseId'   => 'AD',
        'v1_label'         => 'AE',
        'v2_values'        => 'AF',
        'v2_type'          => 'AG',
        'v2_itemId'        => 'AH',
        'v2_warehouseId'   => 'AI',
        'v2_label'         => 'AJ',
        'v3_values'        => 'AK',
        'v3_type'          => 'AL',
        'v3_itemId'        => 'AM',
        'v3_warehouseId'   => 'AN',
        'v3_label'         => 'AO',
        'v4_values'        => 'AP',
        'v4_type'          => 'AQ',
        'v4_itemId'        => 'AR',
        'v4_warehouseId'   => 'AS',
        'v4_label'         => 'AT',
    ];

    private const TYPE_MAP = [
        'COMPRA NACIONAL' => 'COMPRA NACIONAL',
        'IMPORTADOS'      => 'IMPORTADO',
        'IMPORTADO'       => 'IMPORTADO',
        'ENSAMBLADO'      => 'ENSAMBLADO',
        'PRODUCIDO'       => 'PRODUCIDO',
        'PROYECTADOS'     => 'PROYECTADOS',
        'CZCL'            => 'CZCL',
        'COMBO'           => 'COMBO',
        'INSUMO'          => 'INSUMO',
    ];

    public function run(): void
    {
        $filePath = base_path(self::EXCEL_PATH);

        if (!file_exists($filePath)) {
            $this->output("❌ Archivo no encontrado: {$filePath}");
            return;
        }

        if (!$this->prerequisitesMet()) {
            return;
        }

        $this->output('📂 Leyendo Excel...');
        $sheet = IOFactory::load($filePath)->getActiveSheet();
        $totalRows = $sheet->getHighestRow();

        // Resolver ID de store principal en el tenant actual
        $principalStoreId = $this->getPrincipalStoreId();
        if (!$principalStoreId) {
            $this->output('❌ No se encontró ningún store activo en inv_store. Ejecuta primero DefaultStoreSeeder.');
            return;
        }
        $this->output("🏪 Store principal ID: {$principalStoreId}");

        // Garantizar que categoryId=1 exista
        $this->ensureDefaultCategory();

        // Recolectar datos de todas las filas
        $items      = [];
        $stores     = [];
        $values     = [];
        $skipped    = 0;
        $now        = now()->format('Y-m-d H:i:s');

        $this->output("🔍 Procesando {$totalRows} filas...");

        for ($row = 2; $row <= $totalRows; $row++) {
            $id   = (int) $this->cell($sheet, 'id', $row);
            $type = trim((string) $this->cell($sheet, 'type', $row));

            if (!$id) {
                $skipped++;
                continue;
            }

            $mappedType = $this->mapType($type);
            if ($mappedType === null) {
                $this->output("⚠️  Fila {$row}: tipo '{$type}' no reconocido — omitida.");
                $skipped++;
                continue;
            }

            $createdAt = $this->excelDate($this->cell($sheet, 'created_at', $row), $now);
            $updatedAt = $this->excelDate($this->cell($sheet, 'updated_at', $row), $now);

            $taxId = $this->intOrNull($this->cell($sheet, 'taxId', $row)) ?? self::DEFAULT_TAX;

            $items[] = [
                'id'               => $id,
                'api_data_id'      => $this->intOrNull($this->cell($sheet, 'api_data_id', $row)),
                'categoryId'       => $this->intOrNull($this->cell($sheet, 'categoryId', $row)) ?? 1,
                'name'             => trim((string) $this->cell($sheet, 'name', $row)),
                'internal_code'    => trim((string) $this->cell($sheet, 'internal_code', $row)),
                'sku'              => $this->nullIfEmpty($this->cell($sheet, 'sku', $row)),
                'description'      => $this->nullIfEmpty($this->cell($sheet, 'description', $row)),
                'type'             => $mappedType,
                'taxId'            => $taxId,
                'commandId'        => $this->intOrNull($this->cell($sheet, 'commandId', $row)),
                'brandId'          => $this->intOrNull($this->cell($sheet, 'brandId', $row)),
                'houseId'          => $this->intOrNull($this->cell($sheet, 'houseId', $row)),
                'inventoriable'    => (int) $this->cell($sheet, 'inventoriable', $row),
                'purchasing_unit'  => (int) $this->cell($sheet, 'purchasing_unit', $row),
                'consumption_unit' => (int) $this->cell($sheet, 'consumption_unit', $row),
                'handles_serial'   => (int) $this->cell($sheet, 'handles_serial', $row),
                'status'           => (int) $this->cell($sheet, 'status', $row),
                'generic'          => (int) $this->cell($sheet, 'generic', $row),
                'created_at'       => $createdAt,
                'updated_at'       => $updatedAt,
                'deleted_at'       => null,
            ];

            // inv_items_store (solo si tiene storeId)
            $storeIdExcel = $this->intOrNull($this->cell($sheet, 'storeId', $row));
            if ($storeIdExcel !== null) {
                $stores[] = [
                    'itemId'           => $id,
                    'storeId'          => $principalStoreId,
                    'initial_stock'    => (float) ($this->cell($sheet, 'initial_stock', $row) ?? 0),
                    'stock_items_store'=> (float) ($this->cell($sheet, 'stock', $row) ?? 0),
                    'stock_min'        => (float) ($this->cell($sheet, 'stock_min', $row) ?? 0),
                    'stock_max'        => (float) ($this->cell($sheet, 'stock_max', $row) ?? 0),
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }

            // inv_values — hasta 4 bloques por fila
            foreach ([1, 2, 3, 4] as $block) {
                $valLabel  = $this->nullIfEmpty($this->cell($sheet, "v{$block}_label", $row));
                $valValue  = $this->cell($sheet, "v{$block}_values", $row);
                $valType   = $this->nullIfEmpty($this->cell($sheet, "v{$block}_type", $row));

                if ($valLabel === null || $valValue === null || $valValue === '') {
                    continue;
                }

                $values[] = [
                    'itemId'      => $id,
                    'label'       => $valLabel,
                    'values'      => (float) $valValue,
                    'type'        => strtolower(trim($valType ?? 'costo')),
                    'warehouseId' => (int) ($this->cell($sheet, "v{$block}_warehouseId", $row) ?? 0),
                    'date'        => $now,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
        }

        $this->output(sprintf(
            '✅ Leídas %d ítems | %d store-registros | %d valores | %d omitidas',
            count($items), count($stores), count($values), $skipped
        ));

        // ──────────────────────────────────────────────
        // Insertar en BD
        // ──────────────────────────────────────────────
        DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            DB::connection('tenant')->beginTransaction();

            $inserted = $this->batchInsertOrIgnore('inv_items', $items);
            $this->output("  → inv_items:       {$inserted} insertados");

            $inserted = $this->batchInsertOrIgnore('inv_items_store', $stores);
            $this->output("  → inv_items_store: {$inserted} insertados");

            $inserted = $this->batchInsertOrIgnore('inv_values', $values);
            $this->output("  → inv_values:      {$inserted} insertados");

            DB::connection('tenant')->commit();
            $this->output('✅ Importación completada correctamente.');
        } catch (\Throwable $e) {
            DB::connection('tenant')->rollBack();
            $this->output("❌ Error durante la importación: {$e->getMessage()}");
            Log::error('ImportItemsFromExcelSeeder falló', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        } finally {
            DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    private function prerequisitesMet(): bool
    {
        $required = ['inv_items', 'inv_items_store', 'inv_values', 'inv_store', 'inv_categories'];

        foreach ($required as $table) {
            if (!DB::connection('tenant')->getSchemaBuilder()->hasTable($table)) {
                $this->output("❌ Tabla '{$table}' no existe. Ejecuta primero las migraciones del tenant.");
                return false;
            }
        }

        return true;
    }

    private function getPrincipalStoreId(): ?int
    {
        $store = DB::connection('tenant')
            ->table('inv_store')
            ->where('status', 1)
            ->orderBy('id')
            ->first(['id']);

        return $store ? (int) $store->id : null;
    }

    private function ensureDefaultCategory(): void
    {
        $exists = DB::connection('tenant')
            ->table('inv_categories')
            ->where('id', 1)
            ->exists();

        if (!$exists) {
            DB::connection('tenant')->table('inv_categories')->insert([
                'id'         => 1,
                'name'       => 'GENERAL',
                'status'     => 1,
                'api_data_id'=> '0',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->output('📁 Categoría id=1 "GENERAL" creada.');
        }
    }

    private function mapType(string $raw): ?string
    {
        $upper = strtoupper(trim($raw));
        return self::TYPE_MAP[$upper] ?? null;
    }

    private function cell(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $key, int $row): mixed
    {
        $col = self::COL[$key] ?? null;
        if ($col === null) {
            return null;
        }
        $value = $sheet->getCell($col . $row)->getValue();
        // Tratar el string "NULL" (literal en Excel) como null
        if (is_string($value) && strtoupper(trim($value)) === 'NULL') {
            return null;
        }
        return $value;
    }

    private function intOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '' || (is_string($value) && strtoupper(trim($value)) === 'NULL')) {
            return null;
        }
        $int = (int) $value;
        return $int === 0 ? null : $int;
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $str = trim((string) $value);
        return ($str === '' || strtoupper($str) === 'NULL') ? null : $str;
    }

    private function excelDate(mixed $serial, string $fallback): string
    {
        if ($serial === null || $serial === '' || !is_numeric($serial)) {
            return $fallback;
        }
        try {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $serial))
                ->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return $fallback;
        }
    }

    /**
     * Inserta en lotes e ignora duplicados por PK.
     * Devuelve el número de filas efectivamente insertadas.
     */
    private function batchInsertOrIgnore(string $table, array $rows): int
    {
        if (empty($rows)) {
            return 0;
        }

        $inserted = 0;
        foreach (array_chunk($rows, self::CHUNK_SIZE) as $chunk) {
            DB::connection('tenant')->table($table)->insertOrIgnore($chunk);
            $inserted += count($chunk);
        }
        return $inserted;
    }

    private function output(string $message): void
    {
        if (isset($this->command)) {
            $this->command->info($message);
        }
        Log::info($message);
    }
}
