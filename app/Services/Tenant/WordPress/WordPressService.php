<?php

namespace App\Services\Tenant\WordPress;

use App\Models\Tenant\WordPress\WordPressConfig;
use App\Models\Tenant\Items\ImageGallery;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvItemsStore;
use App\Models\Tenant\Items\InvValues;
use App\Models\Tenant\Remissions\InvDetailRemissions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class WordPressService
{
    protected $config;
    protected $baseUrl;
    protected $auth;

    public function __construct()
    {
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        try {
            $this->config = WordPressConfig::where('is_active', true)->first();

            if ($this->config) {
                $this->baseUrl = rtrim($this->config->wp_url, '/') . '/wp-json/wc/v3/';
                $this->auth = [
                    $this->config->wp_user,
                    $this->config->wp_password
                ];
                Log::info('🟢 [WP] Configuración cargada', [
                    'wp_url'   => $this->config->wp_url,
                    'wp_user'  => $this->config->wp_user,
                    'base_url' => $this->baseUrl,
                ]);
            } else {
                Log::warning('⚠️ [WP] No hay configuración activa de WordPress en la BD');
            }
        } catch (Exception $e) {
            Log::error('❌ [WP] Error cargando configuración: ' . $e->getMessage());
        }
    }

    public function isConfigured()
    {
        return !empty($this->config);
    }

    public function findProductBySku($sku)
    {
        if (!$this->isConfigured() || empty($sku)) {
            Log::warning('⚠️ [WP] findProductBySku abortado', [
                'motivo'       => !$this->isConfigured() ? 'Sin configuración WP' : 'SKU vacío',
                'sku_recibido' => $sku,
            ]);
            return null;
        }

        Log::info('🔍 [WP] Buscando producto por SKU', ['sku' => $sku, 'endpoint' => $this->baseUrl . 'products']);

        try {
            $response = Http::withBasicAuth($this->auth[0], $this->auth[1])
                ->get($this->baseUrl . 'products', ['sku' => $sku]);

            Log::info('📡 [WP] Respuesta findProductBySku', [
                'sku'         => $sku,
                'http_status' => $response->status(),
                'exitoso'     => $response->successful(),
            ]);

            if ($response->successful()) {
                $products = $response->json();

                if (count($products) > 0) {
                    $product = $products[0];
                    Log::info('✅ [WP] Producto encontrado', [
                        'sku'        => $sku,
                        'wp_id'      => $product['id'],
                        'wp_name'    => $product['name'],
                        'wp_type'    => $product['type'],
                        'num_images' => count($product['images'] ?? []),
                    ]);
                    return [
                        'id'           => $product['id'],
                        'type'         => $product['type'],
                        'sku'          => $product['sku'],
                        'name'         => $product['name'],
                        'images'       => $product['images'],
                        'is_variation' => ($product['type'] === 'variation'),
                    ];
                }

                Log::warning('⚠️ [WP] SKU no encontrado en WordPress', ['sku' => $sku]);
            } else {
                Log::error('❌ [WP] Error HTTP en findProductBySku', [
                    'sku'         => $sku,
                    'http_status' => $response->status(),
                    'body'        => $response->body(),
                ]);
            }
        } catch (Exception $e) {
            Log::error('❌ [WP] Excepción en findProductBySku', [
                'sku'   => $sku,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    public function uploadMedia($localPath, $filename = null)
    {
        if (!$this->isConfigured()) {
            Log::warning('⚠️ [WP] uploadMedia abortado: sin configuración WP');
            return null;
        }

        Log::info('📤 [WP] Iniciando uploadMedia', ['local_path' => $localPath]);

        try {
            if (!Storage::disk('public')->exists($localPath)) {
                Log::error('❌ [WP] Archivo local no encontrado', ['path' => $localPath]);
                throw new Exception("Archivo local no encontrado: $localPath");
            }

            $absolutePath = Storage::disk('public')->path($localPath);
            $originalSize = filesize($absolutePath);

            Log::info('📁 [WP] Archivo local encontrado', [
                'path'          => $absolutePath,
                'size_kb'       => round($originalSize / 1024, 2),
                'necesita_opt'  => $originalSize > 512000 ? 'Sí' : 'No',
            ]);

            $finalPath = $this->optimizeImage($absolutePath);
            $optimizedSize = filesize($finalPath);

            if ($finalPath !== $absolutePath) {
                Log::info('🔧 [WP] Imagen optimizada', [
                    'original_kb'  => round($originalSize / 1024, 2),
                    'optimized_kb' => round($optimizedSize / 1024, 2),
                    'reduccion_pct' => round((1 - $optimizedSize / $originalSize) * 100, 1) . '%',
                ]);
            }

            $fileContents = file_get_contents($finalPath);
            $name = $filename ?: basename($localPath);
            $mimeType = mime_content_type($finalPath);
            $wpMediaUrl = rtrim($this->config->wp_url, '/') . '/wp-json/wp/v2/media';

            Log::info('🚀 [WP] Enviando imagen a WordPress Media Library', [
                'wp_media_url' => $wpMediaUrl,
                'filename'     => $name,
                'mime_type'    => $mimeType,
                'size_kb'      => round($optimizedSize / 1024, 2),
            ]);

            $response = Http::withBasicAuth($this->auth[0], $this->auth[1])
                ->withHeaders(['Content-Disposition' => 'attachment; filename="' . $name . '"'])
                ->withBody($fileContents, $mimeType)
                ->post($wpMediaUrl);

            // Eliminar temporal si se optimizó
            if ($finalPath !== $absolutePath && file_exists($finalPath)) {
                unlink($finalPath);
            }

            Log::info('📡 [WP] Respuesta uploadMedia', [
                'http_status' => $response->status(),
                'exitoso'     => $response->successful(),
            ]);

            if ($response->successful()) {
                $mediaId = $response->json()['id'];
                $mediaUrl = $response->json()['source_url'] ?? 'N/A';
                Log::info('✅ [WP] Imagen subida correctamente', [
                    'wp_media_id'  => $mediaId,
                    'wp_media_url' => $mediaUrl,
                    'filename'     => $name,
                ]);
                return $mediaId;
            }

            Log::error('❌ [WP] Error al subir imagen', [
                'http_status' => $response->status(),
                'body'        => $response->body(),
                'filename'    => $name,
            ]);
        } catch (Exception $e) {
            Log::error('❌ [WP] Excepción en uploadMedia', [
                'path'  => $localPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return null;
    }

    public function setFeaturedImage($productId, $mediaId)
    {
        if (!$this->isConfigured()) return false;

        Log::info('🖼️ [WP] Asignando imagen principal', [
            'wp_product_id' => $productId,
            'wp_media_id'   => $mediaId,
        ]);

        try {
            $response = Http::withBasicAuth($this->auth[0], $this->auth[1])
                ->put($this->baseUrl . "products/$productId", [
                    'images' => [['id' => (int)$mediaId]]
                ]);

            Log::info('📡 [WP] Respuesta setFeaturedImage', [
                'wp_product_id' => $productId,
                'wp_media_id'   => $mediaId,
                'http_status'   => $response->status(),
                'exitoso'       => $response->successful(),
            ]);

            if (!$response->successful()) {
                Log::error('❌ [WP] Error asignando imagen principal', [
                    'body' => $response->body(),
                ]);
            }

            return $response->successful();
        } catch (Exception $e) {
            Log::error('❌ [WP] Excepción en setFeaturedImage', [
                'wp_product_id' => $productId,
                'error'         => $e->getMessage(),
            ]);
        }

        return false;
    }

    public function addToGallery($productId, $mediaId)
    {
        if (!$this->isConfigured()) return false;

        Log::info('🖼️ [WP] Agregando imagen a galería', [
            'wp_product_id' => $productId,
            'wp_media_id'   => $mediaId,
        ]);

        try {
            $currentProduct = Http::withBasicAuth($this->auth[0], $this->auth[1])
                ->get($this->baseUrl . "products/$productId")
                ->json();

            $images = $currentProduct['images'] ?? [];

            Log::info('📋 [WP] Galería actual del producto', [
                'wp_product_id'    => $productId,
                'num_imgs_actuales' => count($images),
                'ids_actuales'     => collect($images)->pluck('id')->toArray(),
            ]);

            foreach ($images as $img) {
                if ($img['id'] == $mediaId) {
                    Log::info('ℹ️ [WP] Imagen ya existe en galería WP, omitiendo', [
                        'wp_media_id' => $mediaId,
                    ]);
                    return true;
                }
            }

            $images[] = ['id' => (int)$mediaId];

            $response = Http::withBasicAuth($this->auth[0], $this->auth[1])
                ->put($this->baseUrl . "products/$productId", ['images' => $images]);

            Log::info('📡 [WP] Respuesta addToGallery', [
                'wp_product_id'   => $productId,
                'wp_media_id'     => $mediaId,
                'http_status'     => $response->status(),
                'exitoso'         => $response->successful(),
                'total_imgs_ahora' => count($images),
            ]);

            if (!$response->successful()) {
                Log::error('❌ [WP] Error agregando a galería', ['body' => $response->body()]);
            }

            return $response->successful();
        } catch (Exception $e) {
            Log::error('❌ [WP] Excepción en addToGallery', [
                'wp_product_id' => $productId,
                'error'         => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Elimina un media de la librería de WordPress (force=true para saltarse la papelera).
     */
    public function deleteMedia($wpMediaId)
    {
        if (!$this->isConfigured() || !$wpMediaId) {
            Log::warning('⚠️ [WP] deleteMedia abortado', [
                'motivo'      => !$this->isConfigured() ? 'Sin configuración WP' : 'wp_media_id vacío',
                'wp_media_id' => $wpMediaId,
            ]);
            return false;
        }

        Log::info('🗑️ [WP] Eliminando media de WordPress', ['wp_media_id' => $wpMediaId]);

        try {
            $wpMediaUrl = rtrim($this->config->wp_url, '/') . '/wp-json/wp/v2/media/' . $wpMediaId;

            $response = Http::withBasicAuth($this->auth[0], $this->auth[1])
                ->delete($wpMediaUrl, ['force' => true]);

            Log::info('📡 [WP] Respuesta deleteMedia', [
                'wp_media_id' => $wpMediaId,
                'http_status' => $response->status(),
                'exitoso'     => $response->successful(),
            ]);

            if (!$response->successful()) {
                Log::error('❌ [WP] Error eliminando media', [
                    'wp_media_id' => $wpMediaId,
                    'http_status' => $response->status(),
                    'body'        => $response->body(),
                ]);
            }

            return $response->successful();
        } catch (Exception $e) {
            Log::error('❌ [WP] Excepción en deleteMedia', [
                'wp_media_id' => $wpMediaId,
                'error'       => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Elimina una imagen del array de imágenes de un producto en WooCommerce.
     */
    public function removeFromProductImages($productId, $wpMediaId)
    {
        if (!$this->isConfigured()) return false;

        Log::info('🗑️ [WP] Quitando imagen del producto WC', [
            'wp_product_id' => $productId,
            'wp_media_id'   => $wpMediaId,
        ]);

        try {
            $currentProduct = Http::withBasicAuth($this->auth[0], $this->auth[1])
                ->get($this->baseUrl . "products/$productId")
                ->json();

            $images = collect($currentProduct['images'] ?? [])
                ->filter(fn($img) => $img['id'] != $wpMediaId)
                ->values()
                ->toArray();

            $response = Http::withBasicAuth($this->auth[0], $this->auth[1])
                ->put($this->baseUrl . "products/$productId", ['images' => $images]);

            Log::info('📡 [WP] Respuesta removeFromProductImages', [
                'wp_product_id'      => $productId,
                'wp_media_id'        => $wpMediaId,
                'http_status'        => $response->status(),
                'imgs_restantes'     => count($images),
            ]);

            return $response->successful();
        } catch (Exception $e) {
            Log::error('❌ [WP] Excepción en removeFromProductImages', [
                'wp_product_id' => $productId,
                'error'         => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Sincroniza stock y precio de un item del ERP hacia WooCommerce.
     * Bodega: PRINCIPAL (storeId=2) | Precio: Precio Regular + IVA redondeado a decena
     * Reservas: remisiones en estado REGISTRADO descontadas del stock bruto
     */
    public function syncItemStock(Items $item): array
    {
        $result = [
            'success'    => false,
            'item_id'    => $item->id,
            'sku'        => $item->sku,
            'message'    => '',
            'stock_bruto'    => 0,
            'reservas'   => 0,
            'stock_wp'   => 0,
            'precio_wp'  => 0,
        ];

        Log::info('📦 [WP-Stock] Iniciando sync de stock', [
            'item_id' => $item->id,
            'sku'     => $item->sku,
            'name'    => $item->name,
        ]);

        if (!$this->isConfigured()) {
            $result['message'] = 'WordPress no configurado';
            Log::warning('⚠️ [WP-Stock] ' . $result['message']);
            return $result;
        }

        if (empty($item->sku)) {
            $result['message'] = 'Item sin SKU — omitido';
            Log::warning('⚠️ [WP-Stock] ' . $result['message'], ['item_id' => $item->id]);
            return $result;
        }

        // 1. Stock en bodega PRINCIPAL (storeId=2)
        $storeStock = InvItemsStore::where('itemId', $item->id)
            ->where('storeId', 2)
            ->first();

        if (!$storeStock) {
            $result['message'] = 'Sin registro en bodega PRINCIPAL';
            Log::warning('⚠️ [WP-Stock] ' . $result['message'], ['item_id' => $item->id]);
            return $result;
        }

        $stockBruto = (float) $storeStock->stock_items_store;
        $stockMin   = (float) $storeStock->stock_min;

        // 2. Reservas activas (Omitido: se usa 0 para no restar remisiones registradas globales)
        $reservas = 0;

        $reservas = (float) $reservas;

        // 3. Stock disponible neto (igual al stock bruto del almacén)
        $stockNeto = max(0, $stockBruto - $reservas);

        // 4. Gate de mínimo + aplicar porcentaje (igual que sistema anterior)
        $porcentaje = (float) ($storeStock->wp_stock_percentage ?? 100);
        if ($stockNeto >= $stockMin) {
            $stockWP = round($stockNeto * ($porcentaje / 100));
        } else {
            $stockWP = 0;
        }

        $result['stock_bruto'] = $stockBruto;
        $result['reservas']    = $reservas;
        $result['stock_wp']    = $stockWP;

        Log::info('📊 [WP-Stock] Cálculo de stock', [
            'item_id'     => $item->id,
            'stock_bruto' => $stockBruto,
            'reservas'    => $reservas,
            'stock_neto'  => $stockNeto,
            'stock_min'   => $stockMin,
            'stock_wp'    => $stockWP,
        ]);

        // 5. Precio Regular con IVA redondeado a la decena más cercana
        $precioRecord = InvValues::where('itemId', $item->id)
            ->where('label', 'Precio Regular')
            ->first();

        $precioWP = 0;
        if ($precioRecord && (float) $precioRecord->values > 0) {
            $taxRate  = (float) ($item->tax?->percentage ?? 0);
            $precioWP = round((float) $precioRecord->values * (1 + $taxRate / 100) / 10) * 10;
        }

        $result['precio_wp'] = $precioWP;

        Log::info('💰 [WP-Stock] Precio calculado', [
            'item_id'       => $item->id,
            'precio_base'   => $precioRecord?->values ?? 0,
            'precio_con_iva' => $precioWP,
        ]);

        // 6. Buscar producto en WooCommerce por SKU
        $wpProduct = $this->findProductBySku($item->sku);
        if (!$wpProduct) {
            $result['message'] = 'Producto no encontrado en WP con SKU: ' . $item->sku;
            return $result;
        }

        // 7. Actualizar stock y precio en WooCommerce
        $synced = $this->updateProductStockAndPrice($wpProduct['id'], $stockWP, $precioWP);

        $result['success'] = $synced;
        $result['wp_product_id'] = $wpProduct['id'];
        $result['message'] = $synced ? 'Sincronizado correctamente' : 'Error al actualizar en WP';

        Log::info($synced ? '✅ [WP-Stock] Sync OK' : '❌ [WP-Stock] Sync FALLÓ', [
            'item_id'       => $item->id,
            'sku'           => $item->sku,
            'wp_product_id' => $wpProduct['id'],
            'stock_wp'      => $stockWP,
            'precio_wp'     => $precioWP,
        ]);

        return $result;
    }

    /**
     * Actualiza stock y precio de un producto en WooCommerce vía REST API.
     */
    public function updateProductStockAndPrice($wpProductId, $stock, $price): bool
    {
        if (!$this->isConfigured()) return false;

        $stockStatus = $stock > 0 ? 'instock' : 'outofstock';

        Log::info('🔄 [WP-Stock] updateProductStockAndPrice', [
            'wp_product_id' => $wpProductId,
            'stock'         => $stock,
            'stock_status'  => $stockStatus,
            'price'         => $price,
        ]);

        try {
            $data = [
                'manage_stock'   => true,
                'stock_quantity' => (int) $stock,
                'stock_status'   => $stockStatus,
            ];

            if ($price > 0) {
                $data['regular_price'] = (string) $price;
            }

            $response = Http::withBasicAuth($this->auth[0], $this->auth[1])
                ->put($this->baseUrl . "products/{$wpProductId}", $data);

            Log::info('📡 [WP-Stock] Respuesta WC', [
                'wp_product_id' => $wpProductId,
                'http_status'   => $response->status(),
                'exitoso'       => $response->successful(),
            ]);

            if (!$response->successful()) {
                Log::error('❌ [WP-Stock] Error en WC', [
                    'wp_product_id' => $wpProductId,
                    'body'          => $response->body(),
                ]);
            }

            return $response->successful();
        } catch (Exception $e) {
            Log::error('❌ [WP-Stock] Excepción', [
                'wp_product_id' => $wpProductId,
                'error'         => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Sincroniza únicamente el precio de un item del ERP hacia WooCommerce.
     * Precio: Precio Base + IVA redondeado a la decena más cercana.
     */
    public function syncItemPrice(Items $item): array
    {
        $result = [
            'success'   => false,
            'item_id'   => $item->id,
            'sku'       => $item->sku,
            'message'   => '',
            'precio_wp' => 0,
        ];

        Log::info('💰 [WP-Price] Iniciando sync de precio', [
            'item_id' => $item->id,
            'sku'     => $item->sku,
            'name'    => $item->name,
        ]);

        if (!$this->isConfigured()) {
            $result['message'] = 'WordPress no configurado';
            Log::warning('⚠️ [WP-Price] ' . $result['message']);
            return $result;
        }

        if (empty($item->sku)) {
            $result['message'] = 'Item sin SKU — omitido';
            Log::warning('⚠️ [WP-Price] ' . $result['message'], ['item_id' => $item->id]);
            return $result;
        }

        // Obtener el Precio Crédito de los valores
        $precioRecord = InvValues::where('itemId', $item->id)
            ->where('label', 'Precio Crédito')
            ->first();

        // Fallback 1: Si no tiene Precio Crédito, buscar Precio Base
        if (!$precioRecord) {
            $precioRecord = InvValues::where('itemId', $item->id)
                ->where('label', 'Precio Base')
                ->first();
        }

        // Fallback 2: Si tampoco tiene Precio Base, buscar Precio Regular
        if (!$precioRecord) {
            $precioRecord = InvValues::where('itemId', $item->id)
                ->where('label', 'Precio Regular')
                ->first();
        }

        $precioWP = 0;
        if ($precioRecord && (float) $precioRecord->values > 0) {
            $taxRate  = (float) ($item->tax?->percentage ?? 0);
            $precioWP = round((float) $precioRecord->values * (1 + $taxRate / 100) / 10) * 10;
        }

        $result['precio_wp'] = $precioWP;

        Log::info('💰 [WP-Price] Precio calculado para actualización', [
            'item_id'       => $item->id,
            'precio_base'   => $precioRecord?->values ?? 0,
            'precio_con_iva' => $precioWP,
        ]);

        if ($precioWP <= 0) {
            $result['message'] = 'Precio calculado no válido (0 o menor)';
            Log::warning('⚠️ [WP-Price] ' . $result['message'], ['item_id' => $item->id]);
            return $result;
        }

        // Buscar producto en WooCommerce por SKU
        $wpProduct = $this->findProductBySku($item->sku);
        if (!$wpProduct) {
            $result['message'] = 'Producto no encontrado en WP con SKU: ' . $item->sku;
            return $result;
        }

        // Actualizar precio en WooCommerce
        $synced = $this->updateProductPrice($wpProduct['id'], $precioWP);

        $result['success'] = $synced;
        $result['wp_product_id'] = $wpProduct['id'];
        $result['message'] = $synced ? 'Precio sincronizado correctamente' : 'Error al actualizar precio en WP';

        Log::info($synced ? '✅ [WP-Price] Sync Precio OK' : '❌ [WP-Price] Sync Precio FALLÓ', [
            'item_id'       => $item->id,
            'sku'           => $item->sku,
            'wp_product_id' => $wpProduct['id'],
            'precio_wp'     => $precioWP,
        ]);

        return $result;
    }

    /**
     * Actualiza únicamente el precio de un producto en WooCommerce vía REST API.
     */
    public function updateProductPrice($wpProductId, $price): bool
    {
        if (!$this->isConfigured()) return false;

        Log::info('🔄 [WP-Price] updateProductPrice', [
            'wp_product_id' => $wpProductId,
            'price'         => $price,
        ]);

        try {
            $data = [
                'regular_price' => (string) $price,
            ];

            $response = Http::withBasicAuth($this->auth[0], $this->auth[1])
                ->put($this->baseUrl . "products/{$wpProductId}", $data);

            Log::info('📡 [WP-Price] Respuesta WC', [
                'wp_product_id' => $wpProductId,
                'http_status'   => $response->status(),
                'exitoso'       => $response->successful(),
            ]);

            if (!$response->successful()) {
                Log::error('❌ [WP-Price] Error en WC', [
                    'wp_product_id' => $wpProductId,
                    'body'          => $response->body(),
                ]);
            }

            return $response->successful();
        } catch (Exception $e) {
            Log::error('❌ [WP-Price] Excepción', [
                'wp_product_id' => $wpProductId,
                'error'         => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function syncImage(ImageGallery $image, $productSku)
    {
        Log::info('🔄 [WP] Iniciando syncImage', [
            'image_id'    => $image->id,
            'item_id'     => $image->itemId,
            'type'        => $image->type,
            'type_show'   => $image->type_show,
            'img_path'    => $image->img_path,
            'sync_to_wp'  => $image->sync_to_wp,
            'wp_media_id' => $image->wp_media_id,
            'sku'         => $productSku,
        ]);

        if (!$this->isConfigured()) {
            Log::error('❌ [WP] syncImage abortado: WordPress no configurado');
            return false;
        }

        $wpProduct = $this->findProductBySku($productSku);
        if (!$wpProduct) {
            Log::warning('⚠️ [WP] syncImage cancelado: producto no encontrado en WP', [
                'sku' => $productSku,
            ]);
            return false;
        }

        if ($image->wp_media_id) {
            Log::info('ℹ️ [WP] Imagen ya subida previamente, reutilizando wp_media_id', [
                'image_id'    => $image->id,
                'wp_media_id' => $image->wp_media_id,
            ]);
            $mediaId = $image->wp_media_id;
        } else {
            $mediaId = $this->uploadMedia($image->img_path);
            if (!$mediaId) {
                Log::error('❌ [WP] syncImage cancelado: uploadMedia falló', [
                    'image_id' => $image->id,
                    'img_path' => $image->img_path,
                ]);
                return false;
            }

            $image->wp_media_id = $mediaId;
            $image->save();
            Log::info('💾 [WP] wp_media_id guardado en BD local', [
                'image_id'    => $image->id,
                'wp_media_id' => $mediaId,
            ]);
        }

        if ($image->type === 'PRINCIPAL') {
            Log::info('🌟 [WP] Asignando como imagen PRINCIPAL del producto');
            $result = $this->setFeaturedImage($wpProduct['id'], $mediaId);
        } else {
            Log::info('📷 [WP] Agregando a GALERÍA del producto');
            $result = $this->addToGallery($wpProduct['id'], $mediaId);
        }

        Log::info($result ? '✅ [WP] syncImage completado con éxito' : '❌ [WP] syncImage terminó con error', [
            'image_id'      => $image->id,
            'wp_product_id' => $wpProduct['id'],
            'wp_media_id'   => $mediaId,
            'type'          => $image->type,
            'resultado'     => $result ? 'OK' : 'FALLO',
        ]);

        return $result;
    }

    protected function optimizeImage($path)
    {
        $sizeKb = round(filesize($path) / 1024, 2);

        if (!file_exists($path) || filesize($path) < 512000) {
            Log::info('ℹ️ [WP] Imagen no requiere optimización', [
                'path'    => $path,
                'size_kb' => $sizeKb,
            ]);
            return $path;
        }

        Log::info('🔧 [WP] Optimizando imagen', ['path' => $path, 'size_kb' => $sizeKb]);

        try {
            $info = getimagesize($path);
            if (!$info) {
                Log::warning('⚠️ [WP] No se pudo leer info de imagen, se usa original', ['path' => $path]);
                return $path;
            }

            $type = $info[2];
            $source = null;

            switch ($type) {
                case IMAGETYPE_JPEG: $source = imagecreatefromjpeg($path); break;
                case IMAGETYPE_PNG:  $source = imagecreatefrompng($path);  break;
                case IMAGETYPE_WEBP: $source = @imagecreatefromwebp($path); break;
                default:
                    Log::warning('⚠️ [WP] Tipo de imagen no soportado para optimización', ['type' => $type]);
                    return $path;
            }

            if (!$source) {
                Log::warning('⚠️ [WP] No se pudo crear recurso GD, se usa original');
                return $path;
            }

            $width = imagesx($source);
            $height = imagesy($source);
            $maxDimension = 1200;

            if ($width > $maxDimension || $height > $maxDimension) {
                $ratio = $width / $height;
                if ($ratio > 1) {
                    $newWidth  = $maxDimension;
                    $newHeight = (int)($maxDimension / $ratio);
                } else {
                    $newHeight = $maxDimension;
                    $newWidth  = (int)($maxDimension * $ratio);
                }

                Log::info('📐 [WP] Redimensionando imagen', [
                    'original' => "{$width}x{$height}",
                    'nuevo'    => "{$newWidth}x{$newHeight}",
                ]);

                $thumb = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($source);
                $source = $thumb;
            }

            $tempPath = tempnam(sys_get_temp_dir(), 'wp_opt_');
            imagejpeg($source, $tempPath, 80);
            imagedestroy($source);

            Log::info('✅ [WP] Imagen optimizada guardada', [
                'temp_path'    => $tempPath,
                'new_size_kb'  => round(filesize($tempPath) / 1024, 2),
            ]);

            return $tempPath;
        } catch (Exception $e) {
            Log::error('❌ [WP] Error optimizando imagen', [
                'path'  => $path,
                'error' => $e->getMessage(),
            ]);
            return $path;
        }
    }
}
