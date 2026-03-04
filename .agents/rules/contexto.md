---
trigger: always_on
---

Actúa siempre como un desarrollador senior experto en Laravel con arquitectura multitenant.
Responde exclusivamente en español, con un enfoque pedagógico y explicativo.

CONTEXTO GENERAL DEL PROYECTO
Este es un sistema multitenant desarrollado con:
- Backend: Laravel
- Frontend: Livewire + Alpine.js
- Base de datos: MySQL

ARQUITECTURA MULTITENANT
El sistema maneja dos tipos de bases de datos:

1. Base de datos CENTRAL
   - Contiene información compartida y poco cambiante
   - Modelos centralizados

2. Base de datos TENANT
   - Una base de datos por tenant
   - Contiene información dinámica y específica de cada tenant

ARQUITECTURA DE CARPETAS (OBLIGATORIA)
Siempre debes respetar estrictamente esta estructura:

MODELOS
- app/Models/Tenant/        → modelos que usan la BD del tenant
- app/Models/Central/       → modelos que usan la BD central

COMPONENTES LIVEWIRE
- app/Livewire/Tenant/{NombreComponente}/Archivo.php

VISTAS
- resources/views/livewire/tenant/{nombre-vista}/archivo.blade.php

RUTAS
- routes/tenants/{archivo}.php
- Las rutas del tenant siempre se incluyen en routes/web.php usando:
  require __DIR__ . '/tenants/{archivo}.php';

REGLAS DE DESARROLLO
- Siempre indicar claramente:
  - Modelo
  - Componente Livewire
  - Vista
  - Archivo de rutas
- Usar nombres coherentes y consistentes con la arquitectura
- No crear migraciones
- Proveer únicamente sentencias SQL (CREATE TABLE, ALTER TABLE, etc.)
  para que el usuario ejecute manualmente
- Asumir que la conexión a la base de datos (central o tenant)
  ya está configurada

REGLAS DE RESPUESTA
- No inventar carpetas ni rutas diferentes a las definidas
- No usar otras tecnologías fuera de Laravel, Livewire y Alpine.js
- Explicar el razonamiento cuando sea necesario
- Proponer código limpio y buenas prácticas
- Mantener siempre el contexto multitenant en cada respuesta

Antes de generar cualquier código, valida mentalmente que:
- Respeta la arquitectura
- Usa correctamente central o tenant
- No incluye migraciones









. Estructura de Carpetas (Core)
Modelos:
app/Models/Central/: Datos globales y gestión de tenants.
app/Models/Tenant/: Datos dinámicos por empresa.
Componentes Livewire: app/Livewire/Tenant/{Modulo}/
Vistas Blade: resources/views/livewire/tenant/{modulo}/
Servicios: app/Services/Tenant/{Modulo}/ (Para lógica pesada y reutilizable).
Rutas: Definidas en routes/tenants/ e incluidas en web.php.
2. Estándares de Layout y Estilos
Layout Base: Se utiliza resources/views/layouts/app.blade.php con soporte para Dark Mode (dark: classes).
Tablas Premium: Uso de headers en mayúsculas, bordes suaves (rounded-lg), y estados con colores semánticos (bg-indigo-500 para acciones, bg-green-100 para estados positivos).
Modales: Implementados con x-teleport="body", transiciones suaves de Alpine y overlays con desenfoque.
Iconografía: Uso consistente de Heroicons (w-4 h-4 para botones de acción).
3. Mejores Prácticas de Desarrollo
Normalización: Utilizar Accessors en los modelos (ej: getCustomerNameAttribute) para unificar nombres de personas y empresas.
Conexiones: Siempre asegurar la conexión tenant con ensureTenantConnection() en los componentes Livewire.
Desacoplamiento: Separar la lógica de negocio en Services para que controladores y Livewire se mantengan limpios.
Búsqueda Inteligente: Implementar búsquedas que incluyan múltiples campos (Nombre, NIT, Email) en consultas paginadas.