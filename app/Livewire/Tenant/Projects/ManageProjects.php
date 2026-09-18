<?php

namespace App\Livewire\Tenant\Projects;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\Tenant\Projects\Project;
use App\Models\Tenant\Projects\ProjectMention;
use App\Models\Tenant\Projects\ProjectParticipant;
use App\Models\Tenant\Projects\ProjectQuestion;
use App\Models\Tenant\Customer\VntCompany;
use App\Models\Auth\Tenant;
use App\Models\Auth\User;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ManageProjects extends Component
{
    use WithPagination;

    /**
     * Perfil "Vendedor POS". Cuando este perfil crea un proyecto, no elige
     * participantes: se asignan automáticamente por Perfil (no por
     * Departamento) — "Dirigido a" queda con quien tenga Perfil
     * "Laboratorio", y como participantes fijos entran todos los Vendedor
     * POS + todos los Perfil "Importaciones" + todos los Perfil
     * "Administrador". Así no hay que mantener departamentos aparte: si
     * cambia quién tiene cada Perfil, se ajusta desde ahí.
     */
    const SALESPERSON_PROFILE_ID = 4;
    const ADMIN_PROFILE_ID = 2;
    const LABORATORIO_PROFILE_ID = 20;
    const IMPORTACIONES_PROFILE_ID = 21;

    // Búsqueda y filtrado
    public $search = '';
    public $selectedStatus = '';
    public $selectedTab = 'activos'; // activos, archivados, pendientes

    // Campos del Modal Crear Proyecto
    public $showCreateModal = false;
    public $projectType = 'external';
    public $title = '';
    public $description = '';
    public $customerSearch = '';
    public $selectedCustomerId = null;
    public $selectedCustomerName = '';
    public $customerResults = [];
    public $assignedToUserId = null;
    public $requestedDeliveryDate = null;

    // Filtro rápido de vencimiento (proyectos internos)
    public $vencimientoFilter = '';

    // Más filtros del buscador (historial)
    public $searchDateFrom = '';
    public $searchDateTo = '';
    public $searchParticipantId = '';
    public $searchAssignedToId = ''; // Nuevo filtro Dirigido A

    // Filtros del panel "Mis Pendientes"
    public $pendientesStatusFilter = 'pendiente';
    public $pendientesProjectFilter = '';
    public $pendientesDateFilter = '';
    public $pendientesPersonFilter = '';

    // Filtro por tipo de proyecto (interno, externo)
    public $projectTypeFilter = '';

    // Usuario autenticado para el WebSocket
    public $userId;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedStatus' => ['except' => ''],
        'selectedTab' => ['except' => 'activos'],
        'vencimientoFilter' => ['except' => ''],
        'projectTypeFilter' => ['except' => ''],
        'searchAssignedToId' => ['except' => '']
    ];

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    public function mount()
    {
        $this->userId = Auth::id();
        
        if (empty($this->searchDateFrom) && empty($this->searchDateTo)) {
            $this->searchDateFrom = now()->subMonth()->format('Y-m-d');
            $this->searchDateTo = now()->format('Y-m-d');
        }
    }

    // Limpia el buscador y todos los filtros del listado (vencimiento, estado, fechas, participante)
    public function clearFilters()
    {
        $this->reset([
            'search',
            'selectedStatus',
            'selectedTab',
            'vencimientoFilter',
            'projectTypeFilter',
            'searchParticipantId',
            'searchAssignedToId'
        ]);
        
        $this->searchDateFrom = now()->subMonth()->format('Y-m-d');
        $this->searchDateTo = now()->format('Y-m-d');
        
        $this->resetPage();
    }

    #[On('echo-private:user.{userId},.NewProjectNotification')]
    public function onProjectUpdate($payload = null)
    {
        // Fuerza el re-render de la vista para actualizar el listado
        $this->resetPage();
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
        $this->reset(['projectType', 'title', 'description', 'customerSearch', 'selectedCustomerId', 'selectedCustomerName', 'customerResults', 'assignedToUserId', 'requestedDeliveryDate']);
        $this->showCreateModal = true;
    }

    public function createProject()
    {
        $this->ensureTenantConnection();

        $isSalesperson = (int) Auth::user()?->profile_id === self::SALESPERSON_PROFILE_ID;

        if ($this->projectType === 'internal') {
            $rules = [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'requestedDeliveryDate' => 'required|date'
            ];
            $messages = [
                'title.required' => 'El título del proyecto es obligatorio.',
                'description.required' => 'La descripción del proyecto es obligatoria.',
                'requestedDeliveryDate.required' => 'La fecha de entrega solicitada es obligatoria.'
            ];

            // Un Vendedor POS no elige "Dirigido a" — se asigna automático.
            if (!$isSalesperson) {
                $rules['assignedToUserId'] = 'required';
                $messages['assignedToUserId.required'] = 'Debe seleccionar a quién va dirigido el proyecto.';
            }

            $this->validate($rules, $messages);

            $assignedToUserId = $isSalesperson
                ? $this->defaultSalespersonResponsibleId()
                : $this->assignedToUserId;

            $project = Project::create([
                'type' => 'internal',
                'title' => $this->title,
                'description' => $this->description,
                'company_id' => null,
                'created_by' => Auth::id(),
                'assigned_to' => $assignedToUserId,
                'delivery_date' => $this->requestedDeliveryDate,
                'status' => 'cotizacion'
            ]);

            if (!$isSalesperson) {
                $this->registerParticipant($project->id, $assignedToUserId);
            }
        } else {
            $this->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'selectedCustomerId' => 'required|exists:tenant.vnt_companies,id'
            ], [
                'title.required' => 'El título del proyecto es obligatorio.',
                'description.required' => 'La descripción del proyecto es obligatoria.',
                'selectedCustomerId.required' => 'Debe seleccionar un cliente de la lista.',
                'selectedCustomerId.exists' => 'El cliente seleccionado no es válido, intenta buscarlo de nuevo.'
            ]);

            $project = Project::create([
                'type' => 'external',
                'title' => $this->title,
                'description' => $this->description,
                'company_id' => $this->selectedCustomerId,
                'created_by' => Auth::id(),
                'assigned_to' => $isSalesperson ? $this->defaultSalespersonResponsibleId() : null,
                'status' => 'cotizacion'
            ]);
        }

        // Vendedor POS: participantes fijos por departamento (ver
        // defaultSalespersonParticipantIds()) en vez de la selección libre.
        if ($isSalesperson) {
            foreach ($this->defaultSalespersonParticipantIds() as $participantId) {
                $this->registerParticipant($project->id, $participantId);
            }
        }

        // El creador también queda registrado como participante desde ya,
        // para poder recibir el chat en tiempo real aunque aún no haya escrito nada
        $this->registerParticipant($project->id, Auth::id());

        $this->showCreateModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Proyecto creado con éxito']);

        return redirect()->route('tenant.projects.workspace', ['id' => $project->id]);
    }

    private function registerParticipant($projectId, $userId)
    {
        $user = User::find($userId);
        ProjectParticipant::firstOrCreate(
            ['project_id' => $projectId, 'user_id' => $userId],
            ['role' => $user->profile->name ?? 'Sin área']
        );
    }

    /**
     * IDs de usuarios de ESTA empresa (tenant actual) con un Perfil dado.
     * `users` es una tabla central compartida por toda la plataforma — sin
     * este filtro, un profile_id trae usuarios de otras empresas también.
     */
    private function usersWithProfile(int $profileId): array
    {
        return User::where('profile_id', $profileId)
            ->whereHas('tenants', function ($q) {
                $q->where('tenants.id', session('tenant_id'));
            })
            ->pluck('id')
            ->all();
    }

    /**
     * Usuario "Dirigido a" por defecto para proyectos creados por un
     * Vendedor POS: el primer usuario activo con Perfil "Laboratorio".
     */
    private function defaultSalespersonResponsibleId(): ?int
    {
        $ids = $this->usersWithProfile(self::LABORATORIO_PROFILE_ID);

        if (empty($ids)) {
            Log::warning('No hay ningún usuario de esta empresa con Perfil id ' . self::LABORATORIO_PROFILE_ID . ' (Laboratorio) — no se pudo asignar "Dirigido a" automático.');
            return null;
        }

        return (int) $ids[0];
    }

    /**
     * Participantes por defecto para proyectos creados por un Vendedor POS:
     * todos los usuarios con Perfil Vendedor POS + Perfil "Importaciones"
     * (Camilo, necesita ver el proyecto para revisar la Solicitud de
     * Materiales y generar la Salida de Mercancía) + Perfil "Administrador"
     * + el responsable (Perfil "Laboratorio") — todos de esta empresa.
     */
    private function defaultSalespersonParticipantIds(): array
    {
        $ids = $this->usersWithProfile(self::SALESPERSON_PROFILE_ID);
        $ids = array_merge($ids, $this->usersWithProfile(self::ADMIN_PROFILE_ID));
        $ids = array_merge($ids, $this->usersWithProfile(self::IMPORTACIONES_PROFILE_ID));

        $responsibleId = $this->defaultSalespersonResponsibleId();
        if ($responsibleId) {
            $ids[] = $responsibleId;
        }

        return array_values(array_unique(array_map('intval', $ids)));
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

        // 1. Obtener menciones y pendientes del usuario actual, con filtros aplicados
        $mentionsQuery = ProjectMention::where('mentioned_to', $userId)
            ->with(['project', 'sender', 'message']);

        if ($this->pendientesStatusFilter) {
            $mentionsQuery->where('status', $this->pendientesStatusFilter);
        }
        if ($this->pendientesProjectFilter) {
            $mentionsQuery->where('project_id', $this->pendientesProjectFilter);
        }
        if ($this->pendientesDateFilter) {
            $mentionsQuery->whereDate('created_at', $this->pendientesDateFilter);
        }
        if ($this->pendientesPersonFilter) {
            $mentionsQuery->where('mentioned_by', $this->pendientesPersonFilter);
        }

        $myMentions = $mentionsQuery->orderBy('created_at', 'desc')->get();

        // Contador de pendientes por responder (ignora los filtros anteriores)
        $pendientesCount = ProjectMention::where('mentioned_to', $userId)
            ->where('status', 'pendiente')
            ->count();

        // Proyectos disponibles para el filtro del panel (respeta la visibilidad)
        $myMentionProjects = Project::visibleTo(Auth::user())->orderBy('title')->get(['id', 'title']);

        // Usuarios a mostrar en el filtro "Cualquier persona" (Depende del proyecto seleccionado)
        if ($this->pendientesProjectFilter) {
            // Si hay un proyecto seleccionado, mostrar solo los participantes de ese proyecto
            $participantIds = \App\Models\Tenant\Projects\ProjectParticipant::where('project_id', $this->pendientesProjectFilter)->pluck('user_id')->unique()->toArray();
            $mentioningUsers = User::whereIn('id', $participantIds)->orderBy('name')->get(['id', 'name']);
        } else {
            // Si no hay filtro, mostrar a todos los que son participantes de algún proyecto
            $participantIds = \App\Models\Tenant\Projects\ProjectParticipant::pluck('user_id')->unique()->toArray();
            $mentioningUsers = User::whereIn('id', $participantIds)->orderBy('name')->get(['id', 'name']);
        }

        $myQuestions = ProjectQuestion::where('status', 'pendiente')
            ->whereHas('project', function ($q) use ($userId) {
                $q->where('created_by', $userId);
            })
            ->with(['project', 'asker'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Consulta de Proyectos
        //    Los perfiles sin acceso total solo ven los proyectos donde son
        //    creador, "dirigido a" o participante del chat.
        $query = Project::query()
            ->visibleTo(Auth::user())
            ->with(['customer', 'creator', 'assignedUser', 'latestMessage'])
            ->with(['participants' => fn($q) => $q->where('user_id', $userId)])
            ->withCount(['questions' => function ($q) {
                $q->where('status', 'pendiente');
            }]);

        if ($this->projectTypeFilter) {
            $query->where('type', $this->projectTypeFilter);
        }

        // Aplicar filtros por tipo de pestaña
        if ($this->selectedTab === 'archivados') {
            $query->where('status', 'cerrado_entregado');
        } else {
            $query->where('status', '!=', 'cerrado_entregado');
        }

        if ($this->selectedStatus) {
            $query->where('status', $this->selectedStatus);
        }

        // Filtro rápido de vencimiento (solo proyectos internos activos)
        if ($this->vencimientoFilter === 'vencido') {
            $query->where('type', 'internal')
                ->whereNotIn('status', ['terminado', 'cerrado_entregado'])
                ->whereNotNull('delivery_date')
                ->whereDate('delivery_date', '<', now());
        } elseif ($this->vencimientoFilter === 'proximo') {
            $query->where('type', 'internal')
                ->whereNotIn('status', ['terminado', 'cerrado_entregado'])
                ->whereNotNull('delivery_date')
                ->whereDate('delivery_date', '>=', now())
                ->whereRaw('DATEDIFF(delivery_date, NOW()) / GREATEST(DATEDIFF(delivery_date, COALESCE(phase_started_at, created_at)), 1) <= 0.30');
        }

        // Más filtros: rango de fechas de creación y participante
        if ($this->searchDateFrom) {
            $query->whereDate('created_at', '>=', $this->searchDateFrom);
        }
        if ($this->searchDateTo) {
            $query->whereDate('created_at', '<=', $this->searchDateTo);
        }
        if ($this->searchParticipantId) {
            $query->whereHas('participants', function ($q) {
                $q->where('user_id', $this->searchParticipantId);
            });
        }

        if ($this->searchAssignedToId) {
            $query->where('assigned_to', $this->searchAssignedToId);
        }

        // Buscador flexible (multi-palabra) según el estándar del cotizador
        if ($this->search) {
            $words = array_filter(explode(' ', trim($this->search)));
            foreach ($words as $word) {
                // 'creator' (users) vive en la BD central y 'inv_projects' en la BD
                // del tenant: whereHas() no puede unir ambas conexiones en una sola
                // subconsulta EXISTS, así que resolvemos los IDs de usuario aparte.
                $matchingCreatorIds = User::where('name', 'like', '%' . $word . '%')->pluck('id');

                $query->where(function ($q) use ($word, $matchingCreatorIds) {
                    $q->where('title', 'like', '%' . $word . '%')
                      ->orWhere('description', 'like', '%' . $word . '%')
                      ->orWhere('status', 'like', '%' . $word . '%')
                      ->orWhereHas('customer', function ($qSub) use ($word) {
                          $qSub->where('businessName', 'like', '%' . $word . '%')
                               ->orWhere('identification', 'like', '%' . $word . '%')
                               ->orWhere('firstName', 'like', '%' . $word . '%')
                               ->orWhere('lastName', 'like', '%' . $word . '%');
                      })
                      ->orWhereIn('created_by', $matchingCreatorIds);
                });
            }
        }

        $projects = $query->orderBy('created_at', 'desc')->paginate(12);

        // Bandera "no leído" (estilo WhatsApp): comparar el último mensaje del
        // chat contra la marca personal de lectura del usuario actual en ese
        // proyecto. Solo se cuenta el detalle de mensajes sin leer para los
        // proyectos que sí quedaron marcados como no leídos (no para todos),
        // para no multiplicar consultas en listados largos.
        $projects->getCollection()->transform(function ($project) use ($userId) {
            $participant = $project->participants->first();
            $lastReadAt = $participant?->last_read_at;
            $latest = $project->latestMessage;

            $project->has_unread_chat = false;
            $project->unread_chat_count = 0;

            if ($latest && (int) $latest->user_id !== (int) $userId) {
                if (!$lastReadAt || $latest->created_at->gt($lastReadAt)) {
                    $project->has_unread_chat = true;
                    $project->unread_chat_count = $project->messages()
                        ->where('user_id', '!=', $userId)
                        ->when($lastReadAt, fn($q) => $q->where('created_at', '>', $lastReadAt))
                        ->count();
                }
            }

            return $project;
        });

        // Usuarios del tenant para asignar proyectos internos
        $assignableUsers = User::whereHas('tenants', function ($q) {
                $q->where('tenants.id', session('tenant_id'));
            })
            ->whereNotIn('profile_id', [17, 18]) // Solo personal interno de Fervicom
            ->orderBy('name')
            ->get(['id', 'name']);

        // Usuarios que actualmente tienen proyectos asignados a ellos
        $assignedUserIds = Project::whereNotNull('assigned_to')->pluck('assigned_to')->unique();
        $usersWithAssignedProjects = User::whereIn('id', $assignedUserIds)->orderBy('name')->get(['id', 'name']);

        return view('livewire.tenant.projects.manage-projects', [
            'projects' => $projects,
            'myMentions' => $myMentions,
            'myQuestions' => $myQuestions,
            'assignableUsers' => $assignableUsers,
            'usersWithAssignedProjects' => $usersWithAssignedProjects,
            'pendientesCount' => $pendientesCount,
            'myMentionProjects' => $myMentionProjects,
            'mentioningUsers' => $mentioningUsers,
            'isSalesperson' => (int) Auth::user()?->profile_id === self::SALESPERSON_PROFILE_ID,
        ])->layout('layouts.app', ['header' => 'Gestión de Proyectos']);
    }
}
