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
    public $newType = 'single_product';
    
    // Búsqueda en el creador para 'single_product' (Fijo) y 'multiple_products' (Dinámico)
    public $newFixedSearch = '';
    public $newFixedResults = [];
    public $newFixedSelected = null;
    public $newMultipleSelected = [];
    
    // Buscadores por cada atributo
    public $searchQueries = [];
    public $searchResults = [];
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
            $value = $attr->value;
            // Parsear JSON si es multiple_products
            $options = $attr->options;
            if ($attr->field_type === 'multiple_products' && !empty($options) && is_string($options)) {
                $options = json_decode($options, true) ?? [];
            }
            if ($attr->field_type === 'multiple_products' && !empty($value) && is_string($value)) {
                $value = json_decode($value, true) ?? null;
            }
            // Parsear JSON si es single_product
            if ($attr->field_type === 'single_product' && !empty($value) && is_string($value)) {
                $value = json_decode($value, true) ?? null;
            }

            $this->dynamicFields[] = [
                'id' => $attr->id,
                'label' => $attr->label,
                'field_type' => $attr->field_type,
                'options' => $options,
                'value' => $value,
                'order_index' => $attr->order_index,
            ];
            
            $this->searchQueries[$attr->id] = '';
            $this->searchResults[$attr->id] = [];
        }
    }

    private function ensureTenantDb()
    {
        $tenantId = session('tenant_id');

        if ($tenantId) {
            $tenant = \App\Models\Auth\Tenant::find($tenantId);
            if ($tenant) {
                // Establecer conexión tenant
                $tenantManager = app(\App\Services\Tenant\TenantManager::class);
                $tenantManager->setConnection($tenant);
                
                // Inicializar tenancy
                if (function_exists('tenancy')) {
                    tenancy()->initialize($tenant);
                }
            }
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

    public function updatedNewFixedSearch($value)
    {
        if (strlen($value) < 2) {
            $this->newFixedResults = [];
            return;
        }

        $this->ensureTenantDb();

        $words = explode(' ', $value);
        $query = Items::query()->active()->where('type', '!=', 'ENSAMBLADO');

        foreach ($words as $word) {
            if (!empty(trim($word))) {
                $query->where(function ($q) use ($word) {
                    $q->where('name', 'LIKE', '%' . $word . '%')
                      ->orWhere('internal_code', 'LIKE', '%' . $word . '%')
                      ->orWhere('sku', 'LIKE', '%' . $word . '%');
                });
            }
        }

        $this->newFixedResults = $query->take(10)->get()->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->internal_code ?? $item->sku,
            ];
        })->toArray();
    }

    public function selectNewFixedProduct($id, $name, $code)
    {
        if ($this->newType === 'single_product') {
            $this->newFixedSelected = [
                'id' => $id,
                'name' => $name,
                'code' => $code,
                'qty' => 1
            ];
            $this->newFixedSearch = '';
            $this->newFixedResults = [];
        } elseif ($this->newType === 'multiple_products') {
            $exists = collect($this->newMultipleSelected)->contains('id', $id);
            if (!$exists) {
                $this->newMultipleSelected[] = [
                    'id' => $id,
                    'name' => $name,
                    'code' => $code,
                    'qty' => 1
                ];
            }
            $this->newFixedSearch = '';
            $this->newFixedResults = [];
        }
    }

    public function removeNewFixedProduct()
    {
        $this->newFixedSelected = null;
    }

    public function removeNewMultipleProduct($id)
    {
        $this->newMultipleSelected = array_values(array_filter($this->newMultipleSelected, function($item) use ($id) {
            return $item['id'] !== $id;
        }));
    }

    public function addField()
    {
        $this->validate([
            'newLabel' => 'required|string|max:255',
            'newType' => 'required|in:single_product,multiple_products,textarea',
        ]);

        if ($this->newType === 'single_product' && empty($this->newFixedSelected)) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Debe seleccionar un producto del ERP para crear este campo fijo.']);
            return;
        }

        if ($this->newType === 'multiple_products' && empty($this->newMultipleSelected)) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Debe seleccionar al menos un producto para crear este campo dinámico.']);
            return;
        }

        $this->ensureTenantDb();
        
        $valueToSave = null;
        $optionsToSave = null;
        if ($this->newType === 'single_product') {
            $valueToSave = json_encode($this->newFixedSelected);
        } elseif ($this->newType === 'multiple_products') {
            $optionsToSave = json_encode($this->newMultipleSelected);
        }

        DB::connection('tenant')->table('inv_item_dynamic_attributes')->insert([
            'item_id' => $this->itemId,
            'label' => trim($this->newLabel),
            'field_type' => $this->newType,
            'options' => $optionsToSave,
            'value' => $valueToSave,
            'order_index' => count($this->dynamicFields),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->reset(['newLabel', 'newType', 'newFixedSelected', 'newFixedSearch', 'newFixedResults', 'newMultipleSelected']);
        $this->newType = 'single_product';
        $this->loadAttributes();
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Campo agregado exitosamente.']);
    }

    public function deleteField($id)
    {
        $this->ensureTenantDb();
        DB::connection('tenant')->table('inv_item_dynamic_attributes')
            ->where('id', $id)
            ->where('item_id', $this->itemId)
            ->delete();
        $this->loadAttributes();
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Campo eliminado.']);
    }

    public function updateFieldOrder($oldIndex, $newIndex)
    {
        if (!isset($this->dynamicFields[$oldIndex]) || !isset($this->dynamicFields[$newIndex])) {
            return;
        }

        $this->ensureTenantDb();

        $item = array_splice($this->dynamicFields, $oldIndex, 1)[0];
        array_splice($this->dynamicFields, $newIndex, 0, [$item]);

        foreach ($this->dynamicFields as $index => $field) {
            DB::connection('tenant')->table('inv_item_dynamic_attributes')
                ->where('id', $field['id'])
                ->update(['order_index' => $index]);
            
            $this->dynamicFields[$index]['order_index'] = $index;
        }
    }

    public function cloneAttributes()
    {
        if (!$this->cloneItemId) return;

        $sourceAttrs = ItemDynamicAttribute::where('item_id', $this->cloneItemId)->get();
        if ($sourceAttrs->isEmpty()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'El item seleccionado no tiene formulario.']);
            return;
        }

        $this->ensureTenantDb();
        DB::connection('tenant')->beginTransaction();
        try {
            // Eliminar los actuales
            DB::connection('tenant')->table('inv_item_dynamic_attributes')->where('item_id', $this->itemId)->delete();

            // Clonar
            $inserts = [];
            foreach ($sourceAttrs as $attr) {
                $inserts[] = [
                    'item_id' => $this->itemId,
                    'label' => $attr->label,
                    'field_type' => $attr->field_type,
                    'options' => $attr->options,
                    'value' => null,
                    'order_index' => $attr->order_index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (count($inserts) > 0) {
                DB::connection('tenant')->table('inv_item_dynamic_attributes')->insert($inserts);
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
        $this->ensureTenantDb();
        // Guardar todos los valores de los atributos actuales
        foreach ($this->dynamicFields as $attr) {
            if (isset($attr['id'])) {
                $valueToSave = $attr['value'];
                
                // Si es un producto unico o multiple, guardamos como JSON
                if ($attr['field_type'] === 'single_product' || $attr['field_type'] === 'multiple_products') {
                    if (is_string($valueToSave) && !empty($valueToSave)) {
                        $decoded = json_decode($valueToSave, true);
                        $valueToSave = $decoded ?? $valueToSave;
                    }
                    $valueToSave = empty($valueToSave) ? null : json_encode($valueToSave);
                }

                DB::connection('tenant')->table('inv_item_dynamic_attributes')
                    ->where('id', $attr['id'])
                    ->update([
                        'value' => $valueToSave,
                        'updated_at' => now(),
                    ]);
            }
        }
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Valores del formulario guardados.']);
    }

    public function updatedSearchQueries($value, $key)
    {
        $this->searchProducts($key);
    }

    // NOTA: Para múltiples productos, el end-user NO usa este buscador ya que selecciona desde el select, 
    // pero mantenemos los métodos en caso de necesitarlos para otras dinámicas.
    public function searchProducts($attrId)
    {
        $term = $this->searchQueries[$attrId] ?? '';
        
        if (strlen($term) < 2) {
            $this->searchResults[$attrId] = [];
            return;
        }

        $this->ensureTenantDb();

        $words = explode(' ', $term);
        $query = Items::query()->active()->where('type', '!=', 'ENSAMBLADO');

        foreach ($words as $word) {
            if (!empty(trim($word))) {
                $query->where(function ($q) use ($word) {
                    $q->where('name', 'LIKE', '%' . $word . '%')
                      ->orWhere('internal_code', 'LIKE', '%' . $word . '%')
                      ->orWhere('sku', 'LIKE', '%' . $word . '%');
                });
            }
        }

        $this->searchResults[$attrId] = $query->take(20)->get()->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->internal_code ?? $item->sku,
                'price' => $item->price_regular ?? 0,
            ];
        })->toArray();
    }

    public function selectProduct($attrId, $productId, $productName, $productCode)
    {
        // Encontrar el índice en dynamicFields
        $index = collect($this->dynamicFields)->search(fn($item) => $item['id'] == $attrId);
        
        if ($index !== false) {
            $productData = [
                'id' => $productId,
                'name' => $productName,
                'code' => $productCode
            ];

            if ($this->dynamicFields[$index]['field_type'] === 'single_product') {
                $this->dynamicFields[$index]['value'] = $productData;
            } else if ($this->dynamicFields[$index]['field_type'] === 'multiple_products') {
                $currentValues = is_array($this->dynamicFields[$index]['value']) ? $this->dynamicFields[$index]['value'] : [];
                
                // Evitar duplicados
                $exists = collect($currentValues)->contains('id', $productId);
                if (!$exists) {
                    $currentValues[] = $productData;
                    $this->dynamicFields[$index]['value'] = $currentValues;
                }
            }
        }

        // Limpiar búsqueda
        $this->searchQueries[$attrId] = '';
        $this->searchResults[$attrId] = [];
    }

    public function removeProduct($attrId, $productId)
    {
        $index = collect($this->dynamicFields)->search(fn($item) => $item['id'] == $attrId);
        
        if ($index !== false) {
            if ($this->dynamicFields[$index]['field_type'] === 'single_product') {
                $this->dynamicFields[$index]['value'] = null;
            } else if ($this->dynamicFields[$index]['field_type'] === 'multiple_products') {
                $currentValues = is_array($this->dynamicFields[$index]['value']) ? $this->dynamicFields[$index]['value'] : [];
                $this->dynamicFields[$index]['value'] = array_values(array_filter($currentValues, fn($item) => $item['id'] != $productId));
            }
        }
    }

    public function render()
    {
        return view('livewire.tenant.items.item-dynamic-attributes');
    }
}
