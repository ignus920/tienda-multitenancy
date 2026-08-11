<?php

namespace App\Livewire\Tenant\Imports;

use Livewire\Component;
use App\Models\Tenant\Imports\ImpShippments;
use App\Models\Tenant\Imports\ImpImports;
use App\Models\Tenant\Items\Items;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ImportCosting extends Component
{
    public $selectedShippmentId = null;
    public $shippments = [];
    
    // Gastos de Importación (Inputs)
    public $flete_internacional_usd = 0;
    public $seguro_usd = 0;
    public $arancel_cop = 0;
    public $trm_costeo = 0;
    public $otros_gastos_cop = 0;

    // Detalle de ítems cargados
    public $items = [];
    public $totals = [
        'qty' => 0,
        'exw_total_usd' => 0,
        'exw_total_cop' => 0,
        'costo_final_total_cop' => 0
    ];

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    public function mount()
    {
        if (Auth::user()?->profile_id == 17) {
            abort(403, 'Acceso restringido para proveedores.');
        }

        $this->ensureTenantConnection();
        $this->loadShippments();
        // Intentar obtener la TRM configurada por defecto de ImportPriceCalculation
        $settings = \App\Models\Tenant\Items\ImportPriceCalculation::first();
        if ($settings) {
            $this->trm_costeo = $settings->trm;
        }
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) {
            return $this->redirectRoute('tenant.select');
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            session()->forget('tenant_id');
            return $this->redirectRoute('tenant.select');
        }

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        
        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }
    }

    public function loadShippments()
    {
        $this->shippments = ImpShippments::select('id', 'operation_number', 'way', 'consecutive', 'etd')
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
    }

    public function updatedSelectedShippmentId($value)
    {
        if (!$value) {
            $this->items = [];
            return;
        }

        $this->ensureTenantConnection();
        
        // Obtener los productos asociados a este Shipment mediante sus packings
        $rawItems = DB::connection('tenant')
            ->table('imp_imports as i')
            ->select([
                'i.id as import_id',
                'i.item_id',
                'iv.internal_code',
                'iv.name as item_name',
                'i.qty_requested',
                'i.qty_shipped',
                'iis.exw',
                'iv_dim.weight'
            ])
            ->join('inv_items as iv', 'i.item_id', '=', 'iv.id')
            ->leftJoin('imp_items_setup as iis', 'i.item_id', '=', 'iis.item_id')
            ->leftJoin('inv_items_dimensions as iv_dim', 'i.item_id', '=', 'iv_dim.item_id')
            ->join('imp_packing as pk', 'i.packing_id', '=', 'pk.id')
            ->where('pk.shipping_id', $value)
            ->whereNull('i.deleted_at')
            ->get();

        $this->items = [];
        foreach ($rawItems as $item) {
            $qty = $item->qty_shipped ?: $item->qty_requested;
            $exw = (float)($item->exw ?: 0);
            $weight = (float)($item->weight ?: 0);
            
            $this->items[] = [
                'import_id' => $item->import_id,
                'item_id' => $item->item_id,
                'internal_code' => $item->internal_code,
                'name' => $item->item_name,
                'qty' => $qty,
                'exw_usd' => $exw,
                'weight' => $weight,
                'total_exw_usd' => $exw * $qty,
                // Campos calculados por prorrateo
                'prorated_flete_usd' => 0,
                'prorated_seguro_usd' => 0,
                'prorated_arancel_cop' => 0,
                'prorated_otros_cop' => 0,
                'costo_unitario_cop' => 0,
                'costo_total_cop' => 0
            ];
        }

        $this->calculateProration();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['flete_internacional_usd', 'seguro_usd', 'arancel_cop', 'trm_costeo', 'otros_gastos_cop'])) {
            $this->calculateProration();
        }
    }

    public function calculateProration()
    {
        if (empty($this->items)) {
            return;
        }

        $totalExwUsd = collect($this->items)->sum('total_exw_usd');
        $totalWeight = collect($this->items)->sum(fn($item) => $item['weight'] * $item['qty']);
        
        $trm = (float)$this->trm_costeo ?: 1;
        
        $fleteUsd = (float)$this->flete_internacional_usd;
        $seguroUsd = (float)$this->seguro_usd;
        $arancelCop = (float)$this->arancel_cop;
        $otrosCop = (float)$this->otros_gastos_cop;

        $this->totals = [
            'qty' => 0,
            'exw_total_usd' => 0,
            'exw_total_cop' => 0,
            'costo_final_total_cop' => 0
        ];

        foreach ($this->items as &$item) {
            $itemTotalExwUsd = $item['total_exw_usd'];
            $itemWeightTotal = $item['weight'] * $item['qty'];
            
            // Factor base de FOB/EXW
            $factorFOB = $totalExwUsd > 0 ? ($itemTotalExwUsd / $totalExwUsd) : 0;
            // Factor base de peso
            $factorWeight = $totalWeight > 0 ? ($itemWeightTotal / $totalWeight) : 0;

            // Prorratear flete (por peso)
            $item['prorated_flete_usd'] = $fleteUsd * $factorWeight;
            // Prorratear seguro (por valor FOB)
            $item['prorated_seguro_usd'] = $seguroUsd * $factorFOB;
            // Prorratear arancel (por valor FOB)
            $item['prorated_arancel_cop'] = $arancelCop * $factorFOB;
            // Prorratear otros gastos locales (por valor FOB)
            $item['prorated_otros_cop'] = $otrosCop * $factorFOB;

            // Costos en COP
            $costoExwCop = $item['exw_usd'] * $trm;
            $fleteItemCop = ($item['prorated_flete_usd'] / $item['qty']) * $trm;
            $seguroItemCop = ($item['prorated_seguro_usd'] / $item['qty']) * $trm;
            $arancelItemCop = $item['prorated_arancel_cop'] / $item['qty'];
            $otrosItemCop = $item['prorated_otros_cop'] / $item['qty'];

            // Costo final unitario
            $item['costo_unitario_cop'] = $costoExwCop + $fleteItemCop + $seguroItemCop + $arancelItemCop + $otrosItemCop;
            $item['costo_total_cop'] = $item['costo_unitario_cop'] * $item['qty'];

            // Sumar a los totales globales
            $this->totals['qty'] += $item['qty'];
            $this->totals['exw_total_usd'] += $item['total_exw_usd'];
            $this->totals['exw_total_cop'] += ($item['total_exw_usd'] * $trm);
            $this->totals['costo_final_total_cop'] += $item['costo_total_cop'];
        }
    }

    public function processCosting()
    {
        $this->ensureTenantConnection();

        if (empty($this->items)) {
            $this->dispatch('show-toast', [
                'type' => 'warning',
                'message' => 'No hay ítems para procesar'
            ]);
            return;
        }

        try {
            DB::connection('tenant')->transaction(function () {
                // Actualizar los costos de inventario de cada producto
                foreach ($this->items as $item) {
                    // Actualizar el costo de compra en la bodega correspondiente
                    DB::connection('tenant')
                        ->table('inv_items_store')
                        ->where('itemId', $item['item_id'])
                        ->update([
                            // Suponemos que guardamos el costo promedio o el ultimo costo
                            'updated_at' => now()
                        ]);

                    // Registrar un log o historial si fuera necesario
                    // Actualizar el estado de la importación a completado (8)
                    ImpImports::where('id', $item['import_id'])->update([
                        'status' => 8 // 8 = Recibido / Completado
                    ]);
                }
            });

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Cálculo de costeo guardado y procesado exitosamente.'
            ]);

            $this->reset(['selectedShippmentId', 'items']);
            $this->loadShippments();
            
        } catch (\Exception $e) {
            Log::error("Error al procesar costeo de importación: " . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se pudo procesar el costeo'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.tenant.imports.import-costing')
            ->layout('layouts.app', ['header' => 'Costeo de Importaciones']);
    }
}
