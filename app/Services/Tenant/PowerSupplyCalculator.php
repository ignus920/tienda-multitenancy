<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Items\Items;

class PowerSupplyCalculator
{
    /**
     * Calcula y retorna las fuentes o drivers sugeridos para un producto (ej. Cinta LED).
     *
     * @param Items $item Producto base (Cinta, Módulo, etc.)
     * @param float $quantity Cantidad solicitada (metros o unidades)
     * @param int $limit Límite de sugerencias a devolver
     * @return \Illuminate\Support\Collection
     */
    public function getSuggestedPowerSupplies(Items $item, $quantity = 1, $limit = 3)
    {
        // 1. Validar que el producto tenga la pestaña de "Medidas" configurada
        if (!$item->dimensions) {
            return collect(); // Colección vacía si no hay data
        }

        $electricalType = $item->dimensions->electrical_type; // 'CC' o 'CV'
        $voltage = (float) $item->dimensions->voltage;
        $powerPerUnit = (float) $item->dimensions->power; // Potencia por metro o unidad

        // Si faltan parámetros eléctricos obligatorios, abortamos el cálculo
        if ($voltage <= 0 || $powerPerUnit <= 0 || empty($electricalType) || $electricalType === 'N/A') {
            return collect();
        }

        // 2. Calcular la potencia mínima requerida agregando un 20% de margen de seguridad
        $totalPowerRequired = ($quantity * $powerPerUnit) * 1.20;

        // 3. Buscar en la base de datos las fuentes/adaptadores compatibles.
        // Hacemos un JOIN directo con la tabla de dimensiones para poder ordenar por potencia fácilmente.
        $query = Items::query()
            ->select('inv_items.*')
            ->join('inv_items_dimensions', 'inv_items.id', '=', 'inv_items_dimensions.item_id')
            ->where('inv_items.status', 1) // Solo productos activos
            ->where('inv_items.id', '!=', $item->id) // Excluir el producto base
            
            // Reglas eléctricas estrictas:
            ->where('inv_items_dimensions.electrical_type', $electricalType) // CC con CC, CV con CV
            ->where('inv_items_dimensions.voltage', $voltage) // Mismo voltaje (Ej. 12V o 24V)
            ->where('inv_items_dimensions.power', '>=', $totalPowerRequired) // Potencia de la fuente mayor o igual a la requerida
            
            // Filtramos por palabras clave en el nombre para asegurar que solo devuelva fuentes/drivers
            // (Si en un futuro manejan una Categoría específica para esto, se puede cambiar a un 'categoryId' = X)
            ->where(function ($q) {
                $q->where('inv_items.name', 'LIKE', '%FUENTE%')
                  ->orWhere('inv_items.name', 'LIKE', '%DRIVER%')
                  ->orWhere('inv_items.name', 'LIKE', '%ADAPTADOR%')
                  ->orWhere('inv_items.name', 'LIKE', '%TRANSFORMADOR%');
            })
            ->with('dimensions') // Traemos las dimensiones para mostrarlas en la vista
            // 4. ORDENAR DE MENOR A MAYOR: Para sugerir la fuente que esté más cerca de la potencia requerida (evita encarecer cotizaciones)
            ->orderBy('inv_items_dimensions.power', 'asc')
            ->limit($limit);

        return $query->get();
    }
}
