<?php

namespace App\Livewire\Tenant\Items;

use Livewire\Component;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\ItemDynamicAttribute;
use Illuminate\Support\Facades\DB;

class ItemDynamicAttributes extends Component
{
    public $itemId;
    public $dynamicFields = [];
    
    // Para agregar nuevo campo
    public $newLabel = '';
    public $newType = 'text';
    public $newOptions = '';
    
    // Para clonar de otro item
    public $cloneItemId = null;
    public $availableItems = [];

    protected $listeners = ['saveDynamicAttributes' => 'save'];

    public function mount($itemId)
    {
        $this->itemId = $itemId;
        $this->loadAttributes();
        $this->loadAvailableItems();
    }

    public function loadAttributes()
    {
        if (!$this->itemId) return;

        $dbAttrs = ItemDynamicAttribute::where('item_id', $this->itemId)
            ->orderBy('order_index')
            ->get();

        $this->dynamicFields = [];
        foreach ($dbAttrs as $attr) {
            $this->dynamicFields[] = [
                'id' => $attr->id,
                'label' => $attr->label,
                'field_type' => $attr->field_type,
                'options' => $attr->options,
                'value' => $attr->value,
                'options_array' => $attr->options_array,
                'order_index' => $attr->order_index,
            ];
        }
    }

    public function loadAvailableItems()
    {
        // Traer items importados que tengan atributos dinámicos
        $this->availableItems = Items::whereIn('type', ['IMPORTADO', 'CZCL', 'DESCONTINUADOS'])
            ->whereHas('dynamicAttributes')
            ->where('id', '!=', $this->itemId)
            ->select('id', 'name', 'sku')
            ->get()
            ->toArray();
    }

    public function addField()
    {
        $this->validate([
            'newLabel' => 'required|string|max:255',
            'newType' => 'required|in:text,textarea,select',
        ]);

        if ($this->newType === 'select' && empty(trim($this->newOptions))) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Debe ingresar las opciones separadas por coma para el campo select.']);
            return;
        }

        ItemDynamicAttribute::create([
            'item_id' => $this->itemId,
            'label' => trim($this->newLabel),
            'field_type' => $this->newType,
            'options' => $this->newType === 'select' ? trim($this->newOptions) : null,
            'value' => null,
            'order_index' => count($this->dynamicFields)
        ]);

        $this->reset(['newLabel', 'newType', 'newOptions']);
        $this->loadAttributes();
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Campo agregado exitosamente.']);
    }

    public function deleteField($id)
    {
        ItemDynamicAttribute::where('id', $id)->where('item_id', $this->itemId)->delete();
        $this->loadAttributes();
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Campo eliminado.']);
    }

    public function cloneAttributes()
    {
        if (!$this->cloneItemId) return;

        $sourceAttrs = ItemDynamicAttribute::where('item_id', $this->cloneItemId)->get();
        if ($sourceAttrs->isEmpty()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'El item seleccionado no tiene formulario.']);
            return;
        }

        DB::connection('tenant')->beginTransaction();
        try {
            // Eliminar los actuales
            ItemDynamicAttribute::where('item_id', $this->itemId)->delete();

            // Clonar
            foreach ($sourceAttrs as $attr) {
                ItemDynamicAttribute::create([
                    'item_id' => $this->itemId,
                    'label' => $attr->label,
                    'field_type' => $attr->field_type,
                    'options' => $attr->options,
                    'value' => null, // Dejamos el valor vacío
                    'order_index' => $attr->order_index
                ]);
            }
            DB::connection('tenant')->commit();

            $this->cloneItemId = null;
            $this->loadAttributes();
            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Formulario clonado correctamente.']);
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error al clonar.']);
        }
    }

    public function save()
    {
        // Guardar todos los valores de los atributos actuales
        foreach ($this->dynamicFields as $attr) {
            if (isset($attr['id'])) {
                ItemDynamicAttribute::where('id', $attr['id'])->update([
                    'value' => $attr['value'] ?? null
                ]);
            }
        }
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Valores del formulario guardados.']);
    }

    public function render()
    {
        return view('livewire.tenant.items.item-dynamic-attributes');
    }
}
