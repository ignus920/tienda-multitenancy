<?php

namespace App\Livewire\Tenant\Projects;

use Livewire\Component;
use App\Models\Tenant\Projects\Project;
use App\Models\Tenant\Projects\ProjectMessage;
use App\Models\Tenant\Projects\ProjectMention;
use App\Models\Tenant\Projects\ProjectQuestion;
use App\Models\Tenant\Projects\ProjectAdvance;
use App\Models\Auth\User;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectWorkspace extends Component
{
    public $projectId;
    
    // Filtros de chat
    public $chatFilterUser = '';
    public $chatFilterRole = '';

    // Nuevo mensaje
    public $newMessageText = '';
    public $replyingToMessageId = null;
    public $replyingToMessageText = '';

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

    // Control de modales/secciones
    public $showOrderModal = false;
    public $showQuestionModal = false;
    public $showAnswerModal = false;
    public $showAdvanceModal = false;
    public $showLabFinishModal = false;
    public $showCloseModal = false;

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
        $this->validate(['newMessageText' => 'required|string']);

        $message = ProjectMessage::create([
            'project_id' => $this->projectId,
            'user_id' => Auth::id(),
            'message' => $this->newMessageText,
            'reply_to_id' => $this->replyingToMessageId
        ]);

        // Procesar menciones con @
        // Buscamos todas las ocurrencias de @nombre en el texto
        preg_match_all('/@([a-zA-Z0-9_\-\.]+)/', $this->newMessageText, $matches);
        if (!empty($matches[1])) {
            $usernames = array_unique($matches[1]);
            // Buscar usuarios correspondientes en el tenant
            $sessionTenant = session('tenant_id');
            $users = User::whereIn('name', $usernames)
                ->whereHas('tenants', function($q) use ($sessionTenant) {
                    $q->where('tenants.id', $sessionTenant);
                })->get();

            foreach ($users as $user) {
                // Registrar mención
                ProjectMention::create([
                    'project_id' => $this->projectId,
                    'message_id' => $message->id,
                    'mentioned_by' => Auth::id(),
                    'mentioned_to' => $user->id,
                    'status' => 'pendiente'
                ]);
            }
        }

        $this->reset(['newMessageText', 'replyingToMessageId', 'replyingToMessageText']);
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Mensaje enviado']);
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

    // Cambiar estado del proyecto
    public function updateStatus($newStatus)
    {
        $this->ensureTenantConnection();
        $project = Project::findOrFail($this->projectId);
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

    // Iniciar producción (Laboratorio o comercial)
    public function startProduction()
    {
        $this->updateStatus('en_produccion');
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

        // Marcar menciones de este proyecto asociadas a este comercial como respondidas si aplica
        ProjectMention::where('project_id', $this->projectId)
            ->where('mentioned_to', Auth::id())
            ->update(['status' => 'respondida']);

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
        $project->update([
            'real_delivery_date' => $this->real_delivery_date,
            'close_observations' => $this->close_observations,
            'status' => 'archivados' // Archivado en historial
        ]);

        $this->showCloseModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Proyecto entregado y archivado correctamente']);
        
        return redirect()->route('tenant.projects');
    }

    public function render()
    {
        $this->ensureTenantConnection();
        
        $project = Project::with(['customer', 'creator'])->findOrFail($this->projectId);
        
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

        return view('livewire.tenant.projects.project-workspace', [
            'project' => $project,
            'messages' => $messages,
            'advances' => $advances,
            'questions' => $questions,
            'usersList' => $usersList
        ])->layout('layouts.app', ['header' => 'Espacio de Trabajo: ' . $project->title]);
    }
}
