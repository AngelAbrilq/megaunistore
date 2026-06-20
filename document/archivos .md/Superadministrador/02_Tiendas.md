# 🏪 Módulo Tiendas — Superadministrador

> **Rol:** `Superadministrador`
> **Permisos:** `tiendas.view` · `tiendas.create` · `tiendas.update` · `tiendas.toggle` · `tiendas.delete`
> **Controlador:** `TiendaController`
> **Modelo:** `Tienda`
> **Vistas:** `resources/views/tiendas/`

---

## Descripción

El módulo Tiendas permite al Superadministrador gestionar las tiendas de la plataforma multistore. Es el módulo raíz del sistema: sin tiendas no hay inventario, ventas ni caja.

El Superadministrador es el **único rol** que puede crear, editar y eliminar tiendas, ya que los demás roles están vinculados a una tienda específica.

---

## Rutas disponibles

| Ruta | Método | Permiso | Acción |
|---|---|---|---|
| `tiendas.index` | GET | `tiendas.view` | Listar todas las tiendas |
| `tiendas.create` | GET | `tiendas.create` | Formulario nueva tienda (modal) |
| `tiendas.store` | POST | `tiendas.create` | Guardar nueva tienda |
| `tiendas.edit` | GET | `tiendas.update` | Formulario editar tienda (modal) |
| `tiendas.update` | POST | `tiendas.update` | Guardar cambios |
| `tiendas.toggle` | POST | `tiendas.toggle` | Activar / desactivar tienda |
| `tiendas.destroy` | POST | `tiendas.delete` | Eliminar lógicamente |

---

## Controlador: `TiendaController.php`

### `index()` — Listar tiendas

```php
public function index(): void
{
    $tiendas   = $this->tiendaModel->listar();   // Todas las tiendas (activas e inactivas)
    $csrfToken = $this->generarCsrfToken();

    require __DIR__ . '/../../resources/views/tiendas/index.php';
}
```

**Variables para la vista:**
- `$tiendas` — array de tiendas con todos sus campos
- `$csrfToken` — token CSRF para formularios POST inline

---

### `store()` — Crear tienda

```php
public function store(): void
{
    // 1. Solo acepta POST
    // 2. Valida token CSRF
    // 3. Valida y limpia los datos via validarDatosTienda($_POST)
    // 4. Obtiene el usuario actual para registrar como propietario
    // 5. Crea la tienda en BD
    // 6. Responde con JSON (modal) o redirige (formulario normal)

    $this->tiendaModel->crear([
        'nombre'        => $datos['nombre'],
        'descripcion'   => $datos['descripcion'],
        'logo_url'      => $datos['logo_url'],
        'direccion'     => $datos['direccion'],
        'telefono'      => $datos['telefono'],
        'email'         => $datos['email'],
        'propietario_id'=> $usuarioId,
        'plataforma_id' => 1,
        'estado'        => 1,
        'updated_by'    => $usuarioId,
    ]);

    $this->jsonExito('tiendas.index', 'Tienda creada correctamente.');
}
```

---

### `validarDatosTienda()` — Validación interna

```php
private function validarDatosTienda(array $input): ?array
{
    // Obligatorios: nombre, dirección
    // Opcional: descripcion, logo_url, telefono, email
    // Si email no está vacío, valida formato con filter_var()
    // Retorna array limpio o null si hay error (guarda flash de error)
}
```

**Reglas de validación:**
| Campo | Obligatorio | Validación extra |
|---|---|---|
| `nombre` | ✅ | No vacío |
| `direccion` | ✅ | No vacío |
| `email` | ❌ | Si presente, formato válido (`FILTER_VALIDATE_EMAIL`) |
| `descripcion` | ❌ | Se guarda como `null` si vacío |
| `logo_url` | ❌ | Se guarda como `null` si vacío |
| `telefono` | ❌ | Se guarda como `null` si vacío |

---

### `toggleEstado()` — Activar/Desactivar

```php
public function toggleEstado(): void
{
    $id           = (int) ($_POST['id'] ?? 0);
    $estadoActual = (int) ($_POST['estado_actual'] ?? 0);
    $nuevoEstado  = $estadoActual === 1 ? 0 : 1;   // Toggle

    $this->tiendaModel->cambiarEstado($id, $nuevoEstado, $this->usuarioIdActual());
}
```

**Estados:**
- `1` = Activa (visible, operativa)
- `0` = Inactiva (oculta, no puede recibir ventas)

---

### `destroy()` — Eliminación lógica

```php
$this->tiendaModel->eliminarLogico($id, $this->usuarioIdActual());
// NO borra el registro de la BD — usa soft delete (campo deleted_at o estado)
// Preserva la integridad referencial con ventas, inventario, etc.
```

---

## Modelo: `Tienda.php`

### Tabla: `tiendas`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `nombre` | VARCHAR(100) | Nombre de la tienda |
| `descripcion` | TEXT | Descripción opcional |
| `logo_url` | VARCHAR | URL del logo |
| `direccion` | VARCHAR(255) | Dirección física |
| `telefono` | VARCHAR(20) | Teléfono |
| `email` | VARCHAR(100) | Email de contacto |
| `propietario_id` | INT FK | Usuario que la creó |
| `plataforma_id` | INT | Plataforma (siempre 1 en v3) |
| `estado` | TINYINT | 1=activa, 0=inactiva |
| `updated_by` | INT FK | Último usuario que la modificó |
| `created_at` | TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | Última modificación |

### Métodos del modelo

| Método | Descripción |
|---|---|
| `listar()` | Retorna todas las tiendas |
| `buscarPorId(int $id)` | Retorna una tienda por ID o `null` |
| `crear(array $datos)` | INSERT en la tabla |
| `actualizar(int $id, array $datos)` | UPDATE |
| `cambiarEstado(int $id, int $estado, int $userId)` | Cambia el estado activo/inactivo |
| `eliminarLogico(int $id, int $userId)` | Soft delete |

---

## Vista: `tiendas/index.php`

**Funcionalidades de la vista:**
- Lista todas las tiendas con sus datos principales
- Botón "Nueva Tienda" → abre modal con `openModal('index.php?route=tiendas.create&ajax=1')`
- Botón "Editar" → abre modal con `openModal('index.php?route=tiendas.edit&id=X&ajax=1')`
- Botón "Activar/Desactivar" → formulario POST inline con CSRF
- Botón "Eliminar" → formulario POST inline con confirmación JS
- Paginación cliente (10 registros por página)

---

## Sistema Modal en Tiendas

```javascript
// Abrir formulario de creación
openModal('index.php?route=tiendas.create&ajax=1');

// Al enviar el formulario dentro del modal:
submitModalForm();
// → Agrega header X-Modal-Request: 1
// → Controlador responde: { ok: true, ruta: 'tiendas.index', mensaje: 'Tienda creada correctamente.' }
// → JS cierra modal, recarga la vista de tiendas y muestra el toast
```

---

## Flujo completo: Crear tienda nueva

```
1. Usuario en tiendas.index hace clic "Nueva Tienda"
   ↓
2. openModal('index.php?route=tiendas.create&ajax=1')
   → GET: TiendaController::create() → carga vista create.php como fragmento
   ↓
3. Modal muestra formulario con campos: nombre, dirección, email, etc.
   ↓
4. Usuario completa y hace clic "Guardar"
   submitModalForm()
   → POST tiendas.store con header X-Modal-Request: 1
   ↓
5. TiendaController::store()
   → Valida CSRF ✓
   → Valida datos ✓
   → tiendaModel->crear([...]) ✓
   → json_encode(['ok' => true, 'ruta' => 'tiendas.index', 'mensaje' => '...'])
   ↓
6. JS: cierra modal, muestra toast "Tienda creada correctamente."
   loadContent('tiendas.index', true)  ← Recarga la lista
```

---

## Errores comunes y soluciones

| Error | Causa | Solución |
|---|---|---|
| Token CSRF inválido (HTTP 419) | Sesión expirada o formulario duplicado | El usuario debe recargar y volver a intentar |
| "La tienda no existe" | ID manipulado en GET | Validación de `buscarPorId()` retorna `null` |
| "El nombre es obligatorio" | Campo vacío | Completar el campo y reenviar |
| Eliminación falla | Tienda con dependencias (usuarios, inventario) | Desactivar en lugar de eliminar |

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
