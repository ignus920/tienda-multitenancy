<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\TaskPlanner\ManageTasks;
use App\Livewire\Tenant\TaskPlanner\MyTasksToday;

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::get('/tenant/task-planner', ManageTasks::class)->name('tenant.task-planner');
    Route::get('/tenant/mis-tareas', MyTasksToday::class)->name('tenant.task-planner.my-tasks');
});
