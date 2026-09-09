<?php

namespace App\Livewire\Layout;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Tenant\Projects\ProjectNotification;
use App\Models\Tenant\Projects\ProjectMention;
use App\Models\Tenant\Projects\ProjectTask;
use App\Models\Tenant\TaskPlanner\TaskNotification;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class NotificationBell extends Component
{
    public $notifications = [];
    public $unreadCount = 0;
    public $pendingMentions = [];
    public $pendingCount = 0;
    
    public $pendingTasks = [];
    public $taskCount = 0;

    // Planificador Operativo (módulo tsk_*)
    public $operativeNotifications = [];
    public $operativeCount = 0;

    public $activeTab = 'general';
    public $showDropdown = false;
    public $userId;

    public function mount()
    {
        $this->userId = Auth::id();
        $this->loadNotifications();
        $this->loadPendingMentions();
        $this->loadPendingTasks();
        $this->loadOperativeNotifications();
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

    /**
     * Escuchar el evento WebSocket de notificaciones personales.
     * El nombre del listener sigue la convención de Livewire 3:
     * echo-private:user.{userId},.NewProjectNotification
     */
    #[On('echo-private:user.{userId},.NewProjectNotification')]
    public function onNewNotification($payload = null)
    {
        $this->loadNotifications();
        $this->loadPendingMentions(); // Recargar pendientes (por si la notificacion fue una respuesta)
        $this->loadPendingTasks();
        $this->dispatch('play-notification-sound');
    }

    #[On('unanswered-questions-updated')]
    #[On('echo-private:user.{userId},.NewProjectMessage')]
    public function onPendingMentionsUpdate()
    {
        $this->loadPendingMentions();
        $this->loadPendingTasks();
        $this->loadNotifications();
    }

    /**
     * Aviso en tiempo real del Planificador Operativo (tsk_*).
     * Canal privado personal, evento propio: no interfiere con Proyectos.
     */
    #[On('echo-private:user.{userId},.NewTaskPlannerNotification')]
    public function onNewOperativeNotification($payload = null)
    {
        $data = is_array($payload) && isset($payload[0]) ? $payload[0] : $payload;

        $this->loadOperativeNotifications();
        $this->dispatch('play-notification-sound');
        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => $data['message'] ?? 'Tienes una nueva notificación de tareas',
        ]);
    }

    #[On('notifications-updated')]
    public function loadNotifications()
    {
        $this->ensureTenantConnection();

        if (!Auth::check()) return;

        $this->unreadCount = ProjectNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        $this->notifications = ProjectNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->with(['project', 'sender'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'project_id' => $n->project_id,
                    'project_title' => $n->project->title ?? 'Proyecto',
                    'sender_name' => $n->sender->name ?? 'Usuario',
                    'sender_avatar' => $n->sender ? $n->sender->getAvatarUrl() : '',
                    'message_preview' => $n->message->message ?? '',
                    'type' => $n->type,
                    'time_ago' => $n->created_at->diffForHumans(),
                    'created_at' => $n->created_at->toISOString(),
                ];
            })
            ->toArray();

        // El wire:poll.60s ya llama a este método: aprovechamos para refrescar
        // también las notificaciones operativas como respaldo del WebSocket.
        $this->loadOperativeNotifications();
    }

    public function loadOperativeNotifications()
    {
        $this->ensureTenantConnection();

        if (!Auth::check()) {
            $this->operativeNotifications = [];
            $this->operativeCount = 0;
            return;
        }

        try {
            if (!Schema::connection('tenant')->hasTable('tsk_notifications')) {
                $this->operativeNotifications = [];
                $this->operativeCount = 0;
                return;
            }

            $rows = TaskNotification::where('user_id', Auth::id())
                ->whereNull('read_at')
                ->latest('id')
                ->limit(20)
                ->get();

            $this->operativeCount = $rows->count();
            $this->operativeNotifications = $rows->map(fn($n) => [
                'id' => $n->id,
                'task_id' => $n->task_id,
                'type' => $n->type,
                'message' => $n->message,
                'time_ago' => Carbon::parse($n->created_at)->locale('es')->diffForHumans(),
            ])->toArray();
        } catch (\Throwable $e) {
            $this->operativeNotifications = [];
            $this->operativeCount = 0;
        }
    }

    public function markOperativeAsRead($id)
    {
        $this->ensureTenantConnection();

        TaskNotification::where('id', $id)
            ->where('user_id', Auth::id())
            ->update(['read_at' => now()]);

        $this->loadOperativeNotifications();
    }

    public function markAllOperativeAsRead()
    {
        $this->ensureTenantConnection();

        TaskNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->loadOperativeNotifications();
        $this->showDropdown = false;
    }

    public function loadPendingMentions()
    {
        $this->ensureTenantConnection();

        if (!Auth::check()) {
            $this->pendingMentions = [];
            $this->pendingCount = 0;
            return;
        }

        // Obtener menciones/preguntas donde el creador sea el usuario actual y sigan pendientes
        $rawQuestions = ProjectMention::with(['project', 'message', 'recipient'])
            ->where('mentioned_by', Auth::id())
            ->where('status', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->take(15) // Limitamos a 15 para no saturar el menú
            ->get();

        $this->pendingCount = ProjectMention::where('mentioned_by', Auth::id())
            ->where('status', 'pendiente')
            ->count();

        $this->pendingMentions = $rawQuestions->map(function ($q) {
            return [
                'id' => $q->id,
                'project_id' => $q->project_id,
                'project_title' => $q->project ? $q->project->title : 'Proyecto Desconocido',
                'question_preview' => $q->message ? $q->message->message : 'Mención',
                'time_ago' => Carbon::parse($q->created_at)->locale('es')->diffForHumans(),
                'recipient_name' => $q->recipient ? $q->recipient->name : 'Usuario',
                'recipient_avatar' => $q->recipient ? $q->recipient->getAvatarUrl() : ''
            ];
        })->toArray();
    }

    public function loadPendingTasks()
    {
        $this->ensureTenantConnection();

        if (!Auth::check()) {
            $this->pendingTasks = [];
            $this->taskCount = 0;
            return;
        }

        $rawTasks = ProjectTask::with(['project', 'creator'])
            ->where('assigned_to', Auth::id())
            ->where('status', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        $this->taskCount = ProjectTask::where('assigned_to', Auth::id())
            ->where('status', 'pendiente')
            ->count();

        $this->pendingTasks = $rawTasks->map(function ($t) {
            return [
                'id' => $t->id,
                'project_id' => $t->project_id,
                'project_title' => $t->project ? $t->project->title : 'Proyecto Desconocido',
                'title' => $t->title,
                'creator_name' => $t->creator ? $t->creator->name : 'Usuario',
                'creator_avatar' => $t->creator ? $t->creator->getAvatarUrl() : '',
                'time_ago' => Carbon::parse($t->created_at)->locale('es')->diffForHumans(),
            ];
        })->toArray();
    }

    public function markAsRead($notificationId)
    {
        $this->ensureTenantConnection();

        $notification = ProjectNotification::where('id', $notificationId)
            ->where('user_id', Auth::id())
            ->first();

        if ($notification) {
            // No marcar como leída si es una mención, se descuenta solo al responder
            if (!in_array($notification->type, ['mencion', 'mencion_avance'])) {
                $notification->update(['read_at' => now()]);
            }
            $projectId = $notification->project_id;
            $this->loadNotifications();

            // Si tiene message_id, anexarlo para que haga scroll automático
            $routeParams = ['id' => $projectId];
            if ($notification->message_id) {
                $routeParams['msg'] = $notification->message_id;
            }

            return redirect()->route('tenant.projects.workspace', $routeParams);
        }
    }

    public function markAllAsRead()
    {
        $this->ensureTenantConnection();

        ProjectNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->whereNotIn('type', ['mencion', 'mencion_avance']) // Excluir menciones
            ->update(['read_at' => now()]);

        $this->loadNotifications();
        $this->showDropdown = false;
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
    }

    public function render()
    {
        return view('livewire.layout.notification-bell');
    }
}
