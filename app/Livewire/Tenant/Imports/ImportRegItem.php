<?php

namespace App\Livewire\Tenant\Imports;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Tenant\Imports\ImpItemsSetup;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvItemsStore;
use App\Models\Tenant\Items\ImageGallery;
//Servicios
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ImportRegItem extends Component
{
    use WithFileUploads;

    public $items_setup_id;
    public $itemId;
    public $percentage;
    public $cantidad_min;
    public $factor;
    public $factory_ref;
    public $exw;
    public $freight_increase;
    public $pvp_factor;
    public $pvp_min_factor;
    public $internal_code;
    public $descripcion = 'NEW_PRODUCT';

    //Campos de validación
    public $internal_codeExists = false;
    public $validatingInternal_code = false;

    public $data_suppliers = [
        'supplierId' => ''
    ];

    protected $listeners = [
        'supplierSelected'
    ];

    protected $rules = [
        'percentage' => 'required|integer',
        'cantidad_min' => 'required|integer',
        'factor' => 'required' //,
        //'technical_sheet' => 'nullable|file|mimes:pdf|max:2048',
        // 'exw' => ['numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
        // 'freight_increase' => ['numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
        // 'pvp_factor' => ['numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
        // 'pvp_min_factor' => ['numeric', 'regex:/^\d+(\.\d{1,2})?$/']
    ];

    protected $messages = [
        'percentage.required' => 'El porcentaje es obligatorio.',
        'percentage.integer' => 'El pocentage debe ser un número.',
        'cantidad_min.required' => 'La cantidad minima es obligatoría.',
        'cantidad_min.integer' => 'La cantidad minima deber un número.',
        'factor.required' => 'El factor es obligatorio.' //,
        // 'exw.numeric' => 'El campo EXW debe ser un múmero.',
        // 'exw.regex' => 'El campo EXW debe ser un número con un máximo de dos decimales.',
    ];

    public function mount($itemId = null)
    {
        $this->itemId = $itemId;
        $this->edit($this->itemId);
        Log::info('🎈 Cargando itemId', [
            'itemId' => $this->itemId
        ]);

        if (empty($this->data_suppliers['supplierId'])) {
            $this->data_suppliers['supplierId'] = 219;
            $this->data_suppliers['supplierName'] = 'Shenzhen Riyi International';
        }
    }

    public function saveInfoImport()
    {
        try {
            if ($this->itemId) {
                $this->saveWithItem();
            } else {
                $this->saveWithOutItem();
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('✂️ Error al guardar información de importación: ' . $e->getMessage());
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
            return;
        }
    }

    public function saveWithOutItem()
    {
        $this->ensureTenantConnection();
        $this->validate();
        if ($this->internal_codeExists) {
            $this->addError('internal_code', 'Este código interno ya está registrado.');
            return;
        }

        // Validación del proveedor
        if (empty($this->data_suppliers['supplierId'])) {
            $this->addError('data_suppliers.supplierId', 'El proveedor es obligatorio.');
            return;
        }
        try {
            $infoItem = [
                'name' => 'NEW_PRODUCT',
                'internal_code' => $this->internal_code,
                'sku' => $this->internal_code,
                'description' => $this->descripcion,
                'type' => 'IMPORTADO',
                'purchasing_unit' => 35,
                'consumption_unit' => 35,
                'generic' => 1
            ];
            $newItem = Items::create($infoItem);

            $infoItemsStore = [
                'itemId' => $newItem->id,
                'storeId' => 1
            ];

            InvItemsStore::create($infoItemsStore);

            $info = [
                'item_id' => $newItem->id,
                'percentage' => $this->percentage,
                'cantidad_min' => $this->cantidad_min,
                'supplier_id' => !empty($this->data_suppliers['supplierId']) ? (int)$this->data_suppliers['supplierId'] : 0,
                'factory_ref' => $this->factory_ref,
                'exw' => $this->exw,
                'freight_increase' => $this->freight_increase,
                'pvp_factor' => $this->pvp_factor,
                'pvp_min_factor' => $this->pvp_min_factor
            ];


            ImpItemsSetup::create($info);
            $this->resetForm();
            session()->flash('message', 'Registro realizado exitosamente.');
            Log::info('🚚 Registro item desde importación' . $newItem);
            $this->dispatch('refresh-import-list');
            $this->closeItemsImportModal();
        } catch (\Exception $e) {
            Log::error('✂️ Error al guardar información de importación: ' . $e->getMessage());
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
            return;
        }
    }

    public function saveWithItem()
    {
        $this->ensureTenantConnection();
        $this->validate();
        $info = [
            'item_id' => $this->itemId,
            'percentage' => $this->percentage,
            'cantidad_min' => $this->cantidad_min,
            'supplier_id' => !empty($this->data_suppliers['supplierId']) ? (int)$this->data_suppliers['supplierId'] : 0,
            'factory_ref' => $this->factory_ref,
            'exw' => $this->exw,
            'freight_increase' => $this->freight_increase,
            'pvp_factor' => $this->pvp_factor,
            'pvp_min_factor' => $this->pvp_min_factor
        ];
        try {
            // Validación del proveedor
            if (empty($this->data_suppliers['supplierId'])) {
                $this->addError('data_suppliers.supplierId', 'El proveedor es obligatorio.');
                return;
            }

            if ($this->items_setup_id) {
                $item_setup = ImpItemsSetup::findOrFail($this->items_setup_id);
                $item_setup->update($info);
                session()->flash('message', 'Registro actualizado exitosamente.');
            } else {
                ImpItemsSetup::create($info);
                session()->flash('message', 'Registro realizado exitosamente.');
            }
            $this->resetForm();
            $this->dispatch('refresh-import-list');
            $this->closeItemsModal();
        } catch (\Exception $e) {
            Log::error('✂️ Error al guardar información de importación: ' . $e->getMessage());
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
            return;
        }
    }

    public function updatedInternalCode($value)
    {
        $this->validateInternalCodeExits();
    }

    public function validateInternalCodeExits(): void
    {
        if (empty($this->internal_code)) {
            $this->internal_codeExists = false;
            $this->validatingInternal_code = false;
            return;
        }

        $this->validatingInternal_code = true;

        try {
            $this->ensureTenantConnection();
            $query = Items::where('internal_code', $this->internal_code);

            if ($this->itemId) {
                $query->where('id', '!=', $this->itemId);
            }
            $this->internal_codeExists = $query->exists();
            Log::info('💎 Internal_code' . $this->internal_codeExists);
        } catch (\Exception $e) {
            // Log error but don't break the form
            Log::error('Error validating internal_code exists', [
                'error' => $e->getMessage(),
                'internal_code' => $this->internal_code
            ]);
            $this->internal_codeExists = false;
        } finally {
            $this->validatingInternal_code = false;
        }
    }

    public function resetForm()
    {
        $this->percentage = '';
        $this->cantidad_min = '';
        $this->factor = '';
        $this->factory_ref = '';
        $this->exw = '';
        $this->freight_increase = '';
        $this->pvp_factor = '';
        $this->pvp_min_factor = '';
        $this->internal_code = '';
        $this->descripcion = 'NEW_PRODUCT';
        $this->data_suppliers = [
            'supplierId' => 219,
            'supplierName' => 'Shenzhen Riyi International'
        ];
    }

    public function closeItemsModal()
    {
        $this->dispatch('closeItemsModal');
    }

    public function closeItemsImportModal()
    {
        $this->dispatch('closeItemsImportModal');
    }

    /**
     * Handle supplier selection from GenericSelect component
     * 
     * @param mixed $value The selected supplier ID
     * @return void
     */
    public function supplierSelected($value)
    {
        $this->data_suppliers['supplierId'] = $value ?? '';

        // Update supplier name for display in details table
        if (!empty($value)) {
            $supplier = collect($this->suppliers)->firstWhere('id', $value);
            $this->data_suppliers['supplierName'] = $supplier['firstName'] ?? 'N/A';
        } else {
            $this->data_suppliers['supplierName'] = '';
        }
    }

    #[Computed]
    public function suppliers()
    {
        $this->ensureTenantConnection();

        return \App\Models\Auth\User::select('users.id', 'users.name')
            ->join('vnt_contacts', 'users.contact_id', '=', 'vnt_contacts.id')
            ->where('users.profile_id', 17)
            ->where('vnt_contacts.status', 1)
            ->whereNull('vnt_contacts.deleted_at')
            ->distinct()
            ->get()
            ->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'firstName' => $supplier->name
                ];
            })
            ->toArray();
    }

    public function edit($itemId = null)
    {
        if (!$itemId) {
            return;
        }

        $this->ensureTenantConnection();
        $itemImport = ImpItemsSetup::where('item_id', $itemId)->first();

        if ($itemImport) {
            $this->items_setup_id = $itemImport->id;
            $this->percentage = $itemImport->percentage;
            $this->cantidad_min = $itemImport->cantidad_min;
            $this->data_suppliers['supplierId'] = $itemImport->supplier_id;
            $this->factory_ref = $itemImport->factory_ref;
            $this->exw = $itemImport->exw;
            $this->freight_increase = $itemImport->freight_increase;
            $this->pvp_factor = $itemImport->pvp_factor;
            $this->pvp_min_factor = $itemImport->pvp_min_factor;

            // También cargamos la información del ítem base
            $item = Items::find($itemId);
            if ($item) {
                $this->internal_code = $item->internal_code;
                $this->descripcion = $item->description;
            }
        }
    }

    public function getPdfItem()
    {
        $this->ensureTenantConnection();
        return ImageGallery::where('itemId', $this->itemId)
            ->where('type', 'PDF')
            ->whereNull('deleted_at')
            ->first();
    }

    private function removePreviousPdf()
    {
        $previousPdf = $this->getPdfItem();
        if ($previousPdf) {
            // Eliminar archivos físicos
            if ($previousPdf->img_path && Storage::disk('public')->exists($previousPdf->img_path)) {
                Storage::disk('public')->delete($previousPdf->img_path);
            }

            // Eliminar registro
            $previousPdf->delete();
        }
    }

    public function render()
    {
        return view('livewire.tenant.imports.import-reg-item', [
            'itemId' => $this->itemId
        ]);
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

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }
}
