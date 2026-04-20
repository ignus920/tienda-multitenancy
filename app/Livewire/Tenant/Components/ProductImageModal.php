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

    #[On('openImageModal')]
    public function open($productId, \App\Services\Tenant\WordPress\WordPressService $wpService, $context = null)
    {
        $this->ensureTenantConnection();
        $this->productId = $productId;
        $product = Items::find($productId);
        
        if ($product) {
            $this->productName = $product->name;
            $this->isOpen = true;
            
            // Perfil del usuario
            $this->userProfileId = auth()->user()->profile_id;
            
            // Si se pasa un contexto explícito (desde el botón), lo usamos
            if ($context) {
                $this->activeTab = strtoupper($context);
                $this->isContextForced = true;
            } else {
                $this->isContextForced = false;
                // Lógica por defecto según perfil si no hay contexto
                if ($this->userProfileId == 6) {
                    $this->activeTab = 'BODEGA';
                } else {
                    $this->activeTab = 'COMERCIAL';
                }
            }

            // Validar si existe en WordPress
            $this->hasWpProduct = !empty($product->sku) && $wpService->findProductBySku($product->sku) !== null;
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
        $image = ImageGallery::find($imageId);
        
        if ($image) {
            // Borrado físico opcional, por ahora soft delete como pide el modelo
            $image->update(['deleted_at' => now()]);
            $this->dispatch('refreshProductList');
            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Imagen eliminada']);
        }
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
        $this->reset(['mainImage', 'galleryImages', 'productId', 'productName']);
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
