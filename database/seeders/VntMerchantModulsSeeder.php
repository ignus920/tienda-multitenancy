<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VntMerchantModulsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // merchantId => [modulIds]
        // 1=Emprendedor Individual, 2=PYME, 3=Gran Empresa, 4=Corporación, 5=Empresa de Producción
        // Módulos: 1=Ventas, 2=Inventario, 3=Parámetros, 4=Caja, 5=Facturación, 6=Producción
        $merchantModuls = [
            ['id' => 1,  'merchantId' => 1, 'modulId' => 1],
            ['id' => 2,  'merchantId' => 1, 'modulId' => 2],
            ['id' => 3,  'merchantId' => 1, 'modulId' => 3],
            ['id' => 4,  'merchantId' => 1, 'modulId' => 4],
            ['id' => 5,  'merchantId' => 2, 'modulId' => 1],
            ['id' => 6,  'merchantId' => 2, 'modulId' => 2],
            ['id' => 7,  'merchantId' => 2, 'modulId' => 3],
            ['id' => 8,  'merchantId' => 2, 'modulId' => 4],
            ['id' => 9,  'merchantId' => 3, 'modulId' => 1],
            ['id' => 10, 'merchantId' => 3, 'modulId' => 2],
            ['id' => 11, 'merchantId' => 3, 'modulId' => 3],
            ['id' => 12, 'merchantId' => 3, 'modulId' => 4],
            ['id' => 13, 'merchantId' => 3, 'modulId' => 5],
            ['id' => 14, 'merchantId' => 4, 'modulId' => 1],
            ['id' => 15, 'merchantId' => 4, 'modulId' => 2],
            ['id' => 16, 'merchantId' => 4, 'modulId' => 3],
            ['id' => 17, 'merchantId' => 4, 'modulId' => 4],
            ['id' => 18, 'merchantId' => 4, 'modulId' => 5],
            // Empresa de Producción: solo Inventario y Producción
            ['id' => 19, 'merchantId' => 5, 'modulId' => 2],
            ['id' => 20, 'merchantId' => 5, 'modulId' => 6],
        ];

        DB::connection('central')->table('vnt_merchant_moduls')->upsert(
            $merchantModuls,
            ['id'],
            ['merchantId', 'modulId']
        );

        $this->command->info('✅ Módulos por tipo de comerciante insertados/actualizados en base de datos central');
    }
}
