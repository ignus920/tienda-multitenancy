<?php

namespace App\Livewire\Tenant\Components;

use Livewire\Component;
use App\Models\Tenant\Items\Items as Product;
use App\Models\Tenant\Items\InvItemsDimensions;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Livewire\Attributes\On;

class ProductBundlePowerCalculator extends Component
{
    public $isOpen = false;
    
    // Inputs
    public $selectedProductId;
    public $selectedProductLabel = 'Seleccione un producto...';
    public $searchProduct = '';
    public $quantity = 1;

    // Results
    public $calculationResult = false;
    public $formattedMessage = '';

    public function boot()
    {
        $this->ensureTenantConnection();
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

    public function selectProduct($id, $label)
    {
        $this->selectedProductId = $id;
        $this->selectedProductLabel = $label;
        $this->searchProduct = '';
    }

    #[On('openPowerCalculator')]
    public function openModal($productId = null)
    {
        $this->ensureTenantConnection();
        $this->isOpen = true;
        $this->calculationResult = false;
        $this->formattedMessage = '';
        $this->selectedProductId = null;
        $this->selectedProductLabel = 'Seleccione un producto...';
        
        if ($productId) {
            $product = Product::on('tenant')->find($productId);
            if ($product) {
                $this->selectProduct($product->id, "(" . ($product->sku ?: $product->internal_code) . ") - " . $product->name);
                $this->calculatePower();
            }
        }
    }

    public function calculatePower()
    {
        // Validaciones según el JS del usuario
        if (!$this->selectedProductId || $this->selectedProductId == "") {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'text' => 'Debe seleccionar un producto'
            ]);
            return;
        }

        if (!$this->quantity || $this->quantity == "") {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'text' => 'Debe digitar la cantidad'
            ]);
            return;
        }

        $this->ensureTenantConnection();
        
        $product = Product::on('tenant')->find($this->selectedProductId);
        $dimensions = InvItemsDimensions::on('tenant')->where('item_id', $this->selectedProductId)->first();

        if ($product && $dimensions && $dimensions->power > 0 && $dimensions->voltage > 0) {
            $potenciaTotal = $this->quantity * $dimensions->power;
            $corrienteTotal = $potenciaTotal / $dimensions->voltage;
            $potmax = $potenciaTotal * 1.2;
            $cormax = $corrienteTotal * 1.2;

            $this->formattedMessage = "Para #({$this->quantity}) " . ($product->internal_code ? $product->internal_code . '-' : '') . $product->name . "\n" .
                                      " Potencia: {$dimensions->power}W; voltaje: {$dimensions->voltage}V\n" .
                                      " El consumo total es de " . round($corrienteTotal, 2) . "Amp, " . round($potenciaTotal, 2) . "W.\n" .
                                      "Para evitar problemas use una fuente minimo de " . round($cormax, 2) . "Amp o " . round($potmax, 2) . "W";

            $this->calculationResult = true;

            // Trigger copy on frontend
            $this->dispatch('copy-to-clipboard', message: $this->formattedMessage);
        } else {
            $this->formattedMessage = "No hay informacion para el calculo, revise los parametros cargadaos en el item seleccionado";
            $this->calculationResult = true; // Mostramos el mensaje de error en el textarea
        }
    }

    public function render()
    {
        $this->ensureTenantConnection();
        
        $query = Product::on('tenant')
            ->select('inv_items.id', 'inv_items.internal_code', 'inv_items.sku', 'inv_items.name')
            ->join('inv_items_dimensions', 'inv_items.id', '=', 'inv_items_dimensions.item_id')
            ->where('inv_items.status', 1);

        if (!empty($this->searchProduct)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchProduct . '%')
                  ->orWhere('internal_code', 'like', '%' . $this->searchProduct . '%')
                  ->orWhere('sku', 'like', '%' . $this->searchProduct . '%');
            });
        }

        $products = $query->orderBy('name')->limit(50)->get();

        return view('livewire.tenant.components.product-bundle-power-calculator', [
            'products' => $products
        ]);
    }
}
