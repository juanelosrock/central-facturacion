# Central Facturación — AppQimera

Panel administrativo para la gestión de facturación electrónica DIAN (Colombia) mediante la API de Qimera.

## Stack

- **Backend:** PHP 8.2+, Laravel 12
- **Frontend:** Blade, Tailwind CSS 3, Alpine.js 3, Vite 7
- **Base de datos:** MySQL
- **Paquetes clave:** `spatie/laravel-permission`, `laravel/breeze`

## Requisitos

- PHP >= 8.2
- Composer
- Node.js >= 18
- MySQL

## Instalación

```bash
git clone https://github.com/juanelosrock/central-facturacion.git
cd central-facturacion

composer install
npm install

cp .env.example .env
php artisan key:generate

# Configurar DB en .env, luego:
php artisan migrate --seed
npm run build
```

## Credenciales por defecto (desarrollo)

| Campo | Valor |
|-------|-------|
| Email | admin@admin.com |
| Password | password |

> **Cambiar antes de usar en producción.**

## Configuración del API

Ingresar a `/admin/settings` con el rol `admin` y configurar:
- **URL del API Qimera:** `https://factura.grupoqimera.co/api`
- **Token global del API**

## Flujo de configuración por empresa

Cada empresa sigue 4 pasos en orden:

1. **Empresa** — Datos básicos + sincronización con `/ubl2.1/config/{nit}/{dv}`
2. **Software** — Identificador y PIN DIAN → `/ubl2.1/config/software`
3. **Certificado** — Upload del `.p12` / `.pfx` → `/ubl2.1/config/certificate`
4. **Resoluciones** — Habilitación (datos fijos DIAN) + resoluciones de producción

## Roles y permisos

| Rol | Permisos |
|-----|----------|
| `admin` | Todos |
| `editor` | `users.view`, `roles.view` |
| `user` | Ninguno |

Los permisos disponibles son: `users.*`, `roles.*`, `settings.*`, `companies.*` (view, create, edit, delete, sync).

## Servidor de desarrollo

```bash
php artisan serve --port=8081
npm run dev  # en otra terminal (hot reload)
```

## Estructura principal

```
app/
├── Http/Controllers/Admin/
│   ├── CompanyController.php          # CRUD empresas + sync API
│   ├── CompanyResolutionController.php # Resoluciones + factura de prueba
│   ├── UserController.php
│   ├── RoleController.php
│   └── SettingController.php
├── Models/
│   ├── Company.php                    # toApiPayload()
│   ├── CompanySoftware.php
│   ├── CompanyCertificate.php
│   ├── CompanyResolution.php          # habilitationPayload(), habilitationTestInvoicePayload()
│   └── Setting.php                    # Cache-aware key/value store
├── Services/
│   └── QimeraApiService.php           # Cliente cURL + debug panel
└── View/Components/
    └── company-progress.blade.php     # Indicador de progreso 4 pasos
```

## Variables de entorno clave

```env
APP_ENV=local          # cambiar a "production" en prod
APP_DEBUG=true         # cambiar a false en prod
DB_DATABASE=appqimera
DB_USERNAME=root
DB_PASSWORD=
```
