# Módulo de Recepción de Importaciones

## Descripción
Este módulo permite a la empresa recibir pedidos de importación, filtrar por shipment, identificar diferencias entre cantidades enviadas y recibidas, y gestionar novedades del proceso.

## Archivos creados

### Modelos
- `modelos/RecepcionImportacion.php` - Modelo principal con toda la lógica de negocio

### Controladores AJAX
- `ajax/recepcionImportacion.php` - Controlador para todas las operaciones AJAX

### Vistas
- `vistas/recepcionImportacion.php` - Vista principal del módulo
- `vistas/scripts/recepcionImportacion.js` - JavaScript con toda la funcionalidad frontend

### Base de datos
- `bd/tablas_recepcion_importacion.sql` - Script SQL con tablas adicionales

## Funcionalidades implementadas

### 1. Lista de envíos pendientes
- Muestra todos los envíos con productos en estado "En tránsito" (estado = 7)
- Información resumida: consecutivo, DEL, ETD, transportadora, packings, productos, valor total
- Botón para iniciar proceso de recepción

### 2. Recepción por shipment
- Filtrado automático por envío seleccionado
- Vista detallada de todos los productos del shipment
- Resumen con contadores de productos totales, recibidos, con novedades y retrasados

### 3. Identificación de diferencias
- Cálculo automático de diferencias entre cantidades enviadas vs recibidas
- Sumatoria de productos en el shipment
- Sumatoria de valores del shipment
- Colores diferenciados para identificar diferencias rápidamente

### 4. Novedades del pedido
- Espacio para crear items de novedad
- Verificación de trazabilidad de estados
- Gestión de productos que no llegan
- Cambio de estado a "Retrasado" o "Solicitado"

### 5. Ingreso de cantidades reales
- Modal para ingreso de cantidad real recibida
- Validación automática de diferencias
- Solicitud obligatoria de justificación cuando hay diferencias
- Comentarios de recepción

### 6. Gestión de estados
- Cambio automático de estado según cantidad recibida:
  - Si cantidad recibida = cantidad enviada → "Recibido" (estado 8)
  - Si cantidad recibida = 0 → "Retrasado" (estado 9)
  - Si cantidad recibida > 0 pero diferente → "Recibido" con novedad (estado 8, novedades=1)

## Tablas de base de datos

### Tablas principales (existentes)
- `i_importacion` - Productos de importación
- `i_envios` - Envíos/shipments
- `i_picking` - Packings por envío
- `i_estados` - Estados del proceso

### Tablas adicionales (nuevas)
- `i_justificaciones` - Justificaciones para diferencias
- `i_novedades` - Novedades del proceso
- `i_logs` - Log de actividades

## Instalación

### 1. Ejecutar script SQL
```sql
-- Ejecutar el archivo: bd/tablas_recepcion_importacion.sql
```

### 2. Agregar al menú del sistema
Agregar la opción en el menú principal:
```html
<li><a href="recepcionImportacion.php"><i class="fa fa-truck"></i> Recepción Importaciones</a></li>
```

### 3. Configurar permisos
Asegurar que el permiso 'Importaciones' esté configurado para los usuarios que usarán el módulo.

## Uso del módulo

### Flujo de trabajo
1. **Ver envíos pendientes**: Lista de todos los shipments en tránsito
2. **Seleccionar envío**: Hacer clic en "Recibir Envío"
3. **Revisar productos**: Verificar lista completa de productos del shipment
4. **Recibir productos**:
   - Hacer clic en botón de recepción por producto
   - Ingresar cantidad real recibida
   - Agregar justificación si hay diferencias
   - Confirmar recepción
5. **Gestionar novedades**: Para productos con problemas
6. **Confirmar recepción completa**: Cuando todo esté revisado

### Filtros disponibles
- **Por estado**: Todos, En tránsito, Recibidos, Retrasados
- **Por diferencias**: Todos, Con diferencias, Sin diferencias

### Indicadores visuales
- **Verde**: Sin diferencias
- **Amarillo**: Diferencias menores
- **Rojo**: Diferencias significativas o productos faltantes
- **Iconos de novedades**: Para productos con observaciones

## Beneficios

1. **Control total**: Seguimiento detallado de cada producto recibido
2. **Trazabilidad**: Registro completo de cambios y justificaciones
3. **Eficiencia**: Proceso optimizado de recepción
4. **Reportes**: Información consolidada para toma de decisiones
5. **Gestión de novedades**: Manejo sistemático de incidencias

## Notas importantes

- Este módulo NO modifica los archivos existentes de importación
- Utiliza las tablas existentes pero agrega funcionalidad nueva
- Mantiene compatibilidad con el sistema actual
- Permite futuras eliminaciones de productos retrasados