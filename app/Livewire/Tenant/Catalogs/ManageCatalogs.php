<?php

namespace App\Livewire\Tenant\Catalogs;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Tenant\Catalogs\CatCatalogs;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ManageCatalogs extends Component
{
    use WithPagination, WithFileUploads;

    // Estado del modal y CRUD
    public $isOpen = false;
    public $selectedCatalogId = null;

    // Campos del Formulario
    public $family;
    public $title;
    public $archivo;
    public $archivoActual;

    // Filtros e Historial
    public $search = '';
    public $perPage = 10;
    public $sortField = 'title';
    public $sortDirection = 'asc';

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    private function ensureTenantConnection(): void
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) {
            abort(403, 'No tenant selected');
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            abort(403, 'Tenant invalid');
        }

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->reset(['family', 'title', 'archivo', 'archivoActual', 'selectedCatalogId']);
        $this->isOpen = true;
    }

    public function cancel()
    {
        $this->resetValidation();
        $this->reset(['family', 'title', 'archivo', 'archivoActual', 'selectedCatalogId']);
        $this->isOpen = false;
    }

    public function save()
    {
        $this->ensureTenantConnection();
        $tenantId = session('tenant_id');

        $rules = [
            'family' => 'required|min:3|max:255',
            'title' => [
                'required',
                'min:3',
                'max:255',
                Rule::unique('tenant.cat_catalogs', 'title')->ignore($this->selectedCatalogId)
            ],
            'archivo' => $this->selectedCatalogId 
                ? 'nullable|file|mimes:pdf|max:20480' // 20 MB max
                : 'required|file|mimes:pdf|max:20480'
        ];

        $messages = [
            'family.required' => 'La familia es requerida.',
            'title.required' => 'El título es requerido.',
            'title.unique' => 'El título ya existe en otro catálogo.',
            'archivo.required' => 'El archivo PDF es requerido.',
            'archivo.mimes' => 'El archivo debe estar en formato PDF.',
            'archivo.max' => 'El archivo no debe pesar más de 20 MB.'
        ];

        $this->validate($rules, $messages);

        try {
            $slug = $this->generateSlug($this->title);
            $vinculo = $this->archivoActual;
            $archivoOriginal = $this->archivoActual ? basename($this->archivoActual) : '';

            if ($this->selectedCatalogId) {
                // Usamos el título original de la BD para mantener la URL/slug idéntica al actualizar el archivo
                $catalog = CatCatalogs::findOrFail($this->selectedCatalogId);
                $slug = $this->generateSlug($catalog->title);
            }

            if ($this->archivo) {
                $extension = $this->archivo->getClientOriginalExtension();
                $archivoOriginal = $this->archivo->getClientOriginalName();
                $nuevoNombreArchivo = $slug . '.' . $extension;

                // Almacenar archivo en storage public catalogs/{tenant_id}
                $path = $this->archivo->storeAs("catalogs/{$tenantId}", $nuevoNombreArchivo, 'public');
                $vinculo = "storage/" . $path;
            }

            $loginUser = Auth::user()->name ?? Auth::user()->email ?? 'Sistema';

            if ($this->selectedCatalogId) {
                // Actualizar registro existente
                $catalog = CatCatalogs::findOrFail($this->selectedCatalogId);
                
                $catalog->update([
                    'family' => $this->family,
                    'title' => $this->title,
                    'file_name' => $this->archivo ? $archivoOriginal : $catalog->file_name,
                    'link' => $vinculo,
                    'login' => $loginUser
                ]);

                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => 'Catálogo actualizado exitosamente'
                ]);
            } else {
                // Crear nuevo registro
                CatCatalogs::create([
                    'family' => $this->family,
                    'title' => $this->title,
                    'file_name' => $archivoOriginal,
                    'link' => $vinculo,
                    'login' => $loginUser
                ]);

                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => 'Catálogo registrado exitosamente'
                ]);
            }

            $this->cancel();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al guardar el catálogo: ' . $e->getMessage()
            ]);
        }
    }

    public function edit($id)
    {
        $this->ensureTenantConnection();
        $this->resetValidation();

        $catalog = CatCatalogs::findOrFail($id);
        $this->selectedCatalogId = $catalog->id;
        $this->family = $catalog->family;
        $this->title = $catalog->title;
        $this->archivoActual = $catalog->link;
        $this->archivo = null;
        $this->isOpen = true;
    }

    public function delete($id)
    {
        $this->ensureTenantConnection();
        try {
            $catalog = CatCatalogs::findOrFail($id);
            $catalog->delete();

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Catálogo eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al eliminar el catálogo: ' . $e->getMessage()
            ]);
        }
    }

    private function generateSlug($string)
    {
        $string = mb_strtolower($string, 'UTF-8');
        $string = preg_replace('/[^a-z0-9-]/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        return trim($string, '-');
    }

    public function render()
    {
        $this->ensureTenantConnection();

        $catalogs = CatCatalogs::query()
            ->where(function($query) {
                $query->where('family', 'like', '%' . $this->search . '%')
                      ->orWhere('title', 'like', '%' . $this->search . '%')
                      ->orWhere('file_name', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.catalogs.manage-catalogs', [
            'catalogs' => $catalogs
        ])->layout('layouts.app', ['header' => 'Gestión de Catálogos']);
    }
}
