# 🔐 Módulo Contraseñas — PasswordReset

> **Roles:** Todos los usuarios (Flujo A) · Usuarios de tienda (Flujo C)
> **Controlador:** `PasswordController`
> **Modelo:** `PasswordReset`
> **Vistas:** `resources/views/auth/` (request, reset, change)
> **Tablas:** `password_resets` · `solicitudes_cambio_contrasena`

---

## Descripción

El módulo gestiona dos flujos independientes de cambio de contraseña:

- **Flujo A — Reset por email:** el usuario solicita un enlace de reset, recibe el token por correo y establece una contraseña nueva sin intervención humana.
- **Flujo C — Solicitud con aprobación admin:** el trabajador solicita un cambio de contraseña desde su perfil; el admin aprueba o rechaza la solicitud desde el panel administrativo.

---

## Flujo A — Reset por email

### Rutas

| Ruta | Método | Descripción |
|---|---|---|
| `password.request` | GET | Formulario "Olvidé mi contraseña" |
| `password.email` | POST | Envía el email con el token |
| `password.reset` | GET | Formulario de nueva contraseña (con `?token=...`) |
| `password.update` | POST | Aplica la nueva contraseña |

### Proceso

```
1. Usuario → formulario password.request → introduce su email
   ↓
2. POST password.email
   → Verifica que el email existe en la tabla usuarios
   → PasswordReset::crearToken($email)
      → DELETE tokens anteriores no usados del mismo email
      → Genera token = bin2hex(random_bytes(32)) // 64 chars hex
      → expires_at = NOW() + 1 hora
      → INSERT INTO password_resets
   → Mailer::enviarResetPassword($email, $token, $nombre)
   ↓
3. Usuario recibe email con enlace: ?route=password.reset&token=XXXX
   ↓
4. GET password.reset
   → PasswordReset::buscarTokenValido($token)
      → WHERE token = :token AND used_at IS NULL AND expires_at > NOW()
   → Si inválido → error y redirect a password.request
   ↓
5. POST password.update
   → Valida contraseña nueva (longitud, confirmación)
   → password_hash($nuevaPassword, PASSWORD_BCRYPT)
   → UPDATE usuarios SET password_hash = :hash WHERE email = :email
   → PasswordReset::marcarTokenUsado($token)  // SET used_at = NOW()
   → Redirige a login con mensaje de éxito
```

### Tabla `password_resets`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT PK AUTO | Identificador único |
| `email` | VARCHAR(255) | Email del usuario que solicitó el reset |
| `token` | VARCHAR(64) UNIQUE | Token hex de 64 caracteres |
| `expires_at` | DATETIME | Vencimiento del token (1 hora desde creación) |
| `used_at` | DATETIME NULL | Fecha de uso (null = no usado / disponible) |
| `created_at` | DATETIME | Fecha de creación |

> La tabla se crea automáticamente al instanciar `PasswordReset` mediante `crearTablasIfNotExist()` en el constructor. No requiere migración manual.

---

## Flujo C — Solicitud con aprobación admin

### Rutas

| Ruta | Método | Permiso | Descripción |
|---|---|---|---|
| `password.change` | GET | Autenticado | Formulario de solicitud de cambio |
| `password.solicitar` | POST | Autenticado | Envía la solicitud al admin |
| `password.solicitudes` | GET | Admin | Listado de solicitudes pendientes |
| `password.aprobar` | POST | Admin | Aprueba y aplica el nuevo password |
| `password.rechazar` | POST | Admin | Rechaza con motivo |

### Proceso

```
1. Trabajador → password.change → introduce contraseña nueva (x2)
   ↓
2. POST password.solicitar
   → Valida que las contraseñas coincidan y cumplan longitud mínima
   → $hash = password_hash($nueva, PASSWORD_BCRYPT)
   → PasswordReset::crearSolicitud($usuarioId, $hash)
      → UPDATE pendientes anteriores SET estado='rechazada', motivo='Cancelada por nueva solicitud'
      → INSERT INTO solicitudes_cambio_contrasena (usuario_id, nuevo_password_hash, estado='pendiente')
   → Notifica al admin (flash + email opcional)
   ↓
3. Admin → password.solicitudes → ve todas las solicitudes pendientes
   → Superadmin: ve todas las tiendas
   → Admin de Tienda: solo solicitudes de usuarios de su tienda
   ↓
4a. Admin aprueba → POST password.aprobar
   → PasswordReset::aprobarSolicitud($solicitudId, $adminId)
      → beginTransaction()
      → UPDATE usuarios SET password_hash = :hash WHERE id = :usuario_id
      → UPDATE solicitudes SET estado='aprobada', admin_id=:adminId
      → commit()
   → Mailer::enviarAprobacionSolicitud($email, $nombre) (si está configurado)

4b. Admin rechaza → POST password.rechazar
   → PasswordReset::rechazarSolicitud($solicitudId, $adminId, $motivo)
      → UPDATE solicitudes SET estado='rechazada', admin_id=:adminId, motivo_rechazo=:motivo
   → Mailer::enviarRechazoSolicitud($email, $nombre, $motivo)
```

### Tabla `solicitudes_cambio_contrasena`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT UNSIGNED PK | Identificador único |
| `usuario_id` | INT UNSIGNED FK | Usuario que solicita el cambio |
| `nuevo_password_hash` | VARCHAR(255) | Hash de la nueva contraseña (bcrypt) |
| `estado` | ENUM('pendiente','aprobada','rechazada') | Estado de la solicitud |
| `admin_id` | INT UNSIGNED NULL | Admin que procesó la solicitud |
| `motivo_rechazo` | VARCHAR(255) NULL | Motivo del rechazo (solo si rechazada) |
| `created_at` | TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | Última actualización (ON UPDATE CURRENT_TIMESTAMP) |

> La tabla se crea automáticamente al instanciar `PasswordReset`. No requiere migración manual.

---

## Modelo: `PasswordReset.php`

### Auto-creación de tablas

```php
public function __construct()
{
    $this->db = Database::getConnection();
    $this->crearTablasIfNotExist();  // Crea ambas tablas si no existen
}
```

Este patrón garantiza que el módulo funcione incluso en instalaciones nuevas sin necesidad de ejecutar migraciones manualmente. El costo de la verificación es mínimo: `INFORMATION_SCHEMA` retorna `true` después de la primera ejecución.

### Helper privado: `sqlUsuarioRolPrincipal()`

```php
private function sqlUsuarioRolPrincipal(): string
{
    return "
        SELECT ur1.usuario_id, ur1.rol_id, ur1.tienda_id
        FROM usuarios_roles ur1
        INNER JOIN roles r1 ON r1.id = ur1.rol_id
        WHERE r1.activo = 1
          AND NOT EXISTS (
              SELECT 1
              FROM usuarios_roles ur2
              INNER JOIN roles r2 ON r2.id = ur2.rol_id
              WHERE ur2.usuario_id = ur1.usuario_id
                AND r2.activo = 1
                AND (
                    r2.nivel < r1.nivel
                    OR (r2.nivel = r1.nivel AND r2.id < r1.id)
                    OR (r2.nivel = r1.nivel AND r2.id = r1.id AND ur2.id < ur1.id)
                )
          )
    ";
}
```

> **Importante:** La tabla `usuarios_roles` **no tiene** una columna `es_principal`. El rol principal se determina dinámicamente como el rol activo de menor `nivel` (y luego menor `id` como tiebreaker). Este subquery es equivalente a `ORDER BY nivel ASC, id ASC LIMIT 1` pero expresado como subquery para poder usarlo en JOINs. El mismo patrón lo usa `Rol::obtenerRolPrincipalDeUsuario()`.

### Métodos del modelo

**Flujo A:**

| Método | Descripción |
|---|---|
| `crearToken(string $email): string` | Invalida tokens anteriores del email y genera uno nuevo (64 chars hex, 1h de vigencia). Retorna el token en texto plano |
| `buscarTokenValido(string $token): ?array` | Busca un token no usado y no expirado. Retorna la fila o `null` |
| `marcarTokenUsado(string $token): void` | Marca el token como usado (`SET used_at = NOW()`) para garantizar uso único |

**Flujo C:**

| Método | Descripción |
|---|---|
| `crearSolicitud(int $usuarioId, string $hash): int` | Cancela solicitudes previas pendientes e inserta una nueva. Retorna el `id` de la solicitud |
| `listarPendientes(?int $tiendaId): array` | Lista solicitudes con estado `pendiente`. Con `$tiendaId=null` → todas; con `int` → solo esa tienda (via subquery de rol principal) |
| `buscarSolicitudPorId(int $id): ?array` | Retorna una solicitud por id con datos del usuario, rol y tienda |
| `aprobarSolicitud(int $solicitudId, int $adminId): void` | En transacción: aplica el hash al usuario y marca la solicitud como aprobada |
| `rechazarSolicitud(int $solicitudId, int $adminId, string $motivo): void` | Marca la solicitud como rechazada con motivo |
| `contarPendientes(?int $tiendaId): int` | Cuenta las solicitudes pendientes — usado para el badge numérico en el sidebar del admin |

---

## Filtro por tienda en solicitudes

```php
// Superadmin: $tiendaId = null → ve todas
$solicitudes = $passwordReset->listarPendientes(null);

// Admin de Tienda: $tiendaId = 3 → solo su tienda
$solicitudes = $passwordReset->listarPendientes(3);
```

El filtro se implementa con un `LEFT JOIN` al subquery `sqlUsuarioRolPrincipal()` para determinar a qué tienda pertenece cada usuario solicitante.

---

## Seguridad

| Aspecto | Implementación |
|---|---|
| Tokens únicos | `UNIQUE KEY uq_token (token)` en BD |
| Un solo uso | `marcarTokenUsado()` pone `used_at = NOW()` inmediatamente tras el cambio |
| Expiración | `expires_at > NOW()` validado en la consulta — token inválido tras 1 hora |
| Hash seguro | `password_hash($password, PASSWORD_BCRYPT)` — nunca texto plano |
| Hash en solicitudes | El hash bcrypt se almacena en `nuevo_password_hash`, no la contraseña en texto plano |
| Una solicitud activa | `crearSolicitud()` cancela automáticamente las pendientes anteriores del usuario |

---

## Errores comunes

| Error | Causa | Solución |
|---|---|---|
| `Table 'solicitudes_cambio_contrasena' doesn't exist` | Primera ejecución sin migraciones | Resuelto: el constructor auto-crea ambas tablas con `CREATE TABLE IF NOT EXISTS` |
| Token inválido o expirado | El enlace fue usado antes o pasó más de 1 hora | Solicitar un nuevo enlace de reset |
| "La solicitud no existe o ya fue procesada" | Admin intenta aprobar/rechazar una solicitud ya procesada | Actualizar la lista de pendientes |

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
