<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;

class ImportLegacyMovements extends Command
{
    protected $signature = 'import:legacy-movements {--tenant= : El ID del Tenant a importar}';
    protected $description = 'Importa los movimientos de ventas de los últimos 12 meses móviles (hasta mayo 2026 inclusive) del ERP antiguo';

    public function handle()
    {
        $tenantId = $this->option('tenant');

        if (!$tenantId) {
            $this->error('Debe especificar un --tenant ID.');
            return 1;
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            $this->error('Tenant no encontrado.');
            return 1;
        }

        $this->info("Iniciando importación para Tenant: {$tenant->name} ({$tenantId})");

        // 1. Configurar conexión temporal a la base de datos heredada
        config(['database.connections.legacy_temp' => [
            'driver' => 'mysql',
            'host' => config('database.connections.mysql.host', '127.0.0.1'),
            'port' => config('database.connections.mysql.port', '3306'),
            'database' => 'db_legacy_temp',
            'username' => config('database.connections.mysql.username', 'root'),
            'password' => config('database.connections.mysql.password', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'strict' => true,
        ]]);

        try {
            // Testear la conexión a db_legacy_temp
            DB::connection('legacy_temp')->getPdo();
            $this->info("Conexión a db_legacy_temp establecida con éxito.");
        } catch (\Exception $e) {
            $this->error("Error al conectar a db_legacy_temp: " . $e->getMessage());
            return 1;
        }

        // 2. Establecer la conexión del Tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);

        $this->info("Conectado al tenant. Base de datos del Tenant: " . DB::connection('tenant')->getDatabaseName());

        // 3. Limpiar datos antiguos en la tabla legacy_sales_history del tenant
        $this->info("Limpiando registros previos de legacy_sales_history en la BD del tenant...");
        DB::connection('tenant')->table('legacy_sales_history')->truncate();

        // 4. Ejecutar consulta de extracción agrupada (últimos 12 meses finalizando en mayo 2026)
        // Desde '2025-06-01' hasta '2026-05-31'
        $this->info("Consultando ventas históricas en db_legacy_temp...");
        try {
            $historicalData = DB::connection('legacy_temp')->table('v_mov_pt as vmp')
                ->join('v_orden_p as vop', 'vop.id_ped', '=', 'vmp.doc')
                ->join('c_productos as p', 'p.id', '=', 'vmp.id_producto')
                ->select([
                    'p.codigo as sku',
                    DB::raw('YEAR(vop.fecha_reg) as year'),
                    DB::raw('MONTH(vop.fecha_reg) as month'),
                    DB::raw('SUM(vmp.cantidad) as quantity')
                ])
                ->whereNotIn('vop.estado', [2, 21, 22])
                ->where('vop.fecha_reg', '>=', '2025-06-01')
                ->where('vop.fecha_reg', '<', '2026-06-01')
                ->groupBy('p.codigo', DB::raw('YEAR(vop.fecha_reg)'), DB::raw('MONTH(vop.fecha_reg)'))
                ->get();

            $totalRecords = count($historicalData);
            $this->info("Se encontraron {$totalRecords} registros mensuales históricos.");

            if ($totalRecords === 0) {
                $this->warn("No se encontraron registros para importar.");
                return 0;
            }

            // 5. Insertar por lotes (chunk insertion)
            $this->info("Insertando registros en la tabla legacy_sales_history del tenant...");
            $chunks = array_chunk($historicalData->toArray(), 500);

            foreach ($chunks as $index => $chunk) {
                $insertData = [];
                foreach ($chunk as $row) {
                    $insertData[] = [
                        'sku' => $row->sku,
                        'year' => (int) $row->year,
                        'month' => (int) $row->month,
                        'quantity' => (int) $row->quantity,
                        'created_at' => now(),
                    ];
                }
                DB::connection('tenant')->table('legacy_sales_history')->insert($insertData);
                $this->output->write(".");
            }

            $this->newLine();
            $this->info("¡Importación finalizada con éxito!");

        } catch (\Exception $e) {
            $this->error("Ocurrió un error durante la importación: " . $e->getMessage());
            Log::error("Error importando movimientos históricos: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
