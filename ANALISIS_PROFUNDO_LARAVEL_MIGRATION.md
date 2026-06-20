# Análisis Profundo: Mega Uni Store v3 → Laravel
**Fecha:** Junio 2026 | **Estado actual:** PHP 8.1 Vanilla MVC → Migración a Laravel

---

## 1. Inventario Completo del Sistema

### 1.1 Métricas globales

| Componente | Cantidad | Líneas totales |
|---|---|---|
| Controladores PHP | 26 | ~7,995 |
| Modelos PHP | 23 | ~7,008 |
| Rutas (web.php) | 173 | 1,320 |
| Carpetas de vistas | 25 | ~110+ archivos |
| Tablas en BD | 75 | — |
| Tablas con modelo PHP | 23 (directas) | — |
| Tablas sin modelo PHP | 52 (pivotes + sin implementar) | — |
| Documentación .md | 66 archivos | 9 carpetas de rol |
| Servicios | 1 (Mailer.php) | — |
| Tests | 1 (SecuritySandboxTest.php) | — |
| Middlewares PHP | 0 (carpeta vacía) | — |
| Dependencias Composer | 1 (PHPMailer ^6.9) | — |

---

## 2. Arquitectura Actual (Vanilla PHP MVC)

### 2.1 Flujo de una petición

```
Usuario → public/index.php
            ↓ carga config/env.php
            ↓ require routes/web.php
                ↓ switch/case en $_GET['route']
                    ↓ instancia AuthController
                    ↓ requerirAutenticacion() / requerirRol([...]) / requerirPermiso('x.y')
                    ↓ instancia ControllerXxx → método()
                        ↓ instancia ModeloXxx → query PDO
                        ↓ extrae $_GET/$_POST
                        ↓ require view .php
```

Si `?ajax=1` → la vista devuelve solo el panel de contenido (sin layout).
Si `?ajax=0` → la vista detecta `$isAjax = false` → llama `require dashboard_layout.php` y termina.

### 2.2 Patrón SPA-lite

El frontend tiene un shell HTML permanente con una función JS `loadContent(ruta)` que hace fetch a `?route=X&ajax=1` e inyecta la respuesta en el panel principal. Esto simula navegación SPA sin framework. Todos los formularios AJAX usan `fetch()` hacia las rutas PHP. No hay frontend separado (no Vue/React/Inertia).

### 2.3 Trait ControllerHelper

Todos los 26 controladores usan `use ControllerHelper`. Centraliza:

- `redireccionar(string $ruta): never` — `header()` + exit
- `guardarMensaje(string $tipo, string $msg)` — flash en `$_SESSION['flash']`
- `generarCsrfToken()` / `validarCsrfToken()` — token único por sesión en `$_SESSION['csrf_token']`
- `usuarioIdActual(): int` — lee `$_SESSION['auth']['usuario_id']`
- `tiendaIdPermitida(): ?int` — `null` = Superadmin (ve todo), `int` = scoped por tienda
- `validarAccesoATienda(int $tiendaId)` — deniega si el rol no pertenece a esa tienda
- `esPeticionModal(): bool` — detecta `?modal=1`
- `jsonExito(array $data)` / `jsonError(string $msg)` — JSON para AJAX
- `denegarAcceso(string $msg)` — HTTP 403 + mensaje

### 2.4 Sistema de autenticación

- PHP sessions nativas (`session_start()`)
- `$_SESSION['auth']` contiene: `usuario_id`, `nombre`, `email`, `rol_principal['nombre']`, `rol_principal['tienda_id']`, array `permisos[]`
- Roles en BD: tabla `roles` + pivote `usuarios_roles`
- Permisos en BD: tabla `permisos` + pivote `roles_permisos` (37 permisos definidos)
- Login: `password_verify()` contra `password_hash` en tabla `usuarios`
- Password reset: tabla `solicitudes_cambio_contrasena` con token, expiración y HTTPS
- **No hay JWT, no hay tokens de API, no hay Laravel Sanctum**

### 2.5 Capa de datos (PDO raw)

Cada modelo recibe `PDO` por inyección en el constructor (`Database::getConnection()`). No hay ORM. Todas las queries son strings SQL con `prepare()` / `execute()`. Los `JOIN` complejos están inline en los métodos del modelo o directamente en el controlador (BackupController usa PDO directo sin modelo).

---

## 3. Mapa Completo: Controladores → Rutas → Vistas

| Controlador | Rutas | Vista folder | Estado |
|---|---|---|---|
| AuthController | 4 | auth/ | ✅ Completo |
| SetupController | 1 | — | ✅ Setup inicial |
| DashboardController | 10 | dashboard/ | ✅ Completo |
| TiendaController | 7 | tiendas/ | ✅ Completo |
| UsuarioController | 9 | usuarios/ | ✅ Completo |
| CategoriaController | 7 | categorias/ | ✅ Completo |
| UnidadMedidaController | 6 | unidades_medida/ | ✅ Completo |
| ImpuestoController | 7 | impuestos/ | ✅ Completo |
| ProductoController | 7 | productos/ | ✅ Completo |
| InventarioController | 7 | inventario/ | ✅ Completo |
| VentaController | 5 | ventas/ | ✅ Completo |
| CajaController | 10 | caja/ | ✅ Completo |
| DevolucionController | 4 | devoluciones/ | ✅ Completo |
| ClienteController | 6 | clientes/ | ✅ Completo |
| EmpleadoController | 6 | empleados/ | ✅ Completo |
| ProveedorController | 7 | proveedores/ | ✅ Completo |
| CuponController | 7 | cupones/ | ✅ Completo |
| ContratoController | 9 | contratos/ | ✅ Completo |
| NominaController | 7 | nomina/ | ✅ Completo |
| ReporteController | 9 | reportes/ | ✅ Completo |
| AuditoriaController | 1 | auditoria/ | ✅ Completo |
| PortalController | 8 | portal/ | ✅ Completo (Cliente) |
| PasswordController | 10 | password/ | ✅ Completo |
| MetodoPagoController | 8 | metodos_pago/ | ✅ Completo (nuevo) |
| NotificacionController | 6 | notificaciones/ | ✅ Completo (nuevo) |
| BackupController | 3 | backups/ | ✅ Completo (nuevo) |

**Total: 173 rutas en 26 controladores — todos implementados.**

---

## 4. Análisis de Tablas vs Modelos

### 4.1 Tablas con cobertura directa (23 modelos)

| Tabla BD | Modelo PHP | Notas |
|---|---|---|
| usuarios | Usuario.php | Auth, roles, permisos, tiendas |
| roles | Rol.php | incluye pivote usuarios_roles |
| permisos | Permiso.php | incluye pivote roles_permisos |
| tiendas | Tienda.php | multi-tenant |
| productos | Producto.php | incluye productos_impuestos, productos_proveedores |
| categorias | Categoria.php | árbol de categorías |
| unidades_medida | UnidadMedida.php | catálogo |
| impuestos | Impuesto.php | IVA, etc. |
| inventario | Inventario.php | incluye movimientos_inventario parcialmente |
| ventas | Venta.php | incluye ventas_detalle, ventas_cupones |
| cajas | Caja.php | incluye cajas_movimientos |
| devoluciones | Devolucion.php | incluye devoluciones_detalle |
| clientes | Cliente.php | incluye tiendas_clientes |
| empleados | Empleado.php | incluye cargos, areas parcialmente |
| proveedores | Proveedor.php | |
| cupones | Cupon.php | |
| contratos | Contrato.php | |
| nominas | Nomina.php | incluye nomina_detalle, nomina_empleado |
| reportes | Reporte.php | |
| notificaciones | Notificacion.php | |
| metodos_pago | MetodoPago.php | |
| solicitudes_cambio_contrasena | PasswordReset.php | |
| audit_log | Auditoria.php | |

### 4.2 Tablas sin modelo PHP (52 — clasificadas por tipo)

**Pivotes simples** (en Laravel serán `belongsToMany` sin modelo propio):
`roles_permisos`, `usuarios_roles`, `ventas_cupones`, `productos_impuestos`, `productos_proveedores`, `tiendas_productos`, `tiendas_clientes`, `empleados_areas`, `empleados_cargos`, `empleados_horarios`

**Detalles de entidad** (en Laravel serán `hasMany` desde el modelo padre, sin modelo propio si son solo lectura):
`ventas_detalle`, `devoluciones_detalle`, `nomina_detalle`, `nomina_empleado`, `presupuesto_detalle`, `asientos_detalle`

**Módulos documentados pero NO implementados en PHP** (requieren controlador + modelo nuevos en Laravel):
- `compras` + `compras_detalle` → módulo de órdenes de compra a proveedores
- `gastos` → registro de gastos operativos
- `presupuestos` + `presupuesto_detalle` → módulo presupuestal
- `asientos_contables` + `asientos_detalle` + `cuentas_contables` + `periodos_contables` + `centros_costo` → módulo de contabilidad general
- `conciliaciones` → conciliación bancaria
- `planes` + `suscripciones` → modelo de negocio SaaS (plataforma multitienda)
- `plataforma` → configuración global del sistema (nombre, logo, etc.)
- `tiendas_config` → configuración extendida por tienda

**Módulo RRHH avanzado** (documentado parcialmente, no implementado):
`aportes_seguridad_social`, `horas_extra`, `vacaciones`, `prestaciones_sociales`, `conceptos_nomina`, `horarios`, `turnos`

**Organización de empleados** (sin modelo, consultas inline en EmpleadoController):
`areas`, `cargos`, `empleados_areas`, `empleados_cargos`

**Infraestructura** (en Laravel son nativos o irrelevantes):
`sesiones` → reemplazar con `database` session driver de Laravel
`migrations` → reemplazar con migraciones Laravel
`failed_jobs` → Laravel lo crea automáticamente
`personal_access_tokens` → ya existe para Sanctum
`password_resets` + `password_reset_tokens` → Laravel Auth nativo
`envios_reporte` → crear modelo `EnvioReporte` en Laravel
`exportaciones` → crear modelo `Exportacion` en Laravel (actualmente BackupController usa PDO directo)
`atributos` + `productos_atributos` → variantes de productos, no implementado

---

## 5. Gap Analysis: Documentación vs Implementación

### 5.1 Documentación bien alineada con código ✅

| Doc | Controlador | Estado |
|---|---|---|
| Superadministrador/02_Tiendas | TiendaController | ✅ Alineado |
| Superadministrador/03_Usuarios | UsuarioController | ✅ Alineado |
| Superadministrador/07_Productos | ProductoController | ✅ Alineado |
| Superadministrador/08_Inventario | InventarioController | ✅ Alineado |
| Superadministrador/09_Ventas | VentaController | ✅ Alineado |
| Superadministrador/10_Caja | CajaController | ✅ Alineado |
| Superadministrador/15_Devoluciones | DevolucionController | ✅ Alineado |
| Administrador_Tienda/09_Cupones | CuponController | ✅ Alineado |
| Nomina_RRHH/04_Nomina | NominaController | ✅ Alineado |
| Superadministrador/18_Password_Reset | PasswordController | ✅ Alineado |

### 5.2 Gaps documentación → código (documentado pero con advertencias en doc)

**Cliente/00_ROL_OVERVIEW.md** marca como `⚠️ Pendiente`:
- catálogo, pedidos, perfil, feedback

**Realidad:** `PortalController.php` tiene 890 líneas con 14 métodos completamente implementados. Las rutas `portal.catalogo`, `portal.producto`, `portal.carrito.*`, `portal.checkout` existen en web.php (líneas ~1284-1320). **La documentación del rol Cliente está desactualizada — el código existe.**

**Nomina_RRHH**: documentación menciona control de horarios, horas extra, vacaciones. En el código `NominaController` solo cubre nómina básica. Las tablas `horarios`, `horas_extra`, `vacaciones`, `turnos` existen en BD pero no tienen controlador.

**Sistema/02_Modelos_Base.md**: documenta `plataforma` y `tiendas_config`. No existe modelo ni controlador para ninguna de las dos.

### 5.3 Gaps código → documentación (implementado pero no documentado)

- `MetodoPagoController` — sin doc (recién creado)
- `NotificacionController` — sin doc (recién creado)
- `BackupController` — sin doc (recién creado)
- `AuditoriaController` — solo 61 líneas, documentación de Superadmin no la cubre en detalle
- `SetupController` — setup inicial del sistema, sin doc detallada

### 5.4 Módulos en BD sin código ni documentación completa

| Módulo | Tablas BD | Controlador | Modelo | Doc |
|---|---|---|---|---|
| Compras/Proveeduría | compras, compras_detalle | ❌ | ❌ | ❌ |
| Gastos | gastos | ❌ | ❌ | ❌ |
| Contabilidad General | asientos_contables, cuentas_contables, periodos_contables, centros_costo | ❌ | ❌ | ❌ |
| Presupuestos | presupuestos, presupuesto_detalle | ❌ | ❌ | ❌ |
| Conciliación | conciliaciones | ❌ | ❌ | ❌ |
| RRHH Avanzado | horas_extra, vacaciones, aportes_seg_social, prestaciones | ❌ | ❌ | Parcial |
| Atributos/Variantes | atributos, productos_atributos | ❌ | ❌ | ❌ |
| Config Global | plataforma, tiendas_config | ❌ | ❌ | Parcial |
| Planes/Suscripciones | planes, suscripciones | ❌ | ❌ | ❌ |

---

## 6. Problemas Técnicos a Resolver en la Migración

### 6.1 Bug conocido: doble HTML en navbar
`backend/resources/views/layout/narbar.html` (sic) es un archivo HTML completo (con `<html>`, `<head>`, `<body>`) pero se incluye dentro del body del layout. Genera HTML anidado inválido. En Laravel debe convertirse en un partial Blade `@include('layout.navbar')` con solo el fragmento del nav.

### 6.2 Middlewares vacíos
`backend/app/Middlewares/` está vacío. Toda la protección de rutas está inline en `routes/web.php` mediante llamadas a `$authController->requerirRol(...)`. En Laravel esto debe trasladarse a Middleware clases: `CheckRole`, `CheckPermission`, aplicados via `Route::middleware()`.

### 6.3 CSRF manual
El token CSRF es único por sesión (nunca rota). Un token comprometido es válido hasta que el usuario cierra sesión. Laravel genera tokens por sesión pero los rota en cada respuesta. Migrar directamente usando `@csrf` de Blade y el middleware `VerifyCsrfToken` de Laravel.

### 6.4 Sin hashing en exportaciones
`BackupController::registrarExportacion()` registra `archivo_url = null` (no hay archivos físicos, todo se sirve inline). No hay path real. En Laravel se puede usar `Storage::disk('local')` si se quiere guardar CSVs físicamente.

### 6.5 Tablas duplicadas / Legacy Laravel
El backup_deploy.sql contiene `users`, `password_resets`, `password_reset_tokens`, `personal_access_tokens`, `failed_jobs`, `migrations` — tablas que genera Laravel por defecto. Coexisten con `usuarios` (la tabla real usada por el sistema). En la migración Laravel hay que mapear el guard de autenticación a la tabla `usuarios` o renombrarla a `users` con una migración de datos.

### 6.6 Vistas no son Blade
Todos los archivos `.php` en views usan PHP puro con `<?= ... ?>`, `<?php if ... ?>`. No hay ningún componente Blade, no hay `@extends`, no hay `@section`. La migración requiere convertir cada vista a Blade. Hay ~110 archivos de vistas.

### 6.7 Query N+1 latente
Varios modelos hacen queries dentro de bucles en el controlador. Ejemplo: `DashboardController` llama múltiples métodos de distintos modelos en secuencia sin caché. En Laravel estos se resolverán con `with()` (eager loading) de Eloquent.

### 6.8 Sin tests funcionales
Solo existe `SecuritySandboxTest.php`. No hay tests de integración, no hay tests de modelos, no hay tests de rutas. La migración a Laravel debería aprovechar el suite de testing (Pest/PHPUnit) para crear Feature Tests por módulo.

---

## 7. Equivalencias Directas Vanilla PHP → Laravel

| Componente actual | Equivalente Laravel |
|---|---|
| `public/index.php` (front controller) | `public/index.php` de Laravel (automático) |
| `routes/web.php` switch/case 173 cases | `routes/web.php` con `Route::get/post/put/delete` |
| `config/database.php` (PDO singleton) | `config/database.php` + `DB::` facade + `.env` |
| `ControllerHelper` trait | `App\Http\Controllers\Controller` base + Traits |
| `requerirAutenticacion()` | Middleware `auth` |
| `requerirRol(['X','Y'])` | Middleware `CheckRole` personalizado |
| `requerirPermiso('x.y')` | Middleware `CheckPermission` + Gates/Policies |
| `tiendaIdPermitida(): ?int` | Scope Eloquent `scopeForCurrentTenant()` |
| `$_SESSION['auth']` | `Auth::user()` + `auth()->user()->roles` |
| `guardarMensaje()` flash | `session()->flash('message', ...)` o `with('success', ...)` |
| `validarCsrfToken()` manual | Middleware `VerifyCsrfToken` automático + `@csrf` |
| `generarCsrfToken()` | `csrf_token()` helper / `@csrf` directive |
| PDO raw `prepare()`/`execute()` | Eloquent ORM o `DB::table()->...` Query Builder |
| `require view .php` | `return view('modulo.index', compact(...))` |
| `$isAjax = isset($_GET['ajax'])` | `Request::ajax()` o `$request->expectsJson()` |
| `jsonExito()` / `jsonError()` | `response()->json([...])` |
| Mailer.php (PHPMailer) | `Mail::to()->send(new Mailable())` (SMTP/Mailgun) |
| `password_verify()` | `Hash::check()` |
| `bin2hex(random_bytes(32))` | `Str::random(64)` o tokens de Sanctum |
| Tablas pivote manuales | `belongsToMany()` con `withTimestamps()` |
| `LEFT JOIN` inline en modelos | Eloquent `with(['relacion'])` |
| `?ajax=1` panel parcial | Livewire components o Inertia.js |
| Sin tests | Pest Feature Tests por controlador |
| Tablas `sesiones` en BD | Session driver `database` de Laravel |

---

## 8. Decisión Arquitectural Clave para Laravel

### Opción A: Laravel + Blade tradicional (más cercana al código actual)
- Vistas Blade con `@extends('layouts.app')` y `@section('content')`
- Livewire para formularios dinámicos (modales, filtros AJAX)
- Sin cambio de paradigma, migración más directa
- **Recomendado si quieres paridad funcional rápida**

### Opción B: Laravel + Inertia.js + Vue/React
- Backend Laravel como API, frontend en Vue 3 o React
- Reemplaza el SPA-lite actual con SPA real
- Mayor separación de responsabilidades
- **Recomendado si planeas escalar el frontend**

### Opción C: Laravel API + Frontend separado
- `routes/api.php` puro, autenticación con Sanctum tokens
- Frontend independiente (puede ser el que ya está en `frontend/src/`)
- Permite app móvil nativa (el directorio `mobile/` está vacío — oportunidad)
- **Recomendado si planeas la app móvil en paralelo**

**Para este proyecto, la Opción A (Blade + Livewire) es la traducción más directa** del patrón SPA-lite actual y requiere la menor reescritura de lógica de negocio.

---

## 9. Plan de Migración Recomendado (por fases)

### Fase 0 — Scaffolding Laravel (1-2 días)
```
composer create-project laravel/laravel mega-uni-store-laravel
```
- Configurar `.env` con la misma BD (o una copia)
- Configurar guard de auth para tabla `usuarios` (no `users`)
- Instalar: `laravel/breeze` (auth base), `livewire/livewire` (si Opción A), `spatie/laravel-permission` (roles/permisos)
- Crear directorios: `app/Http/Middleware/`, `app/Models/`, `app/Http/Controllers/`
- Copiar el SQL completo como seeder de datos inicial

### Fase 1 — Auth + Roles + Permisos (2-3 días)
Módulo crítico. Sin esto nada más funciona.
- `App\Models\Usuario` extends `Authenticatable` → mapear a tabla `usuarios`
- `App\Models\Rol`, `App\Models\Permiso` con relaciones `belongsToMany`
- Middleware `CheckRole` y `CheckPermission`
- `LoginController`, `LogoutController`
- Equivalente de `tiendaIdPermitida()` como método en `Usuario::tiendaScope()`
- Sesión con estructura `auth.usuario_id`, `auth.rol_principal`, `auth.permisos[]`
- **Prueba de integración:** login con cada uno de los 9 roles

### Fase 2 — Módulos de catálogo base (2-3 días)
Sin dependencias complejas, fáciles de portar:
`Tienda`, `Categoria`, `UnidadMedida`, `Impuesto`, `MetodoPago`
- Un Eloquent Model por tabla, relaciones básicas
- Controlador resource: `php artisan make:controller TiendaController --resource`
- Vistas Blade con layout + formularios simples
- Validación con `FormRequest` (reemplaza `validarDatos()` inline)

### Fase 3 — Módulos de productos e inventario (3-4 días)
- `Producto` con relaciones: `belongsTo(Categoria)`, `belongsTo(Tienda)`, `belongsToMany(Impuesto)`, `belongsToMany(Proveedor)`
- `Inventario` con `movimientos_inventario` como `hasMany(MovimientoInventario)`
- Scopes: `scopeActivos()`, `scopeParaTienda(int $tiendaId)`
- Livewire component para filtros de búsqueda con debounce

### Fase 4 — Módulos de ventas (3-4 días)
El módulo más complejo: `Venta`, `VentaDetalle`, `Caja`, `CajaMovimiento`, `Devolucion`, `Pago`, `MetodoPago`
- `Venta` con `hasMany(VentaDetalle)`, `belongsTo(Caja)`, `belongsToMany(Cupon)`
- Transacciones DB: `DB::transaction()` en `VentaController::store()`
- Caja: apertura/cierre con validación de turno activo

### Fase 5 — Módulos RRHH/Nómina (2-3 días)
`Empleado`, `Contrato`, `Nomina`, `NominaDetalle` + nuevos: `HoraExtra`, `Vacacion`, `Horario`, `Turno`
- Relaciones: `Empleado hasMany Contrato`, `Empleado belongsToMany Area`
- **Oportunidad:** implementar los módulos RRHH avanzados que están en BD pero no en código

### Fase 6 — Portal Cliente (2 días)
`PortalController` → separar en: `CatalogoController`, `CarritoController`, `CheckoutController`, `PedidoController`
- Autenticación separada para clientes (guard `cliente`) o mismo guard con rol
- Actualizar la documentación del rol Cliente

### Fase 7 — Reportes, Notificaciones, Backups (2 días)
- `ReporteController` → mantener lógica, cambiar exports a `maatwebsite/excel` para xlsx real y `barryvdh/laravel-dompdf` para PDF real (eliminar el truco de `window.print()`)
- `NotificacionController` → Livewire para marcado en tiempo real
- `BackupController` → crear modelo `Exportacion` con relación `belongsTo(Reporte)`

### Fase 8 — Password Reset + Setup + Auditoría (1 día)
- Password reset: usar el sistema nativo de Laravel (`Password::sendResetLink()`)
- Auditoría: middleware `LogActivity` automático
- Setup: `php artisan db:seed` con datos base

### Fase 9 — Módulos nuevos (post-migración)
Módulos que existen en BD pero nunca fueron implementados:
- `CompraController` (compras a proveedores)
- `GastoController`
- `ContabilidadController` (asientos, cuentas, periodos)
- `PlataformaController` (configuración global)
- `SuscripcionController` (planes SaaS)

---

## 10. Consideraciones de Seguridad en la Migración

| Riesgo | Situación actual | Solución Laravel |
|---|---|---|
| CSRF | Token único por sesión, no rota | `VerifyCsrfToken` + rotación automática |
| SQL Injection | PDO prepare/execute — bien protegido | Eloquent/QueryBuilder — igual de seguro |
| XSS | `htmlspecialchars()` manual en vistas | Blade `{{ }}` escapa automáticamente |
| Session Fixation | `session_regenerate(true)` en login | `Auth::login()` lo hace automáticamente |
| Masa asignación | Arrays manuales en modelos — seguro | Eloquent: declarar `$fillable` explícitamente |
| Validación | Manual en cada controlador | `FormRequest` centralizado por operación |
| Rate Limiting | Sin throttle en login | Middleware `throttle:5,1` en ruta de login |
| Auth Bypass | `requerirAutenticacion()` inline en cada ruta | Middleware `auth` en grupos `Route::middleware` |
| Exposición .env | `.env` en `backend/config/env.php` manual | `.env` de Laravel + `.gitignore` estándar |

---

## 11. Lo que se Reutiliza Directamente

Estos componentes NO necesitan reescritura, solo adaptación de sintaxis:

- **Toda la lógica SQL de los modelos** → se convierte directamente a QueryBuilder o Eloquent
- **Toda la validación de datos** → se mueve a `FormRequest` clases
- **Los SVG generators** de exportaciones → pueden mantenerse como `View::make()` + response
- **Las vistas export_pdf.php** → se convierten a Blade con `@extends('layouts.print')`
- **El sistema de flash messages** → `session()->flash()` es idéntico
- **La lógica multi-tenant de tienda** → Global Scope en Eloquent
- **Las 37 permissions** → se importan con seeder a `spatie/laravel-permission`
- **Los 9 roles** → se importan con seeder

---

## 12. Resumen Ejecutivo

**Lo que está bien hecho y se porta directamente:**
El proyecto tiene una arquitectura MVC limpia y consistente. La separación controlador/modelo/vista es respetada en los 26 controladores. La lógica de negocio está bien encapsulada en modelos con PDO parametrizado (sin SQL injection). El sistema de roles+permisos basado en BD es sólido y se mapea 1:1 a `spatie/laravel-permission`. Las 173 rutas están organizadas por módulo.

**Lo que requiere trabajo real en la migración:**
1. Convertir ~110 vistas PHP a Blade (mecánico pero tedioso)
2. Reemplazar el switch/case de 1,320 líneas por `Route::` fluent
3. Crear Eloquent Models con relaciones (actualmente son PDO raw)
4. Convertir el trait `ControllerHelper` en Middleware + base Controller
5. Implementar los 9 módulos que tienen tablas en BD pero cero código PHP

**Lo que la migración agrega sin costo extra:**
- Testing framework (Pest) para los 26 módulos
- ORM con eager loading (elimina N+1 queries latentes)
- Queue system para envíos de email (PHPMailer actual es síncrono)
- Broadcasting para notificaciones en tiempo real (reemplaza el polling manual)
- Storage abstraction para exportaciones físicas
- PDF/Excel reales (dompdf + maatwebsite/excel) en lugar del truco window.print()
- Horizon para monitorear jobs de nómina o reportes pesados

**Estimación de esfuerzo:**
- Migración paridad funcional (Fases 0-8): ~25-30 días de desarrollo
- Módulos nuevos (Fase 9): ~10-15 días adicionales
- Tests de integración completos: ~5 días
- **Total estimado: 6-8 semanas de desarrollo a tiempo completo**

---

*Documento generado: Junio 2026 — Mega Uni Store v3 → Laravel Migration Reference*
