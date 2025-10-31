# Componentes de Selects Reutilizables

Esta carpeta contiene componentes reutilizables para selects comunes en la aplicación.

## 📁 Componentes Disponibles

### 1. **Type Identification** - Tipos de Identificación
```blade
<x-selects.type-identification
    wire:model.live="typeIdentificationId"
    label="Tipo de Identificación"
    required
/>
```

### 2. **Country** - Países
```blade
<x-selects.country
    wire:model="countryId"
    label="País"
    placeholder="Selecciona un país"
/>
```

### 3. **City** - Ciudades (filtradas por país)
```blade
<x-selects.city
    wire:model="cityId"
    :country-id="48"
    label="Ciudad"
/>
```

### 4. **Regime** - Regímenes
```blade
<x-selects.regime
    wire:model="regimeId"
    label="Régimen"
/>
```

### 5. **Fiscal Responsibility** - Responsabilidades Fiscales
```blade
<x-selects.fiscal-responsibility
    wire:model="fiscalResponsabilityId"
    label="Responsabilidad Fiscal"
/>
```

## 🎛️ Propiedades Disponibles

Todos los componentes soportan estas propiedades:

| Propiedad | Tipo | Por Defecto | Descripción |
|-----------|------|-------------|-------------|
| `wireModel` | string | null | Modelo de Livewire (`wire:model`) |
| `name` | string | varies | Nombre del campo |
| `id` | string | same as name | ID del elemento |
| `required` | boolean | true | Si es requerido |
| `placeholder` | string | varies | Texto del placeholder |
| `class` | string | default classes | Clases CSS |
| `label` | string | varies | Texto del label |
| `showLabel` | boolean | true | Mostrar o no el label |
| `error` | string | null | Mensaje de error |

### Propiedades específicas:

**City Component:**
- `countryId` (int): ID del país para filtrar ciudades (default: 48 - Colombia)
- `filterByCountry` (boolean): Si filtrar por país (default: true)

## 🔧 Ejemplos de Uso

### Ejemplo Básico
```blade
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-selects.type-identification wire:model.live="typeId" />
    <x-selects.regime wire:model="regimeId" />
</div>
```

### Ejemplo con Personalización
```blade
<x-selects.country
    wire:model="countryId"
    name="country_selection"
    id="my-country"
    label="Selecciona tu país"
    placeholder="Elige un país..."
    class="custom-select-class"
    :required="false"
/>
```

### Ejemplo con Ciudades Filtradas
```blade
{{-- Primero el país --}}
<x-selects.country wire:model.live="selectedCountry" />

{{-- Luego las ciudades filtradas --}}
<x-selects.city
    wire:model="cityId"
    :country-id="$selectedCountry"
    label="Ciudad"
/>
```

### Ejemplo sin Label
```blade
<x-selects.regime
    wire:model="regimeId"
    :show-label="false"
    placeholder="Selecciona régimen..."
/>
```

## 🚀 Ventajas

1. **Reutilizable**: Usa en cualquier formulario
2. **Consistente**: Mismo estilo en toda la app
3. **Mantenible**: Cambios centralizados
4. **Flexible**: Múltiples opciones de personalización
5. **Livewire Ready**: Compatible con wire:model
6. **Auto-actualizable**: Los datos se cargan automáticamente

## 📝 Notas

- Los componentes cargan automáticamente los datos de la base de datos
- Usan conexión central para tablas de configuración
- Soportan Livewire wire:model para reactividad
- Incluyen validación visual de errores
- Responsive y accesibles

## 🔄 Actualizaciones

Para agregar nuevos campos o modificar estos componentes, edita los archivos en:
`resources/views/components/selects/`