<?php

namespace App\Services\Facturacion;

/**
 * IMPORTANTE: Todos los IDs enviados a la API de Alegra deben ser api_data_id o id_alegra,
 * NUNCA los IDs locales de la base de datos.
 *
 * Orden de prioridad para IDs:
 * 1. api_data_id (campo principal para sincronización con APIs externas)
 * 2. id_alegra (campo legacy para Alegra)
 * 3. Otros campos específicos como fallback
 */

use App\Models\Tenant\Quoter\VntQuote;
use App\Models\Auth\Tenant;
use App\Models\Tenant\Items\Items;
use App\Models\Central\VntContact;
use App\Models\Tenant\CnfInvoice;
use App\Services\Tenant\RetentionCalculatorService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class InvoiceDataBuilder
{
    /**
     * Construir datos para factura desde una cotización
     * Traduce la lógica JavaScript del modal de facturación a PHP
     *
     * @param VntQuote $quote Cotización a facturar
     * @param array $paymentMethods Métodos de pago (equivalente a formasPagoArray)
     * @param array $retentions Retenciones aplicadas (opcional, se calcularán automáticamente si están vacías)
     * @param int $termDays Días de término para fecha de vencimiento
     * @param bool $calculateRetentions Si debe calcular las retenciones automáticamente
     * @return array Datos formateados para la API de Alegra
     */
    public static function buildFromQuote(
        VntQuote $quote,
        array $paymentMethods = [],
        array $retentions = [],
        int $termDays = 0,
        bool $calculateRetentions = true,
        ?string $sellerApiId = null   // ID de Alegra del vendedor (override del usuario autenticado)
    ): array {
        // Fecha actual en formato YYYY-MM-DD (equivalente al JavaScript)
        $date = now()->format('Y-m-d');

        // Calcular fecha de vencimiento sumando termDays a la fecha actual
        $dueDate = now()->addDays($termDays)->format('Y-m-d');

        Log::info('🏗️ Construyendo datos de factura desde cotización', [
            'quote_id' => $quote->id,
            'consecutive' => $quote->consecutive,
            'term_days' => $termDays,
            'due_date' => $dueDate
        ]);

        // Construir items de Alegra desde los detalles de la cotización
        $itemsAlegra = self::buildItemsFromQuoteDetails($quote);

        // Obtener datos del cliente (equivalente a getGlobalProduct en JS)
        $customerData = self::getCustomerDataForInvoice($quote);

        // Obtener ID del vendedor: usar el override si se proporcionó (ej. desde remisión),
        // o el usuario autenticado como fallback
        $sellerIdAlegra = $sellerApiId ?? self::getSellerIdFromCurrentUser();

        // Procesar métodos de pago (traducir lógica del for loop de JS)
        $paymentData = self::processPaymentMethods($paymentMethods);

        // Calcular retenciones automáticamente si están vacías y se solicita calcularlas
        if ($calculateRetentions && empty($retentions)) {
            $retentions = self::calculateRetentionsForQuote($quote);
            Log::info('🧮 Retenciones calculadas automáticamente', [
                'quote_id' => $quote->id,
                'retentions' => $retentions
            ]);
        }

        // Validar retenciones (solo las válidas con amount > 0)
        $validRetentions = array_filter($retentions, function ($retention) {
            return ($retention['amount'] ?? 0) > 0;
        });

        // Validaciones requeridas (equivalent to JS validations)
        self::validateInvoiceData($customerData, $itemsAlegra);

        // Validación adicional específica para customer
        if (empty($customerData['id_alegra'])) {
            throw new \Exception('El cliente no tiene ID de Alegra configurado. Debe sincronizar el cliente con Alegra primero.');
        }

        // Construir objeto completo para Alegra (equivalente al dataAlegra en JS)
        $dataAlegra = [
            'date' => $date,
            'dueDate' => $dueDate,
            'client' => [
                'id' => (string)$customerData['id_alegra']
            ],
            'status' => 'open',
            'numberTemplate' => [
                'id' => $customerData['numeracion'] ?? $customerData['warehouse_format'] ?? '16'
            ],
            'paymentForm' => $paymentData['paymentForm'],
            // 'paymentMethod' => 'CASH',
            'items' => $itemsAlegra,
            'stamp' => [
                'generateStamp' => false
            ],
            'warehouse' => (string)($customerData['warehouse_id_alegra'] ?? '1')
        ];

        // Agregar paymentMethod solo si es CASH (igual que el JS)
        if ($paymentData['paymentForm'] === 'CASH' && !empty($paymentData['paymentMethod'])) {
            $dataAlegra['paymentMethod'] = $paymentData['paymentMethod'];
        }

        // Agregar seller si existe
        if ($sellerIdAlegra) {
            $dataAlegra['seller'] = (string)$sellerIdAlegra;
        }

        // Retenciones deshabilitadas: no se envían a Alegra

        Log::info('✅ Datos de factura construidos exitosamente', [
            'quote_id' => $quote->id,
            'items_count' => count($itemsAlegra),
            'payment_form' => $paymentData['paymentForm'],
            'has_seller' => !empty($sellerIdAlegra),
            'has_retentions' => !empty($validRetentions)
        ]);

        // LOGGING COMPLETO DEL dataAlegra PARA VALIDACIÓN
        Log::info('📋 DATA ALEGRA COMPLETO PARA VALIDACIÓN', [
            'dataAlegra' => $dataAlegra
        ]);

        return $dataAlegra;
    }

    /**
     * Construir items de Alegra desde los detalles de la cotización
     * Equivalente al Object.keys(allData).forEach en JavaScript
     */
    protected static function buildItemsFromQuoteDetails(VntQuote $quote): array
    {
        $itemsAlegra = [];

        foreach ($quote->detalles as $detalle) {
            $product = $detalle->item; // Relación ya cargada

            if (!$product) {
                Log::warning('⚠️ Producto no encontrado para detalle', ['detalle_id' => $detalle->id]);
                continue;
            }

            // Solo procesar si tiene id_alegra o api_data_id (equivalente al if (idAlegra) en JS)
            $idAlegra = $product->api_data_id ?? $product->id_alegra ?? $product->alegra_id ?? null;
            if (empty($idAlegra)) {
                Log::warning('⚠️ Producto sin ID de Alegra/api_data_id', [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'api_data_id' => $product->api_data_id ?? 'null',
                    'id_alegra' => $product->id_alegra ?? 'null'
                ]);
                continue;
            }

            // Obtener tax ID desde la relación cnf_taxes (campo api_data_id) — fuente primaria
            $taxIdAlegra = $product->tax?->api_data_id
                ? (string) $product->tax->api_data_id
                : null;

            // Fallback: determinar por porcentaje del detalle usando IDs reales de cnf_taxes
            if (!$taxIdAlegra) {
                $taxPercentage = floatval($detalle->tax ?? 0);

                if ($taxPercentage == 5) {
                    $taxIdAlegra = '3'; // cnf_taxes: Iva 5%  → api_data_id = 3
                } elseif ($taxPercentage == 19) {
                    $taxIdAlegra = '4'; // cnf_taxes: Iva 19% → api_data_id = 4
                } else {
                    $taxIdAlegra = '7'; // cnf_taxes: Exento  → api_data_id = 7
                }

                Log::info('📊 Tax ID determinado por porcentaje (fallback)', [
                    'product_id'     => $product->id,
                    'product_name'   => $product->name ?? 'Sin nombre',
                    'tax_percentage' => $taxPercentage,
                    'tax_id_alegra'  => $taxIdAlegra,
                ]);
            } else {
                Log::info('📊 Tax ID obtenido desde cnf_taxes.api_data_id', [
                    'product_id'       => $product->id,
                    'product_name'     => $product->name ?? 'Sin nombre',
                    'cnf_taxes_id'     => $product->taxId ?? null,
                    'cnf_taxes_api_id' => $taxIdAlegra,
                    'tax_percentage'   => $product->tax?->percentage ?? null,
                ]);
            }

            // Calcular el precio base usando el porcentaje de IVA correcto
            $originalPriceWithIva = floatval($detalle->value ?: 0);
            $taxPercentage = floatval($detalle->tax ?? 0);

            if ($taxPercentage > 0) {
                // Producto con IVA - calcular precio base sin IVA
                $taxMultiplier = 1 + ($taxPercentage / 100);
                $targetSubtotal = round($originalPriceWithIva / $taxMultiplier, 2);
                $finalPrice = $targetSubtotal;

                // Verificación con el IVA correcto
                $verificationTotal = $finalPrice * $taxMultiplier;
            } else {
                // Producto exento de IVA
                $finalPrice = $originalPriceWithIva;
                $verificationTotal = $finalPrice;
                $taxMultiplier = 1;
            }

            Log::info('💰 DETALLE COMPLETO - Cálculo precio para Alegra', [
                'product_id' => $product->id,
                'product_name' => $product->name ?? 'Sin nombre',
                'detalle_value_from_db' => $detalle->value,
                'original_price_with_iva' => $originalPriceWithIva,
                'tax_percentage' => $taxPercentage,
                'tax_multiplier' => $taxMultiplier,
                'raw_division_result' => ($taxPercentage > 0) ? $originalPriceWithIva / $taxMultiplier : $originalPriceWithIva,
                'final_price_after_round' => $finalPrice,
                'verification_total' => $verificationTotal,
                'difference' => $verificationTotal - $originalPriceWithIva,
                'tax_id_alegra' => $taxIdAlegra,
                'quantity' => $detalle->quantity
            ]);

            $itemsAlegra[] = [
                'id' => (string)$idAlegra,
                'name' => (string)($product->name ?: 'Producto sin nombre'),
                'description' => (string)($product->description ?: $product->name ?: 'Producto'),
                'price' => $finalPrice,
                'quantity' => intval($detalle->quantity ?: 1),
                'tax' => [['id' => (string)$taxIdAlegra]],
                'reference' => (string)($product->sku ?: $product->internal_code ?: $product->id)
            ];
        }

        Log::info('📦 Items procesados para factura', [
            'total_details' => count($quote->detalles),
            'valid_items' => count($itemsAlegra),
            'skipped_items' => count($quote->detalles) - count($itemsAlegra)
        ]);

        return $itemsAlegra;
    }

    /**
     * Obtener datos del cliente para la factura
     * Equivalente a getGlobalProduct() en JavaScript
     */
    protected static function getCustomerDataForInvoice(VntQuote $quote): array
    {
        $customer = $quote->customer; // VntContacts
        $warehouse = $quote->warehouse; // VntWarehouse

        // Buscar empresa asociada al contacto
        $company = $customer ? $customer->company : null;

        $customerData = [
            'id_alegra'          => null,
            'warehouse_id_alegra'=> null,
            'warehouse_format'   => null,
            'numeracion'         => null,  // ID de numberTemplate desde cnf_invoices
        ];

        // Obtener ID de Alegra del cliente (prioridad a api_data_id)
        if ($company) {
            $customerData['id_alegra'] = $company->api_data_id ??
                                       $company->customer_id_alegra ??
                                       $company->alegra_id ??
                                       $company->id_alegra ?? null;
        }

        if (empty($customerData['id_alegra']) && $customer) {
            $customerData['id_alegra'] = $customer->api_data_id ??
                                       $customer->customer_id_alegra ??
                                       $customer->alegra_id ??
                                       $customer->id_alegra ?? null;
        }

        // Obtener la bodega (inv_store) desde el warehouseId de la cotización (que almacena el store ID)
        $store = \App\Models\Tenant\Items\InvStore::find($quote->warehouseId);

        // Obtener configuración del warehouse (prioridad a api_data_id de la bodega/store)
        if ($store) {
            $customerData['warehouse_id_alegra'] = $store->api_data_id ??
                                                  $store->warehouse_id_alegra ??
                                                  $store->alegra_warehouse_id ??
                                                  $store->id_alegra ??
                                                  $store->id ?? '1';

            // El formato de numeración se sigue obteniendo de la sucursal (vnt_warehouses) si existe
            $customerData['warehouse_format'] = $warehouse->warehouse_format ??
                                              $warehouse->alegra_format ??
                                              $warehouse->number_template ?? '20';
        } elseif ($warehouse) {
            // Fallback usando el objeto de la sucursal si no se encontró bodega directa
            $customerData['warehouse_id_alegra'] = $warehouse->api_data_id ??
                                                  $warehouse->warehouse_id_alegra ??
                                                  $warehouse->alegra_warehouse_id ??
                                                  $warehouse->id_alegra ?? '1';

            $customerData['warehouse_format'] = $warehouse->warehouse_format ??
                                              $warehouse->alegra_format ??
                                              $warehouse->number_template ?? '20';
        } else {
            // Valores por defecto
            $customerData['warehouse_id_alegra'] = '1';
            $customerData['warehouse_format'] = '20';
        }

        // Obtener numeracion (numberTemplate) desde cnf_invoices via usuario autenticado.
        // DatabaseConfigService::getFacturacionConfigByUser resuelve el flujo:
        // users.contact_id → vnt_contacts.warehouseId (central) → cnf_invoices.numeracion
        $authUser = Auth::user();
        if ($authUser) {
            $facConfig = DatabaseConfigService::getFacturacionConfigByUser($authUser->id);
            if ($facConfig && !empty($facConfig['numeracion'])) {
                $customerData['numeracion'] = (string) $facConfig['numeracion'];
            }
            Log::info('🔢 Numeracion obtenida via usuario', [
                'user_id'    => $authUser->id,
                'numeracion' => $customerData['numeracion'],
                'found'      => !is_null($customerData['numeracion']),
            ]);
        }

        Log::info('👤 Datos de cliente obtenidos', [
            'customer_id_alegra' => $customerData['id_alegra'],
            'warehouse_id_alegra' => $customerData['warehouse_id_alegra'],
            'warehouse_format'   => $customerData['warehouse_format'],
            'numeracion'         => $customerData['numeracion'],
            'customer_details' => [
                'has_company' => !empty($company),
                'company_api_data_id' => $company->api_data_id ?? null,
                'company_customer_id_alegra' => $company->customer_id_alegra ?? null,
                'customer_api_data_id' => $customer->api_data_id ?? null,
                'customer_identification' => $customer->identification ?? null,
                'customer_name' => $customer->businessName ?? ($customer->firstName . ' ' . $customer->lastName)
            ]
        ]);

        return $customerData;
    }

    /**
     * Obtener ID de Alegra del usuario autenticado (seller)
     * IMPORTANTE: El usuario viene de la BD RAP (central), NO del tenant
     * Equivalente a obtenerIdAlegraUsuario() en JavaScript
     */
    protected static function getSellerIdFromCurrentUser(): ?string
    {
        try {
            $user = Auth::user();

            if (!$user) {
                Log::warning('⚠️ No hay usuario autenticado para obtener seller ID');
                return null;
            }

            // IMPORTANTE: El usuario viene de la BD RAP (central), usar api_data_id directamente
            $sellerIdAlegra = $user->api_data_id ?? null;

            if (!empty($sellerIdAlegra)) {
                Log::info('👨‍💼 Seller ID obtenido del usuario (BD RAP)', [
                    'user_id' => $user->id,
                    'seller_id_alegra' => $sellerIdAlegra,
                    'source' => 'users.api_data_id (BD RAP)'
                ]);
                return (string)$sellerIdAlegra;
            }

            Log::warning('⚠️ Usuario sin api_data_id en BD RAP', [
                'user_id' => $user->id,
                'user_email' => $user->email ?? 'N/A'
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('❌ Error obteniendo seller ID desde BD RAP', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return null;
        }
    }

    /**
     * Procesar métodos de pago
     * Traduce la lógica del for loop de JavaScript para methodPayments
     */
    protected static function processPaymentMethods(array $methodPayments): array
    {
        $totalPayment = 0;
        $paymentForm = "CASH";
        $paymentMethod = null;
        $partialPayment = 0;
        $bankPayment = null;

        Log::info('💳 Procesando métodos de pago', ['methods_count' => count($methodPayments)]);

        foreach ($methodPayments as $detailPayment) {
            $descriptionFormaPago = $detailPayment['descriptionFormaPago'] ?? '';
            $nombre = $detailPayment['nombre'] ?? '';
            $valor = floatval($detailPayment['valor'] ?? 0);

            // Verificar si es CASH/EFECTIVO (igual que el JavaScript)
            $isCash = (
                stripos($descriptionFormaPago, 'CASH') !== false ||
                stripos($descriptionFormaPago, 'EFECTIVO') !== false ||
                stripos($nombre, 'EFECTIVO') !== false ||
                stripos($nombre, 'CASH') !== false
            );

            if ($isCash) {
                Log::info('💵 Procesando pago en efectivo', ['valor' => $valor]);
                $totalPayment += $valor;
                $bankPayment = $detailPayment['bank'] ?? null;
            } else {
                Log::info('💳 Procesando pago a crédito', ['valor' => $valor]);
                $paymentForm = "CREDIT";
                $partialPayment = $valor;
            }
        }

        // Determinar paymentMethod si no es CREDIT (igual que el JS)
        if ($paymentForm !== "CREDIT" && count($methodPayments) > 0) {
            $paymentMethod = $methodPayments[0]['method'] ?? null;
        }

        $result = [
            'paymentForm' => $paymentForm,
            'paymentMethod' => $paymentMethod,
            'totalPayment' => $totalPayment,
            'partialPayment' => $partialPayment,
            'bankPayment' => $bankPayment
        ];

        Log::info('✅ Métodos de pago procesados', $result);

        return $result;
    }

    /**
     * Validar datos requeridos para la factura
     * Equivalente a las validaciones del JavaScript
     */
    protected static function validateInvoiceData(array $customerData, array $itemsAlegra): void
    {
        $errors = [];

        if (empty($customerData['id_alegra'])) {
            $errors[] = 'ID del cliente Alegra es requerido';
        }

        if (empty($customerData['warehouse_format'])) {
            $errors[] = 'ID del numberTemplate es requerido';
        }

        if (empty($customerData['warehouse_id_alegra'])) {
            $errors[] = 'ID del warehouse es requerido';
        }

        if (empty($itemsAlegra)) {
            $errors[] = 'Se requiere al menos un item para crear la factura';
        }

        if (!empty($errors)) {
            $errorMessage = 'Datos faltantes para facturación: ' . implode(', ', $errors);
            Log::error('❌ Validación de datos de factura fallida', [
                'errors' => $errors,
                'customer_data' => $customerData,
                'items_count' => count($itemsAlegra)
            ]);
            throw new \Exception($errorMessage);
        }

        Log::info('✅ Datos de factura validados correctamente');
    }

    /**
     * Calcular retenciones automáticamente para una cotización
     *
     * @param VntQuote $quote Cotización para calcular retenciones
     * @return array Array de retenciones calculadas
     */
    protected static function calculateRetentionsForQuote(VntQuote $quote): array
    {
        $retentionCalculator = new RetentionCalculatorService();

        // Obtener datos del cliente
        $customer = $quote->customer;

        if (!$customer) {
            Log::warning('⚠️ No se puede calcular retenciones: cliente no encontrado', [
                'quote_id' => $quote->id
            ]);
            return [];
        }

        // Calcular subtotal dinámicamente desde los detalles (más confiable que el campo guardado)
        $subTotal = 0;
        foreach ($quote->detalles as $detalle) {
            $originalPriceWithIva = floatval($detalle->value ?: 0);
            $taxPercentage = floatval($detalle->tax ?? 0);

            if ($taxPercentage > 0) {
                // Producto con IVA - calcular precio base sin IVA
                $priceBase = round($originalPriceWithIva / (1 + ($taxPercentage / 100)), 2);
            } else {
                // Producto exento
                $priceBase = $originalPriceWithIva;
            }

            $subTotal += $priceBase * $detalle->quantity;
        }
        $subTotal = round($subTotal, 2);

        // Obtener datos necesarios para el cálculo
        $regimeDescription = self::getRegimeDescription($customer);
        $fiscalResponsability = self::getFiscalResponsability($customer);
        $city = self::getCustomerCity($customer);

        Log::info('📊 Datos para cálculo de retenciones (bases oficiales 2026)', [
            'quote_id' => $quote->id,
            'sub_total' => $subTotal,
            'base_fuente' => config('facturacion.retentions.base_amounts.fuente', 524000),
            'base_ica' => config('facturacion.retentions.base_amounts.ica', 1418800),
            'base_iva' => config('facturacion.retentions.base_amounts.iva', 300000),
            'regime_description' => $regimeDescription,
            'fiscal_responsability' => $fiscalResponsability,
            'city' => $city
        ]);

        // Calcular retenciones usando el servicio con bases oficiales 2026
        $calculatedRetentions = $retentionCalculator->calculateAllRetentions([
            'regime_description' => $regimeDescription,
            'fiscal_responsability' => $fiscalResponsability,
            'city' => $city,
            'sub_total' => $subTotal
        ]);

        // Formatear retenciones para Alegra (formato esperado por la API)
        // Formato: [{"id":"14","amount":12341},{"id":"11","amount":12345},{"id":"12","amount":12345}]
        $retentions = [];

        // Retención en la fuente (ID: 14)
        if ($calculatedRetentions['retention_fuente'] > 0) {
            $retentions[] = [
                'id' => config('facturacion.retentions.alegra_ids.fuente', '14'),
                'amount' => $calculatedRetentions['retention_fuente']
            ];
        }

        // Retención ICA (ID: 11)
        if ($calculatedRetentions['retention_ica'] > 0) {
            $retentions[] = [
                'id' => config('facturacion.retentions.alegra_ids.ica', '11'),
                'amount' => $calculatedRetentions['retention_ica']
            ];
        }

        // Retención IVA (ID: 12)
        if ($calculatedRetentions['retention_iva'] > 0) {
            $retentions[] = [
                'id' => config('facturacion.retentions.alegra_ids.iva', '12'),
                'amount' => $calculatedRetentions['retention_iva']
            ];
        }

        Log::info('✅ Retenciones calculadas para envío a Alegra', [
            'quote_id' => $quote->id,
            'retention_fuente' => $calculatedRetentions['retention_fuente'],
            'retention_ica' => $calculatedRetentions['retention_ica'],
            'retention_iva' => $calculatedRetentions['retention_iva'],
            'total_retentions' => count($retentions),
            'alegra_format' => $retentions // Formato exacto que se enviará a Alegra
        ]);

        return $retentions;
    }

    /**
     * Obtener descripción del régimen fiscal del cliente
     */
    protected static function getRegimeDescription($customer): string
    {
        // Lógica para determinar el régimen basado en el cliente
        // Esto depende de cómo esté estructurada la información del cliente

        if ($customer->company && $customer->company->regimen) {
            return $customer->company->regimen === 'COMUN' ? 'COMMON_REGIME' : 'SPECIAL_REGIME';
        }

        // Por defecto, régimen común
        return 'COMMON_REGIME';
    }

    /**
     * Obtener responsabilidad fiscal del cliente
     */
    protected static function getFiscalResponsability($customer): int
    {
        if ($customer->company && $customer->company->fiscal_responsibility_id) {
            return (int) $customer->company->fiscal_responsibility_id;
        }

        if ($customer->fiscal_responsibility_id) {
            return (int) $customer->fiscal_responsibility_id;
        }

        return 0; // Sin responsabilidad fiscal
    }

    /**
     * Obtener ciudad del cliente
     */
    protected static function getCustomerCity($customer): string
    {
        if ($customer->company && $customer->company->city) {
            return $customer->company->city;
        }

        if ($customer->city) {
            return $customer->city;
        }

        return '';
    }
}