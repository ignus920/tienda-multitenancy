<?php

namespace App\Livewire\Tenant\Components;

use Livewire\Component;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\ImageGallery;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use App\Services\Tenant\WordPress\WordPressService;
use Livewire\Attributes\On;

class WordPressSyncModal extends Component
{
    public $isOpen = false;
    public $itemId;
    public $productSku;
    public $wpProduct;
    public $syncing = false;
    public $message = '';
    public $statusType = 'info';
    public $showComparison = false;
    public $onlyInErp = [];
    public $onlyInWp = [];

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    /**
     * Abrir el modal para un producto específico.
     */
    #[On('openWordPressSync')]
    public function openModal($itemId)
    {
        $this->itemId = $itemId;
        $this->ensureTenantConnection();
        $this->loadData();
        $this->isOpen = true;
        // Al abrir sincronización, nos aseguramos que comparación esté cerrada
        $this->showComparison = false;
    }

    /**
     * Abrir SOLO el modal de comparación.
     */
    #[On('openWordPressComparison')]
    public function openComparison($itemId)
    {
        $this->itemId = $itemId;
        $this->ensureTenantConnection();
        $this->loadData();
        $this->showComparison = true;
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) return;

        $tenant = Tenant::find($tenantId);
        if (!$tenant) return;

        // Estabilizar conexión para este componente y el driver tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        
        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }
        
        // Forzar que el driver 'tenant' use la base de datos correcta en tiempo de ejecución
        config(['database.connections.tenant.database' => $tenant->tenancy_db_name]);
    }

    public function loadData()
    {
        $item = Items::find($this->itemId);
        if (!$item) {
            $this->message = "Producto no encontrado.";
            return;
        }

        $this->productSku = $item->sku; // Se usa el campo 'sku' definido en el modelo Items
        
        $wpService = app(WordPressService::class);
        $this->wpProduct = $wpService->findProductBySku($this->productSku);
        
        if (!$this->wpProduct) {
            $this->message = "Producto no encontrado en WordPress con el SKU: {$this->productSku}";
            $this->statusType = 'warning';
        } else {
            $this->message = "Producto vinculado: {$this->wpProduct['name']}";
            $this->statusType = 'success';
            $this->calculateComparison();
        }
    }

    public function calculateComparison()
    {
        $localImages = ImageGallery::where('itemId', $this->itemId)
            ->whereNull('deleted_at')
            ->get();

        $wpImages = $this->wpProduct['images'] ?? [];

        // Solo en ERP: Imágenes locales con sync_to_wp=true que NO tienen wp_media_id (o cuyo wp_media_id no está en WP)
        $this->onlyInErp = $localImages->filter(function($img) use ($wpImages) {
            if (!$img->sync_to_wp) return false;
            if (!$img->wp_media_id) return true;
            
            // Verificar si el ID existe en la respuesta de WP
            return !collect($wpImages)->contains('id', $img->wp_media_id);
        })->values()->toArray();

        // Solo en WP: Imágenes en WP cuyo ID no está vinculado a ninguna imagen local activa de este item
        $localWpIds = $localImages->pluck('wp_media_id')->filter()->toArray();
        $this->onlyInWp = collect($wpImages)->filter(function($wpImg) use ($localWpIds) {
            return !in_array($wpImg['id'], $localWpIds);
        })->values()->toArray();
    }

    public function toggleComparison()
    {
        $this->ensureTenantConnection();
        $this->showComparison = !$this->showComparison;
        
        if ($this->showComparison) {
            $this->loadData();
        }
    }

    /**
     * Alterna el estado de sincronización desde este modal también.
     */
    public function toggleSyncToWp($imageId)
    {
        $this->ensureTenantConnection();
        $image = ImageGallery::find($imageId);
        if ($image) {
            $image->sync_to_wp = !$image->sync_to_wp;
            $image->save();
        }
    }

    /**
     * Sincroniza la imagen principal.
     */
    public function syncMainImage()
    {
        $this->syncing = true;
        
        try {
            $image = ImageGallery::where('itemId', $this->itemId)
                ->where('type', 'PRINCIPAL')
                ->first();

            if (!$image) {
                $this->dispatch('notify', ['message' => 'No hay imagen principal para sincronizar.', 'type' => 'error']);
                return;
            }

            $wpService = app(WordPressService::class);
            $success = $wpService->syncImage($image, $this->productSku);

            if ($success) {
                $this->dispatch('notify', ['message' => 'Imagen principal sincronizada con éxito.', 'type' => 'success']);
                $this->loadData();
                $this->calculateComparison();
            } else {
                $this->dispatch('notify', ['message' => 'Error al sincronizar con WordPress.', 'type' => 'error']);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        } finally {
            $this->syncing = false;
        }
    }

    /**
     * Sincroniza toda la galería.
     */
    public function syncGallery()
    {
        $this->syncing = true;
        
        try {
            $gallery = ImageGallery::where('itemId', $this->itemId)
                ->where('type', 'GALERIA')
                ->where('sync_to_wp', true)
                ->whereNull('deleted_at')
                ->get();

            if ($gallery->isEmpty()) {
                $this->dispatch('notify', ['message' => 'No hay imágenes marcadas con "Para página web" para sincronizar.', 'type' => 'warning']);
                return;
            }

            $wpService = app(WordPressService::class);
            $count = 0;

            foreach ($gallery as $image) {
                if ($wpService->syncImage($image, $this->productSku)) {
                    $count++;
                }
            }

            $this->dispatch('notify', ['message' => "Se sincronizaron $count imágenes de galería.", 'type' => 'success']);
            $this->loadData();
            $this->calculateComparison();
        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        } finally {
            $this->syncing = false;
        }
    }

    public function render()
    {
        $images = collect();
        if ($this->itemId) {
            $images = ImageGallery::where('itemId', $this->itemId)
                ->whereNull('deleted_at')
                ->get();
        }

        return view('livewire.tenant.components.word-press-sync-modal', [
            'localImages' => $images
        ]);
    }
}
