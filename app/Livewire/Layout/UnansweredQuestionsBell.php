<?php

namespace App\Livewire\Layout;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant\Projects\ProjectQuestion;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Carbon\Carbon;

class UnansweredQuestionsBell extends Component
{
    public $questions = [];
    public $unansweredCount = 0;

    public function mount()
    {
        $this->loadQuestions();
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

    public function loadQuestions()
    {
        $userId = Auth::id();

        if (!$userId) {
            $this->questions = [];
            $this->unansweredCount = 0;
            return;
        }

        // Obtener preguntas donde el creador sea el usuario actual y sigan pendientes
        $rawQuestions = ProjectQuestion::with('project')
            ->where('asked_by', $userId)
            ->where('status', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->take(15) // Limitamos a 15 para no saturar el menú
            ->get();

        $this->unansweredCount = ProjectQuestion::where('asked_by', $userId)
            ->where('status', 'pendiente')
            ->count();

        $this->questions = $rawQuestions->map(function ($q) {
            return [
                'id' => $q->id,
                'project_id' => $q->project_id,
                'project_title' => $q->project ? $q->project->title : 'Proyecto Desconocido',
                'question_preview' => $q->question,
                'time_ago' => Carbon::parse($q->created_at)->locale('es')->diffForHumans()
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.layout.unanswered-questions-bell');
    }
}
