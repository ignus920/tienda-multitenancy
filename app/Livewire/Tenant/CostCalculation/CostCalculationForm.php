<?php

namespace App\Livewire\Tenant\CostCalculation;

use Livewire\Component;
use App\Models\Tenant\CostCalculation\CostCalculation;
use App\Models\Tenant\CostCalculation\CostCalculationItem;
use App\Models\Tenant\Items\Items;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CostCalculationForm extends Component
{
    const FULL_ACCESS_PROFILES = [1, 2];

    public $calculationId;

    // Cabecera
    public $name = '';
    public $priceListLabel = null;
    public $priceListOptions = [];
    public $salePrice = null;
    public $maxDiscountPercent = null;
    public $type = 'project';

    // Tiempos de ensamble (opcionales)
    public $time_1_hours = null;
    public $time_1_minutes = null;
    public $time_3_hours = null;
    public $time_3_minutes = null;
    public $time_5_hours = null;
    public $time_5_minutes = null;

    // Metadatos (solo lectura)
    public $creatorId;
    public $creatorName = '';
    public $updatedByName = '';
    public $updatedAtDisplay = '';
    public $createdAtDisplay = '';

    // Líneas — array simple para poder usar wire:model.live="lines.N.campo"
    public $lines = [];

    // Buscador ERP
    public $search = '';
    public $searchResults = [];

    // Producto externo
    public $showExternalForm = false;
    public $extDescription = '';
    public $extQuantity = 1;
    public $extPrice = null;

    // Eliminar
    public $showDeleteModal = false;
    public $deleteReason = '';

    public function mount($calculationId = null)
    {
        $this->ensureTenantConnection();
        $this->calculationId = $calculationId;

        if ($calculationId) {
            $this->loadCalculation($calculationId);
        } else {
            $this->creatorId = Auth::id();
            $this->creatorName = Auth::user()->name ?? '';
            $this->seedPriceListOptions();
        }
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
     * Toma como referencia cualquier ítem activo para saber qué listas de
     * precio existen hoy (Lista, Crédito, Mínimo, etc.) — son las mismas
     * que ya se ven en la pantalla de Items.
     */
    private function seedPriceListOptions()
    {
        $seed = Items::active()->with(['invValues', 'tax'])->first();
        $this->priceListOptions = $seed ? array_keys($seed->all_prices) : [];
        $this->priceListLabel = $this->priceListOptions[0] ?? null;
    }

    private function isGerencia(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        if (in_array((int) $user->profile_id, self::FULL_ACCESS_PROFILES, true)) return true;
        return str_contains(strtolower($user->profile->name ?? ''), 'gerencia');
    }

    public function canEdit(): bool
    {
        if (!$this->calculationId) return true;
        if ($this->isGerencia()) return true;
        if ($this->type === 'finished_product') return false;
        return (int) $this->creatorId === (int) Auth::id();
    }

    private function checkCanEdit(): bool
    {
        if (!$this->canEdit()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Solo quien creó este cálculo de costos (o Gerencia) puede modificarlo.']);
            return false;
        }
        return true;
    }

    private function loadCalculation($id)
    {
        $calc = CostCalculation::with(['items', 'creator', 'updater'])->findOrFail($id);

        $this->name = $calc->name;
        $this->priceListLabel = $calc->price_list_label;
        $this->salePrice = $calc->sale_price;
        $this->maxDiscountPercent = $calc->max_discount_percent;
        $this->type = $calc->type ?? 'project';

        $this->time_1_hours = $calc->time_1_hours;
        $this->time_1_minutes = $calc->time_1_minutes;
        $this->time_3_hours = $calc->time_3_hours;
        $this->time_3_minutes = $calc->time_3_minutes;
        $this->time_5_hours = $calc->time_5_hours;
        $this->time_5_minutes = $calc->time_5_minutes;

        $this->creatorId = $calc->created_by;
        $this->creatorName = $calc->creator->name ?? 'Usuario';
        $this->updatedByName = $calc->updater->name ?? '';
        $this->updatedAtDisplay = $calc->updated_at?->format('d/m/Y h:i A') ?? '';
        $this->createdAtDisplay = $calc->created_at?->format('d/m/Y h:i A') ?? '';

        $seed = Items::active()->with(['invValues', 'tax'])->first();
        $this->priceListOptions = $seed ? array_keys($seed->all_prices) : [];
        if ($this->priceListLabel && !in_array($this->priceListLabel, $this->priceListOptions, true)) {
            $this->priceListOptions[] = $this->priceListLabel;
        }

        $this->lines = $calc->items->map(function ($item) {
            return [
                'db_id' => $item->id,
                'origin' => $item->origin,
                'item_id' => $item->item_id,
                'description' => $item->description,
                'mode' => $item->mode,
                'quantity' => $item->quantity,
                'cm_quantity' => $item->cm_quantity,
                'ext_unit_value' => $item->origin === 'externo' ? (float) $item->unit_value : null,
            ];
        })->toArray();
    }

    // ---------------- Buscador ERP ----------------

    public function updatedSearch()
    {
        $this->ensureTenantConnection();
        if (strlen($this->search) < 2) {
            $this->searchResults = [];
            return;
        }

        $words = array_filter(explode(' ', trim($this->search)));
        $query = Items::with(['invValues', 'tax', 'dimensions'])->active();
        foreach ($words as $word) {
            $query->where(function ($q) use ($word) {
                $q->where('name', 'like', '%' . $word . '%')
                  ->orWhere('internal_code', 'like', '%' . $word . '%')
                  ->orWhere('description', 'like', '%' . $word . '%');
            });
        }

        $this->searchResults = $query->limit(10)->get()->map(function ($item) {
            $price = ceil($this->resolvePriceForLabel($item, $this->priceListLabel));
            $length = optional($item->dimensions)->long;
            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->internal_code,
                'price' => $price,
                'cuttable' => (bool) $item->is_cuttable,
                'cmPrice' => ($item->is_cuttable && $length > 0) ? ceil($price / $length) : null,
                'hasLength' => $length > 0,
            ];
        })->toArray();
    }

    public function selectErpItem($itemId)
    {
        if (!$this->checkCanEdit()) return;

        $item = Items::with(['invValues', 'tax', 'dimensions'])->find($itemId);
        if (!$item) return;

        $fullDescription = $item->internal_code ? "{$item->internal_code} - {$item->name}" : $item->name;
        $isCuttable = (bool) $item->is_cuttable;
        $hasLength = optional($item->dimensions)->long > 0;

        if ($isCuttable && !$hasLength) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Este producto está marcado como "se vende por cm" pero no tiene longitud registrada en el ERP. Complétala primero en Items → Medidas.']);
            return;
        }

        $this->lines[] = [
            'db_id' => null,
            'origin' => 'erp',
            'item_id' => $item->id,
            'description' => $fullDescription,
            'mode' => $isCuttable ? 'cm' : 'unit',
            'quantity' => $isCuttable ? null : 1,
            'cm_quantity' => $isCuttable ? 100 : null,
            'ext_unit_value' => null,
        ];

        $this->reset('search', 'searchResults');
        $this->dispatch('show-toast', ['type' => 'success', 'message' => $isCuttable ? 'Agregado — cotízalo por centímetro' : 'Producto agregado']);
    }

    // ---------------- Producto externo ----------------

    public function addExternalItem()
    {
        if (!$this->checkCanEdit()) return;

        $this->validate([
            'extDescription' => 'required|string|max:255',
            'extQuantity' => 'required|numeric|min:0.01',
            'extPrice' => 'required|numeric|min:0',
        ], [
            'extDescription.required' => 'La descripción es obligatoria.',
            'extQuantity.required' => 'La cantidad es obligatoria.',
            'extPrice.required' => 'El precio es obligatorio.',
        ]);

        $this->lines[] = [
            'db_id' => null,
            'origin' => 'externo',
            'item_id' => null,
            'description' => $this->extDescription,
            'mode' => 'unit',
            'quantity' => $this->extQuantity,
            'cm_quantity' => null,
            'ext_unit_value' => (float) $this->extPrice,
        ];

        $this->reset(['extDescription', 'extPrice']);
        $this->extQuantity = 1;
        $this->showExternalForm = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Producto externo agregado']);
    }

    public function removeLine($index)
    {
        if (!$this->checkCanEdit()) return;
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    // ---------------- Cálculo de precios en vivo ----------------

    private function resolvePriceForLabel(Items $item, ?string $label): float
    {
        $prices = $item->all_prices;
        if ($label && isset($prices[$label])) {
            return (float) $prices[$label];
        }
        
        if ($label && str_ends_with($label, '%')) {
            $requestedPct = (float) str_replace('%', '', $label);
            
            $bestMatchValue = null;
            $bestMatchPct = -1;
            
            foreach ($prices as $k => $v) {
                if ($k === 'Lista') {
                    if ($bestMatchPct === -1) {
                        $bestMatchValue = $v;
                        $bestMatchPct = 0;
                    }
                    continue;
                }
                if (str_ends_with($k, '%')) {
                    $pct = (float) str_replace('%', '', $k);
                    if ($pct <= $requestedPct && $pct > $bestMatchPct) {
                        $bestMatchPct = $pct;
                        $bestMatchValue = $v;
                    }
                }
            }
            
            if ($bestMatchValue !== null) {
                return (float) $bestMatchValue;
            }
        }

        return $prices ? (float) array_values($prices)[0] : 0.0;
    }

    /**
     * Recalcula TODAS las líneas con el precio ACTUAL del ERP — nunca se
     * confía en lo que quedó guardado la última vez, salvo que el ítem ya
     * no exista (se muestra el último precio guardado como respaldo).
     */
    public function computeAllLines(): array
    {
        $this->ensureTenantConnection();

        $itemIds = collect($this->lines)->where('origin', 'erp')->pluck('item_id')->filter()->unique()->all();
        $items = !empty($itemIds)
            ? Items::with(['invValues', 'tax', 'dimensions'])->whereIn('id', $itemIds)->get()->keyBy('id')
            : collect();

        return array_values(array_map(function ($line) use ($items) {
            if ($line['origin'] === 'externo') {
                $qty = (float) ($line['quantity'] ?? 0);
                $unit = ceil((float) ($line['ext_unit_value'] ?? 0));
                return array_merge($line, [
                    'unit_display' => $unit,
                    'qty_display' => $qty,
                    'subtotal' => ceil($unit * $qty),
                    'missing' => false,
                    'mode' => 'unit',
                ]);
            }

            $item = $items->get($line['item_id']);
            if (!$item) {
                // El ítem fue borrado del ERP desde que se agregó esta línea.
                return array_merge($line, [
                    'unit_display' => 0,
                    'qty_display' => $line['mode'] === 'cm' ? ($line['cm_quantity'] ?? 0) : ($line['quantity'] ?? 0),
                    'subtotal' => 0,
                    'missing' => true,
                ]);
            }

            $unitPrice = ceil($this->resolvePriceForLabel($item, $this->priceListLabel));

            if ($line['mode'] === 'cm') {
                $length = optional($item->dimensions)->long;
                $cmPrice = ($length > 0) ? ceil($unitPrice / $length) : 0;
                $qty = (float) ($line['cm_quantity'] ?? 0);
                return array_merge($line, [
                    'unit_display' => $cmPrice,
                    'qty_display' => $qty,
                    'subtotal' => ceil($cmPrice * $qty),
                    'missing' => false,
                    'no_length' => !($length > 0),
                ]);
            }

            $qty = (float) ($line['quantity'] ?? 0);
            return array_merge($line, [
                'unit_display' => $unitPrice,
                'qty_display' => $qty,
                'subtotal' => ceil($unitPrice * $qty),
                'missing' => false,
            ]);
        }, $this->lines));
    }

    public function computeTotals(array $computedLines): array
    {
        $erp = 0;
        $ext = 0;
        foreach ($computedLines as $l) {
            if ($l['origin'] === 'erp') {
                $erp += $l['subtotal'];
            } else {
                $ext += $l['subtotal'];
            }
        }
        return [
            'erp' => $erp,
            'ext' => $ext,
            'total' => $erp + $ext,
        ];
    }

    // ---------------- Guardar ----------------

    public function save()
    {
        if (!$this->checkCanEdit()) return;

        $this->validate([
            'name' => 'required|string|max:255',
            'priceListLabel' => 'required|string',
        ], [
            'name.required' => 'El nombre del cálculo de costos es obligatorio.',
        ]);

        if (empty($this->lines)) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Agrega al menos un producto antes de guardar.']);
            return;
        }

        $this->ensureTenantConnection();
        $computed = $this->computeAllLines();

        DB::connection('tenant')->beginTransaction();
        try {
            $data = [
                'name' => $this->name,
                'price_list_label' => $this->priceListLabel,
                'sale_price' => $this->salePrice ?: null,
                'max_discount_percent' => $this->maxDiscountPercent ?: null,
                'time_1_hours' => $this->time_1_hours ?: null,
                'time_1_minutes' => $this->time_1_minutes ?: null,
                'time_3_hours' => $this->time_3_hours ?: null,
                'time_3_minutes' => $this->time_3_minutes ?: null,
                'time_5_hours' => $this->time_5_hours ?: null,
                'time_5_minutes' => $this->time_5_minutes ?: null,
            ];

            if ($this->calculationId) {
                $calc = CostCalculation::find($this->calculationId);
                $data['updated_by'] = Auth::id();
                $calc->update($data);
                $calc->items()->delete();
            } else {
                $data['created_by'] = Auth::id();
                $calc = CostCalculation::create($data);
                $this->calculationId = $calc->id;
                $this->creatorId = Auth::id();
                $this->creatorName = Auth::user()->name ?? '';
            }

            foreach ($computed as $line) {
                CostCalculationItem::create([
                    'cost_calculation_id' => $calc->id,
                    'origin' => $line['origin'],
                    'item_id' => $line['item_id'],
                    'description' => $line['description'],
                    'mode' => $line['mode'],
                    'quantity' => $line['mode'] === 'unit' ? ($line['quantity'] ?? $line['qty_display']) : null,
                    'cm_quantity' => $line['mode'] === 'cm' ? ($line['cm_quantity'] ?? $line['qty_display']) : null,
                    'unit_value' => $line['unit_display'],
                    'line_cost' => $line['subtotal'],
                ]);
            }

            DB::connection('tenant')->commit();

            $this->updatedAtDisplay = now()->format('d/m/Y h:i A');
            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Cálculo de costos guardado']);
            $this->dispatch('cost-calculation-saved', id: $calc->id);
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error al guardar: ' . $e->getMessage()]);
        }
    }

    // ---------------- Eliminar ----------------

    public function confirmDelete()
    {
        if (!$this->checkCanEdit()) return;
        $this->deleteReason = '';
        $this->showDeleteModal = true;
    }

    public function deleteCalculation()
    {
        if (!$this->checkCanEdit()) return;

        if (trim($this->deleteReason) === '') {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Debes escribir una justificación para eliminar.']);
            return;
        }

        $this->ensureTenantConnection();
        $calc = CostCalculation::find($this->calculationId);
        if ($calc) {
            $calc->update([
                'deleted_by' => Auth::id(),
                'deletion_reason' => $this->deleteReason,
            ]);
            $calc->delete();
        }

        $this->showDeleteModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Cálculo de costos eliminado']);
        $this->dispatch('cost-calculation-deleted');
    }

    // ---------------- Exportar ----------------

    public function exportExcel()
    {
        if (!$this->calculationId) return;
        $this->ensureTenantConnection();

        $lines = collect($this->computeAllLines())->map(fn ($l) => (object) $l);
        $totals = $this->computeTotals($this->computeAllLines());

        $filename = str($this->name ?: 'calculo_costos')->slug() . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CostCalculationExport($lines, $this->name, $totals['total']),
            $filename
        );
    }

    public function exportPdf()
    {
        if (!$this->calculationId) return;
        $this->ensureTenantConnection();

        $calc = CostCalculation::with('creator')->find($this->calculationId);
        $lines = $this->computeAllLines();
        $totals = $this->computeTotals($lines);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.cost-calculation-pdf', [
            'calc' => $calc,
            'lines' => $lines,
            'totals' => $totals,
        ]);

        $filename = str($this->name ?: 'calculo_costos')->slug() . '.pdf';

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename
        );
    }

    public function render()
    {
        $this->ensureTenantConnection();
        $computedLines = $this->computeAllLines();
        $totals = $this->computeTotals($computedLines);

        return view('livewire.tenant.cost-calculation.cost-calculation-form', [
            'computedLines' => $computedLines,
            'totals' => $totals,
            'canEdit' => $this->canEdit(),
        ])->layout('layouts.app', ['header' => $this->name ?: 'Cálculo de Costos']);
    }
}
