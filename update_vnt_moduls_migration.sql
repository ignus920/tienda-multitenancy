-- Script para actualizar el campo migration en vnt_moduls
-- Este script convierte los valores actuales a formatos compatibles con FlexibleTenantService

-- 1. Actualizar módulos con rutas de carpetas específicas
UPDATE `vnt_moduls` SET `migration` = 'ventas/*' WHERE `id` = 1 AND `name` = 'Ventas';
UPDATE `vnt_moduls` SET `migration` = 'inventario/*' WHERE `id` = 2 AND `name` = 'Inventario';
UPDATE `vnt_moduls` SET `migration` = 'contabilidad/*' WHERE `id` = 3 AND `name` = 'Contabilidad';
UPDATE `vnt_moduls` SET `migration` = 'compras/*' WHERE `id` = 4 AND `name` = 'Compras';
UPDATE `vnt_moduls` SET `migration` = 'recursos_humanos/*' WHERE `id` = 5 AND `name` = 'Recursos Humanos';
UPDATE `vnt_moduls` SET `migration` = 'crm/*' WHERE `id` = 6 AND `name` = 'CRM';
UPDATE `vnt_moduls` SET `migration` = 'reportes/*' WHERE `id` = 7 AND `name` = 'Reportes';
UPDATE `vnt_moduls` SET `migration` = 'ecommerce/*' WHERE `id` = 8 AND `name` = 'E-commerce';

-- 2. Verificar los cambios
SELECT id, name, migration FROM `vnt_moduls` WHERE status = 1;

-- 3. Opcional: Configuraciones más específicas si tienes subcarpetas organizadas
-- Ejemplo para Inventario con subcarpetas específicas:
-- UPDATE `vnt_moduls` SET `migration` = '["inventario/productos", "inventario/stock", "inventario/categorias"]' WHERE `id` = 2;

-- Ejemplo para CRM con módulos específicos:
-- UPDATE `vnt_moduls` SET `migration` = '["crm/contactos", "crm/oportunidades", "crm/actividades"]' WHERE `id` = 6;

-- Ejemplo para E-commerce completo:
-- UPDATE `vnt_moduls` SET `migration` = '["inventario/productos", "ecommerce/tienda", "ecommerce/carrito", "ecommerce/pagos"]' WHERE `id` = 8;