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
 
class ProductImageModalCargar extends Component
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
    public $hasWpProduct = false;
 
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
 
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        
        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }
        
        config(['database.connections.tenant.database' => $tenant->tenancy_db_name]);
    }
 
    #[On('openImageModalCargar')]
    public function open($productId, \App\Services\Tenant\WordPress\WordPressService $wpService)
    {
        $this->ensureTenantConnection();
        $this->productId = $productId;
        $product = Items::find($productId);
        
        if ($product) {
            $this->productName = $product->name;
            $this->isOpen = true;
            $this->userProfileId = auth()->user()->profile_id;
            
            if ($this->userProfileId == 6) {
                $this->activeTab = 'BODEGA';
            } else {
                $this->activeTab = 'COMERCIAL';
            }
 
            $this->hasWpProduct = !empty($product->sku) && $wpService->findProductBySku($product->sku) !== null;
        }
    }
 
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
            ImageGallery::where('itemId', $this->productId)
                ->where('type', 'PRINCIPAL')
                ->where('type_show', $this->activeTab)
                ->update(['type' => 'GALERIA']);
 
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
            $images = ImageGallery::with('wpSync')
                ->where('itemId', $this->productId)
                ->whereNull('deleted_at')
                ->where('type_show', $this->activeTab)
                ->orderBy('type', 'asc')
                ->get();
        }
 
        return view('livewire.tenant.components.product-image-modalCargar', [
            'images' => $images
        ]);
    }
}
