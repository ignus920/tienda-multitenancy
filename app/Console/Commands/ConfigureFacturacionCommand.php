<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Auth\Tenant;
use App\Services\Facturacion\TenantConfigManager;

class ConfigureFacturacionCommand extends Command
{
    protected $signature = 'facturacion:configure {tenant_id} {token} {--url=http://127.0.0.1:8000/api}';

    protected $description = 'Configurar facturación para un tenant específico';

    public function handle()
    {
        $tenantId = $this->argument('tenant_id');
        $token = $this->argument('token');
        $url = $this->option('url');

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            $this->error("Tenant {$tenantId} no encontrado");
            return 1;
        }

        $this->info("Configurando facturación para tenant: {$tenant->name}");

        $success = TenantConfigManager::setFacturacionConfig($tenant, [
            'base_url' => $url,
            'token' => $token,
            'username' => '',
            'timeout' => 30,
            'enabled' => true
        ]);

        if ($success) {
            $this->info("✅ Facturación configurada exitosamente");
            $this->info("URL: {$url}");
            $this->info("Token: " . substr($token, 0, 10) . "***");
            return 0;
        } else {
            $this->error("❌ Error configurando facturación");
            return 1;
        }
    }
}