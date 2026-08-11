<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VntModulsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $now = Carbon::now();

        $modules = [
            [
                'id'          => 1,
                'name'        => 'Ventas',
                'description' => 'Módulo para gestión de ventas, facturación y clientes',
                'version'     => '1.0.0',
                'migration'   => 'sales',
                'dev_hours'   => 120,
                'status'      => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 2,
                'name'        => 'Inventario',
                'description' => 'Módulo para gestión de inventarios, productos y almacenes',
                'version'     => '1.0.0',
                'migration'   => 'inventory',
                'dev_hours'   => 80,
                'status'      => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 3,
                'name'        => 'Parámetros',
                'description' => 'Módulo de configuración y parámetros del sistema',
                'version'     => '1.0.0',
                'migration'   => 'parameters',
                'dev_hours'   => 40,
                'status'      => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 4,
                'name'        => 'Caja',
                'description' => 'Módulo para gestión de caja chica y pagos',
                'version'     => '1.0.0',
                'migration'   => 'pettycash',
                'dev_hours'   => 60,
                'status'      => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 5,
                'name'        => 'Facturación electrónica',
                'description' => 'Módulo para emisión de facturas electrónicas',
                'version'     => '1.0.0',
                'migration'   => 'electronic invoice',
                'dev_hours'   => 100,
                'status'      => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => 6,
                'name'        => 'Producción',
                'description' => 'Módulo para gestión de procesos y órdenes de producción',
                'version'     => '1.0.0',
                'migration'   => 'production',
                'dev_hours'   => 160,
                'status'      => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        DB::connection('central')->table('vnt_moduls')->upsert(
            $modules,
            ['id'],
            ['name', 'description', 'version', 'migration', 'dev_hours', 'status', 'updated_at']
        );

        $this->command->info('✅ Módulos del sistema insertados/actualizados en base de datos central');
    }
}
