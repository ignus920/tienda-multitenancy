<?php

use App\Livewire\Tenant\Warranties\WarrantiesList;
use App\Livewire\Tenant\Warranties\WarrantyCreate;
use Illuminate\Support\Facades\Route;

Route::get('/tenant/{tenant_id}/garantias/solicitud', \App\Livewire\Tenant\Warranties\PublicWarrantyRequest::class)
    ->name('tenant.warranties.public.request');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/tenant/warranties/chatbot', \App\Livewire\Tenant\Warranties\ChatbotRequestsList::class)->name('tenant.warranties.chatbot');
    Route::get('/tenant/warranties', WarrantiesList::class)->name('tenant.warranties');
    Route::get('/tenant/warranties/create/{id}', WarrantyCreate::class)->name('tenant.warranties.create');
});
