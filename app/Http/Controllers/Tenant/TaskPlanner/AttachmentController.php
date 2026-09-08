<?php

namespace App\Http\Controllers\Tenant\TaskPlanner;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TaskPlanner\TaskAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Sirve los archivos adjuntos de las tareas operativas.
 *
 * Va por una ruta con el middleware 'tenant' (que inicializa tenancy desde
 * la sesión) para que `storage_path()` quede apuntando a la carpeta de la
 * empresa correcta. Con Storage::url() / tenant_asset() no funcionaba porque
 * este ERP identifica el tenant por sesión, no por dominio.
 */
class AttachmentController extends Controller
{
    public function show(int $attachment)
    {
        $att = TaskAttachment::with('task.assignments')->findOrFail($attachment);

        // Solo Gerencia (perfil 1/2) o un responsable de la tarea.
        $isAdmin = in_array(Auth::user()?->profile_id, [1, 2]);
        $isAssignee = $att->task && $att->task->assignments->contains('user_id', Auth::id());
        abort_unless($isAdmin || $isAssignee, 403);

        abort_unless(Storage::disk('public')->exists($att->file_path), 404);

        return Storage::disk('public')->response(
            $att->file_path,
            $att->file_name,
            ['Cache-Control' => 'private, max-age=3600'],
            'inline'
        );
    }
}
