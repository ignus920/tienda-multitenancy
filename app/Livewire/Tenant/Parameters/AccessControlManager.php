<?php

namespace App\Livewire\Tenant\Parameters;

use Livewire\Component;
use App\Models\Auth\User;
use App\Models\Tenant\CnfAccesoIp;
use App\Models\Tenant\CnfAccesoHorario;
use App\Models\Tenant\CnfLogAcceso;
use Illuminate\Support\Facades\Auth;

class AccessControlManager extends Component
{
    public $selectedUserId;
    public $search = '';
    
    // Datos para nueva IP
    public $newIp = '';
    public $ipDescription = '';
    
    // Datos para nuevo Horario
    public $dayOfWeek = 1;
    public $startTime = '08:00';
    public $endTime = '17:00';

    protected $rules = [
        'newIp' => 'required|ip',
        'ipDescription' => 'nullable|string|max:100',
    ];

    public function selectUser($userId)
    {
        $this->ensureTenantConnection();
        $this->selectedUserId = $userId;
        $this->reset(['newIp', 'ipDescription', 'dayOfWeek', 'startTime', 'endTime']);
    }

    public function addIp()
    {
        $this->ensureTenantConnection();
        if (!$this->selectedUserId) return;
        
        $this->validate();

        CnfAccesoIp::create([
            'user_id' => $this->selectedUserId,
            'ip_allowed' => $this->newIp,
            'description' => $this->ipDescription,
            'is_active' => true,
        ]);

        $this->reset(['newIp', 'ipDescription']);
        $this->dispatch('swal', ['title' => 'IP Agregada', 'icon' => 'success']);
    }

    public function deleteIp($id)
    {
        $this->ensureTenantConnection();
        CnfAccesoIp::destroy($id);
        $this->dispatch('swal', ['title' => 'IP Eliminada', 'icon' => 'warning']);
    }

    public function addSchedule()
    {
        $this->ensureTenantConnection();
        if (!$this->selectedUserId) return;

        CnfAccesoHorario::create([
            'user_id' => $this->selectedUserId,
            'day_of_week' => $this->dayOfWeek,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'is_active' => true,
        ]);

        $this->dispatch('swal', ['title' => 'Horario Agregado', 'icon' => 'success']);
    }

    public function deleteSchedule($id)
    {
        $this->ensureTenantConnection();
        CnfAccesoHorario::destroy($id);
        $this->dispatch('swal', ['title' => 'Horario Eliminado', 'icon' => 'warning']);
    }

    public function applyStandardSchedule()
    {
        $this->ensureTenantConnection();
        if (!$this->selectedUserId) return;

        $days = [1, 2, 3, 4, 5]; // L-V
        foreach ($days as $day) {
            CnfAccesoHorario::updateOrCreate(
                ['user_id' => $this->selectedUserId, 'day_of_week' => $day],
                ['start_time' => '08:00', 'end_time' => '17:30', 'is_active' => true]
            );
        }

        // Sábado
        CnfAccesoHorario::updateOrCreate(
            ['user_id' => $this->selectedUserId, 'day_of_week' => 6],
            ['start_time' => '08:00', 'end_time' => '13:00', 'is_active' => true]
        );

        $this->dispatch('swal', ['title' => 'Horario Estándar Aplicado', 'icon' => 'success']);
    }

    public function render()
    {
        $this->ensureTenantConnection();
        $tenantId = session('tenant_id');
        
        // Listar usuarios del tenant
        $users = User::whereHas('tenants', function($q) use ($tenantId) {
                $q->where('tenants.id', $tenantId);
            })
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->get();

        $selectedUser = $this->selectedUserId ? User::find($this->selectedUserId) : null;
        
        $ips = $this->selectedUserId 
            ? CnfAccesoIp::where('user_id', $this->selectedUserId)->get() 
            : null;
            
        $horarios = $this->selectedUserId 
            ? CnfAccesoHorario::where('user_id', $this->selectedUserId)
                ->orderBy('day_of_week')
                ->get() 
            : null;

        $logs = CnfLogAcceso::orderBy('id', 'desc')
            ->take(50)
            ->get();

        return view('livewire.tenant.parameters.access-control-manager', [
            'users' => $users,
            'selectedUser' => $selectedUser,
            'ips' => $ips,
            'horarios' => $horarios,
            'logs' => $logs,
            'currentIp' => request()->ip()
        ]);
    }

    private function ensureTenantConnection(): void
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            throw new \Exception('No tenant selected');
        }

        $tenant = \App\Models\Auth\Tenant::find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            throw new \Exception('Invalid tenant');
        }

        // Establecer conexión tenant
        $tenantManager = app(\App\Services\Tenant\TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }
}
