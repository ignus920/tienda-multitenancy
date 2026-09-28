<?php

namespace App\Livewire\Tenant\Pricing;

use Livewire\Component;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvItemPricingParam;
use App\Models\Tenant\Imports\ImpShippments;
use App\Models\Tenant\Imports\ImpImports;
use App\Models\Tenant\Imports\ImpPacking;
use App\Models\Tenant\Items\InvValues;

class PricingSimulator extends Component
{
    public $shipments = [];
    public $selectedShipmentId = null;

    public $items = [];

    // Parametros globales sugeridos
    public $globalExchangeRate = 3800;
    public $globalFreightPercent = 10;
    public $globalFactorList = 1.60;
    public $globalFactorMin = 1.38;

    public $search = '';
    public $searchResults = [];

    public function mount()
    {
        // Cargar importaciones (Shipments) recientes
        $this->shipments = ImpShippments::orderBy('id', 'desc')->take(20)->get();
    }

    public function updatedSelectedShipmentId($value)
    {
        $this->items = []; // Limpiar

        if ($value) {
            // Traer todos los productos de esta importación
            // Shipment -> Packings -> Imports (items)
            $packings = ImpPacking::where('shipping_id', $value)->pluck('id');
            $imports = ImpImports::whereIn('packing_id', $packings)->with('itemsSetup')->get();

            $itemIds = $imports->pluck('item_id')->unique()->filter();

            if ($itemIds->count() > 0) {
                $this->loadItems($itemIds->toArray());
            }
        }
    }

    public function updatedSearch($value)
    {
        if (strlen($value) > 2) {
            $this->searchResults = Items::where('name', 'like', "%{$value}%")
                ->orWhere('sku', 'like', "%{$value}%")
                ->orWhere('internal_code', 'like', "%{$value}%")
                ->take(10)
                ->get();
        } else {
            $this->searchResults = [];
        }
    }

    public function addItem($itemId)
    {
        // Evitar duplicados
        foreach ($this->items as $item) {
            if ($item['item_id'] == $itemId) return;
        }

        $this->loadItems([$itemId], true);
        $this->search = '';
        $this->searchResults = [];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    private function loadItems($itemIds, $append = false)
    {
        $dbItems = Items::whereIn('id', $itemIds)->with('pricingParams')->get();

        $newItems = [];
        foreach ($dbItems as $item) {
            $params = $item->pricingParams;
            
            // Si el item ya tiene parámetros guardados, los usamos, sino usamos valores por defecto
            $newItems[] = [
                'item_id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                // Amarillos
                'exw' => $params->exw ?? 0,
                'exchange_rate' => $params->exchange_rate ?? $this->globalExchangeRate,
                'freight_percent' => $params->freight_percent ?? $this->globalFreightPercent,
                'factor_list' => $params->factor_list ?? $this->globalFactorList,
                'factor_min' => $params->factor_min ?? $this->globalFactorMin,
                'max_discount' => $params->max_discount ?? 10,
                'box_discount' => $params->box_discount ?? 15,
                'web_price' => $params->web_price ?? 0,
                'scale_1_qty' => $params->scale_1_qty ?? 20,
                'scale_1_discount' => $params->scale_1_discount ?? 7,
                'scale_2_qty' => $params->scale_2_qty ?? 100,
                'scale_2_discount' => $params->scale_2_discount ?? 15,
                'scale_3_qty' => $params->scale_3_qty ?? 500,
                'scale_3_discount' => $params->scale_3_discount ?? 20,
                'scale_4_qty' => $params->scale_4_qty ?? 1000,
                'scale_4_discount' => $params->scale_4_discount ?? 25,
                
                // Estos valores serian el P (Lista) manual o calculado
                'manual_price_list' => $this->getCurrentPrice($item->id, 'Precio Regular') ?? 0, 
            ];
        }

        if ($append) {
            $this->items = array_merge($this->items, $newItems);
        } else {
            $this->items = $newItems;
        }
    }

    private function getCurrentPrice($itemId, $label)
    {
        $val = InvValues::where('itemId', $itemId)
            ->where('type', 'precio')
            ->where('label', $label)
            ->first();
        return $val ? $val->values : 0;
    }

    public function applyGlobalParams()
    {
        foreach ($this->items as $index => $item) {
            $this->items[$index]['exchange_rate'] = $this->globalExchangeRate;
            $this->items[$index]['freight_percent'] = $this->globalFreightPercent;
            $this->items[$index]['factor_list'] = $this->globalFactorList;
            $this->items[$index]['factor_min'] = $this->globalFactorMin;
        }
    }

    public function saveAndSync()
    {
        // Validacion
        $hasErrors = false;

        foreach ($this->items as $index => $item) {
            // Recalcular formulas para validar
            $exw = floatval($item['exw'] ?: 0);
            $dolar = floatval($item['exchange_rate'] ?: 0);
            $freight = floatval($item['freight_percent'] ?: 0) / 100;
            
            $factorMin = floatval($item['factor_min'] ?: 0);
            $maxDscto = floatval($item['max_discount'] ?: 0) / 100;

            $p = floatval($item['manual_price_list'] ?: 0);
            
            $calcMinimo = (($exw * $dolar * $factorMin) + ($exw * $dolar * $freight)) * 1.19;
            $minimoReal = $p - ($p * $maxDscto);

            $web = floatval($item['web_price'] ?: 0);
            $s1 = floatval($item['scale_1_discount'] ?: 0) / 100;
            $s2 = floatval($item['scale_2_discount'] ?: 0) / 100;
            $s3 = floatval($item['scale_3_discount'] ?: 0) / 100;
            $s4 = floatval($item['scale_4_discount'] ?: 0) / 100;

            $escala1 = $web - ($web * $s1);
            $escala2 = $web - ($web * $s2);
            $escala3 = $web - ($web * $s3);
            $escala4 = $web - ($web * $s4);

            $minLimit = $minimoReal > 0 ? $minimoReal : $calcMinimo;

            if ($web > 0 && ($escala1 < $minLimit || $escala2 < $minLimit || $escala3 < $minLimit || $escala4 < $minLimit)) {
                $this->addError('items.'.$index.'.web_price', 'Error');
                $hasErrors = true;
            }
        }

        if ($hasErrors) {
            $this->dispatch('swal:error', title: 'Error', text: 'Hay escalas web por debajo del precio mínimo.');
            return;
        }

        foreach ($this->items as $item) {
            InvItemPricingParam::updateOrCreate(
                ['item_id' => $item['item_id']],
                [
                    'exw' => $item['exw'] ?: 0,
                    'exchange_rate' => $item['exchange_rate'] ?: 0,
                    'freight_percent' => $item['freight_percent'] ?: 0,
                    'factor_list' => $item['factor_list'] ?: 0,
                    'factor_min' => $item['factor_min'] ?: 0,
                    'max_discount' => $item['max_discount'] ?: 0,
                    'box_discount' => $item['box_discount'] ?: 0,
                    'web_price' => $item['web_price'] ?: 0,
                    'scale_1_qty' => $item['scale_1_qty'] ?: 0,
                    'scale_1_discount' => $item['scale_1_discount'] ?: 0,
                    'scale_2_qty' => $item['scale_2_qty'] ?: 0,
                    'scale_2_discount' => $item['scale_2_discount'] ?: 0,
                    'scale_3_qty' => $item['scale_3_qty'] ?: 0,
                    'scale_3_discount' => $item['scale_3_discount'] ?: 0,
                    'scale_4_qty' => $item['scale_4_qty'] ?: 0,
                    'scale_4_discount' => $item['scale_4_discount'] ?: 0,
                ]
            );

            $p = floatval($item['manual_price_list'] ?: 0);
            if ($p > 0) {
                // Precio 1 (Lista -> Precio Regular)
                InvValues::updateOrCreate(
                    ['itemId' => $item['item_id'], 'type' => 'precio', 'label' => 'Precio Regular'],
                    ['values' => $p]
                );

                // Precio 2 (Mínimo -> Precio Base)
                $maxDscto = floatval($item['max_discount'] ?: 0) / 100;
                $minimoReal = $p - ($p * $maxDscto);
                InvValues::updateOrCreate(
                    ['itemId' => $item['item_id'], 'type' => 'precio', 'label' => 'Precio Base'],
                    ['values' => $minimoReal]
                );

                // Precio 3 (Crédito) = P * 1.10
                $credito = $p * 1.10;
                InvValues::updateOrCreate(
                    ['itemId' => $item['item_id'], 'type' => 'precio', 'label' => 'Precio Crédito'],
                    ['values' => $credito]
                );
            }
        }

        $this->dispatch('swal:success', title: '¡Éxito!', text: 'Precios guardados y encolados para sincronizar con Alegra.');
    }

    public function render()
    {
        return view('livewire.tenant.pricing.pricing-simulator');
    }
}
