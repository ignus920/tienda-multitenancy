<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\Inventory\InventoryConfirmationList;

Route::get('/inventory/confirmations', InventoryConfirmationList::class)->name('inventory.confirmations');
