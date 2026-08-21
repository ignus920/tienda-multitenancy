<?php

namespace App\Livewire\Tenant\Components;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\ImageGallery;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;

class ProductImageModal extends Component
{
    use WithFileUploads;

    public $isOpen = false;
    public $productId;
    public $productName;
    public $productCode;
    
    // Para subida de archivos
    public $mainImage;
    public $galleryImages = [];
    
    public $activeTab = 'COMERCIAL';
    public $userProfileId;
    public $isContextForced = false;
    
    // Lista de imágenes actuales (se carga en render para evitar errores de hidratación)
    // public $images = []; 

    protected $rules = [
        'mainImage' => 'nullable|image|max:2048', // 2MB max
        'galleryImages.*' => 'nullable|mimes:jpg,jpeg,png,webp,pdf|max:2048',
    ];

    public function mount(\App\Services\Tenant\WordPress\WordPressService $wpService, $productId = null)
    {
        if ($productId) {
            $this->open($productId, $wpService);
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

        // Estabilizar conexión para este componente y el driver tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        
        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }
        
        // Forzar que el driver 'tenant' use la base de datos correcta en tiempo de ejecución
        config(['database.connections.tenant.database' => $tenant->tenancy_db_name]);
    }

    public $hasWpProduct = false;
    public $wpProductUrl = null;

    #[On('openImageModal')]
    public function open($productId, $context = null, \App\Services\Tenant\WordPress\WordPressService $wpService = null)
    {
        $this->ensureTenantConnection();
        $this->productId = $productId;
        $product = Items::find($productId);
        
        if ($product) {
            $this->productName = $product->name;
            $this->productCode = $product->internal_code;
            $this->isOpen = true;
            
            // Cargar estado de WooCommerce interactivamente en la apertura
            $wpService = $wpService ?: app(\App\Services\Tenant\WordPress\WordPressService::class);
            $wpProduct = !empty($product->sku) ? $wpService->findProductBySku($product->sku) : null;
            $this->hasWpProduct = $wpProduct !== null;
            $this->wpProductUrl = $wpProduct ? ($wpProduct['permalink'] ?? null) : null;

            \Illuminate\Support\Facades\Log::info('DEBUG WP PRODUCT MODAL', [
                'item_sku' => $product->sku,
                'found_in_wp' => $this->hasWpProduct,
                'wp_product_data' => $wpProduct
            ]);
            
            // Perfil del usuario
            $this->userProfileId = auth()->user()->profile_id;
            
            // Si se pasa un contexto explícito (desde el botón), lo usamos
            if ($context) {
                $this->activeTab = strtoupper($context);
                if ($this->activeTab === 'BODEGA') {
                    $this->isContextForced = false; // Permitimos cambiar a Comercial si estamos en bodega
                } else {
                    $this->isContextForced = true;
                }
            } else {
                $this->isContextForced = false;
                // Lógica por defecto según perfil si no hay contexto
                if ($this->userProfileId == 6) {
                    $this->activeTab = 'BODEGA';
                } else {
                    $this->activeTab = 'COMERCIAL';
                }
            }
        }
    }

    public function loadWpProductStatus(\App\Services\Tenant\WordPress\WordPressService $wpService)
    {
        $this->ensureTenantConnection();
        if ($this->productId) {
            $product = Items::find($this->productId);
            if ($product) {
                $wpProduct = !empty($product->sku) ? $wpService->findProductBySku($product->sku) : null;
                $this->hasWpProduct = $wpProduct !== null;
                $this->wpProductUrl = $wpProduct ? ($wpProduct['permalink'] ?? null) : null;
            }
        }
    }

    // loadImages ya no es necesario como método separado para guardar en propiedad pública

    public function updatedMainImage()
    {
        $this->validate(['mainImage' => 'image|max:2048']);
        $this->saveMainImage();
    }

    public function updatedGalleryImages()
    {
        $this->validate(['galleryImages.*' => 'mimes:jpg,jpeg,png,webp,pdf|max:2048']);
        $this->saveGalleryImages();
    }

    public function saveMainImage()
    {
        $this->ensureTenantConnection();
        
        if ($this->mainImage) {
            // Desactivar imagen principal anterior de la misma categoría (COMERCIAL o BODEGA)
            ImageGallery::where('itemId', $this->productId)
                ->where('type', 'PRINCIPAL')
                ->where('type_show', $this->activeTab)
                ->update(['type' => 'GALERIA']); // O borrarla si prefieres

            $tenantId = session('tenant_id', 'default');
            $path = $this->mainImage->store("items/{$tenantId}", 'public');

            ImageGallery::create([
                'itemId' => $this->productId,
                'img_path' => $path,
                'type' => 'PRINCIPAL',
                'type_show' => $this->activeTab,
            ]);

            $this->mainImage = null;
            $this->dispatch('refreshProductList');
            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Imagen principal actualizada']);
        }
    }

    public function saveGalleryImages()
    {
        $this->ensureTenantConnection();
        
        $tenantId = session('tenant_id', 'default');
        foreach ($this->galleryImages as $file) {
            $path = $file->store("items/{$tenantId}", 'public');
            
            // Determinar el tipo según la extensión
            $extension = strtolower($file->getClientOriginalExtension());
            $type = ($extension === 'pdf') ? 'PDF' : 'GALERIA';

            ImageGallery::create([
                'itemId' => $this->productId,
                'img_path' => $path,
                'type' => $type,
                'type_show' => $this->activeTab,
            ]);
        }

        $this->galleryImages = [];
        $this->dispatch('refreshProductList');
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Archivos agregados a la galería']);
    }

    public function deleteImage($imageId)
    {
        $this->ensureTenantConnection();
        $image = ImageGallery::with('wpSync')->find($imageId);

        if (!$image) return;

        $wpMediaId = $image->wp_media_id;

        if ($wpMediaId) {
            $wpService = app(\App\Services\Tenant\WordPress\WordPressService::class);
            $item = \App\Models\Tenant\Items\Items::find($image->itemId);
            $sku  = $item?->sku;

            if ($sku) {
                $wpProduct = $wpService->findProductBySku($sku);
                if ($wpProduct) {
                    $wpService->removeFromProductImages($wpProduct['id'], $wpMediaId);
                }
            }

            $wpService->deleteMedia($wpMediaId);
        }

        $image->update(['deleted_at' => now()]);
        $this->dispatch('refreshProductList');
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Imagen eliminada' . ($wpMediaId ? ' y removida de WordPress' : '')]);
    }

    public function toggleSyncToWp($imageId)
    {
        $this->ensureTenantConnection();
        $image = ImageGallery::find($imageId);
        
        if ($image) {
            $image->sync_to_wp = !$image->sync_to_wp;
            $image->save();
            
            $status = $image->sync_to_wp ? 'habilitada' : 'deshabilitada';
            $this->dispatch('show-toast', ['type' => 'success', 'message' => "Sincronización $status para esta imagen"]);
        }
    }

    public function close()
    {
        $this->isOpen = false;
        $this->reset(['mainImage', 'galleryImages', 'productId', 'productName', 'productCode']);
    }

    public function render()
    {
        $this->ensureTenantConnection();
        
        $images = [];
        if ($this->productId) {
            $query = ImageGallery::with('wpSync')
                ->where('itemId', $this->productId)
                ->whereNull('deleted_at')
                ->where('type_show', $this->activeTab);
            
            $images = $query->orderBy('type', 'asc')
                ->get();
        }

        return view('livewire.tenant.components.product-image-modal', [
            'images' => $images
        ]);
    }
}
