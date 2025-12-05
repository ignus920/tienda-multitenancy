<?php

use Illuminate\Support\Facades\Route;


Route::get('/transfers', function () {
    return view('livewire.tenant.transfers.transfers');
})->name('transfers.transfers');

