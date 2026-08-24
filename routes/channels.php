<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Autorización para el canal privado del chat de proyectos
Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    // Si el usuario está autenticado, permite conexión. 
    // (A futuro se puede restringir verificando que exista en inv_project_participants)
    return !is_null($user);
});
