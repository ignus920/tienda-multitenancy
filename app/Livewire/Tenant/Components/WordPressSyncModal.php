<?php

namespace App\Livewire\Tenant\Components;

use Livewire\Component;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\ImageGallery;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use App\Services\Tenant\WordPress\WordPressService;
use Illuminate\Support\Facades\Log;
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

    #[On('openWordPressSync')]
    public function openModal($itemId)
    {
        Log::info('🟢 [WPSync] Abriendo modal de sincronización', ['item_id' => $itemId]);
        $this->itemId = $itemId;
        $this->ensureTenantConnection();
        $this->loadData();
        $this->isOpen = true;
        $this->showComparison = false;
    }

    #[On('openWordPressComparison')]
    public function openComparison($itemId)
    {
        Log::info('🟢 [WPSync] Abriendo comparación', ['item_id' => $itemId]);
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

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }

        config(['database.connections.tenant.database' => $tenant->tenancy_db_name]);
    }

    public function loadData()
    {
        Log::info('🔄 [WPSync] loadData()', ['item_id' => $this->itemId]);

        $item = Items::find($this->itemId);
        if (!$item) {
            Log::warning('⚠️ [WPSync] Item no encontrado', ['item_id' => $this->itemId]);
            $this->message = "Producto no encontrado.";
            return;
        }

        $this->productSku = $item->sku;

        Log::info('📦 [WPSync] Item encontrado', [
            'item_id'   => $item->id,
            'item_name' => $item->name,
            'sku'       => $this->productSku,
        ]);

        if (empty($this->productSku)) {
            Log::warning('⚠️ [WPSync] El item no tiene SKU, no se puede sincronizar', ['item_id' => $item->id]);
            $this->message = "El producto no tiene SKU asignado. Asigna un SKU para sincronizar con WordPress.";
            $this->statusType = 'warning';
            return;
        }

        $wpService = app(WordPressService::class);

        if (!$wpService->isConfigured()) {
            Log::warning('⚠️ [WPSync] WordPress no está configurado para este tenant');
            $this->message = "WordPress no está configurado. Ve a Configuración > WordPress para añadir las credenciales.";
            $this->statusType = 'warning';
            return;
        }

        $this->wpProduct = $wpService->findProductBySku($this->productSku);

        if (!$this->wpProduct) {
            Log::warning('⚠️ [WPSync] Producto no encontrado en WP', ['sku' => $this->productSku]);
            $this->message = "Producto no encontrado en WordPress con SKU: {$this->productSku}";
            $this->statusType = 'warning';
        } else {
            Log::info('✅ [WPSync] Producto vinculado con WP', [
                'sku'         => $this->productSku,
                'wp_id'       => $this->wpProduct['id'],
                'wp_name'     => $this->wpProduct['name'],
                'num_imgs_wp' => count($this->wpProduct['images'] ?? []),
            ]);
            $this->message = "Producto vinculado: {$this->wpProduct['name']}";
            $this->statusType = 'success';
            $this->calculateComparison();
        }
    }

    public function calculateComparison()
    {
        $localImages = ImageGallery::with('wpSync')
            ->where('itemId', $this->itemId)
            ->whereNull('deleted_at')
            ->get();

        $wpImages = $this->wpProduct['images'] ?? [];

        Log::info('🔍 [WPSync] Calculando comparación', [
            'item_id'          => $this->itemId,
            'imgs_locales'     => $localImages->count(),
            'imgs_wp'          => count($wpImages),
            'marcadas_sync'    => $localImages->where('sync_to_wp', true)->count(),
            'con_wp_media_id'  => $localImages->whereNotNull('wp_media_id')->count(),
        ]);

        $this->onlyInErp = $localImages->filter(function ($img) use ($wpImages) {
            if (!$img->sync_to_wp) return false;
            if (!$img->wp_media_id) return true;
            return !collect($wpImages)->contains('id', $img->wp_media_id);
        })->values()->toArray();

        $localWpIds = $localImages->pluck('wp_media_id')->filter()->toArray();
        $this->onlyInWp = collect($wpImages)->filter(function ($wpImg) use ($localWpIds) {
            return !in_array($wpImg['id'], $localWpIds);
        })->values()->toArray();

        Log::info('📊 [WPSync] Resultado comparación', [
            'solo_en_erp' => count($this->onlyInErp),
            'solo_en_wp'  => count($this->onlyInWp),
        ]);
    }

    public function deleteFromWordPress($wpMediaId)
    {
        Log::info('🗑️ [WPSync] deleteFromWordPress()', [
            'item_id'     => $this->itemId,
            'wp_media_id' => $wpMediaId,
        ]);

        $this->ensureTenantConnection();

        try {
            $wpService = app(WordPressService::class);

            if ($this->wpProduct) {
                $wpService->removeFromProductImages($this->wpProduct['id'], $wpMediaId);
            }

            $wpService->deleteMedia($wpMediaId);

            Log::info('✅ [WPSync] Media eliminado de WP', ['wp_media_id' => $wpMediaId]);
            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Imagen eliminada de WordPress.']);
            $this->loadData();
        } catch (\Exception $e) {
            Log::error('❌ [WPSync] Error en deleteFromWordPress', [
                'wp_media_id' => $wpMediaId,
                'error'       => $e->getMessage(),
            ]);
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error al eliminar de WordPress: ' . $e->getMessage()]);
        }
    }

    public function toggleComparison()
    {
        $this->ensureTenantConnection();
        $this->showComparison = !$this->showComparison;

        if ($this->showComparison) {
            $this->loadData();
        }
    }

    public function toggleSyncToWp($imageId)
    {
        $this->ensureTenantConnection();
        $image = ImageGallery::find($imageId);
        if ($image) {
            $image->sync_to_wp = !$image->sync_to_wp;
            $image->save();
            Log::info('🔀 [WPSync] Toggle sync_to_wp', [
                'image_id'   => $imageId,
                'nuevo_valor' => $image->sync_to_wp,
            ]);
        }
    }

    public function syncMainImage()
    {
        Log::info('🚀 [WPSync] syncMainImage() iniciado', [
            'item_id' => $this->itemId,
            'sku'     => $this->productSku,
        ]);

        $this->syncing = true;

        try {
            $image = ImageGallery::where('itemId', $this->itemId)
                ->where('type', 'PRINCIPAL')
                ->whereNull('deleted_at')
                ->first();

            if (!$image) {
                Log::warning('⚠️ [WPSync] No hay imagen PRINCIPAL para sincronizar', ['item_id' => $this->itemId]);
                $this->dispatch('show-toast', ['type' => 'warning', 'message' => 'No hay imagen principal para sincronizar.']);
                return;
            }

            Log::info('🖼️ [WPSync] Imagen principal encontrada', [
                'image_id'    => $image->id,
                'img_path'    => $image->img_path,
                'wp_media_id' => $image->wp_media_id,
                'type_show'   => $image->type_show,
            ]);

            $wpService = app(WordPressService::class);
            $success = $wpService->syncImage($image, $this->productSku);

            if ($success) {
                Log::info('✅ [WPSync] Imagen principal sincronizada OK', [
                    'item_id'     => $this->itemId,
                    'image_id'    => $image->id,
                    'wp_media_id' => $image->fresh()->wp_media_id,
                ]);
                $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Imagen principal sincronizada con éxito.']);
                $this->loadData();
            } else {
                Log::error('❌ [WPSync] Falló la sincronización de imagen principal', [
                    'item_id'  => $this->itemId,
                    'image_id' => $image->id,
                ]);
                $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error al sincronizar con WordPress. Revisa los logs del servidor.']);
            }
        } catch (\Exception $e) {
            Log::error('❌ [WPSync] Excepción en syncMainImage', [
                'item_id' => $this->itemId,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error inesperado: ' . $e->getMessage()]);
        } finally {
            $this->syncing = false;
        }
    }

    public function syncGallery()
    {
        Log::info('🚀 [WPSync] syncGallery() iniciado', [
            'item_id' => $this->itemId,
            'sku'     => $this->productSku,
        ]);

        $this->syncing = true;

        try {
            $gallery = ImageGallery::where('itemId', $this->itemId)
                ->where('type', 'GALERIA')
                ->whereNull('deleted_at')
                ->whereHas('wpSync', function ($q) {
                    $q->where('sync_to_wp', true);
                })
                ->get();

            Log::info('📋 [WPSync] Imágenes de galería a sincronizar', [
                'item_id' => $this->itemId,
                'total'   => $gallery->count(),
                'ids'     => $gallery->pluck('id')->toArray(),
            ]);

            if ($gallery->isEmpty()) {
                Log::warning('⚠️ [WPSync] No hay imágenes marcadas para sincronizar', ['item_id' => $this->itemId]);
                $this->dispatch('show-toast', ['type' => 'warning', 'message' => 'No hay imágenes marcadas con "Para página web" para sincronizar.']);
                return;
            }

            $wpService = app(WordPressService::class);
            $count = 0;
            $errors = 0;

            foreach ($gallery as $image) {
                Log::info('🔄 [WPSync] Sincronizando imagen de galería', [
                    'image_id' => $image->id,
                    'img_path' => $image->img_path,
                ]);

                if ($wpService->syncImage($image, $this->productSku)) {
                    $count++;
                    Log::info('✅ [WPSync] Imagen de galería sincronizada', ['image_id' => $image->id]);
                } else {
                    $errors++;
                    Log::error('❌ [WPSync] Falló sincronización de imagen de galería', ['image_id' => $image->id]);
                }
            }

            Log::info('📊 [WPSync] syncGallery finalizado', [
                'item_id'    => $this->itemId,
                'exitosas'   => $count,
                'fallidas'   => $errors,
                'total'      => $gallery->count(),
            ]);

            $msg = "Se sincronizaron {$count} de {$gallery->count()} imágenes.";
            if ($errors > 0) {
                $msg .= " {$errors} fallaron — revisa los logs del servidor.";
            }

            $this->dispatch('show-toast', [
                'type'    => $errors > 0 ? 'warning' : 'success',
                'message' => $msg,
            ]);

            $this->loadData();
        } catch (\Exception $e) {
            Log::error('❌ [WPSync] Excepción en syncGallery', [
                'item_id' => $this->itemId,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error inesperado: ' . $e->getMessage()]);
        } finally {
            $this->syncing = false;
        }
    }

    public function render()
    {
        $images = collect();
        if ($this->itemId) {
            $images = ImageGallery::with('wpSync')
                ->where('itemId', $this->itemId)
                ->whereNull('deleted_at')
                ->get();
        }

        return view('livewire.tenant.components.word-press-sync-modal', [
            'localImages' => $images,
        ]);
    }
}
