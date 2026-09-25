<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant\Customer\VntCompany;
use App\Models\Tenant\Invoices\VntInvoices;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UnassignInactiveSellers extends Command
{
    /**
     * El nombre y firma del comando en la consola.
     *
     * @var string
     */
    protected $signature = 'sellers:unassign-inactive';

    /**
     * Descripción del comando.
     *
     * @var string
     */
    protected $description = 'Desasigna vendedores de los clientes que no han comprado en los últimos 3 meses.';

    /**
     * Ejecuta la lógica del comando.
     */
    public function handle()
    {
        Log::info('🤖 Iniciando cron global: Desasignación de vendedores inactivos...');

        $tenants = \App\Models\Auth\Tenant::where('is_active', true)->get();
        $tenantManager = app(\App\Services\Tenant\TenantManager::class);

        // Fecha límite: 3 meses hacia atrás (90 días)
        $threeMonthsAgo = Carbon::now()->subMonths(3);
        $totalUnassigned = 0;

        foreach ($tenants as $tenant) {
            try {
                $tenantManager->setConnection($tenant);
                
                if (function_exists('tenancy') && (!tenancy()->initialized || tenancy()->tenant?->id !== $tenant->id)) {
                    tenancy()->initialize($tenant);
                }

                $companies = VntCompany::whereNotNull('seller_id')->get();
                $tenantUnassigned = 0;

                foreach ($companies as $company) {
                    $lastInvoice = VntInvoices::whereHas('quote', function ($q) use ($company) {
                        $q->whereHas('branch', function ($b) use ($company) {
                            $b->where('companyId', $company->id);
                        });
                    })->latest('created_at')->first();

                    $shouldUnassign = false;

                    if ($lastInvoice) {
                        if ($lastInvoice->created_at < $threeMonthsAgo) {
                            $shouldUnassign = true;
                        }
                    } else {
                        if ($company->updated_at < $threeMonthsAgo) {
                            $shouldUnassign = true;
                        }
                    }

                    if ($shouldUnassign) {
                        $company->seller_id = null;
                        $company->save();
                        $tenantUnassigned++;
                        Log::info("[Tenant {$tenant->id}] 🧹 Vendedor removido para empresa ID {$company->id} por inactividad.");
                    }
                }

                $totalUnassigned += $tenantUnassigned;

            } catch (\Exception $e) {
                Log::error("[Tenant {$tenant->id}] ❌ Error en cron de desasignación: " . $e->getMessage());
            }
        }

        Log::info("✅ Cron finalizado. Total global clientes desasignados: {$totalUnassigned}");
        $this->info("Proceso completado. Clientes desasignados globalmente: {$totalUnassigned}");
    }
}
