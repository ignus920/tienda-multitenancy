<?php

namespace App\Livewire\Tenant\Projects;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Projects\Project;
use App\Models\Tenant\Projects\ProjectMention;
use App\Models\Tenant\Projects\ProjectQuestion;
use App\Models\Tenant\Customer\VntCompany;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManageProjects extends Component
{
    use WithPagination;

    // Búsqueda y filtrado
    public $search = '';
    public $selectedStatus = '';
    public $selectedTab = 'activos'; // activos, archivados, pendientes

    // Campos del Modal Crear Proyecto
    public $showCreateModal = false;
    public $title = '';
    public $description = '';
    public $customerSearch = '';
    public $selectedCustomerId = null;
    public $selectedCustomerName = '';
    public $customerResults = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedStatus' => ['except' => ''],
        'selectedTab' => ['except' => 'activos']
    ];

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

    public function updatedCustomerSearch()
    {
        $this->ensureTenantConnection();
        
        if (strlen($this->customerSearch) < 2) {
            $this->customerResults = [];
            return;
        }

        $this->customerResults = VntCompany::where('status', 1)
            ->where(function ($q) {
                $q->where('businessName', 'like', '%' . $this->customerSearch . '%')
                  ->orWhere('identification', 'like', '%' . $this->customerSearch . '%')
                  ->orWhere('firstName', 'like', '%' . $this->customerSearch . '%')
                  ->orWhere('lastName', 'like', '%' . $this->customerSearch . '%');
            })
            ->limit(10)
            ->get()
            ->map(function ($company) {
                $name = $company->businessName ?: trim($company->firstName . ' ' . $company->lastName);
                return [
                    'id' => $company->id,
                    'name' => $name,
                    'identification' => $company->identification
                ];
            })
            ->toArray();
    }

    public function selectCustomer($id, $name)
    {
        $this->selectedCustomerId = $id;
        $this->selectedCustomerName = $name;
        $this->customerSearch = '';
        $this->customerResults = [];
    }

    public function clearCustomerSelection()
    {
        $this->selectedCustomerId = null;
        $this->selectedCustomerName = '';
    }

    public function openCreateModal()
    {
        $this->reset(['title', 'description', 'customerSearch', 'selectedCustomerId', 'selectedCustomerName', 'customerResults']);
        $this->showCreateModal = true;
    }

    public function createProject()
    {
        $this->ensureTenantConnection();

        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'selectedCustomerId' => 'required|exists:vnt_companies,id'
        ], [
            'title.required' => 'El título del proyecto es obligatorio.',
            'description.required' => 'La descripción del proyecto es obligatoria.',
            'selectedCustomerId.required' => 'Debe seleccionar un cliente de la lista.'
        ]);

        $project = Project::create([
            'title' => $this->title,
            'description' => $this->description,
            'company_id' => $this->selectedCustomerId,
            'created_by' => Auth::id(),
            'status' => 'cotizacion'
        ]);

        $this->showCreateModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Proyecto creado con éxito']);
        
        return redirect()->route('tenant.projects.workspace', ['id' => $project->id]);
    }

    public function markNotificationAsSeen($mentionId)
    {
        $this->ensureTenantConnection();
        $mention = ProjectMention::find($mentionId);
        if ($mention && $mention->mentioned_to == Auth::id()) {
            $mention->update(['status' => 'vista']);
            return redirect()->route('tenant.projects.workspace', ['id' => $mention->project_id]);
        }
    }

    public function render()
    {
        $this->ensureTenantConnection();
        $userId = Auth::id();

        // 1. Obtener menciones y pendientes del usuario actual
        $myMentions = ProjectMention::where('mentioned_to', $userId)
            ->where('status', 'pendiente')
            ->with(['project', 'sender', 'message'])
            ->orderBy('created_at', 'desc')
            ->get();

        $myQuestions = ProjectQuestion::where('status', 'pendiente')
            ->whereHas('project', function ($q) use ($userId) {
                $q->where('created_by', $userId);
            })
            ->with(['project', 'asker'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Consulta de Proyectos
        $query = Project::with(['customer', 'creator'])
            ->withCount(['questions' => function ($q) {
                $q->where('status', 'pendiente');
            }]);

        // Aplicar filtros por tipo de pestaña
        if ($this->selectedTab === 'archivados') {
            $query->where('status', 'archivados');
        } else {
            $query->where('status', '!=', 'archivados');
        }

        if ($this->selectedStatus) {
            $query->where('status', $this->selectedStatus);
        }

        // Buscador flexible (multi-palabra) según el estándar del cotizador
        if ($this->search) {
            $words = array_filter(explode(' ', trim($this->search)));
            foreach ($words as $word) {
                $query->where(function ($q) use ($word) {
                    $q->where('title', 'like', '%' . $word . '%')
                      ->orWhere('description', 'like', '%' . $word . '%')
                      ->orWhere('status', 'like', '%' . $word . '%')
                      ->orWhereHas('customer', function ($qSub) use ($word) {
                          $qSub->where('businessName', 'like', '%' . $word . '%')
                               ->orWhere('identification', 'like', '%' . $word . '%')
                               ->orWhere('firstName', 'like', '%' . $word . '%')
                               ->orWhere('lastName', 'like', '%' . $word . '%');
                      })
                      ->orWhereHas('creator', function ($qSub) use ($word) {
                          $qSub->where('name', 'like', '%' . $word . '%');
                      });
                });
            }
        }

        $projects = $query->orderBy('created_at', 'desc')->paginate(12);

        return view('livewire.tenant.projects.manage-projects', [
            'projects' => $projects,
            'myMentions' => $myMentions,
            'myQuestions' => $myQuestions
        ])->layout('layouts.app', ['header' => 'Gestión de Proyectos']);
    }
}
