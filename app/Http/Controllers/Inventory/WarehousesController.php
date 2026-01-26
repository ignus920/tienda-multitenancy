<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WarehousesController extends Controller
{
    public function homeWarehouses() {
        return view('inventory.warehouse');
    }
}
