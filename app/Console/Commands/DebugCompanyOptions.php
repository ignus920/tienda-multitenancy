<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;

class DebugCompanyOptions extends Command
{
    protected $signature = 'debug:company-options {tenant_id?} {company_id?}';
    protected $description = 'Debug company options configuration';

    public function handle()
    {
        $tenantId = $this->argument('tenant_id') ?? session('tenant_id');
        $companyId = $this->argument('company_id');

        if (!$tenantId) {
            $this->error('❌ Debe especificar un tenant_id o tener una sesión activa');
            return;
        }

        $this->info('🔍 Debuggeando configuración de company options');
        $this->line('');

        try {
            // Configurar tenant
            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                $this->error("❌ Tenant con ID {$tenantId} no encontrado");
                return;
            }

            $this->info("🏢 Tenant: {$tenant->name} (ID: {$tenantId})");

            // Establecer conexión tenant
            $tenantManager = app(TenantManager::class);
            $tenantManager->setConnection($tenant);
            tenancy()->initialize($tenant);

            $this->info("📊 Base de datos: " . DB::connection('tenant')->getDatabaseName());
            $this->line('');

            // Si no se especifica company_id, buscar todas las companies
            if (!$companyId) {
                $this->showAllCompanies();
                $this->line('');

                $companyId = $this->ask('¿Qué company_id quieres revisar?');
                if (!$companyId) {
                    $this->error('❌ Debe especificar un company_id');
                    return;
                }
            }

            $this->showCompanyOptions($companyId);

        } catch (\Exception $e) {
            $this->error('❌ Error durante el debug: ' . $e->getMessage());
            $this->line('Trace: ' . $e->getTraceAsString());
        }
    }

    private function showAllCompanies()
    {
        try {
            // Buscar todas las companies que tienen opciones configuradas
            $companies = DB::connection('tenant')->table('cnf_company_options')
                ->select('company_id')
                ->distinct()
                ->orderBy('company_id')
                ->get();

            $this->info('🏢 Companies con opciones configuradas:');

            foreach ($companies as $company) {
                $optionCount = DB::connection('tenant')->table('cnf_company_options')
                    ->where('company_id', $company->company_id)
                    ->whereNull('deleted_at')
                    ->count();

                $this->line("   Company ID: {$company->company_id} ({$optionCount} opciones)");
            }

        } catch (\Exception $e) {
            $this->error('❌ Error obteniendo companies: ' . $e->getMessage());
        }
    }

    private function showCompanyOptions($companyId)
    {
        $this->info("🔍 Opciones para Company ID: {$companyId}");
        $this->line('');

        try {
            // Obtener todas las opciones para esta company
            $options = DB::connection('tenant')->table('cnf_company_options')
                ->where('company_id', $companyId)
                ->whereNull('deleted_at')
                ->orderBy('option_id')
                ->get();

            if ($options->isEmpty()) {
                $this->warn("⚠️  No se encontraron opciones para company_id {$companyId}");
                return;
            }

            $this->info("📊 Total de opciones: " . $options->count());
            $this->line('');

            // Mostrar tabla
            $this->table(
                ['ID', 'Option ID', 'Value', 'Created', 'Updated'],
                $options->map(function($option) {
                    return [
                        $option->id,
                        $option->option_id,
                        $option->value,
                        $option->created_at,
                        $option->updated_at
                    ];
                })
            );

            $this->line('');

            // Buscar específicamente la opción 8
            $option8 = $options->where('option_id', 8)->first();
            if ($option8) {
                $this->info("✅ Opción 8 (Facturación Electrónica) encontrada:");
                $this->line("   ID: {$option8->id}");
                $this->line("   Value: {$option8->value}");
                $this->line("   Estado: " . ($option8->value > 0 ? '🟢 HABILITADA' : '🔴 DESHABILITADA'));
            } else {
                $this->warn("⚠️  Opción 8 (Facturación Electrónica) NO encontrada");
            }

            $this->line('');

            // Probar query directa
            $this->testDirectQuery($companyId);

        } catch (\Exception $e) {
            $this->error('❌ Error obteniendo opciones: ' . $e->getMessage());
        }
    }

    private function testDirectQuery($companyId)
    {
        $this->info("🧪 Prueba de query directa para option_id = 8:");

        try {
            // Query usando value()
            $valueResult = DB::connection('tenant')->table('cnf_company_options')
                ->where('company_id', $companyId)
                ->where('option_id', 8)
                ->whereNull('deleted_at')
                ->value('value');

            $this->line("   value() result: " . ($valueResult ?? 'NULL') . " (" . gettype($valueResult) . ")");

            // Query usando first()
            $firstResult = DB::connection('tenant')->table('cnf_company_options')
                ->where('company_id', $companyId)
                ->where('option_id', 8)
                ->whereNull('deleted_at')
                ->first();

            if ($firstResult) {
                $this->line("   first() result: {$firstResult->value} (" . gettype($firstResult->value) . ")");
                $this->line("   Registro completo: " . json_encode($firstResult, JSON_PRETTY_PRINT));
            } else {
                $this->line("   first() result: NULL");
            }

        } catch (\Exception $e) {
            $this->error('❌ Error en query directa: ' . $e->getMessage());
        }
    }
}