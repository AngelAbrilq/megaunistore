# 👤 Módulo Usuarios — Superadministrador

> **Rol:** `Superadministrador`
> **Permisos:** `usuarios.view` · `usuarios.create` · `usuarios.update` · `usuarios.toggle` · `usuarios.delete` · `usuarios.roles.assign`
> **Controlador:** `UsuarioController`
> **Modelo:** `Usuario` + `Rol` + `Tienda`
> **Vistas:** `resources/views/usuarios/`

---

## Descripción

El módulo Usuarios permite al Superadministrador gestionar todos los usuarios de la plataforma. Es el módulo de identidad del sistema: sin usuarios no hay operaciones ni acceso a ningún módulo.

El Superadministrador puede crear, editar, activar/desactivar y eliminar lógicamente cualquier usuario, además de asignarle uno o varios roles vinculados a tiendas específicas.

> **Regla de seguridad crítica:** El Superadministrador **no puede desactivar ni eliminar su propio usuario** en sesión activa. Esta protección se aplica en `toggleEstado()` y `destroy()` comparando `$id === $usuarioSesionId`.

---

## Rutas disponibles

| Ruta | Método | Permiso | Acción |
|---|---|---|---|
| `usuarios.index` | GET | `usuarios.view` | Listar todos los usuarios |
| `usuarios.create` | GET | `usuarios.create` | Formulario nuevo usuario (modal) |
| `usuarios.store` | POST | `usuarios.create` | Guardar nuevo usuario |
| `usuarios.edit` | GET | `usuarios.update` | Formulario editar usuario (modal) |
| `usuarios.update` | POST | `usuarios.update` | Guardar cambios |
| `usuarios.asignar_rol` | GET | `usuarios.roles.assign` | Formulario asignar rol (modal) |
| `usuarios.guardar_rol` | POST | `usuarios.roles.assign` | Guardar rol asignado |
| `usuarios.toggle` | POST | `usuarios.toggle` | Activar / desactivar usuario |
| `usuarios.destroy` | POST | `usuarios.delete` | Eliminación lógica |

---

## Controlador: `UsuarioController.php`

El controlador instancia tres modelos:

```php
public function __construct()
{
    $this->usuarioModel = new Usuario();
    $this->rolModel     = new Rol();
    $this->tiendaModel  = new Tienda();
}
```

---

### `index()` — Listar usuarios

```php
public function index(): void
{
    $usuarios = $this->usuarioModel->listar();

    foreach ($usuarios as $key => $usuario) {
        $usuarios[$key]['roles'] = $this->rolModel->obtenerRolesDeUsuario((int) $usuario['id']);
    }

    $csrfToken = $this->generarCsrfToken();

    require __DIR__ . '/../../resources/views/usuarios/index.php';
}
```

**Variables para la vista:**
- `$usuarios` — array de todos los usuarios (con `deleted_at IS NULL`), cada uno enriquecido con su array de roles
- `$csrfToken` — token CSRF para formularios POST inline (toggle, destroy)

> **Nota:** Los roles se cargan en un segundo pase por usuario (N+1 controlado). Para volúmenes grandes se debería migrar a un JOIN.

---

### `create()` — Formulario nuevo usuario

```php
public function create(): void
{
    $roles   = $this->rolModel->listarActivos();
    $tiendas = $this->tiendaModel->listar();
    $csrfToken = $this->generarCsrfToken();

    require __DIR__ . '/../../resources/views/usuarios/create.php';
}
```

**Variables para la vista:**
- `$roles` — lista de roles activos para el `<select>` de rol
- `$tiendas` — lista de tiendas para el `<select>` de tienda asignada
- `$csrfToken` — token CSRF

---

### `store()` — Crear usuario

```php
public function store(): void
{
    // 1. Solo acepta POST
    // 2. Valida token CSRF
    // 3. Valida y limpia datos con validarDatosUsuario($_POST, true)
    //    → true = contraseña obligatoria en creación
    // 4. Verifica que el email no esté ya registrado
    // 5. Crea el usuario con crearAdministrativo()
    // 6. Asigna el rol enviado en el formulario
    // 7. Responde JSON para el modal

    $usuarioId = $this->usuarioModel->crearAdministrativo([
        'nombre'     => $datos['nombre'],
        'apellido'   => $datos['apellido'],
        'email'      => $datos['email'],
        'password'   => $datos['password'],   // El modelo hace password_hash()
        'telefono'   => $datos['telefono'],
        'avatar_url' => null,
        'estado'     => 1,
    ]);

    $this->asignarRolDesdeFormulario($usuarioId, $_POST);

    $this->jsonExito('usuarios.index', 'Usuario creado correctamente.');
}
```

---

### `update()` — Editar usuario

```php
public function update(): void
{
    // Valida CSRF, ID y existencia del usuario
    // Valida datos con validarDatosUsuario($_POST, false)
    //    → false = contraseña opcional en edición

    $this->usuarioModel->actualizar($id, [
        'nombre'     => $datos['nombre'],
        'apellido'   => $datos['apellido'],
        'email'      => $datos['email'],
        'telefono'   => $datos['telefono'],
        'avatar_url' => $usuarioActual['avatar_url'] ?? null,
        'estado'     => (int) ($_POST['estado'] ?? 1),
    ]);

    // Cambio de contraseña opcional — solo si se envía y tiene ≥ 8 caracteres
    if (!empty($_POST['password'])) {
        $this->usuarioModel->cambiarPassword($id, $_POST['password']);
    }

    $this->jsonExito('usuarios.index', 'Usuario actualizado correctamente.');
}
```

**Protección contra email duplicado al editar:**
```php
$usuarioConEmail = $this->usuarioModel->buscarPorEmail($datos['email']);
if ($usuarioConEmail !== null && (int) $usuarioConEmail['id'] !== $id) {
    // El email pertenece a OTRO usuario → error
}
```

---

### `asignarRol()` — Formulario de asignación de rol

```php
public function asignarRol(): void
{
    $usuario      = $this->usuarioModel->buscarPorId($id);
    $roles        = $this->rolModel->listarActivos();
    $tiendas      = $this->tiendaModel->listar();
    $rolesUsuario = $this->rolModel->obtenerRolesDeUsuario($id);
    $csrfToken    = $this->generarCsrfToken();

    require __DIR__ . '/../../resources/views/usuarios/asignar_rol.php';
}
```

**Variables para la vista:**
- `$usuario` — datos del usuario al que se asigna el rol
- `$roles` — roles disponibles para el `<select>`
- `$tiendas` — tiendas disponibles
- `$rolesUsuario` — roles actualmente asignados al usuario (para mostrar historial)

---

### `asignarRolDesdeFormulario()` — Lógica interna de roles

```php
private function asignarRolDesdeFormulario(int $usuarioId, array $input): void
{
    $rolId = (int) ($input['rol_id'] ?? 0);

    $rol = $this->rolModel->buscarPorId($rolId);

    // Roles globales no requieren tienda_id
    $rolesGlobales = ['Superadministrador', 'Sistema'];

    if (in_array($rol['nombre'], $rolesGlobales, true)) {
        $tiendaId = null;
    } else {
        $tiendaId = (int) $input['tienda_id'];
        // Valida que la tienda exista
    }

    $this->rolModel->asignarRolAUsuario($usuarioId, $rolId, $tiendaId);
}
```

**Regla de tienda:**
- Roles `Superadministrador` y `Sistema` → `tienda_id = null` (global)
- Todos los demás roles → requieren `tienda_id` válido

---

### `toggleEstado()` — Activar/Desactivar

```php
public function toggleEstado(): void
{
    $id              = (int) ($_POST['id'] ?? 0);
    $estadoActual    = (int) ($_POST['estado_actual'] ?? 0);
    $usuarioSesionId = $this->usuarioIdActual();

    // Auto-protección: no puede desactivarse a sí mismo
    if ($id === $usuarioSesionId) {
        $this->guardarMensaje('error', 'No puedes desactivar tu propio usuario.');
        $this->redireccionar('index.php?route=usuarios.index');
    }

    $nuevoEstado = $estadoActual === 1 ? 0 : 1;
    $this->usuarioModel->cambiarEstado($id, $nuevoEstado);
}
```

**Estados:**
- `1` = Activo (puede iniciar sesión)
- `0` = Inactivo (`buscarActivoPorEmail()` lo excluye → no puede hacer login)

---

### `destroy()` — Eliminación lógica

```php
$this->usuarioModel->eliminarLogico($id);
// Marca deleted_at = NOW() y estado = 0
// El usuario queda en BD para integridad referencial (ventas, movimientos, etc.)
// NO se puede eliminar el propio usuario en sesión
```

---

## Validación: `validarDatosUsuario()`

```php
private function validarDatosUsuario(array $input, bool $requierePassword): ?array
{
    // Limpia y valida campos
    // Retorna array limpio o null si hay error (guarda flash de error)
}
```

**Reglas de validación:**

| Campo | Obligatorio | Validación extra |
|---|---|---|
| `nombre` | ✅ | No vacío |
| `apellido` | ✅ | No vacío |
| `email` | ✅ | Formato válido (`FILTER_VALIDATE_EMAIL`), convertido a minúsculas |
| `password` | ✅ en creación / ❌ en edición | Mínimo 8 caracteres si se envía |
| `telefono` | ❌ | Se guarda como `null` si vacío |

---

## Modelo: `Usuario.php`

### Tabla: `usuarios`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `nombre` | VARCHAR | Nombre del usuario |
| `apellido` | VARCHAR | Apellido del usuario |
| `email` | VARCHAR(100) UNIQUE | Correo electrónico (minúsculas) |
| `password_hash` | VARCHAR | Hash `bcrypt` via `password_hash()` |
| `telefono` | VARCHAR(20) | Teléfono opcional |
| `avatar_url` | VARCHAR | URL del avatar opcional |
| `estado` | TINYINT | 1=activo, 0=inactivo |
| `deleted_at` | TIMESTAMP | NULL = no eliminado; fecha = soft delete |
| `created_at` | TIMESTAMP | Fecha de creación |

### Tabla relacionada: `usuarios_roles`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador |
| `usuario_id` | INT FK | Usuario al que se asigna el rol |
| `rol_id` | INT FK | Rol asignado |
| `tienda_id` | INT FK / NULL | Tienda asociada (null para roles globales) |
| `es_principal` | TINYINT | 1 = es el rol principal del usuario |

### Métodos del modelo `Usuario`

| Método | Descripción |
|---|---|
| `listar()` | Retorna todos los usuarios con `deleted_at IS NULL`, orden DESC |
| `buscarPorId(int $id)` | Retorna un usuario por ID o `null` |
| `buscarPorIdConRol(int $id)` | Retorna usuario + rol principal + tienda_id (JOIN) |
| `buscarPorEmail(string $email)` | Busca por email (incluyendo inactivos) |
| `buscarActivoPorEmail(string $email)` | Solo usuarios activos y no eliminados (usado en login) |
| `existeEmail(string $email)` | Verifica si el email ya está registrado (para validación en creación) |
| `crear(array $datos)` | INSERT básico — aplica `password_hash()` internamente |
| `crearAdministrativo(array $datos)` | INSERT con el mismo esquema — usado por el controlador para usuarios del panel |
| `actualizar(int $id, array $datos)` | UPDATE de datos personales (sin contraseña) |
| `cambiarPassword(int $id, string $nuevaPassword)` | UPDATE solo del `password_hash` |
| `cambiarEstado(int $id, int $estado)` | Activa o desactiva el usuario |
| `eliminarLogico(int $id)` | Marca `deleted_at = NOW()` y `estado = 0` |
| `verificarPassword(string $plano, string $hash)` | Wrapper de `password_verify()` |

---

## Vista: `usuarios/index.php`

**Funcionalidades de la vista:**
- Lista todos los usuarios con: nombre, apellido, email, roles asignados, estado, fecha de creación
- Botón "Nuevo Usuario" → abre modal con `openModal('index.php?route=usuarios.create&ajax=1')`
- Botón "Editar" → `openModal('index.php?route=usuarios.edit&id=X&ajax=1')`
- Botón "Asignar Rol" → `openModal('index.php?route=usuarios.asignar_rol&id=X&ajax=1')`
- Botón "Activar/Desactivar" → formulario POST inline con CSRF
- Botón "Eliminar" → formulario POST inline con confirmación JS
- Paginación cliente (10 registros por página)
- Los roles de cada usuario se muestran como badges de color

---

## Flujo completo: Crear usuario nuevo con rol

```
1. Superadmin en usuarios.index hace clic "Nuevo Usuario"
   ↓
2. openModal('index.php?route=usuarios.create&ajax=1')
   → GET: UsuarioController::create() → carga create.php
   → Vista recibe $roles y $tiendas para los <select>
   ↓
3. Modal muestra formulario: nombre, apellido, email, contraseña, teléfono, rol, tienda
   ↓
4. Usuario completa y hace clic "Guardar"
   submitModalForm()
   → POST usuarios.store con header X-Modal-Request: 1
   ↓
5. UsuarioController::store()
   → Valida CSRF ✓
   → validarDatosUsuario($_POST, true) ✓
   → existeEmail() → false ✓
   → usuarioModel->crearAdministrativo([...]) → devuelve $usuarioId
   → asignarRolDesdeFormulario($usuarioId, $_POST)
      → Si rol es 'Superadministrador' o 'Sistema' → tienda_id = null
      → Si no → requiere tienda_id válida
      → rolModel->asignarRolAUsuario($usuarioId, $rolId, $tiendaId)
   → jsonExito('usuarios.index', 'Usuario creado correctamente.')
   ↓
6. JS: cierra modal, toast "Usuario creado correctamente."
   loadContent('usuarios.index', true)  ← Recarga la lista
```

---

## Flujo: Asignar rol adicional a usuario existente

```
1. Superadmin hace clic "Asignar Rol" en la fila del usuario
   ↓
2. openModal('index.php?route=usuarios.asignar_rol&id=5&ajax=1')
   → Vista muestra roles actuales del usuario + formulario de nuevo rol
   ↓
3. Superadmin selecciona nuevo rol y tienda → "Guardar"
   → POST usuarios.guardar_rol
   ↓
4. guardarRol() → asignarRolDesdeFormulario($usuarioId, $_POST)
   → rolModel->asignarRolAUsuario() registra en usuarios_roles
   ↓
5. jsonExito('usuarios.index', 'Rol asignado correctamente.')
```

---

## Errores comunes y soluciones

| Error | Causa | Solución |
|---|---|---|
| "El correo ya está registrado" | Email duplicado al crear | Usar un email diferente |
| "El correo ya está asignado a otro usuario" | Email duplicado al editar | Cambiar el email o verificar cuál usuario lo tiene |
| "La contraseña debe tener mínimo 8 caracteres" | Password corto en creación | Ingresar mínimo 8 caracteres |
| "Debes seleccionar un rol" | Formulario enviado sin rol | Seleccionar un rol del listado |
| "Debes seleccionar una tienda para este rol" | Rol de tienda sin tienda seleccionada | Seleccionar una tienda activa |
| "No puedes desactivar tu propio usuario" | Admin intenta desactivarse | Pedir a otro admin que lo haga |
| "No puedes eliminar tu propio usuario" | Admin intenta eliminarse | Ídem |
| Usuario no puede hacer login tras creación | Se creó pero el estado es 0 | Verificar `estado = 1` en BD |

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
