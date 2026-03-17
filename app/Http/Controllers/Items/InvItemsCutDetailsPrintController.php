<?php

namespace App\Http\Controllers\Items;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Items\InvItemsCutDetails as CutDetail;
use App\Models\Central\VntCompany;
use Illuminate\Http\Request;

class InvItemsCutDetailsPrintController extends Controller
{
    public function print($cutId)
    {
        // Obtener datos del tenant (empresa)
        $company = VntCompany::first(); 

        // Obtener detalles del grupo de corte
        $cutDetails = CutDetail::on('tenant')
            ->with(['item', 'customer'])
            ->where('cut_id', $cutId)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($cutDetails->isEmpty()) {
            abort(404, 'No se encontraron detalles para este grupo de corte.');
        }

        $firstDetail = $cutDetails->first();
        $customer = $firstDetail->customer;
        $date = $firstDetail->created_at;

        return view('livewire.tenant.components.print.inv-items-cut-details-print', [
            'company' => $company,
            'cutDetails' => $cutDetails,
            'customer' => $customer,
            'date' => $date,
            'cutId' => $cutId
        ]);
    }
}
