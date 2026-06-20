# ⚙️ Modelos base del sistema: Rol, Permiso y MetodoPago

> Estos tres modelos no tienen controller propio ni vistas directas — son utilizados internamente por `AuthController`, `PasswordController`, `VentaController` y el módulo de Setup.

---

## Rol.php

**Archivo:** `backend/app/models/Rol.php`  
**Tabla principal:** `roles`  
**Usado por:** `AuthController`, `PasswordController`, Setup

### Métodos públicos

| Método | Descripción |
|---|---|
| `crear(array $datos): int` | INSERT en `roles` — devuelve el ID del nuevo rol |
| `buscarPorId(int $id): ?array` | Busca un rol por su PK |
| `buscarPorNombre(string $nombre): ?array` | Busca rol por `nombre` exacto |
| `listarActivos(): array` | Lista roles con `activo = 1` |
| `asegurarRolesBase(): void` | Crea los 9 roles base si no existen (idempotente) |
| `asignarRolAUsuario(int $usuarioId, int $rolId, ?int $tiendaId): bool` | INSERT en `usuarios_roles` |
| `usuarioTieneRol(int $usuarioId, int $rolId, ?int $tiendaId): bool` | Verifica si el usuario ya tiene ese rol |
| `obtenerRolesDeUsuario(int $usuarioId): array` | Todos los roles de un usuario (array de rows) |
| `obtenerRolPrincipalDeUsuario(int $usuarioId): ?array` | Rol de menor nivel jerárquico del usuario |

### Lógica de rol principal

`obtenerRolPrincipalDeUsuario()` usa un subquery NOT EXISTS para encontrar el rol con menor nivel jerárquico sin depender de una columna `es_principal`:

```sql
SELECT ur1.usuario_id, ur1.rol_id, ur1.tienda_id, r1.nombre AS rol_nombre, r1.nivel AS rol_nivel
FROM usuarios_roles ur1
INNER JOIN roles r1 ON r1.id = ur1.rol_id
WHERE r1.activo = 1
  AND NOT EXISTS (
      SELECT 1 FROM usuarios_roles ur2
      INNER JOIN roles r2 ON r2.id = ur2.rol_id
      WHERE ur2.usuario_id = ur1.usuario_id
        AND r2.activo = 1
        AND (r2.nivel < r1.nivel OR ...)
  )
```

### Roles base (asegurarRolesBase)

| Nombre | Nivel | Con tienda |
|---|---|---|
| Superadministrador | 1 | No |
| Administrador de Tienda | 2 | Sí |
| Supervisor | 3 | Sí |
| Vendedor | 4 | Sí |
| Bodeguero | 5 | Sí |
| Reportero | 6 | No |
| Nómina y RRHH | 7 | No |
| Cliente | 8 | No |
| Sistema | 9 | No |

### Tabla `roles`

| Columna | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador |
| `nombre` | VARCHAR | Nombre del rol |
| `descripcion` | TEXT | Descripción opcional |
| `nivel` | INT | Jerarquía (1 = más alto) |
| `activo` | TINYINT | 1 = activo |

### Tabla `usuarios_roles`

| Columna | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador |
| `usuario_id` | INT FK | Usuario |
| `rol_id` | INT FK | Rol asignado |
| `tienda_id` | INT FK NULL | Tienda donde aplica el rol (null = global) |

---

## Permiso.php

**Archivo:** `backend/app/models/Permiso.php`  
**Tabla principal:** `permisos`  
**Usado por:** `AuthController`, Setup

### Métodos públicos

| Método | Descripción |
|---|---|
| `crear(array $datos): int` | INSERT en `permisos` |
| `buscarPorNombre(string $nombre): ?array` | Busca por nombre |
| `buscarPorAccion(string $accion): ?array` | Busca por acción (ej: `ventas.create`) |
| `listar(): array` | Lista todos los permisos |
| `asignarPermisoARol(int $rolId, int $permisoId): bool` | INSERT en `rol_permisos` |
| `rolTienePermiso(int $rolId, int $permisoId): bool` | Verifica si el rol ya tiene el permiso |
| `obtenerPermisosDeRol(int $rolId): array` | Todos los permisos de un rol |
| `asegurarPermisosBase(): void` | Crea todos los permisos del sistema si no existen |
| `sincronizarPermisosRolesBase(): void` | Asigna los permisos correspondientes a cada rol base |
| `usuarioTienePermiso(int $usuarioId, string $accion, ?int $tiendaId): bool` | Verifica si el usuario tiene la acción permitida |
| `obtenerAccionesDeUsuario(int $usuarioId): array` | Array de strings de acciones del usuario |

### Cómo funciona `usuarioTienePermiso`

```php
// En web.php, antes de cada ruta:
$authController->requerirPermiso('ventas.create');
// → AuthController::requerirPermiso() llama a:
$this->permisoModel->usuarioTienePermiso($usuarioId, 'ventas.create', $tiendaId);
// → Busca en el array $_SESSION['auth']['permisos'] que se cargó en login
// → (no hace query a BD en cada request — usa la sesión)
```

### Tabla `permisos`

| Columna | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador |
| `nombre` | VARCHAR | Nombre descriptivo |
| `modulo` | VARCHAR | Módulo al que pertenece (ej: `ventas`) |
| `accion` | VARCHAR | Acción específica (ej: `ventas.create`) |
| `descripcion` | TEXT | Descripción opcional |

### Tabla `rol_permisos`

| Columna | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador |
| `rol_id` | INT FK | Rol |
| `permiso_id` | INT FK | Permiso asignado |

---

## MetodoPago.php

**Archivo:** `backend/app/models/MetodoPago.php`  
**Tabla principal:** `metodos_pago`  
**Usado por:** `VentaController` (formulario de nueva venta)

### Métodos públicos

| Método | Descripción |
|---|---|
| `listarActivos(): array` | Lista métodos con `activo = 1`; llama `asegurarMetodosBase()` antes |
| `buscarPorId(int $id): ?array` | Busca método por PK |
| `asegurarMetodosBase(): void` | Crea los 4 métodos base si no existen (idempotente) |

### Métodos base (asegurarMetodosBase)

Se crean automáticamente en la primera llamada a `listarActivos()`:

| Método | Descripción |
|---|---|
| Efectivo | Pago en efectivo en punto de venta |
| Transferencia | Pago por transferencia bancaria |
| Tarjeta débito | Pago con tarjeta débito |
| Tarjeta crédito | Pago con tarjeta crédito |

### Uso en el POS

```php
// VentaController::create():
$metodoPago = new MetodoPago();
$metodosPago = $metodoPago->listarActivos();
// → Se pasa a ventas/create.php para el dropdown de método de pago
```

### Tabla `metodos_pago`

| Columna | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador |
| `nombre` | VARCHAR | Nombre del método |
| `descripcion` | TEXT | Descripción |
| `activo` | TINYINT | 1 = visible en POS |

---

## Directorio `views/cursos/` — Huérfano

El directorio `backend/resources/views/cursos/` existe en el sistema de archivos pero está **completamente vacío**. No tiene:
- Controller (`CursoController.php` no existe)
- Rutas en `web.php`
- Permisos en la matriz de permisos
- Documentación

**Estado:** Código huérfano — fue creado como placeholder para un módulo de cursos/capacitaciones que nunca se implementó.

**Decisión recomendada:**
- **Opción A (recomendada):** Eliminar el directorio vacío para mantener el proyecto limpio
- **Opción B:** Implementar el módulo de cursos en una versión futura (requiere: model, controller, views, rutas, permisos)

---

*Documentado: mayo 2026 — Ángel Nicolás Abril*
