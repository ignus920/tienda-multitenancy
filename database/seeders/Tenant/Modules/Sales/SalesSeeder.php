<?php

namespace Database\Seeders\Tenant\Modules\Sales;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesSeeder extends Seeder
{
    /**
     * Seed de datos iniciales para el módulo de Ventas.
     */
    public function run(): void
    {
        Log::info('🔄 Ejecutando SalesSeeder');

        // Seed de métodos de pago
        if ($this->tableExists('sal_payment_methods')) {
            $this->seedPaymentMethods();
        }

        // Seed de tipos de documento
        if ($this->tableExists('sal_document_types')) {
            $this->seedDocumentTypes();
        }

        // Seed de estados de venta
        if ($this->tableExists('sal_sale_statuses')) {
            $this->seedSaleStatuses();
        }

        Log::info('✅ SalesSeeder completado');
    }

    /**
     * Seed de métodos de pago
     */
    protected function seedPaymentMethods(): void
    {
        $methods = [
            ['code' => 'CASH', 'name' => 'Efectivo', 'description' => 'Pago en efectivo', 'status' => 1],
            ['code' => 'CARD', 'name' => 'Tarjeta', 'description' => 'Pago con tarjeta débito/crédito', 'status' => 1],
            ['code' => 'TRANSFER', 'name' => 'Transferencia', 'description' => 'Transferencia bancaria', 'status' => 1],
            ['code' => 'CHECK', 'name' => 'Cheque', 'description' => 'Pago con cheque', 'status' => 1],
            ['code' => 'CREDIT', 'name' => 'Crédito', 'description' => 'Venta a crédito', 'status' => 1],
        ];

        foreach ($methods as $method) {
            DB::connection('tenant')->table('sal_payment_methods')->updateOrInsert(
                ['code' => $method['code']],
                $method
            );
        }

        Log::info('✅ Métodos de pago insertados', ['count' => count($methods)]);
    }

    /**
     * Seed de tipos de documento
     */
    protected function seedDocumentTypes(): void
    {
        $types = [
            ['code' => 'INV', 'name' => 'Factura', 'prefix' => 'INV', 'status' => 1],
            ['code' => 'QUO', 'name' => 'Cotización', 'prefix' => 'COT', 'status' => 1],
            ['code' => 'REM', 'name' => 'Remisión', 'prefix' => 'REM', 'status' => 1],
            ['code' => 'ORD', 'name' => 'Orden de Venta', 'prefix' => 'ORD', 'status' => 1],
        ];

        foreach ($types as $type) {
            DB::connection('tenant')->table('sal_document_types')->updateOrInsert(
                ['code' => $type['code']],
                $type
            );
        }

        Log::info('✅ Tipos de documento insertados', ['count' => count($types)]);
    }

    /**
     * Seed de estados de venta
     */
    protected function seedSaleStatuses(): void
    {
        $statuses = [
            ['code' => 'DRAFT', 'name' => 'Borrador', 'color' => '#6c757d'],
            ['code' => 'PENDING', 'name' => 'Pendiente', 'color' => '#ffc107'],
            ['code' => 'CONFIRMED', 'name' => 'Confirmada', 'color' => '#17a2b8'],
            ['code' => 'COMPLETED', 'name' => 'Completada', 'color' => '#28a745'],
            ['code' => 'CANCELLED', 'name' => 'Cancelada', 'color' => '#dc3545'],
        ];

        foreach ($statuses as $status) {
            DB::connection('tenant')->table('sal_sale_statuses')->updateOrInsert(
                ['code' => $status['code']],
                $status
            );
        }

        Log::info('✅ Estados de venta insertados', ['count' => count($statuses)]);
    }

    /**
     * Verifica si una tabla existe en la BD tenant
     */
    protected function tableExists(string $tableName): bool
    {
        try {
            return DB::connection('tenant')
                ->getSchemaBuilder()
                ->hasTable($tableName);
        } catch (\Exception $e) {
            return false;
        }
    }
}
