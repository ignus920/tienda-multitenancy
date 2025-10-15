# Sistema Multitenancy RAP

Sistema de multi-tenancy con Laravel 12, Livewire 3 y autenticación 2FA.

## 🚀 Características

- **Multi-tenancy**: Cada empresa tiene su propia base de datos aislada
- **Autenticación 2FA**: Email, WhatsApp y Google Authenticator (TOTP)
- **Gestión de roles**: Sistema de permisos por tenant con Spatie Permission
- **Módulos**: Clientes, Productos, Ventas, Cajas, Inventario
- **Auto-registro**: Los usuarios pueden crear su propia empresa al registrarse

## 📋 Requisitos

- PHP 8.2 o superior
- MySQL 8.0 o superior
- Composer
- Node.js y NPM (para compilar assets)

## 🔧 Instalación

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio>
cd tienda-multitenancy
```

### 2. Instalar dependencias

```bash
composer install
npm install
```

### 3. Configurar el archivo .env

```bash
cp .env.example .env
```

Editar el archivo `.env` y configurar la base de datos principal:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rap
DB_USERNAME=root
DB_PASSWORD=

# Configuración de email para 2FA (opcional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# 2FA Settings
GOOGLE2FA_ENABLED=true
```

### 4. Generar la clave de aplicación

```bash
php artisan key:generate
```

### 5. Crear la base de datos central

Crear la base de datos `rap` en MySQL:

```sql
CREATE DATABASE rap CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Ejecutar las migraciones de la base central

```bash
php artisan migrate
```

**IMPORTANTE**: Esto solo creará las tablas en la base de datos central `rap` (usuarios, tenants, etc.). Las tablas de cada tenant se crean automáticamente cuando se registra una nueva empresa.

### 7. Compilar assets

```bash
npm run build
```

### 8. Iniciar el servidor

```bash
php artisan serve
```

Acceder a: `http://127.0.0.1:8000`

## 👤 Crear el primer usuario y empresa

### Opción 1: A través del registro web (Recomendado)

1. Ir a `http://127.0.0.1:8000/register`
2. Completar el formulario:
   - Nombre
   - Email
   - **Nombre de la Empresa**
   - Contraseña
3. El sistema automáticamente:
   - Creará el usuario
   - Creará la empresa/tenant
   - Creará la base de datos del tenant con todas las tablas
   - Te asignará como administrador
   - Te redirigirá al dashboard

### Opción 2: Mediante comandos Artisan

```bash
# 1. Crear un usuario
php artisan tinker
>>> $user = App\Models\User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('password')]);
>>> exit

# 2. Crear un tenant con el comando
php artisan tenant:create "Mi Empresa" "empresa@example.com" --owner-email="admin@example.com"
```

## 📁 Estructura de Bases de Datos

### Base de datos central: `rap`

Contiene las tablas globales:
- `users` - Todos los usuarios del sistema
- `tenants` - Información de las empresas/tenants
- `user_tenants` - Relación usuarios-empresas (un usuario puede acceder a varias empresas)
- `two_factor_codes` - Códigos de autenticación 2FA
- `sessions`, `cache`, `jobs` - Tablas del sistema

### Bases de datos de tenants: `tenant_{uuid}`

Cada empresa tiene su propia base de datos con:
- `clientes` - Clientes de la empresa
- `productos` - Catálogo de productos
- `categorias` - Categorías de productos
- `ventas` y `detalle_ventas` - Sistema de ventas
- `cajas` y `movimiento_cajas` - Control de caja
- `movimiento_inventarios` - Control de inventario
- `roles` y `permissions` - Permisos específicos del tenant

## 🔐 Autenticación

### Login
```
Email: admin@example.com
Password: password
```

### Flujo de autenticación
1. **Login** → Ingresa email y contraseña
2. **2FA** (si está habilitado) → Verifica código de autenticación
3. **Selección de Tenant** → Elige la empresa a la que deseas acceder
4. **Dashboard** → Acceso al panel de la empresa

## 📝 Comandos Artisan Disponibles

### Gestión de Tenants

```bash
# Crear un nuevo tenant
php artisan tenant:create "Nombre Empresa" "email@empresa.com" --owner-email="usuario@email.com"

# Asignar un usuario a un tenant
php artisan tenant:assign-user usuario@email.com {tenant-id} --role=admin

# Listar todos los tenants
php artisan tinker
>>> App\Models\Tenant::all();
```

## 🛠️ Desarrollo

### Crear nuevas migraciones para tenants

Las migraciones de los tenants deben ir en la carpeta `database/migrations/tenant/`:

```bash
# Crear una migración para tenants
php artisan make:migration create_nueva_tabla_table

# Mover manualmente el archivo a: database/migrations/tenant/
```

### Modificar el draft.yaml

El archivo `draft.yaml` contiene la definición de los modelos de los tenants. Usa Blueprint para generar código:

```bash
php artisan blueprint:build
```

Luego mueve las migraciones generadas a `database/migrations/tenant/`.

## 🔒 Seguridad

- Las contraseñas se hashean con bcrypt
- Protección CSRF habilitada
- Autenticación 2FA opcional
- Aislamiento total de datos entre tenants
- Cada tenant tiene su propia base de datos física

## 📚 Tecnologías Utilizadas

- **Laravel 12** - Framework PHP
- **Livewire 3** - Framework de componentes reactivos
- **TailwindCSS** - Framework CSS
- **Stancl Tenancy v3** - Sistema de multi-tenancy
- **Spatie Laravel Permission** - Sistema de roles y permisos
- **Laravel Breeze** - Autenticación
- **Blueprint** - Generador de código

## 🐛 Solución de Problemas

### Las migraciones de tenant se ejecutan en la base central

Si las tablas de clientes, productos, etc., aparecen en la base de datos `rap`, elimínalas:

```bash
php artisan tinker
>>> DB::statement('SET FOREIGN_KEY_CHECKS=0');
>>> DB::statement('DROP TABLE IF EXISTS clientes, productos, categorias, ventas, detalle_ventas, cajas, movimiento_cajas, movimiento_inventarios');
>>> DB::statement('SET FOREIGN_KEY_CHECKS=1');
```

Luego vuelve a crear el tenant con el comando o mediante registro.

### Error al crear tenant

Si un tenant falla al crearse, elimina la base de datos del tenant:

```bash
php artisan tinker
>>> $tenant = App\Models\Tenant::where('email', 'email@empresa.com')->first();
>>> DB::statement('DROP DATABASE IF EXISTS ' . $tenant->db_name);
>>> $tenant->delete();
```

## 📄 Licencia

Este proyecto es privado y de uso interno.
