<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Tenant\Projects\ManageProjects;
use App\Livewire\Tenant\Projects\ProjectWorkspace;

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::get('/tenant/projects', ManageProjects::class)->name('tenant.projects');
    Route::get('/tenant/projects/{id}', ProjectWorkspace::class)->name('tenant.projects.workspace');
});
