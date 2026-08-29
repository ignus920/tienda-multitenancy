<?php

namespace App\Livewire\Layout;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Tenant\Projects\ProjectNotification;
use App\Models\Tenant\Projects\ProjectMention;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationBell extends Component
{
    public $notifications = [];
    public $unreadCount = 0;
    public $pendingMentions = [];
    public $pendingCount = 0;
    public $activeTab = 'general';
    public $showDropdown = false;
    public $userId;

    public function mount()
    {
        $this->userId = Auth::id();
        $this->loadNotifications();
        $this->loadPendingMentions();
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
        $this->dispatch('play-notification-sound');
    }

    #[On('unanswered-questions-updated')]
    #[On('echo-private:user.{userId},.NewProjectMessage')]
    public function onPendingMentionsUpdate()
    {
        $this->loadPendingMentions();
        $this->loadNotifications();
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
