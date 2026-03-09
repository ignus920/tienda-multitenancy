<?php

namespace Database\Seeders\Tenant\Modules\Marketing;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarketingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Datos para cmp_campaigns
        DB::table('cmp_campaigns')->updateOrInsert(['id' => 1], [
            'name' => 'pruebas de campaña',
            'description' => 'campalña depruebas para regloss de clientes antiguos ',
            'start_date' => '2026-03-04',
            'end_date' => '2026-03-08',
            'status' => 'activo',
            'gift_quantity' => 100,
            'gifts_sent' => 0,
            'max_per_order' => 1,
            'assignment_type' => 'antiguos_frecuentes',
            'created_at' => '2026-03-04 14:41:26',
            'updated_at' => '2026-03-04 14:41:26',
        ]);

        DB::table('cmp_campaigns')->updateOrInsert(['id' => 2], [
            'name' => 'prueba todos',
            'description' => 'pruebas para todos',
            'start_date' => '2026-03-04',
            'end_date' => '2026-03-17',
            'status' => 'activo',
            'gift_quantity' => 200,
            'gifts_sent' => 1,
            'max_per_order' => 1,
            'assignment_type' => 'todos',
            'created_at' => '2026-03-04 15:14:56',
            'updated_at' => '2026-03-04 15:18:50',
        ]);

        // 2. Datos para cmp_campaign_customers
        DB::table('cmp_campaign_customers')->updateOrInsert(['id' => 1], [
            'campaign_id' => 2,
            'customer_id' => 64,
            'delivered_at' => '2026-03-04 15:18:50',
            'created_at' => '2026-03-04 15:18:50',
            'updated_at' => '2026-03-04 15:18:50',
        ]);

        // 3. Datos para inv_wordpress_configs
        DB::table('inv_wordpress_configs')->updateOrInsert(['id' => 1], [
            'wp_url' => 'https://www.fervicom.com',
            'wp_user' => 'fervicom',
            'wp_password' => 'KNRJ kYEm Bau2 KSG7 CEHJ AhqC',
            'use_wp_load' => 1,
            'is_active' => 1,
            'created_at' => '2026-03-05 11:15:28',
            'updated_at' => '2026-03-05 11:15:28',
        ]);
    }
}
