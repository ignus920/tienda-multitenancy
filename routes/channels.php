<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Auth\Tenant;
use App\Models\Tenant\Projects\ProjectParticipant;
use App\Services\Tenant\TenantManager;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Autorización para el canal privado del chat de proyectos:
// el usuario debe ser un participante real del proyecto (inv_project_participants).
Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    if (is_null($user)) {
        return false;
    }

    // Esta petición llega solo con el middleware 'web' (sin el grupo 'tenant'),
    // así que hay que resolver la conexión del tenant manualmente, igual que
    // hace ensureTenantConnection() en los componentes Livewire de Proyectos.
    $tenantId = session('tenant_id');
    if (!$tenantId) {
        return false;
    }

    $tenant = Tenant::find($tenantId);
    if (!$tenant) {
        return false;
    }

    app(TenantManager::class)->setConnection($tenant);
    if (!tenancy()->initialized) {
        tenancy()->initialize($tenant);
    }
    config(['database.connections.tenant.database' => $tenant->tenancy_db_name]);

    return ProjectParticipant::where('project_id', $projectId)
        ->where('user_id', $user->id)
        ->exists();
});

// Autorización para el canal privado de notificaciones personales del usuario
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

