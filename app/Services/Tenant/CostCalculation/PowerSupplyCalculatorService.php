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
            $voltage = $dimensions ? floatval($dimensions->voltage) : 0;
            $electricalType = $dimensions ? $dimensions->electrical_type : null;
            $power = $dimensions ? floatval($dimensions->power) : 0;
            $itemName = strtoupper($item->name);

            // Validar que el producto NO sea una fuente de poder (las fuentes no consumen energía, la proveen)
            if (str_contains($itemName, 'FUENTE') || str_contains($itemName, 'DRIVER') || str_contains($itemName, 'TRANSFORMADOR')) {
                continue;
            }

            // Ignorar productos que van directos a la red eléctrica (110V, 120V, 220V) porque no usan fuente
            if (in_array($voltage, [110, 120, 220, 240])) {
                continue;
            }

            // Validación "A prueba de tontos": Si el producto NO tiene voltaje o potencia, verificamos si es algo que debería tenerlo
            if ($voltage <= 0 || $power <= 0) {
                if (str_contains($itemName, 'CINTA') || str_contains($itemName, 'LED') || str_contains($itemName, 'MODULO') || str_contains($itemName, 'NEON') || str_contains($itemName, 'MANGUERA')) {
                    return [
                        'status' => 'error',
                        'message' => "El producto '{$item->internal_code} - {$item->name}' es un artículo de iluminación, pero NO tiene su Voltaje o Potencia configurados. Por favor parametrice estos valores numéricos en la pestaña 'Medidas' de su ficha técnica para poder calcular."
                    ];
                }
                // Si no es un artículo de iluminación, simplemente lo ignoramos (ej. un tornillo o un cable)
                continue;
            }

            // Si llegamos aquí, es porque SÍ tiene voltaje y potencia válidos

            // Si el producto no tiene voltaje o potencia, quizás no es un producto que consuma energía
            // Sin embargo, si es una cinta LED o módulo, debería tenerlo.
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
                'electrical_type' => $electricalType,
                'power' => $power,
                'qty' => $qty,
                'total_power' => $power * $qty
            ];
        }

        if (empty($productsToPower)) {
            return [
                'status' => 'error',
                'message' => 'No se encontraron productos en la lista que requieran alimentación (sin voltaje ni potencia registrados en Medidas).'
            ];
        }

        // 2. Agrupar por Voltaje y Tipo Eléctrico (CC/VC)
        $groupedByVoltage = [];
        foreach ($productsToPower as $prod) {
            $v = (string)$prod['voltage'];
            $et = (string)$prod['electrical_type'];
            $key = $v . '|' . $et;
            if (!isset($groupedByVoltage[$key])) {
                $groupedByVoltage[$key] = [
                    'voltage' => $prod['voltage'],
                    'electrical_type' => $prod['electrical_type'],
                    'installed_power' => 0,
                    'theoretical_intensity' => 0
                ];
            }
            $groupedByVoltage[$key]['installed_power'] += $prod['total_power'];
            
            // Sumar la intensidad teórica (Lm/m * cantidad)
            $lmm = floatval($prod['item']->dimensions->lumens_per_meter ?? 0);
            $groupedByVoltage[$key]['theoretical_intensity'] += ($lmm * $prod['qty']);
        }

        // Cargar Grupos Comerciales para mapear nombres
        $commercialGroups = \Illuminate\Support\Facades\DB::connection('tenant')
            ->table('inv_commercial_groups')
            ->pluck('name', 'id')
            ->toArray();

        $results = [];

        // 4. Calcular alternativas para cada grupo de voltaje y tipo eléctrico
        foreach ($groupedByVoltage as $group) {
            $voltage = $group['voltage'];
            $electricalType = $group['electrical_type'];
            $installedPower = $group['installed_power'];
            $requiredPower = $installedPower * 1.20; // 20% margen

            // Buscar fuentes de este voltaje y tipo eléctrico por NOMBRE
            $sources = Items::select('inv_items.*', 'inv_items_dimensions.power as source_power', 'inv_items_dimensions.voltage as source_voltage')
                ->with(['invValues'])
                ->join('inv_items_dimensions', 'inv_items.id', '=', 'inv_items_dimensions.item_id')
                ->where(function($q) {
                    $q->where('inv_items.name', 'like', '%FUENTE%')
                      ->orWhere('inv_items.name', 'like', '%DRIVER%')
                      ->orWhere('inv_items.name', 'like', '%TRANSFORMADOR%');
                })
                ->where('inv_items.status', 1)
                ->where('inv_items_dimensions.voltage', $voltage)
                ->where('inv_items_dimensions.power', '>', 0)
                ->when($electricalType, function($query, $electricalType) {
                    return $query->where('inv_items_dimensions.electrical_type', $electricalType);
                })
                ->get();

            if ($sources->isEmpty()) {
                $tipoEtiqueta = $electricalType ? " ($electricalType)" : '';
                return [
                    'status' => 'error',
                    'message' => "No se encontraron fuentes de alimentación compatibles registradas para {$voltage}V{$tipoEtiqueta}."
                ];
            }

            // Agrupar fuentes disponibles por Grupo Comercial (commercial_group_id)
            $sourcesByGroup = [];
            foreach ($sources as $source) {
                $gId = $source->commercial_group_id ?: 'sin_clasificar'; // Si es nulo, agrupar como sin clasificar
                if (!isset($sourcesByGroup[$gId])) {
                    $sourcesByGroup[$gId] = [];
                }
                $sourcesByGroup[$gId][] = $source;
            }

            $brandAlternatives = [];

            foreach ($sourcesByGroup as $gId => $brandSources) {
                $brandName = $gId === 'sin_clasificar' ? 'Otras Fuentes' : ($commercialGroups[$gId] ?? 'Desconocida');
                
                // Ordenar fuentes del grupo por potencia ascendente
                $brandSources = collect($brandSources)->sortBy('source_power')->values();

                $options = []; 

                if ($requiredPower <= 450) {
                    $bestSource = $brandSources->first(function($src) use ($requiredPower) {
                        return floatval($src->source_power) >= $requiredPower;
                    });

                    if ($bestSource) {
                        $unitPrice = 0;
                        foreach ($bestSource->all_prices as $k => $v) {
                            if (strtoupper($k) === 'LISTA') {
                                $unitPrice = $v;
                                break;
                            }
                        }

                        $options[] = [
                            'item_id' => $bestSource->id,
                            'code' => $bestSource->internal_code ?: $bestSource->sku,
                            'description' => $bestSource->description,
                            'stock' => $bestSource->stock_disponible_venta,
                            'quantity' => 1,
                            'unit_power' => floatval($bestSource->source_power),
                            'total_power' => floatval($bestSource->source_power),
                            'unit_price' => $unitPrice
                        ];
                    }
                } else {
                    // Requerimiento > 450W: Solo sugerimos obligatoriamente 2 fuentes (se divide la carga)
                    $halfPower = $requiredPower / 2;
                    $bestPairSource = $brandSources->first(function($src) use ($halfPower) {
                        return floatval($src->source_power) >= $halfPower;
                    });

                    if ($bestPairSource) {
                        $unitPrice = 0;
                        foreach ($bestPairSource->all_prices as $k => $v) {
                            if (strtoupper($k) === 'LISTA') {
                                $unitPrice = $v;
                                break;
                            }
                        }

                        $options[] = [
                            'item_id' => $bestPairSource->id,
                            'code' => $bestPairSource->internal_code ?: $bestPairSource->sku,
                            'description' => $bestPairSource->description,
                            'stock' => $bestPairSource->stock_disponible_venta,
                            'quantity' => 2,
                            'unit_power' => floatval($bestPairSource->source_power),
                            'total_power' => floatval($bestPairSource->source_power) * 2,
                            'unit_price' => $unitPrice
                        ];
                    }
                }

                if (!empty($options)) {
                    $brandAlternatives[] = [
                        'brand_name' => $brandName,
                        'options' => $options // Siempre tendrá 1 solo elemento ahora
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
                'theoretical_intensity' => $group['theoretical_intensity'],
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
