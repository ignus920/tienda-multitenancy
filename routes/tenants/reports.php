<?php

use App\Livewire\Tenant\Reports\ReportsList;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/reports', ReportsList::class)->name('tenant.reports.list');
});
