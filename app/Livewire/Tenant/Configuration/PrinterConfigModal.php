<?php

namespace App\Livewire\Tenant\Configuration;

use Livewire\Component;
use App\Models\Tenant\Configuration\PrinterConfig;
use Illuminate\Support\Facades\Auth;

class PrinterConfigModal extends Component
{
    public $contexto = 'estacion_empaque';
    public $printer_name = '';
    public $proxy_url = '';
    public $showModal = false;
    public $isLoading = false;

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    protected $listeners = ['openPrinterConfig' => 'open'];

    public function mount($contexto = 'estacion_empaque')
    {
        $this->contexto = $contexto;
        $this->loadConfig();
    }

    public function loadConfig()
    {
        $config = PrinterConfig::getConfig($this->contexto, Auth::id());
        
        if ($config) {
            $this->printer_name = $config->printer_name;
            $this->proxy_url = $config->proxy_url;
        }
    }

    public function open($contexto = null)
    {
        if ($contexto) {
            $this->contexto = $contexto;
            $this->loadConfig();
        }
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'printer_name' => 'required|string',
            'proxy_url' => 'required|url',
        ]);

        PrinterConfig::updateOrCreate(
            ['context' => $this->contexto, 'user_id' => Auth::id()],
            [
                'printer_name' => $this->printer_name,
                'proxy_url' => $this->proxy_url,
            ]
        );

        $this->showModal = false;

        $this->dispatch('printerConfigUpdated', [
            'printer_name' => $this->printer_name,
            'proxy_url' => $this->proxy_url,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Configuración de impresora guardada'
        ]);
    }

    public function render()
    {
        return view('livewire.tenant.configuration.printer-config-modal');
    }

    /**
     * Asegura que exista una conexión válida con el tenant basada en la sesión.
     */
    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return;
        }

        $tenant = \App\Models\Auth\Tenant::find($tenantId);

        if (!$tenant) {
            return;
        }

        $tenantManager = app(\App\Services\Tenant\TenantManager::class);
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);
    }
}
