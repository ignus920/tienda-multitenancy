<?php

namespace App\Services\Tenant\CostCalculation;

use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\Brand;

class PowerSupplyCalculatorService
{
    /**
     * Category ID that identifies Power Supplies in the system.
     * We'll need to parameterize this or search for it.
     */
    protected $powerSupplyCategoryName = 'FUENTES'; // Or we can search dynamically by name

    public function calculate($lines)
    {
        $productsToPower = [];
        
        // 1. Validar y recolectar productos que requieren cálculo
        foreach ($lines as $line) {
            // Ignorar productos externos o sin ID del ERP
            if (empty($line['item_id']) || $line['origin'] !== 'erp') {
                continue;
            }

            $item = Items::with('dimensions')->find($line['item_id']);
            if (!$item) continue;

            $dimensions = $item->dimensions;
            if (!$dimensions) {
                // No tiene dimensiones
                continue;
            }

            // Validar que el producto NO sea una fuente de poder (las fuentes no consumen energía, la proveen)
            $itemName = strtoupper($item->name);
            if (str_contains($itemName, 'FUENTE') || str_contains($itemName, 'DRIVER') || str_contains($itemName, 'TRANSFORMADOR')) {
                continue;
            }

            $voltage = floatval($dimensions->voltage);
            $power = floatval($dimensions->power);

            // Si el producto no tiene voltaje o potencia, quizás no es un producto que consuma energía
            // Sin embargo, si es una cinta LED o módulo, debería tenerlo.
            if ($voltage > 0 && $power > 0) {
                // Calcular cantidad real (Metros o Unidades)
                $qty = 0;
                if (isset($line['mode']) && $line['mode'] === 'cm') {
                    // Si se cobra por cm, la cantidad en metros es cm / 100
                    $qty = floatval($line['cm_quantity']) / 100;
                } else {
                    $qty = floatval($line['quantity']);
                }

                if ($qty <= 0) {
                    return [
                        'status' => 'error',
                        'message' => "El producto {$item->internal_code} tiene cantidad 0 o inválida."
                    ];
                }

                $productsToPower[] = [
                    'item' => $item,
                    'voltage' => $voltage,
                    'power' => $power,
                    'qty' => $qty,
                    'total_power' => $power * $qty
                ];
            } else {
                // Validación "A prueba de tontos": Si el producto no tiene voltaje o potencia, pero por su nombre sabemos que debería tenerlo
                if (str_contains($itemName, 'CINTA') || str_contains($itemName, 'LED') || str_contains($itemName, 'MODULO') || str_contains($itemName, 'NEON') || str_contains($itemName, 'MANGUERA')) {
                    return [
                        'status' => 'error',
                        'message' => "El producto '{$item->internal_code} - {$item->name}' es un artículo de iluminación, pero NO tiene su Voltaje o Potencia configurados. Por favor parametrice estos valores numéricos en la pestaña 'Medidas' de su ficha técnica para poder calcular."
                    ];
                }
            }
        }

        if (empty($productsToPower)) {
            return [
                'status' => 'error',
                'message' => 'No se encontraron productos en la lista que requieran alimentación (sin voltaje ni potencia registrados en Medidas).'
            ];
        }

        // 2. Agrupar por Voltaje
        $groupedByVoltage = [];
        foreach ($productsToPower as $prod) {
            $v = (string)$prod['voltage'];
            if (!isset($groupedByVoltage[$v])) {
                $groupedByVoltage[$v] = [
                    'voltage' => $prod['voltage'],
                    'installed_power' => 0
                ];
            }
            $groupedByVoltage[$v]['installed_power'] += $prod['total_power'];
        }

        // Cargar marcas (Brand) para mapear nombres
        $brands = Brand::pluck('name', 'id')->toArray();

        $results = [];

        // 4. Calcular alternativas para cada grupo de voltaje
        foreach ($groupedByVoltage as $group) {
            $voltage = $group['voltage'];
            $installedPower = $group['installed_power'];
            $requiredPower = $installedPower * 1.20; // 20% margen

            // Buscar fuentes de este voltaje por NOMBRE y no por categoría
            // Hacemos join con inv_items_dimensions para filtrar por voltaje y obtener potencia
            $sources = Items::select('inv_items.*', 'inv_items_dimensions.power as source_power', 'inv_items_dimensions.voltage as source_voltage')
                ->join('inv_items_dimensions', 'inv_items.id', '=', 'inv_items_dimensions.item_id')
                ->where(function($q) {
                    $q->where('inv_items.name', 'like', '%FUENTE%')
                      ->orWhere('inv_items.name', 'like', '%DRIVER%')
                      ->orWhere('inv_items.name', 'like', '%TRANSFORMADOR%');
                })
                ->where('inv_items.status', 1)
                ->where('inv_items_dimensions.voltage', $voltage)
                ->where('inv_items_dimensions.power', '>', 0)
                ->get();

            if ($sources->isEmpty()) {
                return [
                    'status' => 'error',
                    'message' => "No se encontraron fuentes de alimentación compatibles para el voltaje de {$voltage}V registradas en el inventario."
                ];
            }

            // Agrupar fuentes disponibles por marca (brandId)
            $sourcesByBrand = [];
            foreach ($sources as $source) {
                $bId = $source->brandId ?: 'generica'; // Si es nulo, agrupar como genérica
                if (!isset($sourcesByBrand[$bId])) {
                    $sourcesByBrand[$bId] = [];
                }
                $sourcesByBrand[$bId][] = $source;
            }

            $brandAlternatives = [];

            foreach ($sourcesByBrand as $bId => $brandSources) {
                $brandName = $bId === 'generica' ? 'Genérica' : ($brands[$bId] ?? 'Desconocida');
                
                // Ordenar fuentes de la marca por potencia ascendente
                $brandSources = collect($brandSources)->sortBy('source_power')->values();

                $options = [];

                if ($requiredPower <= 450) {
                    // Buscar 1 fuente individual >= requiredPower
                    $bestSource = $brandSources->first(function($src) use ($requiredPower) {
                        return floatval($src->source_power) >= $requiredPower;
                    });

                    if ($bestSource) {
                        $options[] = [
                            'item_id' => $bestSource->id,
                            'type' => 1,
                            'code' => $bestSource->internal_code ?: $bestSource->sku,
                            'quantity' => 1,
                            'unit_power' => floatval($bestSource->source_power),
                            'total_power' => floatval($bestSource->source_power)
                        ];
                    }
                } else {
                    // Requerimiento > 450W
                    // Opción 1: Buscar fuente individual grande
                    $bestSource = $brandSources->first(function($src) use ($requiredPower) {
                        return floatval($src->source_power) >= $requiredPower;
                    });
                    if ($bestSource) {
                        $options[] = [
                            'item_id' => $bestSource->id,
                            'type' => 1,
                            'code' => $bestSource->internal_code ?: $bestSource->sku,
                            'quantity' => 1,
                            'unit_power' => floatval($bestSource->source_power),
                            'total_power' => floatval($bestSource->source_power)
                        ];
                    }

                    // Opción 2: Buscar 2 fuentes
                    // Potencia de cada fuente debe ser >= requiredPower / 2
                    $halfPower = $requiredPower / 2;
                    $bestPairSource = $brandSources->first(function($src) use ($halfPower) {
                        return floatval($src->source_power) >= $halfPower;
                    });

                    if ($bestPairSource) {
                        $options[] = [
                            'item_id' => $bestPairSource->id,
                            'type' => 2,
                            'code' => $bestPairSource->internal_code ?: $bestPairSource->sku,
                            'quantity' => 2,
                            'unit_power' => floatval($bestPairSource->source_power),
                            'total_power' => floatval($bestPairSource->source_power) * 2
                        ];
                    }
                }

                if (!empty($options)) {
                    $brandAlternatives[] = [
                        'brand_name' => $brandName,
                        'options' => $options
                    ];
                }
            }

            if (empty($brandAlternatives)) {
                return [
                    'status' => 'error',
                    'message' => "Existen fuentes para {$voltage}V pero ninguna (o combinación de dos) tiene potencia suficiente para soportar los " . number_format($requiredPower, 1) . "W requeridos."
                ];
            }

            $results[] = [
                'voltage' => $voltage,
                'installed_power' => $installedPower,
                'margin_power' => $installedPower * 0.20,
                'required_power' => $requiredPower,
                'brands' => $brandAlternatives
            ];
        }

        return [
            'status' => 'success',
            'data' => $results
        ];
    }
}
