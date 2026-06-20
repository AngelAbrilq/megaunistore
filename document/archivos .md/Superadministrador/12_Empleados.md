# 👔 Módulo Empleados — Superadministrador

> **Rol:** `Superadministrador` (ve todos los empleados) · `Administrador de Tienda` (solo su tienda)
> **Permisos:** `empleados.view` · `empleados.manage`
> **Controlador:** `EmpleadoController`
> **Modelos:** `Empleado` + `Usuario` + `Tienda`
> **Vistas:** `resources/views/empleados/`

---

## Descripción

El módulo Empleados vincula un **Usuario del sistema** con una **Tienda**, creando un registro de empleado con datos laborales (código, fecha de ingreso, salario base). Es el puente entre la identidad del sistema (autenticación) y el rol operacional dentro de una tienda.

### Conceptos clave

- **Un empleado = un Usuario + una Tienda** — La tabla `empleados` no almacena datos personales; los hereda del usuario vinculado.
- **Código de empleado** — Único por tienda (no globalmente). Dos tiendas distintas pueden tener un empleado con el mismo código.
- **Un usuario no puede ser empleado dos veces en la misma tienda** — Se valida con `usuarioYaEsEmpleadoEnTienda()`.
- **Soft delete**: `deleted_at = NOW()` + `estado = 'inactivo'`.
- **`validarDatos($input, $requiereTienda)`** — El parámetro booleano controla si se exigen `usuario_id` y `tienda_id` (obligatorio en create, no en update porque la tienda no cambia).

---

## Rutas disponibles

| Ruta | Método | Permiso | Acción |
|---|---|---|---|
| `empleados.index` | GET | `empleados.view` | Listar empleados |
| `empleados.create` | GET | `empleados.manage` | Formulario nuevo empleado (modal) |
| `empleados.store` | POST | `empleados.manage` | Crear empleado |
| `empleados.edit` | GET | `empleados.manage` | Formulario editar empleado (modal) |
| `empleados.update` | POST | `empleados.manage` | Guardar cambios |
| `empleados.destroy` | POST | `empleados.manage` | Desvincular empleado (soft delete) |

---

## Controlador: `EmpleadoController.php`

```php
final class EmpleadoController
{
    use ControllerHelper;
    private Empleado $empleadoModel;
    private Usuario  $usuarioModel;
    private Tienda   $tiendaModel;
}
```

---

### `index()` — Listar empleados

```php
public function index(): void
{
    $tiendaId  = $this->tiendaIdPermitida();  // null = Superadmin
    $empleados = $this->empleadoModel->listar($tiendaId);
    $csrfToken = $this->generarCsrfToken();
}
```

**Comportamiento según rol:**
- `null` → Superadmin: todos los empleados de todas las tiendas, ordenados por `e.id DESC`.
- `int` → Rol con tienda: solo empleados de esa tienda, ordenados por `u.nombre ASC`.

---

### `create()` — Formulario nuevo empleado

```php
public function create(): void
{
    $tiendaId = $this->tiendaIdPermitida();
    $usuarios = $this->usuarioModel->listar();  // Todos los usuarios del sistema
    $tiendas  = $tiendaId === null
        ? $this->tiendaModel->listar()           // Superadmin: todas las tiendas
        : [$this->tiendaModel->buscarPorId($tiendaId)];  // Rol con tienda: solo la suya
}
```

> El formulario deja seleccionar **cualquier usuario** del sistema, sin importar el rol que tenga. La restricción de un usuario por tienda se valida en `store()`.

---

### `store()` — Crear empleado

```php
public function store(): void
{
    // 1. Valida CSRF
    // 2. validarDatos($_POST, true)  ← true = requiere usuario_id y tienda_id
    // 3. validarAccesoATienda(datos['tienda_id'])  → 403 si no es la tienda del rol
    // 4. existeCodigoEnTienda(codigo, tiendaId)
    //    → error si el código ya existe en esa tienda
    // 5. usuarioYaEsEmpleadoEnTienda(usuarioId, tiendaId)
    //    → error si el usuario ya es empleado activo en esa tienda

    $this->empleadoModel->crear($datos);
    $this->jsonExito('empleados.index', 'Empleado registrado correctamente.');
}
```

**Dos validaciones de unicidad en `store()`:**
1. `existeCodigoEnTienda(codigo, tiendaId)` — El código es único dentro de la tienda.
2. `usuarioYaEsEmpleadoEnTienda(usuarioId, tiendaId)` — Un usuario solo puede tener un registro activo de empleado por tienda.

---

### `update()` — Actualizar empleado

```php
public function update(): void
{
    // validarDatos($_POST, false)  ← false = NO requiere usuario_id ni tienda_id
    // La tienda NO cambia en la edición (se toma del registro existente)

    // existeCodigoEnTienda(codigo, empleado['tienda_id'], $id)
    //   ← excluye el empleado actual para no bloquearse a sí mismo

    $this->empleadoModel->actualizar($id, $datos);
    $this->jsonExito('empleados.index', 'Empleado actualizado correctamente.');
}
```

> **Nota crítica:** En `update()`, la tienda no se puede cambiar. El código de empleado se valida contra la tienda original del registro, excluyendo el ID actual.

---

### `destroy()` — Desvincular empleado

```php
public function destroy(): void
{
    $empleado = $this->empleadoModel->buscarPorId($id);
    if ($empleado !== null) {
        $this->validarAccesoATienda((int) $empleado['tienda_id']);
    }

    $this->empleadoModel->eliminarLogico($id);
    // → UPDATE empleados SET deleted_at = NOW(), estado = 'inactivo'

    $this->guardarMensaje('success', 'Empleado desvinculado correctamente.');
    $this->redireccionar('index.php?route=empleados.index');
}
```

> El mensaje dice "desvinculado" (no "eliminado") porque el usuario del sistema sigue existiendo; solo se cierra su vínculo con la tienda.

---

## Validación: `validarDatos(array $input, bool $requiereTienda)`

```php
private function validarDatos(array $input, bool $requiereTienda): ?array
{
    // Obligatorio siempre:
    // - codigo_empleado → no vacío
    // - fecha_ingreso   → no vacío
    // - salario_base    → numérico, >= 0; normalizado a 2 decimales

    // Estado: solo 'activo' o 'inactivo'; default 'activo'
    $estado = in_array($estado, ['activo', 'inactivo'], true) ? $estado : 'activo';

    // Obligatorio si $requiereTienda === true (en create):
    // - usuario_id → int > 0
    // - tienda_id  → int > 0
}
```

**Reglas de validación:**

| Campo | Contexto | Obligatorio | Validación extra |
|---|---|---|---|
| `codigo_empleado` | Create + Update | ✅ | No vacío; único por tienda |
| `fecha_ingreso` | Create + Update | ✅ | No vacío (formato date del input HTML) |
| `salario_base` | Create + Update | ✅ | Numérico, `>= 0`, 2 decimales |
| `estado` | Create + Update | ❌ | Solo `'activo'` o `'inactivo'`; default `'activo'` |
| `usuario_id` | Solo Create | ✅ | `> 0`; usuario existente en el sistema |
| `tienda_id` | Solo Create | ✅ | `> 0`; tienda a la que pertenece el acceso del rol |

---

## Modelo: `Empleado.php`

### Tabla: `empleados`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `usuario_id` | INT FK | Usuario del sistema vinculado |
| `tienda_id` | INT FK | Tienda a la que pertenece |
| `codigo_empleado` | VARCHAR | Código único dentro de la tienda |
| `fecha_ingreso` | DATE | Fecha de ingreso a la tienda |
| `salario_base` | DECIMAL(10,2) | Salario base mensual |
| `estado` | ENUM('activo','inactivo') | Estado laboral |
| `deleted_at` | TIMESTAMP / NULL | Soft delete |

> **No tiene** `nombre` ni `apellido` propios — se obtienen del JOIN con `usuarios`.

### Métodos del modelo

| Método | Descripción |
|---|---|
| `listar(?int $tiendaId)` | INNER JOIN con `usuarios` y `tiendas`. Sin tiendaId: todos; con tiendaId: solo esa tienda |
| `buscarPorId(int $id)` | Retorna empleado con datos del usuario y tienda vinculados |
| `existeCodigoEnTienda(string $codigo, int $tiendaId, ?int $excluirId)` | Valida unicidad del código dentro de la tienda; con excluirId ignora el registro actual |
| `usuarioYaEsEmpleadoEnTienda(int $usuarioId, int $tiendaId, ?int $excluirId)` | Valida que el usuario no esté ya vinculado como empleado activo en esa tienda |
| `crear(array $datos)` | INSERT en `empleados`; retorna el nuevo `id` |
| `actualizar(int $id, array $datos)` | UPDATE de código, fecha, salario y estado (NO cambia usuario_id ni tienda_id) |
| `eliminarLogico(int $id)` | `UPDATE SET deleted_at = NOW(), estado = 'inactivo'` |
| `listarParaSelect(?int $tiendaId)` | Lista simplificada (id, codigo, nombre del usuario) para `<select>` en otros módulos |

### Detalle: `actualizar()` — campos inmutables

```sql
UPDATE empleados
SET codigo_empleado = :codigo_empleado,
    fecha_ingreso   = :fecha_ingreso,
    salario_base    = :salario_base,
    estado          = :estado
WHERE id = :id AND deleted_at IS NULL
```

`usuario_id` y `tienda_id` **no se actualizan nunca**. Si se necesita cambiar de tienda, se elimina el empleado y se crea uno nuevo.

### Detalle: `listarParaSelect()` — para uso en otros módulos

```sql
SELECT e.id, e.codigo_empleado,
       u.nombre AS usuario_nombre, u.apellido AS usuario_apellido
FROM empleados e
INNER JOIN usuarios u ON u.id = e.usuario_id
WHERE e.deleted_at IS NULL AND e.estado = 'activo'
  [AND e.tienda_id = :tienda_id]
ORDER BY u.nombre ASC
```

Usado por módulos como Caja o Nómina para vincular operaciones a empleados activos.

---

## Vista: `empleados/index.php`

**Funcionalidades:**
- Lista empleados con: código, nombre completo (del usuario), email, tienda, fecha ingreso, salario base, estado (badge activo/inactivo)
- Botón "Nuevo Empleado" → `openModal('index.php?route=empleados.create&ajax=1')`
- Botón "Editar" → `openModal('index.php?route=empleados.edit&id=X&ajax=1')`
- Botón "Desvincular" → formulario POST inline con confirmación JS
- Paginación cliente (10 registros por página)

---

## Flujo completo: Vincular empleado a tienda

```
1. Superadmin en empleados.index hace clic "Nuevo Empleado"
   ↓
2. openModal('index.php?route=empleados.create&ajax=1')
   → GET: EmpleadoController::create()
   → Vista recibe: todos los usuarios, todas las tiendas
   ↓
3. Modal muestra:
   - usuario_id* (select con usuarios del sistema)
   - tienda_id*  (select con tiendas)
   - codigo_empleado* (text)
   - fecha_ingreso* (date)
   - salario_base* (number)
   - estado (select: activo/inactivo)
   ↓
4. Admin completa: usuario=María López, tienda=Tienda Norte, código=EMP-001, salario=2000000
   submitModalForm()
   → POST empleados.store con X-Modal-Request: 1
   ↓
5. EmpleadoController::store()
   → Valida CSRF ✓
   → validarDatos($_POST, true) → todos los campos válidos ✓
   → validarAccesoATienda(tiendaId) ✓
   → existeCodigoEnTienda('EMP-001', tiendaId) → false ✓
   → usuarioYaEsEmpleadoEnTienda(usuarioId, tiendaId) → false ✓
   → empleadoModel->crear([...]) → id=8
   → jsonExito('empleados.index', 'Empleado registrado correctamente.')
   ↓
6. JS: cierra modal, toast, recarga empleados.index
   → María López aparece como empleada de Tienda Norte con código EMP-001
```

---

## Errores comunes y soluciones

| Error | Causa | Solución |
|---|---|---|
| "El codigo de empleado es obligatorio" | Campo código vacío | Completar el código |
| "La fecha de ingreso es obligatoria" | Campo fecha vacío | Seleccionar una fecha |
| "El salario base debe ser un valor numérico positivo" | Salario vacío, con letras o negativo | Ingresar número >= 0 |
| "Debes seleccionar un usuario" | No se eligió usuario en el select | Seleccionar un usuario del sistema |
| "Debes seleccionar una tienda" | No se eligió tienda | Seleccionar una tienda |
| "El código de empleado ya existe en esta tienda" | Código duplicado dentro de la tienda | Usar un código diferente |
| "Este usuario ya está registrado como empleado en esta tienda" | Mismo usuario vinculado dos veces en la misma tienda | Un usuario solo puede ser empleado una vez por tienda |

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
