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
     * @param array $retentions Retenciones aplicadas
     * @param int $termDays Días de término para fecha de vencimiento
     * @return array Datos formateados para la API de Alegra
     */
    public static function buildFromQuote(
        VntQuote $quote,
        array $paymentMethods = [],
        array $retentions = [],
        int $termDays = 0
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

        // Obtener ID del vendedor (usuario autenticado)
        $sellerIdAlegra = self::getSellerIdFromCurrentUser();

        // Procesar métodos de pago (traducir lógica del for loop de JS)
        $paymentData = self::processPaymentMethods($paymentMethods);

        // Validar retenciones (solo las válidas con amount > 0)
        $validRetentions = array_filter($retentions, function ($retention) {
            return ($retention['amount'] ?? 0) > 0;
        });

        // Validaciones requeridas (equivalent to JS validations)
        self::validateInvoiceData($customerData, $itemsAlegra);

        // Construir objeto completo para Alegra (equivalente al dataAlegra en JS)
        $dataAlegra = [
            'date' => $date,
            'dueDate' => $dueDate,
            'client' => [
                'id' => (string)$customerData['id_alegra']
            ],
            'status' => 'open',
            'numberTemplate' => [
                'id' => "16"
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

        // Agregar retenciones si existen
        if (!empty($validRetentions)) {
            $dataAlegra['retentions'] = array_values($validRetentions);
        }

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

            $taxIdAlegra = $product->tax_api_data_id ?? $product->taxIdAlegra ?? $product->tax_id_alegra ?? $product->api_tax_id ?? null;

            // Si no hay tax configurado, usar IVA 19% por defecto para Colombia (ID común en Alegra)
            if (!$taxIdAlegra) {
                $taxIdAlegra = '3'; // ID estándar para IVA 19% en Alegra
                Log::warning('⚠️  Producto sin tax ID configurado, usando IVA 19% por defecto', [
                    'product_id' => $product->id,
                    'product_name' => $product->name ?? 'Sin nombre',
                    'default_tax_id' => $taxIdAlegra
                ]);
            }

            // ENFOQUE DIFERENTE: Calcular el precio base exacto que Alegra necesita
            // Basándose en la evidencia: Alegra muestra $43,488 de subtotal para $51,750.72 total
            // Esto significa: $43,488 * 1.19 = $51,750.72

            $originalPriceWithIva = floatval($detalle->value ?: 0);

            // Para un producto de $17,250, el subtotal en Alegra debería ser $14,496
            // Verificamos: $14,496 * 1.19 = $17,250.24 (muy cercano a $17,250)

            // Calculamos el precio base que Alegra necesita para este comportamiento
            $targetSubtotal = round($originalPriceWithIva / 1.19, 0); // Redondear subtotal a entero
            $finalPrice = $targetSubtotal;

            // Log para verificar los cálculos
            $verificationTotal = $finalPrice * 1.19;

            Log::info('💰 Calculando precio sin IVA', [
                'product_id' => $product->id,
                'original_price_with_iva' => $originalPriceWithIva,
                'target_subtotal' => $targetSubtotal,
                'final_price' => $finalPrice,
                'verification_total' => $verificationTotal,
                'difference' => $verificationTotal - $originalPriceWithIva,
                'tax_id' => $taxIdAlegra
            ]);

            $itemsAlegra[] = [
                'id' => (string)$idAlegra,
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
            'id_alegra' => null,
            'warehouse_id_alegra' => null,
            'warehouse_format' => null,
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

        // Obtener configuración del warehouse (prioridad a api_data_id)
        if ($warehouse) {
            $customerData['warehouse_id_alegra'] = $warehouse->api_data_id ??
                                                  $warehouse->warehouse_id_alegra ??
                                                  $warehouse->alegra_warehouse_id ??
                                                  $warehouse->id_alegra ?? '1';

            $customerData['warehouse_format'] = $warehouse->warehouse_format ??
                                              $warehouse->alegra_format ??
                                              $warehouse->number_template ?? '20';
        }

        Log::info('👤 Datos de cliente obtenidos', [
            'customer_id_alegra' => $customerData['id_alegra'],
            'warehouse_id_alegra' => $customerData['warehouse_id_alegra'],
            'warehouse_format' => $customerData['warehouse_format']
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
}