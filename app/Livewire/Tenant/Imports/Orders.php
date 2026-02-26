<?php

namespace App\Livewire\Tenant\Imports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Imports\ImpStatus;
use App\Models\Tenant\Imports\ImpImports;
use App\Models\Tenant\Imports\ImpComments;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant\Imports\ImpLabels;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class Orders extends Component
{
    use WithPagination;

    public $filterStatus = '';
    public $search = '';
    public $perPage = 10;
    public $selectedLabelId = null;
    public $selectedLabelName = 'Programming';
    public $allLabels = [];

    protected $listeners = [
        'labelSelected' => 'onLabelSelected',  // Add this line to handle both formats
        'testEvent' => 'testEvent',
    ];

    public function mount()
    {
        $this->ensureTenantConnection();
    }

    #[Computed]
    public function status()
    {
        $this->ensureTenantConnection();
        return DB::connection('tenant')
            ->table('imp_imports as i')
            ->rightJoin('imp_status as s', 'i.status', '=', 's.id')
            ->select('s.name as Nombre del Estado', DB::raw('COUNT(i.id) as cant'), 's.id')
            ->groupBy('s.id', 's.name')
            ->orderBy('s.id')
            ->get();
    }

    public function putFilter($statusId)
    {
        // Si se hace clic en el mismo estado, se limpia el filtro (opcional)
        if ($this->filterStatus == $statusId) {
            $this->filterStatus = '';
        } else {
            $this->filterStatus = $statusId;
        }
        $this->resetPage();
    }

    #[Computed]
    public function orders()
    {
        $this->ensureTenantConnection();
        return DB::connection('tenant')
            ->table('imp_imports as i')
            ->select([
                'i.id',
                DB::raw("CONCAT(iv.internal_code, ' - ', iv.name) AS item"),
                'iis.factory_ref',
                'iis.exw',
                'i.qty_requested',
                'il.name AS label',
                'ist.translated_name',
                'i.qty_shipped'
            ])
            ->join('imp_items_setup as iis', 'i.item_id', '=', 'iis.item_id')
            ->join('inv_items as iv', 'iis.item_id', '=', 'iv.id')
            ->join('imp_labels as il', 'i.label_id', '=', 'il.id')
            ->join('imp_status as ist', 'i.status', '=', 'ist.id')
            ->when($this->filterStatus, function ($query) {
                return $query->where('i.status', $this->filterStatus);
            })
            ->paginate($this->perPage);
    }

    #[Computed]
    public function labels()
    {
        Log::info('=== LABELS COMPUTED PROPERTY CALLED ===');

        try {
            $this->ensureTenantConnection();
            Log::info('Conexión tenant establecida correctamente');

            // Verificar si hay conexión a la base de datos
            Log::info('Intentando obtener labels de ImpLabels con cantidad total');

            $labels = ImpImports::select([
                'imp_imports.label_id',
                'imp_labels.name'
            ])
                ->join('imp_labels', function ($join) {
                    $join->on('imp_imports.label_id', '=', 'imp_labels.id')
                        ->whereNull('imp_imports.deleted_at');
                })
                ->groupBy([
                    'imp_imports.label_id',
                    'imp_labels.name'
                ])
                ->get();

            Log::info('Total de labels encontrados: ' . $labels->count());


            Log::info('=== FIN LABELS COMPUTED PROPERTY ===');

            return $labels;
        } catch (\Exception $e) {
            Log::error('Error al obtener labels: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return collect(); // Retornar colección vacía en caso de error
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function saveComment($idImport, $comment)
    {
        $this->ensureTenantConnection();
        $query = ImpComments::where('import_id', $idImport)->where('initiator', 1)->first();
        $initiatorExists = !is_null($query);
        try {
            if ($initiatorExists) {
                ImpComments::create([
                    'import_id' => $idImport,
                    'comment' => $comment,
                    'user_id' => Auth::id(),
                    'initiator' => 0
                ]);
            } else {
                ImpComments::create([
                    'import_id' => $idImport,
                    'comment' => $comment,
                    'user_id' => Auth::id(),
                    'initiator' => 1
                ]);
            }
            Log::info('Dispatching $refresh after saving comment');
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            Log::error('❌ Error al guardar el comentario: ' . $e->getMessage());
            return;
        }
    }

    public function viewHistoryComment($idImport)
    {
        dd('Comentario de la importación:' . $idImport);
    }

    #[On('labelSelected')]
    public function onLabelSelected($labelId)
    {
        dd('llego');
        $this->ensureTenantConnection();
        Log::info("=== LABEL SELECTED EVENT ===");
        Log::info("Label ID recibido: {$labelId}");
        Log::info("Tipo de dato: " . gettype($labelId));

        // Find the label name from the labels collection
        $labelName = '';
        $labelsCollection = $this->labels;

        if ($labelsCollection && $labelsCollection->count() > 0) {
            $selectedLabel = $labelsCollection->firstWhere('id', $labelId);
            if ($selectedLabel) {
                $labelName = is_array($selectedLabel) ? $selectedLabel['name'] : $selectedLabel->name;
            }
        }

        Log::info("Label encontrado: {$labelName}");

        // Handle different selection options
        if ($labelId == -1) {
            // "Programación" option - show all items without filter
            $this->selectedLabelId = null;
            $this->selectedLabelName = 'Programming';
            Log::info("Mostrando todos los items sin filtro (Programación seleccionado)");
        } elseif ($labelId == 0) {
            // "Con etiqueta" option - show all items with any label
            $this->selectedLabelId = null;
            $this->selectedLabelName = 'Con etiqueta';
            Log::info("Mostrando todos los items (Con etiqueta seleccionado)");
        } else {
            // Specific label selected
            $this->selectedLabelId = $labelId;
            // Remover el contador de items del nombre para mostrarlo limpio
            $cleanName = preg_replace('/\s*\(\d+\s*items?\)$/', '', $labelName);
            $this->selectedLabelName = $cleanName ?: $labelName;
            Log::info("Filtrando por label ID: {$labelId}");
        }

        $this->selectedLabel = [
            'id' => $labelId,
            'name' => $labelName
        ];

        $this->resetPage(); // Reset pagination when filter changes

        // Clear the computed property cache to force re-evaluation
        unset($this->items);

        // Force Livewire to re-render
        $this->dispatch('$refresh');

        Log::info("selectedLabelId final: " . ($this->selectedLabelId ?? 'null'));
        Log::info("selectedLabelName final: " . $this->selectedLabelName);
        Log::info("=== FIN LABEL SELECTED EVENT ===");
    }

    public $selectedLabel = [
        'id' => '',
        'name' => ''
    ];

    public function render()
    {
        $labels = $this->labels;
        return view(
            'livewire.tenant.imports.orders',
            [
                'labels' => $labels,
            ]
        )
            ->layout('layouts.app', ['header' => 'Gestión de Ordenes']);
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return $this->redirectRoute('tenant.select');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            return $this->redirectRoute('tenant.select');
        }

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }
}
