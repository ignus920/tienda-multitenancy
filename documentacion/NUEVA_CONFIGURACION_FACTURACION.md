# Nueva Configuración de Facturación - Prioridad Base de Datos

## 📋 Resumen

Este documento describe la nueva implementación de configuración de facturación que **PRIORIZA LA BASE DE DATOS**:
- **Configuración desde base de datos** (tabla `cnf_invoices` filtrada por `warehouse`) - **PRIMERA PRIORIDAD**
- **Configuración por tenant** (almacenada en `tenant.settings['facturacion']`) - **SOLO FALLBACK**

## 🏗️ Arquitectura

### 1. Prioridades de Configuración ⚠️ IMPORTANTE

El sistema busca configuración en el siguiente orden:

1. **Base de Datos** (Primera prioridad - SIEMPRE)
   - Tabla: `cnf_invoices`
   - Relación: `tenant.company_id` → `vnt_warehouses.companyId` → `cnf_invoices.id_warehouses`
   - **NO hay valores hardcodeados**

2. **Settings del Tenant** (Fallback únicamente)
   - Ubicación: `tenant.settings['facturacion']`
   - Solo se usa si NO existe configuración en BD
   - Se registra WARNING para migrar a BD

### 2. Flujo de Datos

```
Tenant
├── company_id (campo directo)
└── settings['facturacion'] (opcional)

Company ID
├── vnt_warehouses (BD Central/RAP)
│   └── filtrar por companyId
└── cnf_invoices (BD Tenant)
    └── filtrar por id_warehouses
```

## 📁 Estructura de Archivos

### Nuevos Archivos

1. **`app/Models/Tenant/CnfInvoice.php`**
   - Modelo para tabla `cnf_invoices`
   - Relaciones con warehouses

2. **`app/Services/Facturacion/DatabaseConfigService.php`**
   - Servicio para consultar configuración desde BD
   - Maneja relación tenant → company → warehouses → cnf_invoices

3. **`app/Console/Commands/TestFacturacionIntegration.php`**
   - Comando de prueba para validar integración
   - Uso: `php artisan test:facturacion-integration {tenant_id?}`

### Archivos Modificados

1. **`app/Services/Facturacion/TenantConfigManager.php`**
   - Integra ambas fuentes de configuración
   - Mantiene compatibilidad con lógica existente

2. **`app/Services/Facturacion/ApiClient.php`**
   - Actualizado para usar TenantConfigManager
   - Automáticamente usa la nueva lógica de prioridades

## 🗄️ Estructura de Base de Datos

### Tabla: `cnf_invoices`

```sql
CREATE TABLE cnf_invoices (
    id INT PRIMARY KEY,
    token VARCHAR(255) NOT NULL,        -- Token de autenticación API
    id_warehouses INT NOT NULL,         -- ID del warehouse
    numeracion INT,                     -- Numeración de facturas
    facturador VARCHAR(255),           -- URL base de la API (base_url)
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Mapeo de campos:**
- `token` → Token de autenticación
- `facturador` → **base_url** de la API
- `id_warehouses` → ID del warehouse
- `numeracion` → Numeración de facturas

### Relaciones

```
tenants.company_id → vnt_warehouses.companyId → cnf_invoices.id_warehouses
```

## 🔧 Uso

### 1. Obtener Configuración

```php
use App\Services\Facturacion\TenantConfigManager;

$tenant = Tenant::find('tenant-uuid');
$config = TenantConfigManager::getFacturacionConfig($tenant);

// La configuración retornada incluye:
// - base_url, token, username, timeout (estándar)
// - source: 'settings' | 'database'
// - warehouse_id, numeracion, facturador (si viene de BD)
```

### 2. Usar ApiClient

```php
use App\Services\Facturacion\ApiClient;

$tenant = Tenant::find('tenant-uuid');
$apiClient = ApiClient::forTenant($tenant);

// Automáticamente usa la configuración con prioridades
```

### 3. Obtener Configuraciones Específicas

```php
// Todas las configuraciones de warehouses para un tenant
$allConfigs = TenantConfigManager::getAllWarehouseConfigs($tenant);

// Configuración específica por warehouse
$warehouseConfig = TenantConfigManager::getConfigByWarehouseId($warehouseId);
```

## 🧪 Pruebas

### Comando de Prueba

```bash
# Probar un tenant específico
php artisan test:facturacion-integration tenant-uuid

# Probar todos los tenants
php artisan test:facturacion-integration
```

### Verificaciones del Comando

1. **Información del tenant**
   - ID, nombre, company_id, estado

2. **Configuración desde Settings**
   - Valida si existe configuración en `tenant.settings['facturacion']`
   - Muestra token, URL base, estado

3. **Configuración desde BD**
   - Busca company_id → warehouses → cnf_invoices
   - Lista todos los warehouses configurados

4. **Configuración Final**
   - Muestra qué configuración se está usando (prioridad)
   - Indica la fuente (settings vs database)

5. **Prueba ApiClient**
   - Verifica que ApiClient se cree correctamente
   - Valida configuración aplicada

## ⚠️ Consideraciones Importantes

### 1. Compatibilidad

- **Totalmente compatible** con configuraciones existentes en `tenant.settings['facturacion']`
- Si un tenant ya tiene configuración en settings, se seguirá usando
- La BD solo se consulta si no hay configuración válida en settings

### 2. Rendimiento

- Las consultas a BD solo se hacen cuando no hay configuración en settings
- Se implementa logging para monitorear el flujo de configuración

### 3. Fallbacks

1. Settings del tenant (si válido)
2. Base de datos (si company_id existe y hay warehouses/cnf_invoices)
3. Configuración por defecto (valores hardcodeados)

## 🔍 Troubleshooting

### Problema: No se encuentra configuración

1. **Verificar company_id en tenant**
   ```php
   $tenant = Tenant::find('tenant-uuid');
   dd($tenant->company_id);
   ```

2. **Verificar warehouses para company**
   ```php
   use App\Models\Central\VntWarehouse;
   $warehouses = VntWarehouse::where('companyId', $companyId)->get();
   ```

3. **Verificar cnf_invoices para warehouses**
   ```php
   use App\Models\Tenant\CnfInvoice;
   $configs = CnfInvoice::whereIn('id_warehouses', $warehouseIds)->get();
   ```

### Problema: ApiClient no funciona

1. **Ejecutar comando de prueba**
   ```bash
   php artisan test:facturacion-integration tenant-uuid
   ```

2. **Revisar logs**
   - Los servicios registran información detallada en logs
   - Buscar por tenant_id para seguir el flujo

## 📈 Beneficios

1. **Flexibilidad**: Permite configuración por settings o BD
2. **Escalabilidad**: Configuración centralizada para múltiples warehouses
3. **Compatibilidad**: No rompe configuraciones existentes
4. **Mantenibilidad**: Centraliza la lógica en TenantConfigManager
5. **Auditoría**: Logging detallado del flujo de configuración