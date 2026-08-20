<?php

namespace App\Livewire\Tenant\Warranties;

use App\Models\Tenant\Remissions\InvRemissions;
use App\Models\Tenant\Sales\VntWarranty;
use App\Models\Tenant\Sales\VntWarrantyItem;
use App\Models\Tenant\Sales\VntWarrantyEvidence;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WarrantyCreate extends Component
{
    use WithFileUploads;

    public $remissionId;
    protected $remission;
    public $items = []; // Estructura: [ ['item_id' => X, 'description' => Z, 'available_qty' => Q, 'qty' => 0, 'failure' => '', 'request' => ''] ]
    
    // Almacena temporalmente los archivos subidos. Estructura: [ index => [file1, file2] ]
    public $tempEvidences = [];

    // Propiedades para el sub-modal de evidencias
    public $isEvidenceModalOpen = false;
    public $activeItemIndex = null;
    public $evidenceFiles = []; // Enlace temporal para el input file de evidencias

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
        tenancy()->initialize($tenant);
    }

    public function mount($id)
    {
        $this->ensureTenantConnection();
        $this->remissionId = $id;
        $this->loadRemission($id);
    }

    public function loadRemission($id)
    {
        $this->remission = InvRemissions::with(['details.item', 'quote'])->find($id);

        if (!$this->remission) {
            session()->flash('error', 'OP/Remisión no encontrada');
            return redirect()->route('tenant.remissions'); // o la ruta de listado de pedidos
        }

        $this->items = [];
        $this->tempEvidences = [];

        foreach ($this->remission->details as $index => $detail) {
            // Obtener cuántas unidades ya están en garantía
            $alreadyInWarranty = VntWarrantyItem::whereHas('warranty', function ($q) use ($id) {
                    $q->where('remission_id', $id);
                })
                ->where('item_id', $detail->itemId)
                ->sum('quantity');

            $availableQty = $detail->quantity - $alreadyInWarranty;

            $this->items[] = [
                'item_id' => $detail->itemId,
                'codigo' => $detail->item->internal_code ?? 'N/A',
                'description' => $detail->item->name ?? $detail->description ?? 'Producto sin nombre',
                'original_qty' => $detail->quantity,
                'previously_returned' => $alreadyInWarranty,
                'available_qty' => $availableQty,
                'qty' => 0,
                'failure' => '',
                'request' => '',
                'isSelected' => false
            ];
            
            $this->tempEvidences[$index] = [];
        }
    }

    // Métodos para el sub-modal de evidencias
    public function openEvidenceUploadModal($index)
    {
        $this->activeItemIndex = $index;
        $this->evidenceFiles = [];
        $this->isEvidenceModalOpen = true;
    }

    public function closeEvidenceUploadModal()
    {
        $this->isEvidenceModalOpen = false;
        $this->activeItemIndex = null;
        $this->evidenceFiles = [];
    }

    public function updatedEvidenceFiles()
    {
        $this->validate([
            'evidenceFiles.*' => 'file|max:15360' // Límite de 15MB por archivo para admitir videos
        ]);

        if ($this->activeItemIndex !== null) {
            foreach ($this->evidenceFiles as $file) {
                $this->tempEvidences[$this->activeItemIndex][] = $file;
            }
        }

        $this->evidenceFiles = []; // Limpiar entrada temporal
    }

    public function removeEvidenceFile($fileIndex)
    {
        if ($this->activeItemIndex !== null && isset($this->tempEvidences[$this->activeItemIndex][$fileIndex])) {
            unset($this->tempEvidences[$this->activeItemIndex][$fileIndex]);
            $this->tempEvidences[$this->activeItemIndex] = array_values($this->tempEvidences[$this->activeItemIndex]);
        }
    }

    public function save()
    {
        $this->ensureTenantConnection();

        $selectedItems = array_filter($this->items, function ($item) {
            return $item['isSelected'] && $item['qty'] > 0;
        });

        if (empty($selectedItems)) {
            $this->dispatch('show-toast', ['type' => 'warning', 'message' => 'Debe seleccionar al menos un producto e ingresar una cantidad válida.']);
            return;
        }

        foreach ($selectedItems as $index => $item) {
            if ($item['qty'] > $item['available_qty']) {
                $this->dispatch('show-toast', ['type' => 'error', 'message' => "La cantidad de {$item['description']} supera la cantidad disponible."]);
                return;
            }
            if (empty(trim($item['failure']))) {
                $this->dispatch('show-toast', ['type' => 'error', 'message' => "Debe ingresar una descripción de la falla para {$item['description']}."]);
                return;
            }
            if (empty(trim($item['request']))) {
                $this->dispatch('show-toast', ['type' => 'error', 'message' => "Debe ingresar qué solicita el cliente para {$item['description']}."]);
                return;
            }
        }

        try {
            DB::connection('tenant')->beginTransaction();

            // Generar Consecutivo GAR-XXXX
            $lastWarranty = VntWarranty::orderBy('id', 'desc')->first();
            $nextNumber = $lastWarranty ? intval(substr($lastWarranty->consecutive, 4)) + 1 : 1;
            $consecutive = 'GAR-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // Crear Cabecera de Garantía
            $warranty = VntWarranty::create([
                'remission_id' => $this->remissionId,
                'consecutive' => $consecutive,
                'user_id' => Auth::id(),
                'status' => 1, // Pendiente Admin
            ]);

            // Crear Detalle e insertar evidencias
            foreach ($this->items as $index => $item) {
                if ($item['isSelected'] && $item['qty'] > 0) {
                    $warrantyItem = VntWarrantyItem::create([
                        'warranty_id' => $warranty->id,
                        'item_id' => $item['item_id'],
                        'quantity' => $item['qty'],
                        'failure_description' => $item['failure'],
                        'client_request' => $item['request'],
                    ]);

                    // Guardar archivos de evidencia
                    if (!empty($this->tempEvidences[$index])) {
                        foreach ($this->tempEvidences[$index] as $file) {
                            $path = $file->store('warranties/evidences', 'public');
                            $extension = strtolower($file->getClientOriginalExtension());
                            $fileType = in_array($extension, ['mp4', 'mov', 'avi', '3gp', 'webm']) ? 'video' : 'image';

                            VntWarrantyEvidence::create([
                                'warranty_item_id' => $warrantyItem->id,
                                'file_path' => $path,
                                'file_type' => $fileType,
                            ]);
                        }
                    }
                }
            }

            DB::connection('tenant')->commit();

            session()->flash('success', "Garantía {$consecutive} creada con éxito.");
            return redirect()->route('tenant.warranties');

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error al registrar: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.tenant.warranties.warranty-create', [
            'remission' => $this->remission
        ])->layout('layouts.app');
    }
}
