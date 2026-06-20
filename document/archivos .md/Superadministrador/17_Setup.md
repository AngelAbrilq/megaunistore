# ⚙️ Módulo Setup — Superadministrador

> **Rol:** Sin autenticación de sesión — protegido por `SETUP_KEY` en `.env`
> **Controlador:** `SetupController`
> **Modelos:** `Rol` + `Permiso`
> **Ruta:** `GET index.php?route=setup&key=TU_CLAVE`
> **Uso:** Una sola vez al desplegar el sistema por primera vez

---

## Descripción

El módulo Setup es el **inicializador del sistema**. Crea los roles base y sus permisos en la base de datos mediante un único endpoint GET protegido por clave. No usa sesión ni middleware de autenticación — su única protección es comparar la `key` recibida por GET contra la variable de entorno `SETUP_KEY`.

**Cuándo ejecutarlo:** Una sola vez en el primer despliegue, antes de crear el usuario Superadministrador. Después de ejecutarlo, la ruta debe ser deshabilitada o eliminada en producción.

---

## Ruta

```
GET index.php?route=setup&key=TU_CLAVE_SECRETA
```

---

## Controlador: `SetupController.php`

```php
final class SetupController
{
    use ControllerHelper;
    private Rol    $rolModel;
    private Permiso $permisoModel;

    public function ejecutar(): void
    {
        // 1. Leer SETUP_KEY de getenv('SETUP_KEY')
        // 2. Comparar con $_GET['key']
        //    → Si no coinciden o está vacía: HTTP 403 + mensaje + exit
        // 3. rolModel->asegurarRolesBase()     → crea roles que no existen
        // 4. permisoModel->sincronizarPermisosRolesBase() → crea permisos y asigna a roles
        // 5. Renderiza página HTML con el log de lo que se hizo
    }
}
```

**Respuesta en éxito:** Página HTML con fondo oscuro listando los pasos completados y advertencia de deshabilitar la ruta.

**Respuesta en error de clave:** HTTP 403 con mensaje simple.

---

## Modelo: `Rol.php`

### Tabla: `roles`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `nombre` | VARCHAR | Nombre del rol (ej: "Superadministrador") |
| `descripcion` | TEXT / NULL | Descripción del rol |
| `nivel` | INT | Jerarquía numérica (1=más alto, 5=más bajo) |
| `activo` | TINYINT | 1=activo, 0=inactivo |

### Tabla pivote: `usuarios_roles`

| Campo | Tipo | Descripción |
|---|---|---|
| `usuario_id` | INT FK | Usuario |
| `rol_id` | INT FK | Rol asignado |
| `tienda_id` | INT FK / NULL | Tienda del rol; `NULL` = rol global (Superadmin, Sistema) |

### Métodos del modelo `Rol`

| Método | Descripción |
|---|---|
| `crear(array $datos)` | INSERT en `roles` |
| `buscarPorId(int $id)` | Retorna rol por ID |
| `buscarPorNombre(string $nombre)` | Retorna rol por nombre exacto (usado por Setup) |
| `listarActivos()` | Roles activos ordenados por nivel ASC, nombre ASC |
| `asegurarRolesBase()` | Itera los 9 roles base; crea solo los que no existen |
| `asignarRolAUsuario(int $uid, int $rid, ?int $tid)` | INSERT en `usuarios_roles` (idempotente) |
| `usuarioTieneRol(int $uid, int $rid, ?int $tid)` | Verifica si la asignación ya existe |
| `obtenerRolesDeUsuario(int $uid)` | INNER JOIN roles; retorna todos los roles activos del usuario |
| `obtenerRolPrincipalDeUsuario(int $uid)` | El rol de menor nivel (mayor jerarquía) del usuario — usado en login |

---

## Los 9 roles base

| Nombre | Nivel | Scope |
|---|---|---|
| `Superadministrador` | 1 | Global (sin `tienda_id`) |
| `Sistema` | 1 | Global (sin `tienda_id`) |
| `Administrador de Tienda` | 2 | Por tienda |
| `Supervisor` | 3 | Por tienda |
| `Nómina y RRHH` | 3 | Por tienda |
| `Vendedor` | 4 | Por tienda |
| `Bodeguero` | 4 | Por tienda |
| `Reportero` | 4 | Por tienda |
| `Cliente` | 5 | Global o por tienda |

> **Nivel 1 = máxima jerarquía.** El método `obtenerRolPrincipalDeUsuario()` ordena por `nivel ASC` y toma el primero — así un Superadmin nunca tiene como rol principal uno de nivel inferior aunque tenga múltiples roles asignados.

---

## Modelo: `Permiso.php`

### Tabla: `permisos`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `nombre` | VARCHAR | Nombre legible (ej: "Ver productos") |
| `modulo` | VARCHAR | Módulo al que pertenece (ej: "productos") |
| `accion` | VARCHAR UNIQUE | Clave de acción usada en el código (ej: "productos.view") |
| `descripcion` | TEXT / NULL | Descripción del permiso |

### Tabla pivote: `roles_permisos`

| Campo | Tipo | Descripción |
|---|---|---|
| `rol_id` | INT FK | Rol |
| `permiso_id` | INT FK | Permiso asignado |

### Métodos del modelo `Permiso`

| Método | Descripción |
|---|---|
| `crear(array $datos)` | INSERT en `permisos` |
| `buscarPorNombre(string $nombre)` | Retorna permiso por nombre |
| `buscarPorAccion(string $accion)` | Retorna permiso por acción (usado por Setup) |
| `listar()` | Todos los permisos ordenados por módulo + acción |
| `asignarPermisoARol(int $rolId, int $permisoId)` | INSERT en `roles_permisos` (idempotente) |
| `rolTienePermiso(int $rolId, int $permisoId)` | Verifica si la asignación ya existe |
| `obtenerPermisosDeRol(int $rolId)` | INNER JOIN permisos; retorna permisos del rol |
| `asegurarPermisosBase()` | Crea los permisos base que no existen aún |
| `sincronizarPermisosRolesBase()` | Llama a `asegurarPermisosBase()` + aplica la matriz de roles |
| `usuarioTienePermiso(int $uid, string $accion, ?int $tiendaId)` | Verificación en tiempo real usada por `ControllerHelper` |
| `obtenerAccionesDeUsuario(int $uid)` | Retorna array de strings con todas las acciones del usuario |

---

## Los 36 permisos base

| Módulo | Acción | Descripción |
|---|---|---|
| `dashboard` | `dashboard.view` | Ver el panel principal |
| `tiendas` | `tiendas.view` | Ver tiendas |
| `tiendas` | `tiendas.create` | Crear tiendas |
| `tiendas` | `tiendas.update` | Editar tiendas |
| `tiendas` | `tiendas.toggle` | Activar/desactivar tiendas |
| `tiendas` | `tiendas.delete` | Eliminar tiendas |
| `usuarios` | `usuarios.view` | Ver usuarios |
| `usuarios` | `usuarios.create` | Crear usuarios |
| `usuarios` | `usuarios.update` | Editar usuarios |
| `usuarios` | `usuarios.roles.assign` | Asignar roles a usuarios |
| `usuarios` | `usuarios.toggle` | Activar/desactivar usuarios |
| `usuarios` | `usuarios.delete` | Eliminar usuarios |
| `productos` | `productos.view` | Ver productos |
| `productos` | `productos.create` | Crear productos |
| `productos` | `productos.update` | Editar productos |
| `productos` | `productos.delete` | Eliminar productos |
| `inventario` | `inventario.view` | Ver inventario |
| `inventario` | `inventario.move` | Mover inventario |
| `inventario` | `inventario.alerts` | Ver alertas de stock |
| `ventas` | `ventas.view` | Ver ventas |
| `ventas` | `ventas.create` | Crear ventas |
| `ventas` | `ventas.cancel` | Anular ventas |
| `caja` | `caja.view` | Ver caja |
| `caja` | `caja.manage` | Gestionar caja |
| `reportes` | `reportes.view` | Ver reportes |
| `reportes` | `reportes.export` | Exportar reportes |
| `rrhh` | `empleados.view` | Ver empleados |
| `rrhh` | `empleados.manage` | Gestionar empleados |
| `nomina` | `nomina.view` | Ver nómina |
| `nomina` | `nomina.manage` | Gestionar nómina |
| `catalogo` | `catalogo.view` | Ver catálogo (rol Cliente) |
| `pedidos` | `pedidos.own.manage` | Gestionar pedidos propios |
| `perfil` | `perfil.own.manage` | Gestionar perfil propio |
| `feedback` | `feedback.create` | Calificar productos |
| `auditoria` | `auditoria.view` | Ver auditoría |
| `notificaciones` | `notificaciones.manage` | Gestionar notificaciones |
| `sistema` | `backups.manage` | Gestionar respaldos |

---

## Matriz de roles y permisos

| Permiso | Superadmin | Sistema | Admin Tienda | Supervisor | Vendedor | Bodeguero | Reportero | Nómina | Cliente |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| `dashboard.view` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `tiendas.*` | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `usuarios.*` | ✅ | ❌ | `view` | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `productos.view` | ✅ | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `productos.create/update/delete` | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `inventario.view` | ✅ | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| `inventario.move` | ✅ | ❌ | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| `inventario.alerts` | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| `ventas.view` | ✅ | ❌ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| `ventas.create` | ✅ | ❌ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `ventas.cancel` | ✅ | ❌ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `caja.view` | ✅ | ❌ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `caja.manage` | ✅ | ❌ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `reportes.view` | ✅ | ❌ | ✅ | ✅ | ❌ | ❌ | ✅ | ✅ | ❌ |
| `reportes.export` | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ |
| `empleados.view/manage` | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ |
| `nomina.view/manage` | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ |
| `notificaciones.manage` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `backups.manage` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `catalogo.view` | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| `pedidos.own.manage` | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| `perfil.own.manage` | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| `feedback.create` | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| `auditoria.view` | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

> **Superadministrador** tiene **todos** los permisos sin excepción.

---

## Cómo funciona `usuarioTienePermiso()` en tiempo real

```php
// En ControllerHelper — usado antes de cada acción del controlador:
public function usuarioTienePermiso(int $usuarioId, string $accion, ?int $tiendaId): bool
{
    // Consulta: usuarios_roles → roles_permisos → permisos
    // WHERE usuario_id=X AND accion=Y AND rol.activo=1
    // Si $tiendaId != null: AND (tienda_id IS NULL OR tienda_id = $tiendaId)
    // → Permite que Superadmin (tienda_id=null en usuarios_roles) pase el check de cualquier tienda
}
```

Y para cargar todos los permisos en sesión al hacer login:

```php
// En AuthController al iniciar sesión:
$acciones = $permisoModel->obtenerAccionesDeUsuario($usuarioId);
// → ['dashboard.view', 'productos.view', 'ventas.create', ...]
// Se guardan en $_SESSION['auth']['permisos']
// La vista los usa para mostrar/ocultar secciones del menú
```

---

## Flujo completo: Primer despliegue

```
1. Configurar .env:
   SETUP_KEY=mi_clave_super_secreta_2026

2. Crear las tablas con el schema SQL del proyecto

3. Acceder a la URL una sola vez:
   http://localhost/index.php?route=setup&key=mi_clave_super_secreta_2026

4. SetupController::ejecutar()
   → Verifica SETUP_KEY ✓
   → rolModel->asegurarRolesBase()
       → buscarPorNombre('Superadministrador') → null → crear()
       → buscarPorNombre('Sistema') → null → crear()
       → ... (9 roles en total)
       → ✅ Roles base verificados/creados.
   → permisoModel->sincronizarPermisosRolesBase()
       → asegurarPermisosBase() → crea los 37 permisos que no existen
       → matrizRolesPermisos() → asigna permisos a cada rol
       → ✅ Permisos base verificados/creados y asignados a roles.
   → Renderiza página de resultado

5. Crear el primer usuario Superadministrador manualmente en BD
   o a través del formulario de registro inicial

6. Deshabilitar la ruta 'setup' en routes/web.php o en .env
```

---

## Seguridad: advertencias

| Riesgo | Descripción | Mitigación |
|---|---|---|
| Ruta activa en producción | Cualquiera con la clave puede re-ejecutar el setup | Eliminar o comentar la ruta en `routes/web.php` tras el despliegue |
| `SETUP_KEY` débil | Clave predecible puede ser adivinada | Usar clave aleatoria de al menos 32 caracteres |
| `SETUP_KEY` vacía | Si `.env` no tiene la variable, se rechaza el acceso (HTTP 403) | ✅ El controlador valida `$claveEnv === ''` antes de cualquier comparación |
| Re-ejecución | El setup es idempotente: `asegurar*` y `asignar*` son no-destructivos | ✅ Solo crea lo que no existe; nunca borra ni sobreescribe |

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
