# 🔐 Autenticación y Contraseñas — Sistema Global

> **Controladores:** `AuthController` · `PasswordController`
> **Sin permiso requerido** (rutas públicas) + rutas autenticadas para cambio de contraseña

---

## Estructura de controladores

```
AuthController.php        → login, registro, logout, helpers de sesión
PasswordController.php    → 3 flujos de gestión de contraseña
```

Ambos controladores usan `ControllerHelper` (trait) para `guardarMensaje()`, `redireccionar()`, `denegarAcceso()`.

---

## Rutas del módulo auth

| Route key | Método | Controller::método | Requiere auth |
|---|---|---|---|
| `login` | GET | `AuthController::mostrarLogin()` | No |
| `login.post` | POST | `AuthController::login()` | No |
| `register` | GET | `AuthController::mostrarRegistro()` | No |
| `register.post` | POST | `AuthController::registrar()` | No |
| `logout` | GET | `AuthController::logout()` | Implícito |
| `password.request` | GET | `PasswordController::mostrarFormularioReset()` | No |
| `password.request.post` | POST | `PasswordController::enviarLinkReset()` | No |
| `password.reset` | GET | `PasswordController::mostrarFormularioNuevoPassword()` | No (token en URL) |
| `password.reset.post` | POST | `PasswordController::aplicarNuevoPassword()` | No (token en POST) |
| `password.change` | GET | `PasswordController::mostrarFormularioCambio()` | ✅ Sí |
| `password.change.post` | POST | `PasswordController::procesarCambio()` | ✅ Sí |
| `password.requests` | GET | `PasswordController::listarSolicitudes()` | ✅ Admin/Superadmin |
| `password.approve` | POST | `PasswordController::aprobarSolicitud()` | ✅ Admin/Superadmin |
| `password.deny` | POST | `PasswordController::rechazarSolicitud()` | ✅ Admin/Superadmin |
| `password.admin.set` | POST | `PasswordController::adminSetPassword()` | ✅ Admin/Superadmin |

---

## Vistas del módulo auth

```
backend/resources/views/auth/
    login.php             → formulario de login
    register.php          → formulario de registro público
    password_forgot.php   → paso 1: ingresa email para recibir link
    password_pending.php  → pantalla de espera (Flujo C tras enviar solicitud)
    password_reset.php    → paso 2: nueva contraseña con token válido

backend/resources/views/password/
    change.php            → formulario solicitud de cambio (autenticado)
    requests.php          → listado de solicitudes pendientes (solo gestores)
```

---

## Flujo A — Reset por email (público)

Para usuarios que no recuerdan su contraseña.

```
GET  password.request      → password_forgot.php
     └─ Usuario ingresa su email

POST password.request.post → enviarLinkReset()
     ├─ Busca usuario activo por email (anti-enumeración: mismo mensaje siempre)
     ├─ Si existe: genera token (PasswordReset::crearToken($email))
     ├─ Envía email HTML con link: /index.php?route=password.reset&token=XXX
     └─ Redirige a password.request con flash success

GET  password.reset?token=XXX → password_reset.php
     └─ PasswordReset::buscarTokenValido($token) → si null → error + redirect

POST password.reset.post
     ├─ Valida token nuevamente (doble check)
     ├─ Valida password mínimo 8 caracteres y confirmación
     ├─ Usuario::cambiarPassword($usuarioId, $password)
     ├─ PasswordReset::marcarTokenUsado($token)
     └─ Flash success + redirect a login
```

**Seguridad del token:** expira en 1 hora (`created_at + 3600 segundos`). Una vez usado, `usado = 1` y no puede reutilizarse.

---

## Flujo B — Login normal

```
GET  login      → login.php
POST login.post → AuthController::login()
     ├─ Valida email + password (no vacíos, formato email)
     ├─ buscarActivoPorEmail($email) → null → "Credenciales incorrectas" (no distingue motivo)
     ├─ verificarPassword($password, $hash) → false → mismo error genérico
     ├─ obtenerRolPrincipalDeUsuario($id) → null → "sin rol asignado"
     ├─ session_regenerate_id(true)
     ├─ Puebla $_SESSION['auth'] con: usuario_id, nombre, apellido, email, avatar_url,
     │   roles[], permisos[], rol_principal{rol_id, rol_nombre, rol_nivel, tienda_id}
     └─ redireccionarSegunRolPrincipal() → cada rol tiene su propio dashboard
```

### Mapa rol → dashboard

| Rol | Route |
|---|---|
| Superadministrador | `dashboard.superadmin` |
| Administrador de Tienda | `dashboard.admin_tienda` |
| Supervisor | `dashboard.supervisor` |
| Vendedor | `dashboard.vendedor` |
| Bodeguero | `dashboard.bodeguero` |
| Reportero | `dashboard.reportero` |
| Nómina y RRHH | `dashboard.nomina` |
| Cliente | `dashboard.cliente` |
| Sistema | `dashboard.sistema` |

El mapa vive en `AuthController::mapaRolDashboard()` — única fuente de verdad usada también en `web.php`.

---

## Flujo C — Trabajador solicita cambio (requiere aprobación admin)

Para empleados (no admins) que quieren cambiar su contraseña. Requiere aprobación.

```
GET  password.change      → change.php
     └─ El formulario sabe si el usuario es admin directo (cambia el texto)

POST password.change.post → procesarCambio()
     ├─ Si rol está en ROLES_ADMIN_DIRECTO ['Superadministrador', 'Administrador de Tienda']:
     │   └─ Aplica cambio directo → Usuario::cambiarPassword() → flash + redirect dashboard
     └─ Si rol es otro (Vendedor, Bodeguero, etc.):
         ├─ hash = password_hash($password, PASSWORD_DEFAULT)
         ├─ PasswordReset::crearSolicitud($usuarioId, $hash) → INSERT solicitudes_cambio_contrasena
         └─ Flash "solicitud enviada" + redirect dashboard (estado: pending)

GET  password.requests     → listarSolicitudes() → requests.php
     └─ Solo ROLES_GESTORES ['Superadministrador', 'Administrador de Tienda']
     └─ Admin de Tienda: solo ve solicitudes de su tienda ($tiendaId filter)

POST password.approve      → aprobarSolicitud()
     ├─ Aplica el hash pre-calculado a la cuenta del trabajador
     ├─ Envía email de confirmación al trabajador
     └─ Flash success + redirect password.requests

POST password.deny         → rechazarSolicitud()
     ├─ Marca solicitud como rechazada con motivo
     ├─ Envía email de notificación al trabajador con el motivo
     └─ Flash + redirect password.requests
```

---

## Flujo D — Admin asigna contraseña directamente a otro usuario

```
POST password.admin.set    → adminSetPassword()
     ├─ Solo ROLES_GESTORES
     ├─ Admin de Tienda: no puede cambiar contraseñas de Superadmin ni de otro Admin
     ├─ Admin de Tienda: verifica que el usuario target pertenezca a su tienda
     ├─ Usuario::cambiarPassword($targetUserId, $password)
     └─ Redirect usuarios.show&id=X
```

Este flujo se dispara desde el perfil del usuario en el módulo Usuarios (botón "Cambiar contraseña").

---

## Registro público

```
GET  register      → register.php
POST register.post → AuthController::registrar()
     ├─ Valida campos: nombre, apellido, email, password, password_confirm
     ├─ password mínimo 8 caracteres
     ├─ existeEmail($email) → error si duplicado
     ├─ Usuario::crear(...) → INSERT usuarios
     ├─ Rol::asegurarRolesBase() → garantiza que exista rol 'Cliente'
     ├─ Rol::buscarPorNombre('Cliente') + asignarRolAUsuario()
     └─ Flash success + redirect login

Nota de seguridad: cualquier registro público queda como rol 'Cliente'.
Los roles administrativos solo se asignan desde gestión de usuarios (admin).
```

---

## Sesión PHP — estructura de $_SESSION['auth']

```php
$_SESSION['auth'] = [
    'usuario_id'    => int,
    'nombre'        => string,
    'apellido'      => string,
    'email'         => string,
    'avatar_url'    => string|null,
    'roles'         => array,           // todos los roles del usuario
    'permisos'      => array,           // todas las acciones permitidas
    'rol_principal' => [
        'usuario_rol_id' => int,
        'rol_id'         => int,
        'rol_nombre'     => string,
        'rol_nivel'      => int,        // nivel jerárquico (menor = mayor jerarquía)
        'tienda_id'      => int|null,   // null para roles globales
    ],
    'login_at' => 'Y-m-d H:i:s',
];
```

---

## Seguridad de sesión

```php
// iniciarSesionSegura() — llamado en AuthController::__construct()
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
session_set_cookie_params(['secure' => $httpsActivo, 'samesite' => 'Lax', ...]);
session_start();

// En login exitoso:
session_regenerate_id(true);  // previene session fixation
```

---

## Helpers de autenticación (disponibles para todos los controllers)

Estos métodos están en `AuthController` pero son llamados desde `web.php` o controllers:

| Método | Descripción |
|---|---|
| `requerirAutenticacion()` | Redirige a login si no hay sesión |
| `requerirRol(array $roles)` | 403 si rol principal no está en la lista |
| `requerirPermiso(string $accion, ?int $tiendaId)` | 403 si el permiso no existe para ese usuario/tienda |
| `requerirPermisoEnTienda(string $accion, int $tiendaId)` | Versión estricta: requiere tiendaId > 0 |
| `estaAutenticado()` | `bool` — si existe `$_SESSION['auth']['usuario_id']` |
| `usuarioActual()` | `?array` — todo el array `$_SESSION['auth']` |

---

## Tablas involucradas

| Tabla | Propósito |
|---|---|
| `usuarios` | Credenciales y datos del usuario |
| `roles` | Catálogo de roles con nivel jerárquico |
| `usuarios_roles` | Asignación usuario ↔ rol ↔ tienda |
| `permisos` | Catálogo de acciones |
| `rol_permisos` | Asignación rol ↔ permiso |
| `password_resets` | Tokens de reset por email (Flujo A) |
| `solicitudes_cambio_contrasena` | Solicitudes de cambio pendientes (Flujo C) |

Ambas tablas de password se crean automáticamente via `CREATE TABLE IF NOT EXISTS` en el constructor de `PasswordReset`.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
