<?php

namespace App\Livewire\Tenant\Components;

use Livewire\Component;
use App\Models\Tenant\Items\Items as Product;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use App\Models\Tenant\Items\ImportPriceCalculation;
use App\Models\Tenant\Imports\ImpItemsSetup;
use App\Models\Tenant\Items\InvItemsDimensions;
use Livewire\Attributes\On;

class ImportCostCalculator extends Component
{
    public $isOpen = false;
    public $productId;
    public $productCode = '';
    public $productName = '';
    
    // Parámetros globales de cálculo
    public $idcp;
    public $trm = 0;
    public $pKilo = 0;

    // Datos del producto
    public $exw = 0;
    public $weight = 0;

    // Resultados de cálculo
    public $maritimePrice = 0;
    public $freight = 0;
    public $unitPrice = 0;
    public $discount4 = 0;
    public $discount6 = 0;

    // Estado de errores
    public $errorEXW = false;
    public $errorWeight = false;

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    public function mount()
    {
        $this->ensureTenantConnection();
        $this->loadGlobalSettings();
    }

    private function loadGlobalSettings()
    {
        $settings = ImportPriceCalculation::on('tenant')->first();
        if ($settings) {
            $this->idcp = $settings->id;
            $this->trm = $settings->trm;
            $this->pKilo = $settings->price_per_kilo;
        }
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) return;

        $tenant = Tenant::find($tenantId);
        if (!$tenant) return;

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        
        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }
    }

    #[On('openCalculationModal')]
    public function openModal($productId)
    {
        $this->ensureTenantConnection();
        $this->productId = $productId;
        
        $product = Product::on('tenant')->find($productId);
        
        if ($product) {
            $this->productCode = $product->internal_code ?? $product->id;
            $this->productName = $product->name;
            
            // Cargar EXW
            $setup = ImpItemsSetup::on('tenant')->where('item_id', $productId)->first();
            $this->exw = $setup ? $setup->exw : 0;

            // Cargar Peso
            $dimensions = InvItemsDimensions::on('tenant')->where('item_id', $productId)->first();
            $this->weight = $dimensions ? $dimensions->weight : 0;

            $this->loadGlobalSettings();
            $this->calculate();
            $this->isOpen = true;
        }
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['trm', 'pKilo'])) {
            $this->calculate();
        }
    }

    public function calculate()
    {
        // Validaciones de errores
        $this->errorEXW = ($this->exw <= 0);
        $this->errorWeight = ($this->weight <= 0);

        if ($this->errorEXW || $this->errorWeight) {
            $this->maritimePrice = 0;
            $this->freight = 0;
            $this->unitPrice = 0;
            $this->discount4 = 0;
            $this->discount6 = 0;
            return;
        }

        // 1. Precio Marítimo (maritimePrice)
        // Fórmula: EXW * TRM * 1.42 * 1.19
        $this->maritimePrice = $this->exw * $this->trm * 1.42 * 1.19;

        // 2. Flete (freight)
        // Fórmula: ((peso / 1000) * pkilo * trm * 1.19) + 30000
        $this->freight = (($this->weight / 1000) * $this->pKilo * $this->trm * 1.19) + 30000;

        // 3. Precio Unitario (unitPrice)
        // Fórmula: flete + precio_maritimo
        $this->unitPrice = $this->freight + $this->maritimePrice;

        // 4. Descuentos
        $this->discount4 = $this->unitPrice * 0.96;
        $this->discount6 = $this->unitPrice * 0.94;
    }

    public function saveSettings()
    {
        $this->ensureTenantConnection();
        
        $this->validate([
            'trm' => 'required|numeric|min:1',
            'pKilo' => 'required|numeric|min:0.1',
        ]);

        ImportPriceCalculation::on('tenant')->updateOrCreate(
            ['id' => $this->idcp],
            [
                'trm' => $this->trm,
                'price_per_kilo' => $this->pKilo,
            ]
        );

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Configuración de cálculo guardada correctamente'
        ]);
    }

    public function render()
    {
        $this->ensureTenantConnection();
        return view('livewire.tenant.components.import-cost-calculator');
    }
}
