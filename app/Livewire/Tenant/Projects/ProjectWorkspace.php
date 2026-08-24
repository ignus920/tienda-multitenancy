<?php

namespace App\Livewire\Tenant\Projects;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Tenant\Projects\Project;
use App\Models\Tenant\Projects\ProjectMessage;
use App\Models\Tenant\Projects\ProjectMention;
use App\Models\Tenant\Projects\ProjectNotification;
use App\Models\Tenant\Projects\ProjectParticipant;
use App\Models\Tenant\Projects\ProjectQuestion;
use App\Models\Tenant\Projects\ProjectAdvance;
use App\Models\Tenant\Projects\ProjectStatusHistory;
use App\Models\Auth\User;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use App\Events\Tenant\Projects\NewProjectNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectWorkspace extends Component
{
    public $projectId;

    // Pestaña activa del workspace
    public $activeTab = 'chat';

    // Filtros de chat
    public $chatFilterUser = '';
    public $chatFilterRole = '';

    // Nuevo mensaje
    public $newMessageText = '';
    public $replyingToMessageId = null;
    public $replyingToMessageText = '';
    public $mentionedUserIds = [];

    // Campos de la Orden de Producción (Comercial)
    public $qty;
    public $price_unit;
    public $total_value;
    public $delivery_date;
    public $prod_observations;

    // Campos de Pregunta para el Cliente (Laboratorio)
    public $newQuestionText = '';

    // Campos para Responder Pregunta (Comercial)
    public $answeringQuestionId = null;
    public $answerText = '';

    // Campos de Avance de Laboratorio
    public $advanceDescription = '';
    public $advancePercentage = 0;

    // Campos de Cierre Laboratorio
    public $completion_date;
    public $lab_observations;

    // Campos de Cierre Comercial
    public $real_delivery_date;
    public $close_observations;

    // Campo de Fecha Sugerida (Proyecto Interno)
    public $suggested_delivery_date;

    // Control de modales/secciones
    public $showOrderModal = false;
    public $showQuestionModal = false;
    public $showAnswerModal = false;
    public $showAdvanceModal = false;
    public $showLabFinishModal = false;
    public $showCloseModal = false;
    public $showStartDevelopmentModal = false;

    #[On('echo-private:project.{projectId},.NewProjectMessage')]
    public function refreshChat()
    {
        // Al recibir el WebSocket, este método vacío obliga a Livewire a hacer re-render 
        // y traer los nuevos mensajes actualizados de la base de datos automáticamente.
    }

    public function mount($id)
    {
        $this->projectId = $id;
        $this->ensureTenantConnection();
        $project = Project::findOrFail($id);

        // Inicializar campos de la orden
        $this->qty = $project->qty;
        $this->price_unit = $project->price_unit;
        $this->total_value = $project->total_value;
        $this->delivery_date = $project->delivery_date ? $project->delivery_date->format('Y-m-d') : null;
        $this->prod_observations = $project->prod_observations;
        
        $this->completion_date = $project->completion_date ? $project->completion_date->format('Y-m-d') : date('Y-m-d');
        $this->lab_observations = $project->lab_observations;
        $this->real_delivery_date = $project->real_delivery_date ? $project->real_delivery_date->format('Y-m-d') : date('Y-m-d');
        $this->close_observations = $project->close_observations;
    }

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) return;

        $tenant = Tenant::find($tenantId);
        if (!$tenant) return;

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        
        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }
        
        config(['database.connections.tenant.database' => $tenant->tenancy_db_name]);
    }

    // Lógica para enviar mensajes y procesar menciones
    public function sendMessage()
    {
        $this->ensureTenantConnection();

        $isParticipant = ProjectParticipant::where('project_id', $this->projectId)
            ->where('user_id', Auth::id())
            ->exists();

        if (!$isParticipant) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No eres participante de este proyecto']);
            return;
        }

        $this->validate(['newMessageText' => 'required|string']);

        $message = ProjectMessage::create([
            'project_id' => $this->projectId,
            'user_id' => Auth::id(),
            'message' => $this->newMessageText,
            'reply_to_id' => $this->replyingToMessageId
        ]);

        // Si el mensaje responde a otro que generó un pendiente dirigido a este usuario,
        // se marca automáticamente como "respondida" (punto 49 de la espec)
        if ($this->replyingToMessageId) {
            ProjectMention::where('message_id', $this->replyingToMessageId)
                ->where('mentioned_to', Auth::id())
                ->where('status', '!=', 'respondida')
                ->update(['status' => 'respondida']);
        }

        // Disparar evento WebSocket al túnel de Reverb sin toOthers() para evitar errores de Socket ID
        broadcast(new \App\Events\Tenant\Projects\NewProjectMessage($message));

        // Obtener datos del proyecto para las notificaciones
        $project = Project::find($this->projectId);
        $senderName = Auth::user()->name;
        $messagePreview = mb_substr($this->newMessageText, 0, 80);

        // Procesar menciones seleccionadas explícitamente desde el autocompletado @ (por ID, no por texto)
        $mentionedUserIds = [];
        if (!empty($this->mentionedUserIds)) {
            $sessionTenant = session('tenant_id');
            // Validar que los IDs recibidos del cliente realmente pertenecen al tenant actual
            $validUserIds = User::whereHas('tenants', function ($q) use ($sessionTenant) {
                $q->where('tenants.id', $sessionTenant);
            })->whereIn('id', $this->mentionedUserIds)->pluck('id')->toArray();

            foreach ($validUserIds as $userId) {
                ProjectMention::create([
                    'project_id' => $this->projectId,
                    'message_id' => $message->id,
                    'mentioned_by' => Auth::id(),
                    'mentioned_to' => $userId,
                    'status' => 'pendiente'
                ]);
                $mentionedUserIds[] = $userId;
            }
        }

        // Crear notificaciones y enviar broadcast
        if (!empty($mentionedUserIds)) {
            // Si hay menciones, notificar SOLO a los mencionados (excepto al emisor)
            $recipientIds = array_filter($mentionedUserIds, fn($id) => $id !== Auth::id());
            foreach ($recipientIds as $userId) {
                $notification = ProjectNotification::create([
                    'user_id' => $userId,
                    'project_id' => $this->projectId,
                    'message_id' => $message->id,
                    'sender_id' => Auth::id(),
                    'type' => 'mencion',
                ]);
                broadcast(new NewProjectNotification(
                    $userId, $this->projectId, $project->title ?? 'Proyecto',
                    $senderName, $messagePreview, 'mencion', $notification->id
                ));
            }
        } else {
            // Sin menciones: notificar a TODOS los participantes (excepto al emisor)
            $participantIds = ProjectParticipant::where('project_id', $this->projectId)
                ->where('user_id', '!=', Auth::id())
                ->pluck('user_id');

            foreach ($participantIds as $userId) {
                $notification = ProjectNotification::create([
                    'user_id' => $userId,
                    'project_id' => $this->projectId,
                    'message_id' => $message->id,
                    'sender_id' => Auth::id(),
                    'type' => 'mensaje',
                ]);
                broadcast(new NewProjectNotification(
                    $userId, $this->projectId, $project->title ?? 'Proyecto',
                    $senderName, $messagePreview, 'mensaje', $notification->id
                ));
            }
        }

        $this->reset(['newMessageText', 'replyingToMessageId', 'replyingToMessageText', 'mentionedUserIds']);
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Mensaje enviado']);
    }

    // Editar un mensaje propio, solo dentro de los primeros 10 segundos (punto 53)
    public function editMessage($messageId, $newText)
    {
        $this->ensureTenantConnection();

        $newText = trim($newText);
        if ($newText === '') {
            return;
        }

        $message = ProjectMessage::find($messageId);
        if (!$message || (int) $message->user_id !== (int) Auth::id()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No puedes editar este mensaje']);
            return;
        }

        if (now()->diffInSeconds($message->created_at) > 10) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'El tiempo para editar este mensaje ya expiró']);
            return;
        }

        $message->update(['message' => $newText]);
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Mensaje actualizado']);
    }

    public function selectReplyMessage($messageId)
    {
        $this->ensureTenantConnection();
        $msg = ProjectMessage::with('user')->find($messageId);
        if ($msg) {
            $this->replyingToMessageId = $msg->id;
            $this->replyingToMessageText = $msg->user->name . ': ' . substr($msg->message, 0, 50) . '...';
        }
    }

    public function clearReply()
    {
        $this->reset(['replyingToMessageId', 'replyingToMessageText']);
    }

    // Registra en la bitácora cada cambio de estado del proyecto (punto 64 - trazabilidad)
    private function logStatusChange(Project $project, string $newStatus)
    {
        if ($project->status === $newStatus) {
            return;
        }

        ProjectStatusHistory::create([
            'project_id' => $project->id,
            'from_status' => $project->status,
            'to_status' => $newStatus,
            'changed_by' => Auth::id()
        ]);
    }

    // Cambiar estado del proyecto
    public function updateStatus($newStatus)
    {
        $this->ensureTenantConnection();
        $project = Project::findOrFail($this->projectId);
        $this->logStatusChange($project, $newStatus);
        $project->update(['status' => $newStatus]);
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Estado del proyecto actualizado']);
    }

    // Crear orden de producción (Comercial)
    public function saveProductionOrder()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'qty' => 'required|integer|min:1',
            'price_unit' => 'required|numeric|min:0',
            'delivery_date' => 'required|date',
            'prod_observations' => 'nullable|string'
        ]);

        $project = Project::findOrFail($this->projectId);

        $total = $this->qty * $this->price_unit;

        $this->logStatusChange($project, 'orden_creada');
        $project->update([
            'qty' => $this->qty,
            'price_unit' => $this->price_unit,
            'total_value' => $total,
            'delivery_date' => $this->delivery_date,
            'prod_observations' => $this->prod_observations,
            'status' => 'orden_creada' // Cambia de cotización a orden creada
        ]);

        $this->total_value = $total;
        $this->showOrderModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Orden de producción creada y guardada']);
    }

    // Iniciar producción (Laboratorio o comercial) - Proyecto externo
    public function startProduction()
    {
        $this->updateStatus('en_produccion');
    }

    // Marcar en negociación (Comercial) - Proyecto externo
    public function markNegotiation()
    {
        $this->updateStatus('negociacion');
    }

    // Guardar fecha sugerida e iniciar desarrollo (área responsable) - Proyecto interno
    public function startInternalDevelopment()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'suggested_delivery_date' => 'required|date'
        ], [
            'suggested_delivery_date.required' => 'La fecha sugerida de entrega es obligatoria.'
        ]);

        $project = Project::findOrFail($this->projectId);
        $this->logStatusChange($project, 'en_produccion');
        $project->update([
            'suggested_delivery_date' => $this->suggested_delivery_date,
            'status' => 'en_produccion'
        ]);

        $this->showStartDevelopmentModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Desarrollo iniciado']);
    }

    // Crear pregunta para el cliente (Laboratorio)
    public function createQuestion()
    {
        $this->ensureTenantConnection();
        $this->validate(['newQuestionText' => 'required|string']);

        ProjectQuestion::create([
            'project_id' => $this->projectId,
            'asked_by' => Auth::id(),
            'question' => $this->newQuestionText,
            'status' => 'pendiente'
        ]);

        $this->reset(['newQuestionText']);
        $this->showQuestionModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Pregunta enviada al asesor comercial']);
    }

    // Abrir modal para responder pregunta (Comercial)
    public function openAnswerModal($questionId)
    {
        $this->ensureTenantConnection();
        $q = ProjectQuestion::findOrFail($questionId);
        $this->answeringQuestionId = $q->id;
        $this->answerText = $q->answer;
        $this->showAnswerModal = true;
    }

    // Guardar respuesta de pregunta (Comercial)
    public function saveAnswer()
    {
        $this->ensureTenantConnection();
        $this->validate(['answerText' => 'required|string']);

        $q = ProjectQuestion::findOrFail($this->answeringQuestionId);
        $q->update([
            'answer' => $this->answerText,
            'answered_by' => Auth::id(),
            'status' => 'respondida'
        ]);

        $this->reset(['answeringQuestionId', 'answerText']);
        $this->showAnswerModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Respuesta registrada con éxito']);
    }

    // Cerrar pregunta resuelta (Laboratorio)
    public function closeQuestion($questionId)
    {
        $this->ensureTenantConnection();
        $q = ProjectQuestion::findOrFail($questionId);
        $q->update(['status' => 'cerrada']);
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Pregunta marcada como cerrada']);
    }

    // Agregar avance de producción (Laboratorio)
    public function addAdvance()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'advanceDescription' => 'required|string',
            'advancePercentage' => 'required|integer|min:0|max:100'
        ]);

        ProjectAdvance::create([
            'project_id' => $this->projectId,
            'user_id' => Auth::id(),
            'description' => $this->advanceDescription,
            'percentage' => $this->advancePercentage
        ]);

        $this->reset(['advanceDescription', 'advancePercentage']);
        $this->showAdvanceModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Avance técnico guardado']);
    }

    // Terminar producción (Laboratorio)
    public function finishProduction()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'completion_date' => 'required|date',
            'lab_observations' => 'nullable|string'
        ]);

        $project = Project::findOrFail($this->projectId);
        $this->logStatusChange($project, 'terminado');
        $project->update([
            'completion_date' => $this->completion_date,
            'lab_observations' => $this->lab_observations,
            'status' => 'terminado' // Listo para entregar
        ]);

        $this->showLabFinishModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Producción marcada como terminada con éxito']);
    }

    // Cerrar y archivar proyecto (Comercial)
    public function closeProject()
    {
        $this->ensureTenantConnection();
        $this->validate([
            'real_delivery_date' => 'required|date',
            'close_observations' => 'nullable|string'
        ]);

        $project = Project::findOrFail($this->projectId);
        $this->logStatusChange($project, 'cerrado_entregado');
        $project->update([
            'real_delivery_date' => $this->real_delivery_date,
            'close_observations' => $this->close_observations,
            'status' => 'cerrado_entregado'
        ]);

        $this->showCloseModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Proyecto finalizado correctamente']);
        
        return redirect()->route('tenant.projects');
    }

    public function render()
    {
        $this->ensureTenantConnection();
        
        $project = Project::with(['customer', 'creator', 'assignedUser', 'statusHistory.user'])->findOrFail($this->projectId);
        
        // 1. Obtener lista de usuarios para el autocompletado de menciones @ en Alpine
        $sessionTenant = session('tenant_id');
        $usersList = User::whereHas('tenants', function($q) use ($sessionTenant) {
                $q->where('tenants.id', $sessionTenant);
            })->get()->map(function($u) {
            return ['id' => $u->id, 'name' => $u->name];
        })->toArray();

        // 2. Consulta de chat con filtros aplicados
        $chatQuery = ProjectMessage::where('project_id', $this->projectId)
            ->with(['user.profile', 'repliedTo.user']);

        if ($this->chatFilterUser) {
            $chatQuery->where('user_id', $this->chatFilterUser);
        }

        if ($this->chatFilterRole) {
            // Los usuarios están en la conexión central, no en tenant.
            // Primero obtenemos los IDs desde central y luego filtramos.
            $roleUserIds = User::where('profile_id', $this->chatFilterRole)->pluck('id')->toArray();
            $chatQuery->whereIn('user_id', $roleUserIds);
        }

        $messages = $chatQuery->orderBy('created_at', 'asc')->get();

        // 3. Obtener avances y preguntas
        $advances = ProjectAdvance::where('project_id', $this->projectId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $questions = ProjectQuestion::where('project_id', $this->projectId)
            ->with(['asker', 'answerer'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 4. ¿El usuario actual es participante? (controla si puede escribir en el chat)
        $isParticipant = ProjectParticipant::where('project_id', $this->projectId)
            ->where('user_id', Auth::id())
            ->exists();

        return view('livewire.tenant.projects.project-workspace', [
            'project' => $project,
            'messages' => $messages,
            'advances' => $advances,
            'questions' => $questions,
            'usersList' => $usersList,
            'isParticipant' => $isParticipant
        ])->layout('layouts.app', ['header' => 'Espacio de Trabajo: ' . $project->title]);
    }
}
