<?php

use App\Livewire\Tenant\TransferRequests\TransferRequests;
use Illuminate\Support\Facades\Route;

Route::get('/tenant/transfer_requests', TransferRequests::class)
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('tenant.transfer_requests');
