<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;

class ImportAccesoriosAsInsumo extends Command
{
    protected $signature = 'import:accesorios-insumo
                            {tenant_id : ID del tenant donde se importarán}
                            {--csv= : Ruta al CSV (default: accesorios/c_detalle_accesorio.csv)}
                            {--category=1 : categoryId a usar}
                            {--tax=3 : taxId a usar}
                            {--brand=1 : brandId a usar}
                            {--house=1 : houseId a usar}
                            {--unit=35 : ID de unidad de compra y consumo}';

    protected $description = 'Importa accesorios del ERP antiguo como items tipo INSUMO';

    public function handle(): int
    {
        $tenantId = $this->argument('tenant_id');
        $csvPath  = $this->option('csv') ?? base_path('accesorios/c_detalle_accesorio.csv');

        if (!file_exists($csvPath)) {
            $this->error("No se encontró el archivo: {$csvPath}");
            return self::FAILURE;
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            $this->error("Tenant {$tenantId} no encontrado.");
            return self::FAILURE;
        }

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);

        $names = $this->readUniqueNames($csvPath);
        $this->info("Accesorios únicos encontrados: " . count($names));

        $categoryId = (int) $this->option('category');
        $taxId      = (int) $this->option('tax');
        $brandId    = (int) $this->option('brand');
        $houseId    = (int) $this->option('house');
        $unit       = (int) $this->option('unit');

        $created = 0;
        $skipped = 0;
        $now     = now();

        $this->output->progressStart(count($names));

        foreach ($names as $index => $name) {
            $exists = DB::connection('tenant')
                ->table('inv_items')
                ->where('name', $name)
                ->where('type', 'INSUMO')
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                $skipped++;
                $this->output->progressAdvance();
                continue;
            }

            $code = $this->generateCode($name, $index + 1);

            DB::connection('tenant')->table('inv_items')->insert([
                'api_data_id'      => null,
                'categoryId'       => $categoryId,
                'name'             => $name,
                'internal_code'    => $code,
                'sku'              => $code,
                'description'      => '',
                'type'             => 'INSUMO',
                'taxId'            => $taxId,
                'commandId'        => null,
                'brandId'          => $brandId,
                'houseId'          => $houseId,
                'inventoriable'    => 0,
                'purchasing_unit'  => $unit,
                'consumption_unit' => $unit,
                'handles_serial'   => 0,
                'status'           => 1,
                'generic'          => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
                'deleted_at'       => null,
            ]);

            $created++;
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->table(
            ['Resultado', 'Cantidad'],
            [
                ['Creados',                $created],
                ['Omitidos (ya existían)', $skipped],
                ['Total únicos CSV',       count($names)],
            ]
        );

        return self::SUCCESS;
    }

    private function readUniqueNames(string $csvPath): array
    {
        $names  = [];
        $handle = fopen($csvPath, 'r');
        fgetcsv($handle, 0, ';'); // saltar encabezado

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (isset($row[2])) {
                $name = trim($row[2], " \t\n\r\0\x0B\"");
                if ($name !== '') {
                    $names[$name] = $name;
                }
            }
        }

        fclose($handle);
        return array_values($names);
    }

    private function generateCode(string $name, int $index): string
    {
        $words  = preg_split('/\s+/', strtoupper(trim($name)));
        $prefix = implode('', array_map(fn($w) => substr($w, 0, 2), array_slice($words, 0, 3)));
        $prefix = preg_replace('/[^A-Z0-9]/', '', $prefix);
        $prefix = substr($prefix, 0, 6) ?: 'INS';

        return $prefix . '-' . str_pad($index, 3, '0', STR_PAD_LEFT);
    }
}
