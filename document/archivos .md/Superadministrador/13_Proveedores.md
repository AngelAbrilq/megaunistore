# 🏭 Módulo Proveedores — Superadministrador

> **Rol:** `Superadministrador` (y `Administrador de Tienda`)
> **Permisos:** `proveedores.view` · `proveedores.manage`
> **Controlador:** `ProveedorController`
> **Modelo:** `Proveedor`
> **Vistas:** `resources/views/proveedores/`

---

## Descripción

El módulo Proveedores gestiona el catálogo de empresas o personas que abastecen de productos a la plataforma. Es un módulo **global** (no es multitienda): los proveedores son compartidos entre todas las tiendas. El identificador clave de un proveedor es su `ruc_nit`, que debe ser único en todo el sistema.

### Conceptos clave

- **Global, no multitienda** — No tiene `tienda_id`. `ProveedorController` no usa `tiendaIdPermitida()`.
- **`ruc_nit` único** — Validado tanto en create como en update (con exclusión del propio ID).
- **Soft delete** con desactivación: `deleted_at = NOW()` + `estado = 0`.
- **`toggleEstado()`** — Permite activar/desactivar sin eliminar, igual que Impuestos.
- **`listarParaSelect()`** — Solo proveedores activos (`estado = 1`); usado por otros módulos (ej: órdenes de compra).

---

## Rutas disponibles

| Ruta | Método | Permiso | Acción |
|---|---|---|---|
| `proveedores.index` | GET | `proveedores.view` | Listar proveedores |
| `proveedores.create` | GET | `proveedores.manage` | Formulario nuevo proveedor (modal) |
| `proveedores.store` | POST | `proveedores.manage` | Crear proveedor |
| `proveedores.edit` | GET | `proveedores.manage` | Formulario editar proveedor (modal) |
| `proveedores.update` | POST | `proveedores.manage` | Guardar cambios |
| `proveedores.toggle` | POST | `proveedores.manage` | Activar / desactivar |
| `proveedores.destroy` | POST | `proveedores.manage` | Eliminación lógica |

---

## Controlador: `ProveedorController.php`

```php
final class ProveedorController
{
    use ControllerHelper;
    private Proveedor $proveedorModel;
    // Un solo modelo — sin Tienda ni Usuario
}
```

---

### `index()` — Listar proveedores

```php
public function index(): void
{
    $proveedores = $this->proveedorModel->listar();
    // Sin filtro de tienda — lista global completa
    $csrfToken = $this->generarCsrfToken();
}
```

---

### `store()` — Crear proveedor

```php
public function store(): void
{
    // 1. Valida CSRF
    // 2. validarDatos($_POST)
    // 3. existeNit(datos['ruc_nit'])  ← sin excluirId
    //    → error si el NIT ya existe en otro proveedor activo

    $this->proveedorModel->crear($datos);
    $this->jsonExito('proveedores.index', 'Proveedor registrado correctamente.');
}
```

---

### `update()` — Editar proveedor

```php
public function update(): void
{
    // existeNit(datos['ruc_nit'], $id)  ← con excluirId
    //   → error si el NIT existe en OTRO proveedor (excluye el actual)

    $this->proveedorModel->actualizar($id, $datos);
    $this->jsonExito('proveedores.index', 'Proveedor actualizado correctamente.');
}
```

---

### `toggleEstado()` — Activar / Desactivar

```php
public function toggleEstado(): void
{
    $nuevoEstado = $estadoActual === 1 ? 0 : 1;
    $this->proveedorModel->cambiarEstado($id, $nuevoEstado);

    $this->guardarMensaje('success', $nuevoEstado === 1 ? 'Proveedor activado.' : 'Proveedor desactivado.');
    $this->redireccionar('index.php?route=proveedores.index');
    // → Flash + redirect (no JSON)
}
```

---

### `destroy()` — Eliminación lógica

```php
public function destroy(): void
{
    $this->proveedorModel->eliminarLogico($id);
    // → UPDATE proveedores SET deleted_at = NOW(), estado = 0

    $this->guardarMensaje('success', 'Proveedor eliminado correctamente.');
    $this->redireccionar('index.php?route=proveedores.index');
    // → Flash + redirect (no JSON)
}
```

---

## Validación: `validarDatos()`

```php
private function validarDatos(array $input): ?array
{
    // Obligatorio: nombre (no vacío)
    // Obligatorio: ruc_nit (no vacío; unicidad se valida por separado)
    // Opcional: email → si se ingresa: FILTER_VALIDATE_EMAIL

    return [
        'nombre'          => $nombre,
        'ruc_nit'         => $rucNit,
        'telefono'        => trim($input['telefono'] ?? ''),
        'email'           => $email,           // lowercase
        'direccion'       => trim($input['direccion'] ?? ''),
        'contacto_nombre' => trim($input['contacto_nombre'] ?? ''),
        'estado'          => (int) ($input['estado'] ?? 1),
    ];
}
```

**Reglas de validación:**

| Campo | Obligatorio | Validación extra |
|---|---|---|
| `nombre` | ✅ | No vacío |
| `ruc_nit` | ✅ | No vacío; único globalmente (validado post-normalización) |
| `email` | ❌ | Si se ingresa: formato válido vía `FILTER_VALIDATE_EMAIL`; se guarda en lowercase |
| `telefono` | ❌ | Sin validación de formato |
| `direccion` | ❌ | Sin validación |
| `contacto_nombre` | ❌ | Nombre del contacto dentro de la empresa proveedora |
| `estado` | ❌ | Default `1` (activo) |

> **Nota:** En `crear()`, el modelo fuerza `estado = 1` ignorando el valor enviado. Solo `actualizar()` respeta el `estado` del formulario.

---

## Modelo: `Proveedor.php`

### Tabla: `proveedores`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `nombre` | VARCHAR | Nombre de la empresa o persona proveedora |
| `ruc_nit` | VARCHAR UNIQUE | Identificador fiscal único |
| `telefono` | VARCHAR / NULL | Teléfono de contacto |
| `email` | VARCHAR / NULL | Email de contacto (lowercase) |
| `direccion` | VARCHAR / NULL | Dirección física |
| `contacto_nombre` | VARCHAR / NULL | Nombre del contacto dentro de la empresa |
| `estado` | TINYINT | 1=activo, 0=inactivo |
| `deleted_at` | TIMESTAMP / NULL | Soft delete |

### Métodos del modelo

| Método | Descripción |
|---|---|
| `listar()` | Todos los proveedores activos (sin soft delete), orden DESC |
| `buscarPorId(int $id)` | Retorna proveedor por ID o `null` si no existe o está eliminado |
| `existeNit(string $nit, ?int $excluirId)` | Verifica unicidad del NIT; con excluirId ignora el propio proveedor |
| `crear(array $datos)` | INSERT; siempre guarda `estado = 1` sin importar el formulario |
| `actualizar(int $id, array $datos)` | UPDATE de todos los campos incluyendo `estado` |
| `cambiarEstado(int $id, int $estado)` | UPDATE solo el campo `estado` |
| `eliminarLogico(int $id)` | UPDATE `deleted_at = NOW(), estado = 0` |
| `listarParaSelect()` | Lista reducida (id, nombre, ruc_nit) de proveedores activos para `<select>` |

### Detalle: `listarParaSelect()` — para uso en otros módulos

```sql
SELECT id, nombre, ruc_nit
FROM proveedores
WHERE deleted_at IS NULL AND estado = 1
ORDER BY nombre ASC
```

Disponible para módulos como órdenes de compra o entradas de inventario que necesiten vincular un proveedor.

---

## Vista: `proveedores/index.php`

**Funcionalidades:**
- Lista proveedores con: nombre, NIT/RUC, teléfono, email, contacto, estado (badge activo/inactivo)
- Botón "Nuevo Proveedor" → `openModal('index.php?route=proveedores.create&ajax=1')`
- Botón "Editar" → `openModal('index.php?route=proveedores.edit&id=X&ajax=1')`
- Botón "Activar/Desactivar" → formulario POST inline con CSRF (flash + redirect)
- Botón "Eliminar" → formulario POST inline con confirmación JS (flash + redirect)
- Paginación cliente (10 registros por página)

---

## Flujo completo: Crear proveedor

```
1. Superadmin en proveedores.index hace clic "Nuevo Proveedor"
   ↓
2. openModal('index.php?route=proveedores.create&ajax=1')
   → GET: ProveedorController::create()
   → Vista simple, sin dependencias de BD
   ↓
3. Modal muestra:
   - nombre* (text)
   - ruc_nit* (text)
   - telefono, email, dirección, contacto_nombre (todos opcionales)
   ↓
4. Admin completa: nombre="Distribuidora Alfa S.A.", ruc_nit="900123456-7", email="ventas@alfa.com"
   submitModalForm()
   → POST proveedores.store con X-Modal-Request: 1
   ↓
5. ProveedorController::store()
   → Valida CSRF ✓
   → validarDatos() → nombre y ruc_nit presentes ✓, email válido ✓
   → existeNit('900123456-7') → false ✓
   → proveedorModel->crear([...]) → id=12
   → jsonExito('proveedores.index', 'Proveedor registrado correctamente.')
   ↓
6. JS: cierra modal, toast, recarga proveedores.index
```

---

## Errores comunes y soluciones

| Error | Causa | Solución |
|---|---|---|
| "El nombre del proveedor es obligatorio" | Campo nombre vacío | Completar el nombre |
| "El NIT/RUC es obligatorio" | Campo ruc_nit vacío | Ingresar el NIT o RUC |
| "Ya existe un proveedor con ese NIT/RUC" | NIT duplicado al crear | Verificar si el proveedor ya está registrado |
| "Ya existe otro proveedor con ese NIT/RUC" | NIT duplicado al editar | Cambiar el NIT o buscar el proveedor existente |
| "El correo electrónico no tiene un formato válido" | Email con formato incorrecto | Verificar formato (ej: contacto@empresa.com) |

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
