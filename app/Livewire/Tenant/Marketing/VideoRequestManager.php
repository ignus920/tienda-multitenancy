<?php

namespace App\Livewire\Tenant\Marketing;

use App\Helpers\PermissionHelper;
use App\Models\Auth\Tenant;
use App\Models\Auth\User;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Marketing\VideoRequest;
use App\Services\Tenant\Marketing\VideoRequestService;
use App\Services\Tenant\TenantManager;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class VideoRequestManager extends Component
{
    use WithPagination;

    private const PERMISSION = 'Gestión de Videos';

    /* ── Listado / filtros ───────────────────────────── */
    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'estado', history: true)]
    public string $statusFilter = '';

    #[Url(as: 'canal', history: true)]
    public string $channelFilter = '';

    #[Url(as: 'vista', history: true)]
    public string $viewMode = 'matriz'; // matriz | lista

    public int $perPage = 15;
    public string $sortField = 'smart';
    public string $sortDirection = 'asc';

    /* ── Modal: crear solicitud ──────────────────────── */
    public bool $showCreateModal = false;
    public string $productSearch = '';
    public ?int $selectedItemId = null;
    public ?string $selectedItemLabel = null;
    public string $newInstructions = '';
    public ?int $newGestorId = null;

    /* ── Modal: detalle / lista de chequeo ───────────── */
    public bool $showDetailModal = false;
    public ?int $currentRequestId = null;
    public ?int $detailGestorId = null;
    /** @var array<string,array{status:string,link:?string}> */
    public array $taskInput = [];

    public function mount()
    {
        $this->ensureTenantConnection();

        abort_unless(PermissionHelper::userCan(self::PERMISSION, 'show'), 403);
    }

    protected function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return redirect()->route('tenant.select');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            return redirect()->route('tenant.select');
        }

        app(TenantManager::class)->setConnection($tenant);
        tenancy()->initialize($tenant);
    }

    /* ── Permisos ────────────────────────────────────── */
    public function getCanCreateProperty(): bool
    {
        return PermissionHelper::userCan(self::PERMISSION, 'create');
    }

    public function getCanEditProperty(): bool
    {
        return PermissionHelper::userCan(self::PERMISSION, 'edit');
    }

    /* ── Filtros ─────────────────────────────────────── */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingChannelFilter(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function setView(string $mode): void
    {
        $this->viewMode = in_array($mode, ['matriz', 'lista'], true) ? $mode : 'matriz';
    }

    /* ── Crear solicitud ─────────────────────────────── */
    public function openCreateModal(): void
    {
        $this->reset(['productSearch', 'selectedItemId', 'selectedItemLabel', 'newInstructions', 'newGestorId']);
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function selectProduct(int $itemId): void
    {
        $this->ensureTenantConnection();
        $item = Items::on('tenant')->find($itemId);

        if (!$item) {
            return;
        }

        $this->selectedItemId = $item->id;
        $this->selectedItemLabel = trim(($item->internal_code ?: $item->sku) . ' — ' . $item->name);
        $this->productSearch = '';
    }

    public function clearProduct(): void
    {
        $this->selectedItemId = null;
        $this->selectedItemLabel = null;
    }

    public function generateRequest(VideoRequestService $service)
    {
        $this->ensureTenantConnection();

        abort_unless($this->canCreate, 403);

        $this->validate([
            'selectedItemId'  => ['required', 'integer'],
            'newInstructions' => ['nullable', 'string', 'max:5000'],
            'newGestorId'     => ['nullable', 'integer'],
        ], [
            'selectedItemId.required' => 'Debe seleccionar un producto del inventario.',
        ]);

        $request = $service->create(
            (int) $this->selectedItemId,
            $this->newInstructions !== '' ? $this->newInstructions : null,
            $this->newGestorId ?: null,
        );

        $this->closeCreateModal();
        $this->resetPage();
        $this->dispatch('show-toast', ['type' => 'success', 'message' => "Solicitud {$request->request_number} generada correctamente."]);
        $this->openDetail($request->id);
    }

    /* ── Detalle / lista de chequeo ──────────────────── */
    public function openDetail(int $id): void
    {
        $this->ensureTenantConnection();

        $request = VideoRequest::on('tenant')->with('tasks')->find($id);

        if (!$request) {
            return;
        }

        $this->currentRequestId = $request->id;
        $this->detailGestorId = $request->gestor_id;
        $this->taskInput = [];

        foreach (VideoRequest::CHANNELS as $channel => $cfg) {
            $task = $request->tasks->firstWhere('channel', $channel);
            $this->taskInput[$channel] = [
                'status' => $task->status ?? 'pendiente',
                'link'   => $task->link ?? null,
            ];
        }

        $this->resetValidation();
        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->currentRequestId = null;
        $this->taskInput = [];
    }

    public function saveDetail(VideoRequestService $service)
    {
        $this->ensureTenantConnection();

        abort_unless($this->canEdit, 403);

        $request = VideoRequest::on('tenant')->with('tasks')->findOrFail($this->currentRequestId);

        $rules = ['detailGestorId' => ['nullable', 'integer']];
        foreach (VideoRequest::CHANNELS as $channel => $cfg) {
            if ($cfg['requires_link']) {
                $rules["taskInput.$channel.link"] = ['nullable', 'url', 'max:500'];
            }
        }
        $this->validate($rules, [], $this->linkAttributeNames());

        // Validación de dominio por canal
        foreach (VideoRequest::CHANNELS as $channel => $cfg) {
            if (!$cfg['requires_link']) {
                continue;
            }
            $link = trim((string) ($this->taskInput[$channel]['link'] ?? ''));
            if ($link !== '' && !VideoRequest::linkMatchesChannel($channel, $link)) {
                $this->addError("taskInput.$channel.link", "El enlace no corresponde a {$cfg['label']}.");
                return;
            }
        }

        $service->saveTasks($request, $this->taskInput, $this->detailGestorId ?: null);

        $this->openDetail($request->id);
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Cambios guardados.']);
    }

    private function linkAttributeNames(): array
    {
        $names = [];
        foreach (VideoRequest::CHANNELS as $channel => $cfg) {
            if ($cfg['requires_link']) {
                $names["taskInput.$channel.link"] = 'enlace de ' . $cfg['label'];
            }
        }
        return $names;
    }

    /* ── Render ──────────────────────────────────────── */
    public function render()
    {
        $this->ensureTenantConnection();

        $tenantId = session('tenant_id');

        $query = VideoRequest::on('tenant')
            ->leftJoin('inv_items', 'inv_items.id', '=', 'mkt_video_requests.item_id')
            ->select('mkt_video_requests.*')
            ->with(['tasks', 'item'])
            ->when($this->search !== '', function ($q) {
                $s = trim($this->search);
                $q->where(function ($qq) use ($s) {
                    $qq->where('mkt_video_requests.request_number', 'like', "%{$s}%")
                        ->orWhere('inv_items.internal_code', 'like', "%{$s}%")
                        ->orWhere('inv_items.sku', 'like', "%{$s}%")
                        ->orWhere('inv_items.name', 'like', "%{$s}%")
                        ->orWhere('inv_items.description', 'like', "%{$s}%")
                        ->orWhere('mkt_video_requests.product_code', 'like', "%{$s}%")
                        ->orWhere('mkt_video_requests.product_name', 'like', "%{$s}%");
                });
            })
            ->when($this->statusFilter !== '', fn ($q) => $q->where('mkt_video_requests.status', $this->statusFilter))
            ->when($this->channelFilter !== '', function ($q) {
                $channel = str_replace('sin_', '', $this->channelFilter);
                $q->whereHas('tasks', fn ($t) => $t->where('channel', $channel)->where('status', '!=', 'listo'));
            });

        $sortMap = [
            'request_number' => 'mkt_video_requests.request_number',
            'created_at'     => 'mkt_video_requests.created_at',
            'status'         => 'mkt_video_requests.status',
            'progress'       => 'mkt_video_requests.progress_percent',
            'product_code'   => 'inv_items.internal_code',
            'product_name'   => 'inv_items.name',
            'updated_at'     => 'mkt_video_requests.updated_at',
        ];

        if ($this->sortField === 'smart' || !isset($sortMap[$this->sortField])) {
            $query->orderByRaw("FIELD(mkt_video_requests.status, 'pendiente', 'en_proceso', 'terminado')")
                ->orderBy('mkt_video_requests.created_at', 'asc');
        } else {
            $query->orderBy($sortMap[$this->sortField], $this->sortDirection);
        }

        $requests = $query->paginate($this->perPage);

        /* Nombres de usuarios (central) */
        $userIds = collect($requests->items())
            ->flatMap(fn ($r) => [$r->requested_by, $r->gestor_id])
            ->filter()
            ->unique()
            ->values();

        $userNames = $userIds->isNotEmpty()
            ? User::on('central')->whereIn('id', $userIds)->pluck('name', 'id')
            : collect();

        /* Gestores asignables (usuarios del tenant) */
        $gestores = User::on('central')
            ->when($tenantId, fn ($q) => $q->whereHas('tenants', fn ($t) => $t->where('tenants.id', $tenantId)))
            ->orderBy('name')
            ->get(['id', 'name']);

        /* Detalle activo */
        $detail = null;
        $detailHistory = collect();
        $detailUserNames = collect();

        if ($this->showDetailModal && $this->currentRequestId) {
            $detail = VideoRequest::on('tenant')
                ->with(['tasks', 'logs', 'item'])
                ->find($this->currentRequestId);

            if ($detail) {
                $detailHistory = VideoRequest::on('tenant')
                    ->where('item_id', $detail->item_id)
                    ->where('id', '!=', $detail->id)
                    ->orderBy('created_at')
                    ->get(['id', 'request_number', 'status', 'progress_done', 'progress_total', 'progress_percent', 'created_at']);

                $ids = $detail->logs->pluck('user_id')
                    ->merge([$detail->requested_by, $detail->gestor_id, $detail->created_by, $detail->updated_by])
                    ->filter()->unique();

                $detailUserNames = $ids->isNotEmpty()
                    ? User::on('central')->whereIn('id', $ids)->pluck('name', 'id')
                    : collect();
            }
        }

        return view('livewire.tenant.marketing.video-request-manager', [
            'requests'        => $requests,
            'userNames'       => $userNames,
            'gestores'        => $gestores,
            'channels'        => VideoRequest::CHANNELS,
            'detail'          => $detail,
            'detailHistory'   => $detailHistory,
            'detailUserNames' => $detailUserNames,
            'productResults'  => $this->productResults(),
        ])->layout('layouts.app', ['header' => 'Gestión de Videos']);
    }

    private function productResults()
    {
        if (!$this->showCreateModal || mb_strlen(trim($this->productSearch)) < 2) {
            return collect();
        }

        $words = array_filter(explode(' ', trim($this->productSearch)));

        return Items::on('tenant')
            ->where('status', 1)
            ->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where(function ($qq) use ($word) {
                        $qq->where('name', 'like', "%{$word}%")
                            ->orWhere('internal_code', 'like', "%{$word}%")
                            ->orWhere('sku', 'like', "%{$word}%")
                            ->orWhere('description', 'like', "%{$word}%");
                    });
                }
            })
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'internal_code', 'sku', 'description']);
    }
}
