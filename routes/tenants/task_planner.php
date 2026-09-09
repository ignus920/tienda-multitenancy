<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\TaskPlanner\ManageTasks;
use App\Livewire\Tenant\TaskPlanner\MyTasksToday;
use App\Http\Controllers\Tenant\TaskPlanner\AttachmentController;

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::get('/tenant/task-planner', ManageTasks::class)->name('tenant.task-planner');
    Route::get('/tenant/mis-tareas', MyTasksToday::class)->name('tenant.task-planner.my-tasks');

    Route::get('/tenant/task-planner/adjunto/{attachment}', [AttachmentController::class, 'show'])
        ->whereNumber('attachment')
        ->name('tenant.task-planner.attachment');
});
