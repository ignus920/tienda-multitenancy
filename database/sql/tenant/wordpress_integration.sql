-- ==========================================================
-- INTEGRACIÓN WORDPRESS / WOOCOMMERCE - MIGRACIÓN ERP
-- ==========================================================

-- 1. Tabla de configuración de WordPress (Centralizada por Tenant)
CREATE TABLE IF NOT EXISTS `inv_wordpress_configs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `wp_url` VARCHAR(255) NOT NULL,
    `wp_user` VARCHAR(255) NOT NULL COMMENT 'Consumer Key de WooCommerce',
    `wp_password` VARCHAR(255) NOT NULL COMMENT 'Consumer Secret de WooCommerce',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Inserción inicial de configuración (Basado en wordpress_config.php)
INSERT INTO `inv_wordpress_configs` (`wp_url`, `wp_user`, `wp_password`, `is_active`) 
VALUES (
    'http://fervicom.com/', 
    'ck_6bad96515a8c9fc4388656d028eebb9651c6e1e8', 
    'cs_c515714041b34ea6cd95ded916eec285ec5d6657', 
    1
);

-- 3. Actualización de la tabla de galería de imágenes
-- Agregar campo para rastrear ID de medio en WordPress
ALTER TABLE `inv_image_gallery` ADD COLUMN IF NOT EXISTS `wp_media_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `type`;

-- Agregar campo para selección individual de sincronización
ALTER TABLE `inv_image_gallery` ADD COLUMN IF NOT EXISTS `sync_to_wp` TINYINT(1) NOT NULL DEFAULT 0 AFTER `wp_media_id`;
