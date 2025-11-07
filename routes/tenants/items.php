<?php

use Illuminate\Support\Facades\Route;
use App\Auth\Livewire\SelectTenant;
use App\Http\Controllers\Items\ItemsController;
use App\Http\Controllers\Inventory\CategoriesController;

//Items
Route::prefix('/items')->group(function(){
    Route::get('/items', [ItemsController::class, 'homeItems'])->name('items');
});

//Categories
Route::prefix('/inventory')->group(function(){
    Route::get('/categories', [CategoriesController::class, 'homeCategories'])->name('categories');
});