<?php

namespace App\Livewire\Tenant\CostCalculation;

use Livewire\Component;
use App\Models\Tenant\CostCalculation\CostCalculation;
use App\Models\Tenant\CostCalculation\CostCalculationItem;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvStore;
use App\Models\Tenant\Items\InvValues;
use App\Models\Tenant\CnfTaxes;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use App\Services\Tenant\Movements\MovementsService;
use App\Services\Facturacion\DatabaseConfigService;
use App\Services\Facturacion\ApiClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    public $assignedFinishedProductCode = null;

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

    // Cálculo de Fuentes
    public $showPowerSupplyModal = false;
    public $powerSupplyResults = [];

    // Opciones Variables
    public $showOptionSearchModal = false;
    public $targetLineIndex = null;
    public $optionSearch = '';
    public $optionSearchResults = [];

    // Producto Terminado
    public $showFinishedProductModal = false;
    public $fp_code = '';
    public $fp_name = '';
    public $fp_sku = '';
    public $fp_type = 'ENSAMBLADO';
    public $fp_category_id = null;
    public $fp_tax_id = null;
    public $fp_brand_id = null;
    public $fp_house_id = null;
    public $fp_purchasing_unit = null;
    public $fp_consumption_unit = null;
    public $fp_handles_serial = 0;
    public $fp_inventoriable = 1;
    public $fp_supplier_id = null;
    public $fp_temp_values = [];
    public $categoriesList = [];
    public $taxesList = [];
    public $brandsList = [];
    public $housesList = [];
    public $purchasingUnitsList = [];
    public $consumptionUnitsList = [];
    public $suppliersList = [];
    public $fpItemTypes = [
        'ENSAMBLADO'      => 'Ensamblado',
        'IMPORTADO'       => 'Importado',
        'COMPRA NACIONAL' => 'Compra nacional',
        'PRODUCIDO'       => 'Producido',
        'INSUMO'          => 'Insumo',
        'SERVICIO'        => 'Servicio',
    ];

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

        $this->loadSelectOptions();
    }

    private function loadSelectOptions()
    {
        $db = DB::connection('tenant');
        $this->categoriesList       = $db->table('inv_categories')->whereNull('deleted_at')->get(['id', 'name'])->toArray();
        $this->taxesList            = $db->table('cnf_taxes')->where('status', 1)->get(['id', 'name', 'percentage'])->toArray();
        $this->brandsList           = $db->table('inv_item_brand')->where('status', 1)->get(['id', 'name'])->toArray();
        $this->housesList           = $db->table('inv_item_house')->where('status', 1)->get(['id', 'name'])->toArray();
        $this->purchasingUnitsList  = $db->table('inv_unit_measurements')->where('status', 1)->get(['id', 'description as name'])->toArray();
        $this->consumptionUnitsList = $db->table('inv_unit_measurements')->where('status', 1)->get(['id', 'description as name'])->toArray();
        $this->suppliersList        = $db->table('vnt_companies')->where('type', 'PROVEEDOR')->whereNull('deleted_at')->get(['id', 'businessName as name'])->toArray();
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

        if ($this->type === 'finished_product') {
            $fp = Items::where('cost_calculation_id', $this->calculationId)->first();
            if ($fp) {
                $this->assignedFinishedProductCode = $fp->internal_code;
            }
        }

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
                'is_variable' => (bool) $item->is_variable,
                'options' => is_array($item->variable_options) ? $item->variable_options : (json_decode($item->variable_options, true) ?: [])
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

        $minCutLength = optional($item->dimensions)->min_cut_length ? floatval($item->dimensions->min_cut_length) : 1;
        $initialCmQty = $isCuttable ? (ceil(100 / $minCutLength) * $minCutLength) : null;

        $this->lines[] = [
            'db_id' => null,
            'origin' => 'erp',
            'item_id' => $item->id,
            'description' => $fullDescription,
            'mode' => $isCuttable ? 'cm' : 'unit',
            'quantity' => $isCuttable ? null : 1,
            'cm_quantity' => $initialCmQty,
            'ext_unit_value' => null,
            'is_variable' => false,
            'options' => []
        ];

        $this->reset('search', 'searchResults');
        $this->dispatch('show-toast', ['type' => 'success', 'message' => $isCuttable ? 'Agregado — cotízalo por centímetro' : 'Producto agregado']);
    }

    // ---------------- Cálculo de Fuentes ----------------

    public function calculatePowerSupplies()
    {
        $service = new \App\Services\Tenant\CostCalculation\PowerSupplyCalculatorService();
        $response = $service->calculate($this->lines);

        if ($response['status'] === 'error') {
            $this->dispatch('show-toast', [
                'type' => 'error', 
                'message' => $response['message'],
                'time' => 8000
            ]);
            return;
        }

        $this->powerSupplyResults = $response['data'];
        $this->showPowerSupplyModal = true;
    }

    public function addPowerSupplyToLines($itemId, $quantity)
    {
        if (!$this->checkCanEdit()) return;

        $item = Items::with(['invValues', 'tax', 'dimensions'])->find($itemId);
        if (!$item) return;

        $fullDescription = $item->internal_code ? "{$item->internal_code} - {$item->name}" : $item->name;

        $this->lines[] = [
            'db_id' => null,
            'origin' => 'erp',
            'item_id' => $item->id,
            'description' => $fullDescription,
            'mode' => 'unit',
            'quantity' => $quantity,
            'cm_quantity' => null,
            'ext_unit_value' => null,
            'is_variable' => false,
            'options' => []
        ];

        $this->showPowerSupplyModal = false; // Cerramos el modal
        $this->dispatch('show-toast', ['type' => 'success', 'message' => "Se agregaron {$quantity}x {$fullDescription} a la lista"]);
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
            'is_variable' => false,
            'options' => []
        ];

        $this->reset(['extDescription', 'extPrice']);
        $this->extQuantity = 1;
        $this->showExternalForm = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Producto externo agregado']);
    }

    public function toggleVariable($index)
    {
        if (!$this->checkCanEdit()) return;
        if (isset($this->lines[$index])) {
            $this->lines[$index]['is_variable'] = !($this->lines[$index]['is_variable'] ?? false);
            if (!isset($this->lines[$index]['options'])) {
                $this->lines[$index]['options'] = [];
            }
        }
    }

    public function openOptionSearch($index)
    {
        $this->targetLineIndex = $index;
        $this->showOptionSearchModal = true;
        $this->optionSearch = '';
        $this->optionSearchResults = [];
    }

    public function updatedOptionSearch()
    {
        $this->ensureTenantConnection();
        if (strlen($this->optionSearch) < 2) {
            $this->optionSearchResults = [];
            return;
        }

        $words = array_filter(explode(' ', trim($this->optionSearch)));
        $query = Items::with(['invValues', 'tax', 'dimensions'])->active();
        foreach ($words as $word) {
            $query->where(function ($q) use ($word) {
                $q->where('name', 'like', '%' . $word . '%')
                  ->orWhere('internal_code', 'like', '%' . $word . '%');
            });
        }

        $this->optionSearchResults = $query->limit(5)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->internal_code,
            ];
        })->toArray();
    }

    public function selectOptionItem($itemId)
    {
        $item = Items::find($itemId);
        if (!$item) return;

        $this->lines[$this->targetLineIndex]['options'][] = [
            'item_id' => $item->id,
            'code' => $item->internal_code,
            'name' => $item->name
        ];

        $this->showOptionSearchModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Opción agregada']);
    }
    
    public function removeOption($lineIndex, $optionIndex)
    {
        unset($this->lines[$lineIndex]['options'][$optionIndex]);
        $this->lines[$lineIndex]['options'] = array_values($this->lines[$lineIndex]['options']);
    }

    public function removeLine($index)
    {
        if (!$this->checkCanEdit()) return;
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    // ---------------- Cálculo de precios en vivo ----------------

    public function updated($propertyName, $value)
    {
        if (preg_match('/^lines\.(\d+)\.cm_quantity$/', $propertyName, $matches)) {
            $index = $matches[1];
            if (isset($this->lines[$index]) && $this->lines[$index]['origin'] === 'erp') {
                $item = Items::with('dimensions')->find($this->lines[$index]['item_id']);
                if ($item && $item->is_cuttable) {
                    $minCut = optional($item->dimensions)->min_cut_length ? floatval($item->dimensions->min_cut_length) : 1;
                    if ($minCut > 0) {
                        $floatVal = floatval($value);
                        // Usar fmod para decimales, compara la diferencia con una pequeña tolerancia
                        if (abs(fmod($floatVal, $minCut)) > 0.01 && abs(fmod($floatVal, $minCut) - $minCut) > 0.01) {
                            $rounded = round($floatVal / $minCut) * $minCut;
                            if ($rounded <= 0) $rounded = $minCut;
                            
                            $this->lines[$index]['cm_quantity'] = $rounded;
                            
                            $this->dispatch('show-toast', [
                                'type' => 'warning', 
                                'message' => "El corte debe ser múltiplo de {$minCut}. Se ajustó a {$rounded} cm."
                            ]);
                        }
                    }
                }
            }
        }
    }

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
                $minCutLength = optional($item->dimensions)->min_cut_length;
                $cmPrice = ($length > 0) ? ceil($unitPrice / $length) : 0;
                $qty = (float) ($line['cm_quantity'] ?? 0);
                return array_merge($line, [
                    'unit_display' => $cmPrice,
                    'qty_display' => $qty,
                    'subtotal' => ceil($cmPrice * $qty),
                    'missing' => false,
                    'no_length' => !($length > 0),
                    'min_cut_length' => $minCutLength != null ? floatval($minCutLength) : null,
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
                    'is_variable' => $line['is_variable'] ?? false,
                    'variable_options' => ($line['is_variable'] ?? false) ? ($line['options'] ?? []) : null,
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

    public function openFinishedProductModal()
    {
        if (!$this->checkCanEdit()) return;
        if (empty($this->lines)) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Agrega al menos un producto a la lista.']);
            return;
        }
        $this->showFinishedProductModal = true;
    }

    public function saveAsFinishedProduct()
    {
        if (!$this->checkCanEdit()) return;

        $this->validate([
            'fp_code'             => 'required|string|max:50',
            'fp_name'             => 'required|string|min:3|max:255',
            'fp_category_id'      => 'required|integer',
            'fp_type'             => 'required|string',
            'fp_tax_id'           => 'required|integer',
            'fp_supplier_id'      => 'required|integer',
        ], [
            'fp_code.required'        => 'El código interno es obligatorio.',
            'fp_name.required'        => 'El nombre del producto es obligatorio.',
            'fp_name.min'             => 'El nombre debe tener al menos 3 caracteres.',
            'fp_category_id.required' => 'La categoría es obligatoria.',
            'fp_type.required'        => 'El tipo de producto es obligatorio.',
            'fp_tax_id.required'      => 'El impuesto es obligatorio.',
            'fp_supplier_id.required' => 'El proveedor es obligatorio.',
        ]);

        $this->ensureTenantConnection();

        $exists = Items::where('internal_code', $this->fp_code)->exists();
        if ($exists) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Ese código ya existe en el inventario.']);
            return;
        }

        // 1. Guardamos el cálculo de costos normal
        $this->save();

        if (!$this->calculationId) {
            return; // Algo falló al guardar
        }

        // Validar configuración de facturación de Alegra antes de proceder
        $user = Auth::user();
        $optimizedConfig = $user ? DatabaseConfigService::getFacturacionConfigByUser($user->id) : null;
        if (!$optimizedConfig) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se encontró configuración de facturación con Alegra para su usuario. El producto no puede crearse.'
            ]);
            return;
        }

        $apiClient = ApiClient::forConfig($optimizedConfig);
        $principalStore = InvStore::where('status', 1)->orderBy('id', 'asc')->first();
        $warehouseApiId = $principalStore?->api_data_id ? (string) $principalStore->api_data_id : '1';

        // Precios y Costos
        $costoInicial  = (float) ($this->fp_temp_values['Costo Inicial']  ?? 0);
        $precioBase    = (float) ($this->fp_temp_values['Precio Base']    ?? 0);
        $precioRegular = (float) ($this->fp_temp_values['Precio Regular'] ?? 0);
        $precioCredito = (float) ($this->fp_temp_values['Precio Crédito'] ?? 0);

        // Resolver listas de precios compatibles con la cuenta de Alegra (Producción vs Sandbox)
        $pricePayload = [];
        try {
            $plResult = $apiClient->get('price-lists');
            $remotePriceLists = ($plResult['success'] ?? false) && is_array($plResult['data'] ?? null)
                ? $plResult['data']
                : [];

            $availableIds = array_map(fn($pl) => (string)($pl['id'] ?? ''), $remotePriceLists);

            $productionPriceMap = [
                '019ac5f3-5f72-7440-874c-6e53c92fbfde' => $precioBase,
                '019b8e1a-f3fa-73b3-91d7-03f867191b3c' => $precioRegular,
                '019b8e1b-ab7b-71da-8c15-cf1e136e06c3' => $precioCredito,
            ];

            $matchedAny = false;
            foreach ($productionPriceMap as $uuid => $prc) {
                if (in_array((string)$uuid, $availableIds, true)) {
                    $pricePayload[] = ['idPriceList' => $uuid, 'price' => $prc];
                    $matchedAny = true;
                }
            }

            if (!$matchedAny && !empty($remotePriceLists)) {
                $firstListId = $remotePriceLists[0]['id'] ?? 1;
                $pricePayload[] = ['idPriceList' => $firstListId, 'price' => ($precioBase ?: $precioRegular)];
            }
        } catch (\Exception $ePl) {
            Log::warning('⚠️ [CostCalc] No se pudieron consultar listas de precios de Alegra: ' . $ePl->getMessage());
        }

        if (empty($pricePayload)) {
            $pricePayload = [
                ['idPriceList' => 1, 'price' => ($precioBase ?: $precioRegular)]
            ];
        }

        $createdAlegraItemId = null;
        $createdExitAdjustmentId = null;

        DB::connection('tenant')->beginTransaction();

        try {
            // 2. Creamos el producto en la BD local dentro de la transacción
            $item = Items::create([
                'categoryId'          => $this->fp_category_id,
                'name'                => $this->fp_name,
                'internal_code'       => $this->fp_code,
                'sku'                 => $this->fp_sku ?: $this->fp_code,
                'description'         => $this->fp_name,
                'type'                => $this->fp_type,
                'taxId'               => $this->fp_tax_id,
                'brandId'             => $this->fp_brand_id ?: null,
                'houseId'             => $this->fp_house_id ?: null,
                'purchasing_unit'     => $this->fp_purchasing_unit ?: null,
                'consumption_unit'    => $this->fp_consumption_unit ?: null,
                'handles_serial'      => $this->fp_handles_serial ? 1 : 0,
                'inventoriable'       => $this->fp_inventoriable ? 1 : 0,
                'status'              => 1,
                'cost_calculation_id' => $this->calculationId,
            ]);

            // Registro de bodega (storeId = 2 = bodega principal)
            DB::connection('tenant')->table('inv_items_store')->insert([
                'itemId'              => $item->id,
                'storeId'             => 2,
                'initial_stock'       => 0,
                'stock_items_store'   => 0,
                'stock_min'           => 0,
                'stock_max'           => 0,
                'wp_stock_percentage' => 0,
                'wp_min_stock'        => 0,
            ]);

            // Guardar proveedor en imp_items_setup
            if ($this->fp_supplier_id) {
                DB::connection('tenant')->table('imp_items_setup')->insert([
                    'item_id'     => $item->id,
                    'supplier_id' => $this->fp_supplier_id,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            // Guardar tabla de valores (precios y costos)
            $valueTypeMap = [
                'Costo Inicial'          => 'costo',
                'Costo'                  => 'costo',
                'Precio Base'            => 'precio',
                'Precio Regular'         => 'precio',
                'Precio Crédito'         => 'precio',
                'Precio unitario x caja' => 'precio',
            ];

            foreach ($valueTypeMap as $label => $type) {
                $val = $this->fp_temp_values[$label] ?? null;
                if ($val !== null && $val !== '') {
                    DB::connection('tenant')->table('inv_values')->insert([
                        'itemId'      => $item->id,
                        'label'       => $label,
                        'type'        => $type,
                        'values'      => (string)(float)$val,
                        'date'        => now()->toDateString(),
                        'warehouseId' => 0,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }

            // 3. Crear el producto en Alegra
            $tax = CnfTaxes::find($this->fp_tax_id);
            $taxData = [
                'inventoryAccount'            => $tax?->inventoryAccount,
                'inventariablePurchaseAccount' => $tax?->inventariablePurchaseAccount,
            ];

            $apiData = [
                'name'        => $item->name,
                'reference'   => $item->sku ?? $item->internal_code,
                'description' => $item->description ?? '',
                'type'        => $item->inventoriable == 1 ? 'product' : 'service',
                'tax'         => $item->taxId ? (string) $item->taxId : '0',
                'inventory'   => [
                    'unit'         => 'unit',
                    'unitCost'     => $costoInicial,
                    'negativeSale' => false,
                    'warehouses'   => [
                        ['id' => $warehouseApiId, 'initialQuantity' => 0, 'minQuantity' => 0, 'maxQuantity' => 0]
                    ],
                ],
                'price'       => $pricePayload,
            ];

            if ($item->inventoriable != 1) {
                unset($apiData['inventory']);
            }

            Log::info('🚀 [CostCalc] Creando producto en Alegra', [
                'item_id' => $item->id,
                'name'    => $item->name,
                'payload' => $apiData,
            ]);

            $apiResult = $apiClient->createItem($apiData);

            if (!($apiResult['success'] ?? false) || empty($apiResult['data']['id'])) {
                Log::error('❌ [CostCalc] Error detallado de Alegra al crear item', [
                    'api_result' => $apiResult,
                ]);

                // Extraer el mensaje más descriptivo posible de la respuesta de Alegra
                $detailMsg = '';
                if (!empty($apiResult['data']['error'])) {
                    $detailMsg = is_array($apiResult['data']['error']) ? json_encode($apiResult['data']['error'], JSON_UNESCAPED_UNICODE) : (string)$apiResult['data']['error'];
                } elseif (!empty($apiResult['data']['message'])) {
                    $detailMsg = (string)$apiResult['data']['message'];
                } elseif (!empty($apiResult['validation_errors'])) {
                    $detailMsg = json_encode($apiResult['validation_errors'], JSON_UNESCAPED_UNICODE);
                } elseif (!empty($apiResult['error_details']['response_body'])) {
                    $detailMsg = json_encode($apiResult['error_details']['response_body'], JSON_UNESCAPED_UNICODE);
                } else {
                    $detailMsg = $apiResult['message'] ?? 'Error desconocido';
                }

                throw new \Exception('Alegra rechazó la creación del producto: ' . $detailMsg);
            }

            $createdAlegraItemId = (int) $apiResult['data']['id'];
            $item->update(['api_data_id' => $createdAlegraItemId]);
            Log::info('✅ [CostCalc] Producto creado en Alegra exitosamente', ['item_id' => $item->id, 'api_data_id' => $createdAlegraItemId]);

            // 4. Salida de materiales (ajuste tipo 'out') en Alegra
            $itemsSalidaAlegra = [];
            foreach ($this->lines as $line) {
                if (($line['origin'] ?? '') !== 'erp') continue;
                if (empty($line['item_id'])) continue;

                $erpItem = Items::find($line['item_id']);
                if (!$erpItem || !$erpItem->api_data_id) continue;

                $qty = (float) ($line['cm_quantity'] ?? $line['quantity'] ?? 0);
                if ($qty <= 0) continue;

                $unitCost = (float) ($line['unit_cost'] ?? 0);
                if ($unitCost <= 0) {
                    $valCosto = DB::connection('tenant')->table('inv_values')
                        ->where('itemId', $erpItem->id)
                        ->whereIn('label', ['Costo', 'Costo Inicial'])
                        ->value('values');
                    $unitCost = $valCosto ? (float) $valCosto : 0.0;
                }

                $itemsSalidaAlegra[] = [
                    'type'     => 'out',
                    'id'       => (string) $erpItem->api_data_id,
                    'unitCost' => $unitCost,
                    'quantity' => abs($qty),
                ];
            }

            $movementsService = new MovementsService();

            if (!empty($itemsSalidaAlegra)) {
                $exitPayload = [
                    'date'         => now()->format('Y-m-d'),
                    'warehouse'    => ['id' => $warehouseApiId],
                    'observations' => 'Salida de materiales - Prod. Terminado: ' . $item->internal_code . ' (Cálculo #' . $this->calculationId . ')',
                    'items'        => $itemsSalidaAlegra,
                ];

                Log::info('📦 [CostCalc] Enviando salida de materiales a Alegra', [
                    'calc_id'     => $this->calculationId,
                    'items_count' => count($itemsSalidaAlegra),
                ]);

                $exitResult = $movementsService->syncAdjustmentToApi($exitPayload);
                if (!($exitResult['success'] ?? false)) {
                    $exitError = $exitResult['message'] ?? 'Error desconocido en salida de materiales';
                    throw new \Exception('Falló la salida de materiales en Alegra: ' . $exitError);
                }

                $createdExitAdjustmentId = $exitResult['api_data_id'] ?? null;
                Log::info('✅ [CostCalc] Salida de materiales registrada en Alegra exitosamente', ['adjustment_id' => $createdExitAdjustmentId]);
            }

            // 5. Entrada del producto terminado (ajuste tipo 'in') en Alegra
            $entryPayload = [
                'date'         => now()->format('Y-m-d'),
                'warehouse'    => ['id' => $warehouseApiId],
                'observations' => 'Entrada prod. terminado: ' . $item->internal_code . ' - Cálculo #' . $this->calculationId,
                'items'        => [
                    [
                        'type'     => 'in',
                        'id'       => (string) $createdAlegraItemId,
                        'unitCost' => $costoInicial,
                        'quantity' => 1,
                    ],
                ],
            ];

            Log::info('📦 [CostCalc] Enviando entrada de producto terminado a Alegra', [
                'item_id'     => $item->id,
                'api_data_id' => $createdAlegraItemId,
            ]);

            $entryResult = $movementsService->syncAdjustmentToApi($entryPayload);
            if (!($entryResult['success'] ?? false)) {
                $entryError = $entryResult['message'] ?? 'Error desconocido en entrada de producto terminado';
                throw new \Exception('Falló la entrada del producto terminado en Alegra: ' . $entryError);
            }

            Log::info('✅ [CostCalc] Entrada de producto terminado registrada en Alegra exitosamente');

            // 6. Todo en Alegra y en BD fue exitoso -> Marcar el cálculo de costos y confirmar transacción
            CostCalculation::where('id', $this->calculationId)->update(['type' => 'finished_product']);

            DB::connection('tenant')->commit();

            $this->showFinishedProductModal = false;
            $this->type = 'finished_product';
            $this->assignedFinishedProductCode = $item->internal_code;
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => '¡Producto Terminado creado con éxito! Se sincronizó con Alegra, se realizó la salida de insumos y la entrada del producto terminado.'
            ]);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();

            Log::error('❌ [CostCalc] Falló el proceso completo. Se cancela creación en ERP: ' . $e->getMessage());

            // Si se llegó a crear el ítem o el ajuste de salida en Alegra antes de que fallara otro paso, revertir en Alegra
            if ($createdExitAdjustmentId) {
                try {
                    $movementsService->deleteAdjustmentFromApi((string) $createdExitAdjustmentId);
                    Log::info('🧹 [CostCalc] Ajuste de salida revertido en Alegra tras fallo');
                } catch (\Exception $exRollback) {
                    Log::error('⚠️ [CostCalc] No se pudo revertir ajuste de salida en Alegra: ' . $exRollback->getMessage());
                }
            }

            if ($createdAlegraItemId) {
                try {
                    $apiClient->deleteItem($createdAlegraItemId);
                    Log::info('🧹 [CostCalc] Producto revertido/eliminado en Alegra tras fallo');
                } catch (\Exception $exRollback) {
                    Log::error('⚠️ [CostCalc] No se pudo eliminar producto en Alegra: ' . $exRollback->getMessage());
                }
            }

            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se creó el producto en el ERP: ' . $e->getMessage()
            ]);
        }
    }

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
