<?php

namespace App\Traits\Livewire;

use App\Models\Tenant\Parameters\CnfButtons;
use Livewire\Attributes\Computed;

trait HasDynamicButtons
{
    /**
     * Define el identificador del módulo en el componente que use el trait.
     * Ejemplo en el componente: public $moduleKey = 'products';
     */

    #[Computed]
    public function dynamicButtons()
    {
        // Si no se define moduleKey en el componente, retornamos una colección vacía
        if (!property_exists($this, 'moduleKey') || empty($this->moduleKey)) {
            return collect();
        }

        return CnfButtons::where('module', $this->moduleKey)
            ->where('status', 1)
            ->get();
    }

    /**
     * Retorna las clases de Tailwind según el color guardado en BD
     */
    public function getDynamicButtonClasses($color)
    {
        return match(strtolower($color)) {
            'azul'     => 'bg-blue-600 hover:bg-blue-700 text-white',
            'verde'    => 'bg-green-600 hover:bg-green-700 text-white',
            'amarillo' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
            'rojo'     => 'bg-red-600 hover:bg-red-700 text-white',
            'gris'     => 'bg-gray-600 hover:bg-gray-700 text-white',
            'morado'   => 'bg-purple-600 hover:bg-purple-700 text-white',
            default    => 'bg-indigo-600 hover:bg-indigo-700 text-white',
        };
    }
}
