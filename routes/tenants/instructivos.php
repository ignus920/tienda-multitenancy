<?php

use App\Http\Controllers\Tenant\Instructivos\AttachmentController;
use App\Livewire\Tenant\Instructivos\DepartmentShow;
use App\Livewire\Tenant\Instructivos\Home;
use App\Livewire\Tenant\Instructivos\InstructivoShow;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::get('/instructivos', Home::class)->name('tenant.instructivos');
    Route::get('/instructivos/departamento/{department}', DepartmentShow::class)
        ->whereNumber('department')
        ->name('tenant.instructivos.department');
    Route::get('/instructivos/{instructivo}', InstructivoShow::class)
        ->whereNumber('instructivo')
        ->name('tenant.instructivos.show');

    Route::get('/instructivos/adjunto/{attachment}', [AttachmentController::class, 'show'])
        ->whereNumber('attachment')
        ->name('tenant.instructivos.attachment');
});
