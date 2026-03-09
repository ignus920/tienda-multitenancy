<?php

namespace App\Http\Controllers\Tenant\Production;

use App\Http\Controllers\Controller;
use App\Models\Auth\Tenant;
use App\Models\Tenant\Production\PrdProductionOrder;
use App\Models\Tenant\Production\PrdProcessItem;
use App\Models\Tenant\Production\PrdDataProcessOrder;
use App\Models\Tenant\Production\PrdMaterialItems;
use App\Models\Tenant\Items\UnitMeasurements;
use App\Services\Tenant\TenantManager;

class ProductionOrderPdfController extends Controller
{
    public function print(int $orderId)
    {
        // Establecer conexión tenant
        $tenantId = session('tenant_id');
        abort_if(!$tenantId, 403, 'Sin tenant seleccionado.');

        $tenant = Tenant::findOrFail($tenantId);
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);

        // Cargar la orden con todas las relaciones necesarias
        $order = PrdProductionOrder::with(['item', 'contact', 'productiveProcess'])
            ->findOrFail($orderId);

        // Ruta de procesos del item ordenada
        $processRoute = PrdProcessItem::with('process')
            ->where('itemId', $order->item_id)
            ->whereNull('deleted_at')
            ->orderBy('process_route_order')
            ->get();

        // Datos de campos registrados por proceso
        $processData = PrdDataProcessOrder::with('field')
            ->where('production_order_id', $orderId)
            ->get()
            ->groupBy('field.processId');

        // Materiales consumidos con unidad
        $materials = PrdMaterialItems::with('item')
            ->where('production_order_id', $orderId)
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($m) {
                $unit = $m->unit_measurement
                    ? UnitMeasurements::find($m->unit_measurement)
                    : null;
                return [
                    'name'  => $m->item?->name ?? '—',
                    'qty'   => $m->qty,
                    'unit'  => $unit?->description ?? 'UND',
                ];
            });

        $data = [
            'order'        => $order,
            'processRoute' => $processRoute,
            'processData'  => $processData,
            'materials'    => $materials,
            'orderNumber'  => str_pad($order->id, 4, '0', STR_PAD_LEFT),
        ];

        $html = view('pdf.production-order', $data)->render();

        return response($html, 200, [
            'Content-Type'  => 'text/html; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
