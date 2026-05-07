<?php

use App\Livewire\Tenant\Dispatches\DispatchManager;
use Illuminate\Support\Facades\Route;

Route::get('/despachos', DispatchManager::class)->name('tenant.dispatches');
