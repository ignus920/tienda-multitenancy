<?php

namespace Database\Seeders\Tenant\Modules\Sales;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Importa clientes, sucursales y contactos desde:
 *   archivos/vnt_companies_totales.xlsx
 *
 * Estructura del Excel (fila 2 = encabezados, datos desde fila 3):
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  A–U   → vnt_companies  (empresa/cliente)                       │
 * │  V–AN  → vnt_warehouses (sucursal del cliente)                  │
 * │  AO–BC → vnt_contacts   (contacto de la sucursal)               │
 * └─────────────────────────────────────────────────────────────────┘
 *
 * Consideraciones aplicadas:
 *  - vnt_companies.identification tiene restricción UNIQUE → insertOrIgnore
 *    (1 identificación duplicada detectada en el Excel)
 *  - vnt_warehouses.address es NOT NULL → se usa 'SIN DIRECCIÓN' cuando está vacía
 *    (38 registros sin dirección)
 *  - creditLimit es NULL en todos los registros → se usa '0' por defecto
 *  - Se garantiza existencia de termId=1 y positionId=1 antes de insertar
 *  - Los IDs originales del Excel se preservan en las tres tablas
 *
 * Ejecución:
 *   php artisan db:seed --class="Database\Seeders\Tenant\Modules\Sales\ImportCompaniesFromExcelSeeder"
 */
class ImportCompaniesFromExcelSeeder extends Seeder
{
    private const EXCEL_PATH       = 'archivos/vnt_companies_totales.xlsx';
    private const SHEET_INDEX      = 0;
    private const DATA_START_ROW   = 3;   // fila 2 = encabezados, datos desde fila 3
    private const CHUNK_SIZE       = 300;
    private const DEFAULT_ADDRESS  = 'SIN DIRECCIÓN';

    // ─── Columnas del Excel (fila 2 tiene los nombres de campo) ───────────────

    // vnt_companies
    private const C_COMPANY_ID          = 'A';
    private const C_BUSINESS_NAME       = 'B';
    private const C_API_DATA_ID         = 'C';   // id_alegra
    private const C_BILLING_EMAIL       = 'D';
    private const C_FIRST_NAME          = 'E';
    private const C_INTEGRATION_ID      = 'F';
    private const C_IDENTIFICATION      = 'G';
    private const C_CHECK_DIGIT         = 'H';
    private const C_LAST_NAME           = 'I';
    private const C_SECOND_LAST_NAME    = 'J';
    private const C_SECOND_NAME         = 'K';
    private const C_STATUS_COMPANY      = 'L';
    private const C_TYPE_PERSON         = 'M';
    private const C_TYPE_IDENT_ID       = 'N';
    private const C_REGIME_ID           = 'O';
    private const C_CODE_CIIU           = 'P';
    private const C_FISCAL_RESP_ID      = 'Q';
    private const C_TYPE_COMPANY        = 'R';
    private const C_CREATED_COMPANY     = 'S';
    private const C_UPDATED_COMPANY     = 'T';
    private const C_DELETED_COMPANY     = 'U';

    // vnt_warehouses (sucursal del cliente)
    private const C_BRANCH_ID           = 'V';
    private const C_BRANCH_COMPANY_ID   = 'W';
    private const C_BRANCH_NAME         = 'X';
    private const C_ADDRESS             = 'Y';
    private const C_POSTCODE            = 'Z';
    private const C_CITY_ID             = 'AA';
    private const C_BILLING_FORMAT      = 'AB';
    private const C_IS_CREDIT           = 'AC';
    private const C_TERM_ID             = 'AD';
    private const C_CREDIT_LIMIT        = 'AE';
    private const C_PRICE_LIST          = 'AF';
    private const C_STATUS_BRANCH       = 'AG';
    private const C_DISTRICT            = 'AH';
    private const C_WAREHOUSE_API_ID    = 'AI';
    private const C_MAIN                = 'AJ';
    private const C_BRANCH_TYPE         = 'AK';
    private const C_CREATED_BRANCH      = 'AL';
    private const C_UPDATED_BRANCH      = 'AM';
    private const C_DELETED_BRANCH      = 'AN';

    // vnt_contacts
    private const C_CONTACT_ID          = 'AO';
    private const C_CONTACT_FIRST_NAME  = 'AP';
    private const C_CONTACT_SECOND_NAME = 'AQ';
    private const C_CONTACT_LAST_NAME   = 'AR';
    private const C_CONTACT_2ND_LAST    = 'AS';
    private const C_CONTACT_EMAIL       = 'AT';
    private const C_CONTACT_BIZ_PHONE   = 'AU';
    private const C_CONTACT_PERS_PHONE  = 'AV';
    private const C_STATUS_CONTACT      = 'AW';
    private const C_CONTACT_API_ID      = 'AX';
    private const C_CONTACT_WAREHOUSE   = 'AY';
    private const C_CONTACT_POSITION    = 'AZ';
    private const C_CREATED_CONTACT     = 'BA';
    private const C_UPDATED_CONTACT     = 'BB';
    private const C_DELETED_CONTACT     = 'BC';

    // ─────────────────────────────────────────────────────────────────────────

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

        $this->ensureDefaultTermExists();
        $this->ensureDefaultPositionExists();

        $this->output('📂 Leyendo Excel...');
        $sheet   = IOFactory::load($filePath)->getSheet(self::SHEET_INDEX);
        $maxRow  = $sheet->getHighestRow();
        $now     = now()->format('Y-m-d H:i:s');

        $companies = [];
        $branches  = [];
        $contacts  = [];
        $skipped   = 0;
        $nullAddressCount = 0;

        $this->output("🔍 Procesando " . ($maxRow - self::DATA_START_ROW + 1) . " filas...");

        for ($row = self::DATA_START_ROW; $row <= $maxRow; $row++) {
            $v = fn(string $col): mixed => $this->cellValue($sheet, $col, $row);

            $companyId = $this->toInt($v(self::C_COMPANY_ID));
            if (!$companyId) {
                $skipped++;
                continue;
            }

            // ── vnt_companies ─────────────────────────────────────────────
            $companies[] = [
                'id'                   => $companyId,
                'businessName'         => $this->toNullableStr($v(self::C_BUSINESS_NAME)),
                'api_data_id'          => $this->toNullableStr($v(self::C_API_DATA_ID)),
                'billingEmail'         => $this->toNullableStr($v(self::C_BILLING_EMAIL)),
                'firstName'            => $this->toNullableStr($v(self::C_FIRST_NAME)),
                'integrationDataId'    => $this->toNullableInt($v(self::C_INTEGRATION_ID)),
                'identification'       => $this->toNullableStr($v(self::C_IDENTIFICATION)),
                'checkDigit'           => $this->toNullableInt($v(self::C_CHECK_DIGIT)),
                'lastName'             => $this->toNullableStr($v(self::C_LAST_NAME)),
                'secondLastName'       => $this->toNullableStr($v(self::C_SECOND_LAST_NAME)),
                'secondName'           => $this->toNullableStr($v(self::C_SECOND_NAME)),
                'status'               => (int) ($v(self::C_STATUS_COMPANY) ?? 1),
                'typePerson'           => $this->toNullableStr($v(self::C_TYPE_PERSON)),
                'typeIdentificationId' => $this->toNullableInt($v(self::C_TYPE_IDENT_ID)),
                'regimeId'             => $this->toNullableInt($v(self::C_REGIME_ID)),
                'code_ciiu'            => $this->toNullableStr($v(self::C_CODE_CIIU)),
                'fiscalResponsabilityId' => $this->toNullableInt($v(self::C_FISCAL_RESP_ID)),
                'type'                 => $this->toNullableStr($v(self::C_TYPE_COMPANY)) ?? 'CLIENTE',
                'created_at'           => $this->toDatetime($v(self::C_CREATED_COMPANY), $now),
                'updated_at'           => $this->toDatetime($v(self::C_UPDATED_COMPANY), $now),
                'deleted_at'           => $this->toNullableDatetime($v(self::C_DELETED_COMPANY)),
            ];

            // ── vnt_warehouses (sucursal) ─────────────────────────────────
            $address = $this->toNullableStr($v(self::C_ADDRESS));
            if ($address === null) {
                $address = self::DEFAULT_ADDRESS;
                $nullAddressCount++;
            }

            $branches[] = [
                'id'            => $this->toInt($v(self::C_BRANCH_ID)),
                'companyId'     => $this->toInt($v(self::C_BRANCH_COMPANY_ID)),
                'name'          => $this->toNullableStr($v(self::C_BRANCH_NAME)) ?? 'Principal',
                'address'       => $address,
                'postcode'      => $this->toNullableStr($v(self::C_POSTCODE)),
                'cityId'        => $this->toNullableInt($v(self::C_CITY_ID)),
                'billingFormat' => (int) ($v(self::C_BILLING_FORMAT) ?? 16),
                'is_credit'     => (int) ($v(self::C_IS_CREDIT) ?? 0),
                'termId'        => (int) ($v(self::C_TERM_ID) ?? 1),
                'creditLimit'   => (string) ($this->toNullableStr($v(self::C_CREDIT_LIMIT)) ?? '0'),
                'priceList'     => (int) ($v(self::C_PRICE_LIST) ?? 0),
                'status'        => (int) ($v(self::C_STATUS_BRANCH) ?? 1),
                'district'      => $this->toNullableInt($v(self::C_DISTRICT)),
                'api_data_id'   => $this->toNullableInt($v(self::C_WAREHOUSE_API_ID)),
                'main'          => (int) ($v(self::C_MAIN) ?? 1),
                'branch_type'   => $this->toNullableStr($v(self::C_BRANCH_TYPE)) ?? 'FIJA',
                'created_at'    => $this->toDatetime($v(self::C_CREATED_BRANCH), $now),
                'updated_at'    => $this->toDatetime($v(self::C_UPDATED_BRANCH), $now),
                'deleted_at'    => $this->toNullableDatetime($v(self::C_DELETED_BRANCH)),
            ];

            // ── vnt_contacts ──────────────────────────────────────────────
            $contacts[] = [
                'id'             => $this->toInt($v(self::C_CONTACT_ID)),
                'firstName'      => $this->toNullableStr($v(self::C_CONTACT_FIRST_NAME)),
                'secondName'     => $this->toNullableStr($v(self::C_CONTACT_SECOND_NAME)),
                'lastName'       => $this->toNullableStr($v(self::C_CONTACT_LAST_NAME)),
                'secondLastName' => $this->toNullableStr($v(self::C_CONTACT_2ND_LAST)),
                'email'          => $this->toNullableStr($v(self::C_CONTACT_EMAIL)),
                'business_phone' => $this->toNullableStr($v(self::C_CONTACT_BIZ_PHONE)),
                'personal_phone' => $this->toNullableStr($v(self::C_CONTACT_PERS_PHONE)),
                'status'         => (int) ($v(self::C_STATUS_CONTACT) ?? 1),
                'api_data_id'    => $this->toNullableInt($v(self::C_CONTACT_API_ID)),
                'warehouseId'    => $this->toInt($v(self::C_CONTACT_WAREHOUSE)),
                'positionId'     => (int) ($v(self::C_CONTACT_POSITION) ?? 1),
                'created_at'     => $this->toDatetime($v(self::C_CREATED_CONTACT), $now),
                'updated_at'     => $this->toDatetime($v(self::C_UPDATED_CONTACT), $now),
                'deleted_at'     => $this->toNullableDatetime($v(self::C_DELETED_CONTACT)),
            ];
        }

        $this->output(sprintf(
            '✅ Leídos %d empresas | %d sucursales | %d contactos | %d filas omitidas | %d direcciones reemplazadas',
            count($companies), count($branches), count($contacts), $skipped, $nullAddressCount
        ));

        // ── Insertar en la BD ─────────────────────────────────────────────
        DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            DB::connection('tenant')->beginTransaction();

            // vnt_companies: insertOrIgnore por la restricción UNIQUE en identification
            $inserted = $this->batchInsertOrIgnore('vnt_companies', $companies);
            $this->output("  → vnt_companies:  {$inserted} procesadas (duplicadas por identification: ignoradas)");

            $inserted = $this->batchInsertOrIgnore('vnt_warehouses', $branches);
            $this->output("  → vnt_warehouses: {$inserted} procesadas");

            $inserted = $this->batchInsertOrIgnore('vnt_contacts', $contacts);
            $this->output("  → vnt_contacts:   {$inserted} procesadas");

            DB::connection('tenant')->commit();
            $this->output('🎉 Importación completada correctamente.');

        } catch (\Throwable $e) {
            DB::connection('tenant')->rollBack();
            $this->output("❌ Error durante la importación: {$e->getMessage()}");
            Log::error('ImportCompaniesFromExcelSeeder falló', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        } finally {
            DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    // ─── Prerequisitos ────────────────────────────────────────────────────────

    private function prerequisitesMet(): bool
    {
        $required = ['vnt_companies', 'vnt_warehouses', 'vnt_contacts', 'vnt_terms', 'cnf_positions'];
        foreach ($required as $table) {
            if (!DB::connection('tenant')->getSchemaBuilder()->hasTable($table)) {
                $this->output("❌ Tabla '{$table}' no existe. Ejecuta primero las migraciones del tenant.");
                return false;
            }
        }
        return true;
    }

    /**
     * Garantiza que exista un plazo de pago con id=1 (requerido por todas las sucursales).
     */
    private function ensureDefaultTermExists(): void
    {
        $exists = DB::connection('tenant')->table('vnt_terms')->where('id', 1)->exists();
        if (!$exists) {
            DB::connection('tenant')->table('vnt_terms')->insert([
                'id'         => 1,
                'name'       => 'Contado',
                'days'       => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->output('📋 Plazo de pago id=1 "Contado" creado en vnt_terms.');
        }
    }

    /**
     * Garantiza que exista una posición/cargo con id=1 (requerido por todos los contactos).
     */
    private function ensureDefaultPositionExists(): void
    {
        $exists = DB::connection('tenant')->table('cnf_positions')->where('id', 1)->exists();
        if (!$exists) {
            DB::connection('tenant')->table('cnf_positions')->insert([
                'id'         => 1,
                'name'       => 'General',
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->output('📋 Posición id=1 "General" creada en cnf_positions.');
        }
    }

    // ─── Inserción por lotes ──────────────────────────────────────────────────

    private function batchInsertOrIgnore(string $table, array $rows): int
    {
        if (empty($rows)) {
            return 0;
        }

        $total = 0;
        foreach (array_chunk($rows, self::CHUNK_SIZE) as $chunk) {
            DB::connection('tenant')->table($table)->insertOrIgnore($chunk);
            $total += count($chunk);
        }
        return $total;
    }

    // ─── Helpers de celda y tipos ────────────────────────────────────────────

    private function cellValue(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $col, int $row): mixed
    {
        $value = $sheet->getCell($col . $row)->getValue();
        if (is_string($value) && strtoupper(trim($value)) === 'NULL') {
            return null;
        }
        return $value;
    }

    private function toNullableStr(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $str = trim((string) $value);
        return ($str === '' || strtoupper($str) === 'NULL') ? null : $str;
    }

    private function toInt(mixed $value): int
    {
        return (int) $value;
    }

    private function toNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value) && strtoupper(trim($value)) === 'NULL') {
            return null;
        }
        $int = (int) $value;
        return $int === 0 ? null : $int;
    }

    private function toDatetime(mixed $value, string $fallback): string
    {
        if ($value === null || $value === '' || (is_string($value) && strtoupper(trim($value)) === 'NULL')) {
            return $fallback;
        }
        // Si ya es un string con formato de fecha, lo devolvemos directo
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return $value;
        }
        // Si es un número (serial Excel) lo convertimos
        if (is_numeric($value)) {
            try {
                return \Carbon\Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)
                )->format('Y-m-d H:i:s');
            } catch (\Throwable) {
                return $fallback;
            }
        }
        return $fallback;
    }

    private function toNullableDatetime(mixed $value): ?string
    {
        if ($value === null || $value === '' || (is_string($value) && strtoupper(trim($value)) === 'NULL')) {
            return null;
        }
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return $value;
        }
        return null;
    }

    private function output(string $message): void
    {
        if (isset($this->command)) {
            $this->command->info($message);
        }
        Log::info($message);
    }
}
