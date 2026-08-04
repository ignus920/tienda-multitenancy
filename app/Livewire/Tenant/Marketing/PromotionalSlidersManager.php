<?php

namespace App\Livewire\Tenant\Marketing;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Tenant\Marketing\PromotionalSlider;
use Illuminate\Support\Facades\Storage;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;

class PromotionalSlidersManager extends Component
{
    use WithPagination, WithFileUploads;

    public $sliderId = null;
    public $title = '';
    public $image = null;
    public $existingImage = '';
    public $action_button_text = '';
    public $action_url = '';
    public $status = true;
    public $order = 0;

    public $isOpen = false;
    public $search = '';

    protected $paginationTheme = 'tailwind';

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'image' => $this->sliderId ? 'nullable|mimes:jpg,jpeg,png,webp,gif|max:2048' : 'required|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'action_button_text' => 'nullable|string|max:100',
            'action_url' => 'nullable|url|max:255',
            'status' => 'required|boolean',
            'order' => 'required|integer|min:0',
        ];
    }

    protected $messages = [
        'title.required' => 'El título es obligatorio.',
        'image.required' => 'La imagen es obligatoria.',
        'image.image' => 'El archivo debe ser una imagen válida.',
        'image.max' => 'La imagen no debe pesar más de 2MB.',
        'action_url.url' => 'El enlace de redirección debe ser una URL válida (ej. http://example.com).',
        'order.required' => 'El orden es obligatorio.',
        'order.integer' => 'El orden debe ser un número entero.',
    ];

    public function mount()
    {
        $this->ensureTenantConnection();
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) {
            return redirect()->route('tenant.select');
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            session()->forget('tenant_id');
            return redirect()->route('tenant.select');
        }

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal($id = null)
    {
        $this->ensureTenantConnection();
        $this->resetValidation();
        $this->resetFields();

        if ($id) {
            $this->sliderId = $id;
            $slider = PromotionalSlider::findOrFail($id);
            $this->title = $slider->title;
            $this->existingImage = $slider->image_path;
            $this->action_button_text = $slider->action_button_text;
            $this->action_url = $slider->action_url;
            $this->status = $slider->status;
            $this->order = $slider->order;
        }

        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetFields();
    }

    private function resetFields()
    {
        $this->sliderId = null;
        $this->title = '';
        $this->image = null;
        $this->existingImage = '';
        $this->action_button_text = '';
        $this->action_url = '';
        $this->status = true;
        $this->order = 0;
    }

    public function save()
    {
        $this->ensureTenantConnection();
        
        \Illuminate\Support\Facades\Log::info('Sliders - Intentando guardar slider:', [
            'sliderId' => $this->sliderId,
            'title' => $this->title,
            'has_new_image' => !is_null($this->image),
            'existing_image' => $this->existingImage
        ]);

        $this->validate();

        $data = [
            'title' => $this->title,
            'action_button_text' => $this->action_button_text,
            'action_url' => $this->action_url,
            'status' => $this->status,
            'order' => $this->order,
        ];

        if ($this->image) {
            $tenantId = session('tenant_id');
            $path = $this->image->store("tenants/{$tenantId}/sliders", 'public');
            $data['image_path'] = Storage::url($path);
            
            \Illuminate\Support\Facades\Log::info('Sliders - Nueva imagen subida:', ['path' => $data['image_path']]);

            if ($this->sliderId && $this->existingImage) {
                $oldPath = str_replace('/storage/', '', $this->existingImage);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
        } else {
            \Illuminate\Support\Facades\Log::info('Sliders - No se subió imagen nueva, se conserva la anterior si existe.');
        }

        if ($this->sliderId) {
            $slider = PromotionalSlider::findOrFail($this->sliderId);
            $slider->update($data);
            $message = 'Slider actualizado con éxito.';
        } else {
            PromotionalSlider::create($data);
            $message = 'Slider creado con éxito.';
        }

        $this->closeModal();
        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => $message
        ]);
    }

    public function toggleStatus($id)
    {
        $this->ensureTenantConnection();
        $slider = PromotionalSlider::findOrFail($id);
        $slider->status = !$slider->status;
        $slider->save();

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Estado del slider actualizado.'
        ]);
    }



    public function render()
    {
        $this->ensureTenantConnection();

        $sliders = PromotionalSlider::query()
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('action_url', 'like', '%' . $this->search . '%');
            })
            ->orderBy('order', 'asc')
            ->paginate(10);

        return view('livewire.tenant.marketing.promotional-sliders-manager', [
            'sliders' => $sliders
        ])->layout('layouts.app', ['header' => 'Gestión de Sliders']);
    }
}
