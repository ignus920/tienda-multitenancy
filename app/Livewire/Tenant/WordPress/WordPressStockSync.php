<?php

namespace App\Livewire\Tenant\WordPress;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tenant\Items\Items;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use App\Services\Tenant\WordPress\WordPressService;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
class WordPressStockSync extends Component
{
    public $items        = [];
    public $selected     = [];
    public $results      = [];
    public $syncing      = false;
    public $search       = '';
    public $statusFilter = 'all'; // all | pending | synced | error

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    public function mount()
    {
        $this->ensureTenantConnection();
        $this->loadItems();
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) return;
        $tenant = Tenant::find($tenantId);
        if (!$tenant) return;
        app(TenantManager::class)->setConnection($tenant);
        if (!tenancy()->initialized) tenancy()->initialize($tenant);
    }

    public function loadItems()
    {
        $this->items = Items::where('status', 1)
            ->whereNotNull('sku')
            ->where('sku', '!=', '')
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%')
                  ->orWhere('internal_code', 'like', '%' . $this->search . '%');
            }))
            ->with(['tax', 'invItemsStore'])
            ->limit(20)
            ->get()
            ->map(function ($item) {
                $store      = $item->invItemsStore->firstWhere('storeId', 2);
                $stockBruto = (float) ($store?->stock_items_store ?? 0);
                $stockMin   = (float) ($store?->wp_min_stock ?? 0);
                $porcentaje = (float) ($store?->wp_stock_percentage ?? 100);

                // Reservas activas (remisiones REGISTRADO)
                $reservas = \App\Models\Tenant\Remissions\InvDetailRemissions::whereHas(
                    'remission', fn($q) => $q->where('status', 'REGISTRADO')
                )->where('itemId', $item->id)->sum('quantity');

                $stockNeto = max(0, $stockBruto - (float) $reservas);
                $stockWP   = ($stockNeto >= $stockMin)
                    ? (int) round($stockNeto * ($porcentaje / 100))
                    : 0;

                return [
                    'id'            => $item->id,
                    'name'          => $item->name,
                    'sku'           => $item->sku,
                    'internal_code' => $item->internal_code,
                    'stock'         => $stockBruto,
                    'reservas'      => (float) $reservas,
                    'stock_neto'    => $stockNeto,
                    'stock_min'     => $stockMin,
                    'porcentaje'    => $porcentaje,
                    'stock_wp'      => $stockWP,
                ];
            })
            ->toArray();
    }

    public function updatedSearch()
    {
        $this->loadItems();
    }

    public function toggleSelect($itemId)
    {
        if (in_array($itemId, $this->selected)) {
            $this->selected = array_values(array_filter($this->selected, fn($id) => $id !== $itemId));
        } else {
            $this->selected[] = $itemId;
        }
    }

    public function selectAll()
    {
        $this->selected = array_column($this->items, 'id');
    }

    public function clearSelection()
    {
        $this->selected = [];
    }

    public function syncSelected()
    {
        if (empty($this->selected)) {
            $this->dispatch('show-toast', ['type' => 'warning', 'message' => 'Selecciona al menos un item.']);
            return;
        }

        $this->syncing = true;
        $this->results = [];

        Log::info('🚀 [WP-Stock] Sync manual iniciado', [
            'items_seleccionados' => count($this->selected),
            'ids' => $this->selected,
        ]);

        $this->ensureTenantConnection();
        $wpService = app(WordPressService::class);

        if (!$wpService->isConfigured()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'WordPress no está configurado para este tenant.']);
            $this->syncing = false;
            return;
        }

        $ok   = 0;
        $fail = 0;

        foreach ($this->selected as $itemId) {
            $item = Items::with(['tax', 'invItemsStore'])->find($itemId);
            if (!$item) continue;

            $result = $wpService->syncItemStock($item);
            $this->results[] = $result;

            if ($result['success']) $ok++;
            else $fail++;
        }

        Log::info('✅ [WP-Stock] Sync manual finalizado', ['ok' => $ok, 'fail' => $fail]);

        $msg = "Sincronización completada: {$ok} OK" . ($fail > 0 ? ", {$fail} fallidos." : '.');
        $this->dispatch('show-toast', [
            'type'    => $fail > 0 ? 'warning' : 'success',
            'message' => $msg,
        ]);

        $this->syncing = false;
    }

    public function render()
    {
        return view('livewire.tenant.wordpress.word-press-stock-sync');
    }
}
