# CLAUDE.md — Contexto de desarrollo para Claude Code

Este archivo documenta el contexto completo del proyecto para asistir el desarrollo con Claude Code.

## Que es este proyecto

**Central Facturación / AppQimera** es un panel administrativo Laravel 12 que permite gestionar
la configuración de empresas colombianas para facturación electrónica ante la DIAN, usando la
API REST de Qimera (`https://factura.grupoqimera.co/api`).

No es un sistema de facturación en sí — es el panel de onboarding y configuración que prepara
a cada empresa para poder facturar. La emisión real de facturas ocurre fuera de esta app.

## Stack técnico

- PHP 8.2+, Laravel 12, MySQL
- Blade + Tailwind CSS 3 + Alpine.js 3 + Vite 7
- `spatie/laravel-permission` para RBAC
- `laravel/breeze` para autenticación
- cURL nativo (no Guzzle) para llamadas al API

## Arquitectura y patrones usados

### Patron toApiPayload()
Cada modelo tiene un método `toApiPayload()` que devuelve exactamente el body JSON que espera
el endpoint correspondiente. No mezclar lógica de presentación con lógica de API.

### QimeraApiService
Cliente cURL centralizado en `app/Services/QimeraApiService.php`. Maneja autenticación,
headers, debug logging y errores. Siempre lanzar `RuntimeException` en errores para que
los controllers los capturen con `catch (Throwable $e)`.

### Debug panel
`QimeraApiService::$lastDebug` captura el último request/response. Los controllers lo pasan
a la sesión con `$this->flashDebug()` y las vistas lo muestran en un panel amarillo.
Esto es intencional para desarrollo — no remover.

### Setting model
`app/Models/Setting.php` es un key/value store con cache automático (`rememberForever` +
invalidación en `saved`/`deleted`). Usar `Setting::get($key, $default)` y `Setting::set($key, $value)`.
No leer settings directamente de la DB — siempre usar estos métodos.

### Flujo de 4 pasos por empresa
El componente `x-company-progress` muestra el estado de cada paso. El paso está "done" cuando:
1. Empresa: `$company->api_token` no está vacío (el API devuelve el token al crear)
2. Software: `$company->software->last_synced_at` no es null
3. Certificado: `$company->certificate->last_synced_at` no es null
4. Resoluciones: `$company->habilitation_passed === true` (toggle manual)

## Modelos y relaciones

```
Company hasOne  CompanySoftware
Company hasOne  CompanyCertificate
Company hasMany CompanyResolution
CompanyResolution belongsTo Company
```

La resolución de habilitación se distingue por `is_habilitation = true`.
Solo puede haber una por empresa (updateOrCreate con ese flag).

## Rutas importantes

```
GET  /admin/companies                        companies.index
GET  /admin/companies/create                 companies.create
POST /admin/companies                        companies.store
GET  /admin/companies/{company}/edit         companies.edit
PUT  /admin/companies/{company}              companies.update
POST /admin/companies/{company}/sync         companies.sync
GET  /admin/companies/{company}/software     companies.software.edit
PUT  /admin/companies/{company}/software     companies.software.update
GET  /admin/companies/{company}/certificate  companies.certificate.edit
PUT  /admin/companies/{company}/certificate  companies.certificate.update
GET  /admin/companies/{company}/resolutions  companies.resolutions.index
POST /admin/companies/{company}/resolutions/habilitation    companies.resolutions.habilitation
POST /admin/companies/{company}/resolutions/test-invoice    companies.resolutions.test-invoice
POST /admin/companies/{company}/resolutions/toggle-habilitation
POST /admin/companies/{company}/resolutions  companies.resolutions.store
DELETE /admin/companies/{company}/resolutions/{resolution}  companies.resolutions.destroy
```

> Nota: `Route::resource('companies', ...)` está declarado pero el método `show` no existe
> en el controller. Agregar `->except(['show'])` o implementar el método.

## Endpoints del API Qimera

| Método | URL | Auth | Controller |
|--------|-----|------|-----------|
| POST | `/ubl2.1/config/{nit}/{dv}` | No | CompanyController@store/update/sync |
| PUT | `/ubl2.1/config/software` | Token empresa | CompanyController@updateSoftware |
| PUT | `/ubl2.1/config/certificate` | Token empresa | CompanyController@updateCertificate |
| PUT | `/ubl2.1/config/resolution` | Token empresa | CompanyResolutionController@store/storeHabilitation |
| POST | `/ubl2.1/invoice` | Token empresa | CompanyResolutionController@sendTestInvoice |

El token de empresa viene en la respuesta del primer endpoint (`response.token`).
Se guarda en `companies.api_token`.

## Permisos disponibles

```
users.view, users.create, users.edit, users.delete
roles.view, roles.create, roles.edit, roles.delete
settings.view, settings.edit
companies.view, companies.create, companies.edit, companies.delete, companies.sync
```

## Bugs conocidos y pendientes

### Bugs activos
1. **`show` method faltante** — `Route::resource` registra `GET /companies/{id}` pero
   `CompanyController` no tiene método `show`. Fix: agregar `->except(['show'])` en routes/web.php
   o implementar el método.

2. **`$step4` duplicado** — `company-progress-badges.blade.php` define la variable dos veces
   (líneas 8 y 39). Eliminar la segunda definición.

3. **`mail_password` en texto plano** — `_form.blade.php` usa `type="text"` para la contraseña
   de correo. Cambiar a `type="password"`.

### Seguridad pendiente para producción
- SSL desactivado en `QimeraApiService` (líneas 121-122) — togglear por `APP_ENV`
- Campos sensibles sin cifrar: `api_token`, `api_password`, `mail_password`, `certificate.password`
  → usar cast `encrypted` en los modelos
- Credenciales del seeder (`admin@admin.com` / `password`) deben cambiarse

### Deuda técnica
- Catálogos DIAN (tipo documento, organización, municipio, etc.) como campos numéricos libres.
  Deberían ser `<select>` con datos del catálogo DIAN.
- Sin softDeletes en Company (eliminar hace cascade permanente)
- `CompanyResolutionController` — todos los métodos requieren `companies.edit`, incluyendo `index`.
  Separar para que `companies.view` pueda ver resoluciones.
- Sin alerta proactiva de vencimiento de certificado
- Sin audit log
- Sin tests

## Comandos útiles

```bash
# Servidor de desarrollo
php artisan serve --port=8081

# Migraciones
php artisan migrate
php artisan migrate:fresh --seed   # BORRA TODO y recrea con seeder

# Seeder (crea roles, permisos y admin@admin.com)
php artisan db:seed

# Ver rutas
php artisan route:list --path=admin

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Convenciones del proyecto

- Los controllers del admin van en `app/Http/Controllers/Admin/`
- Las vistas del admin van en `resources/views/admin/{módulo}/`
- Formularios compartidos como partials `_form.blade.php`
- Cada controller implementa `HasMiddleware` con permisos por método
- Siempre `try/catch (Throwable $e)` en llamadas al API con fallback gracioso
- Usar `data_get($response, 'path.to.field')` para leer respuestas del API
- Los timestamps de sync se guardan en `last_synced_at` por tabla

## Ambiente de desarrollo

- XAMPP en Windows, PHP 8.2 en puerto 8081
- MySQL en 127.0.0.1:3306, base de datos `appqimera`, usuario `root` sin contraseña
- `APP_DEBUG=true`, queue/cache/session en database (no Redis)
- Mail driver: log (los emails se escriben en storage/logs)
