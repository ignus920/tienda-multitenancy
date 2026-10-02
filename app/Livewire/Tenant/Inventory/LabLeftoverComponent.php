<?php

namespace App\Livewire\Tenant\Inventory;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Inventory\LabLeftover;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use App\Exports\GenericExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LabLeftoverComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $dateFrom;
    public $dateTo;

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        $this->ensureTenantConnection();
        // Por defecto el último mes
        $this->dateFrom = now()->subMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        // Al buscar reseteamos la página pero conservamos las fechas
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->dateFrom = now()->subMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->resetPage();
    }

    private function getFilteredQuery()
    {
        $this->ensureTenantConnection();

        $query = LabLeftover::with(['item.dimensions']);

        // Filtro de búsqueda multi-palabra (algoritmo del cotizador)
        if (!empty(trim($this->search))) {
            $words = array_filter(explode(' ', trim($this->search)));
            $query->whereHas('item', function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where(function ($sub) use ($word) {
                        $sub->where('name', 'like', "%{$word}%")
                            ->orWhere('internal_code', 'like', "%{$word}%")
                            ->orWhere('sku', 'like', "%{$word}%")
                            ->orWhere('description', 'like', "%{$word}%");
                    });
                }
            });
        }

        // Filtro por rango de fechas (sin resetear el buscador)
        if (!empty($this->dateFrom)) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if (!empty($this->dateTo)) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function exportExcel()
    {
        $leftovers = $this->getFilteredQuery()->get();

        $headings = [
            'Código / SKU',
            'Nombre del Insumo',
            'Sobrante Disponible (cm)',
            'Largo Original (cm)',
            'Fecha de Registro',
        ];

        $mapping = function ($row) {
            $sku = $row->item->internal_code ?? $row->item->sku ?? 'N/A';
            $nombre = $row->item->name ?? $row->item->display_name ?? 'Insumo';
            $sobrante = (string) (float) $row->available_cm;
            $largoOrig = ($row->item && $row->item->dimensions && $row->item->dimensions->long > 0)
                ? (string) (float) $row->item->dimensions->long
                : '0';
            $fecha = $row->created_at ? $row->created_at->format('d/m/Y H:i') : '';

            return [
                $sku,
                $nombre,
                $sobrante,
                $largoOrig,
                $fecha,
            ];
        };

        $filename = 'Sobrantes_Laboratorio_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new GenericExport($leftovers, $headings, $mapping), $filename);
    }

    public function exportCsv()
    {
        $leftovers = $this->getFilteredQuery()->get();

        $headings = [
            'Código / SKU',
            'Nombre del Insumo',
            'Sobrante Disponible (cm)',
            'Largo Original (cm)',
            'Fecha de Registro',
        ];

        $mapping = function ($row) {
            return [
                $row->item->internal_code ?? $row->item->sku ?? 'N/A',
                $row->item->name ?? $row->item->display_name ?? 'Insumo',
                (string) (float) $row->available_cm,
                ($row->item && $row->item->dimensions && $row->item->dimensions->long > 0) ? (string)(float)$row->item->dimensions->long : '0',
                $row->created_at ? $row->created_at->format('d/m/Y H:i') : '',
            ];
        };

        $filename = 'Sobrantes_Laboratorio_' . now()->format('Ymd_His') . '.csv';

        return Excel::download(new GenericExport($leftovers, $headings, $mapping), $filename, \Maatwebsite\Excel\Excel::CSV);
    }

    public function exportPdf()
    {
        $leftovers = $this->getFilteredQuery()->get();

        $html = view('exports.lab-leftovers-pdf', [
            'leftovers' => $leftovers,
            'dateFrom'  => $this->dateFrom,
            'dateTo'    => $this->dateTo,
            'search'    => $this->search,
        ])->render();

        $pdf = Pdf::loadHTML($html);
        $filename = 'Sobrantes_Laboratorio_' . now()->format('Ymd_His') . '.pdf';

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename
        );
    }

    private function ensureTenantConnection(): void
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
    }

    public function render()
    {
        $leftovers = $this->getFilteredQuery()->paginate($this->perPage);

        return view('livewire.tenant.inventory.lab-leftover-component', [
            'leftovers' => $leftovers
        ])->layout('layouts.app');
    }
}
