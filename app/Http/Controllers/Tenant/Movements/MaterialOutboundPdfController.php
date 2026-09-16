<?php

namespace App\Http\Controllers\Tenant\Movements;

use App\Http\Controllers\Controller;
use App\Models\Auth\Tenant;
use App\Models\Tenant\Movements\InvInventoryAdjustment;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaterialOutboundPdfController extends Controller
{
    /**
     * "Orden de Alistamiento" — documento para que Bodega prepare los
     * productos de una Salida de Mercancía generada a partir de una
     * Solicitud de Materiales de Proyectos.
     */
    public function print(int $adjustmentId)
    {
        $tenantId = session('tenant_id');
        abort_if(!$tenantId, 403, 'Sin tenant seleccionado.');

        $tenant = Tenant::findOrFail($tenantId);
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);

        $movement = InvInventoryAdjustment::with(['details.item', 'reason', 'project', 'store'])
            ->findOrFail($adjustmentId);

        $companyName = DB::connection('central')
            ->table('vnt_contacts as c')
            ->join('vnt_warehouses as w', 'c.warehouseId', '=', 'w.id')
            ->join('vnt_companies as cm', 'w.companyId', '=', 'cm.id')
            ->join('users as u', 'u.contact_id', '=', 'c.id')
            ->where('u.id', Auth::id())
            ->value('cm.businessName');

        $html = view('pdf.material-outbound-order', [
            'movement' => $movement,
            'companyName' => $companyName ?? '—',
            'orderNumber' => str_pad($movement->consecutive, 6, '0', STR_PAD_LEFT),
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
