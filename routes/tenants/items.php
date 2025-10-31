<?php

use Illuminate\Support\Facades\Route;
use App\Auth\Livewire\SelectTenant;
use App\Http\Controllers\Items\ItemsController;


Route::prefix('/items')->group(function(){
    Route::get('/items', [ItemsController::class, 'homeItems'])->name('items');

});