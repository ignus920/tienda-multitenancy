<?php

namespace App\Livewire\Layout;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Tenant\Projects\ProjectNotification;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Auth;

class NotificationBell extends Component
{
    public $notifications = [];
    public $unreadCount = 0;
    public $showDropdown = false;
    public $userId;

    public function mount()
    {
        $this->userId = Auth::id();
        $this->loadNotifications();
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
        // Al recibir el WebSocket, recargamos las notificaciones de la base de datos
        $this->loadNotifications();

        // Despachar evento al navegador para reproducir el sonido de notificación
        $this->dispatch('play-notification-sound');
    }

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

    public function markAsRead($notificationId)
    {
        $this->ensureTenantConnection();

        $notification = ProjectNotification::where('id', $notificationId)
            ->where('user_id', Auth::id())
            ->first();

        if ($notification) {
            $notification->update(['read_at' => now()]);
            $projectId = $notification->project_id;
            $this->loadNotifications();

            return redirect()->route('tenant.projects.workspace', $projectId);
        }
    }

    public function markAllAsRead()
    {
        $this->ensureTenantConnection();

        ProjectNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
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
