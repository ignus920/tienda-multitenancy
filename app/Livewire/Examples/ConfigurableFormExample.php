<?php

namespace App\Livewire\Examples;

use App\Traits\HasCompanyConfiguration;
use Livewire\Component;

/**
 * Ejemplo de cómo usar el trait HasCompanyConfiguration en tus componentes Livewire
 */
class ConfigurableFormExample extends Component
{
    use HasCompanyConfiguration;

    // Propiedades del formulario
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public bool $isActive = true;

    // Configuración del módulo
    protected string $moduleName = 'VENTAS'; // Nombre del módulo en tu BD

    public function mount()
    {
        // SIEMPRE inicializar la configuración en mount()
        $this->initializeCompanyConfiguration();

        // DEBUG: Mostrar información para depuración
        \Log::info('🔍 DEBUG ConfigurableFormExample', [
            'currentCompanyId' => $this->currentCompanyId,
            'currentPlainId' => $this->currentPlainId,
            'moduleName' => $this->moduleName,
            'user' => auth()->user()->id ?? 'no-auth',
        ]);
    }

    /**
     * Ejemplo: Obtener campos visibles según configuración
     */
    public function getVisibleFields(): array
    {
        // Usar las opciones reales de tu base de datos para VENTAS
        $allFields = ['cotiza', 'imprime', 'impuestos', 'clientes y proveedores', 'vendedores'];
        $visibleFields = [];

        foreach ($allFields as $field) {
            if ($this->shouldShowField($this->moduleName, $field)) {
                $visibleFields[] = $field;
            }
        }

        return $visibleFields;
    }

    /**
     * Ejemplo: Validación dinámica según configuración
     */
    public function save()
    {
        // Reglas base
        $baseRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'isActive' => ['boolean'],
        ];

        // Filtrar reglas según configuración
        $validatedRules = $this->validateFormFields($this->moduleName, $baseRules);

        // Validar solo campos que deben mostrarse
        $this->validate($validatedRules);

        // Filtrar datos según configuración
        $dataToSave = $this->filterDataByConfiguration($this->moduleName, [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'is_active' => $this->isActive,
        ]);

        // Guardar en BD...
        // Model::create($dataToSave);

        session()->flash('message', 'Usuario guardado exitosamente.');
    }

    /**
     * Ejemplo: Obtener configuración específica
     */
    public function getFieldLabel(string $field): string
    {
        // Obtener etiqueta personalizada desde configuración
        $customLabel = $this->getConfigValue($this->moduleName, "label_{$field}");

        return $customLabel ?: ucfirst($field);
    }

    /**
     * Ejemplo: Verificar permisos específicos
     */
    public function canEditField(string $field): bool
    {
        return $this->shouldShowField($this->moduleName, "edit_{$field}");
    }

    public function render()
    {
        return view('livewire.examples.configurable-form-example', [
            'visibleFields' => $this->getVisibleFields(),
            'moduleConfig' => $this->getModuleConfig($this->moduleName),
        ]);
    }
}