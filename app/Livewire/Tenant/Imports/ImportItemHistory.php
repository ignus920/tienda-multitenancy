<?php

namespace App\Livewire\Tenant\Imports;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Tenant\Imports\ImpImports;
use App\Models\Tenant\Items\Items;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\Log;

class ImportItemHistory extends Component
{
    public $showModal = false;
    public $itemId = null;
    public $labelId = null;
    public $isAllMode = false; // true = Programación (mostrar todos), false = filtrar por label
    public $itemSku = null;
    public $itemName = null;
    public $history;
    public $loading = false;

    public function mount()
    {
        $this->history = collect([]);
    }

    #[On('open-item-history')]
    public function openHistory($itemId, $labelId = null, $isAllMode = false)
    {
        Log::info('=== OPEN ITEM HISTORY ===');
        Log::info("ItemId: {$itemId}, LabelId: " . ($labelId ?? 'null') . ", IsAllMode: " . ($isAllMode ? 'true' : 'false'));

        $this->loading = true;
        $this->itemId = $itemId;
        $this->labelId = $labelId;
        $this->isAllMode = $isAllMode;
        
        $this->showModal = true;
        $this->loadHistory();
        $this->loading = false;

        Log::info('=== FIN OPEN ITEM HISTORY ===');
    }

    public function loadHistory()
    {
        try {
            $this->ensureTenantConnection();

            // Cargar información del item
            $item = Items::find($this->itemId);

            if (!$item) {
                Log::warning("Item no encontrado: {$this->itemId}");
                $this->history = collect([]);
                return;
            }

            // Guardar solo los datos necesarios, no el modelo completo
            $this->itemSku = $item->sku;
            $this->itemName = $item->name;

            // Construir query del historial
            $query = ImpImports::with('label')
                ->where('item_id', $this->itemId)
                ->orderBy('created_at', 'desc');

            // Aplicar filtro condicional
            if (!$this->isAllMode && $this->labelId) {
                // Estado con etiqueta específica: filtrar por label_id
                $query->where('label_id', $this->labelId);
                Log::info("Filtrando historial por label_id: {$this->labelId}");
            } else {
                // Estado Programación: mostrar todos los registros
                Log::info("Mostrando todos los registros del historial");
            }

            // Convertir a array para evitar problemas de hidratación
            $this->history = $query->get()->map(function($record) {
                return [
                    'id' => $record->id,
                    'qty_requested' => $record->qty_requested,
                    'qty_shipped' => $record->qty_shipped,
                    'price' => $record->price,
                    'status' => $record->status,
                    'label_name' => $record->label->name ?? 'Sin etiqueta',
                    'created_at' => $record->created_at->format('d/m/Y H:i'),
                ];
            })->toArray();

            Log::info("Historial cargado: " . count($this->history) . " registros");

        } catch (\Exception $e) {
            Log::error('Error al cargar historial: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            $this->history = [];
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->itemId = null;
        $this->labelId = null;
        $this->itemSku = null;
        $this->itemName = null;
        $this->history = [];
        $this->loading = false;
    }

    public function getStatusLabel($status)
    {
        $statuses = [
            1 => ['label' => 'Pendiente', 'color' => 'yellow'],
            2 => ['label' => 'Confirmado', 'color' => 'blue'],
            3 => ['label' => 'En proceso', 'color' => 'indigo'],
            4 => ['label' => 'Enviado', 'color' => 'purple'],
            5 => ['label' => 'En tránsito', 'color' => 'cyan'],
            6 => ['label' => 'Recibido', 'color' => 'green'],
            7 => ['label' => 'Completado', 'color' => 'emerald'],
            8 => ['label' => 'Cancelado', 'color' => 'red'],
        ];

        return $statuses[$status] ?? ['label' => 'Desconocido', 'color' => 'gray'];
    }

    public function render()
    {
        return view('livewire.tenant.imports.components.import-item-history');
    }

    private function ensureTenantConnection(): void
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            throw new \Exception('No tenant selected');
        }
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            session()->forget('tenant_id');
            throw new \Exception('Invalid tenant');
        }
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);
    }
}
