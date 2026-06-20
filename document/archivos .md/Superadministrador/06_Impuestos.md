# 🧾 Módulo Impuestos — Superadministrador

> **Rol:** `Superadministrador` (y `Administrador de Tienda`)
> **Permisos:** `productos.view` · `productos.create` · `productos.update` · `productos.delete`
> **Controlador:** `ImpuestoController`
> **Modelo:** `Impuesto`
> **Vistas:** `resources/views/impuestos/`

---

## Descripción

El módulo Impuestos permite definir los tipos de impuesto que se aplican a los productos (IVA, INC, exento, etc.) con su porcentaje correspondiente. Al crear un producto, se selecciona un impuesto del catálogo y el sistema aplica el porcentaje al calcular el precio final.

Al igual que las Unidades de Medida, los impuestos también usan **eliminación física** (`DELETE` real), con un `try/catch` para proteger la integridad referencial cuando hay productos que los usan. Sin embargo, a diferencia de las unidades, los impuestos sí tienen campo `activo` para desactivarlos sin eliminarlos.

---

## Rutas disponibles

| Ruta | Método | Permiso | Acción |
|---|---|---|---|
| `impuestos.index` | GET | `productos.view` | Listar todos los impuestos |
| `impuestos.create` | GET | `productos.create` | Formulario nuevo impuesto (modal) |
| `impuestos.store` | POST | `productos.create` | Guardar nuevo impuesto |
| `impuestos.edit` | GET | `productos.update` | Formulario editar impuesto (modal) |
| `impuestos.update` | POST | `productos.update` | Guardar cambios |
| `impuestos.toggle` | POST | `productos.update` | Activar / desactivar impuesto |
| `impuestos.destroy` | POST | `productos.delete` | Eliminación física (con protección FK) |

---

## Controlador: `ImpuestoController.php`

```php
final class ImpuestoController
{
    use ControllerHelper;
    private Impuesto $impuestoModel;

    public function __construct()
    {
        $this->impuestoModel = new Impuesto();
    }
}
```

---

### `index()` — Listar impuestos

```php
public function index(): void
{
    $impuestos = $this->impuestoModel->listar();
    $csrfToken = $this->generarCsrfToken();

    require __DIR__ . '/../../resources/views/impuestos/index.php';
}
```

**Variables para la vista:**
- `$impuestos` — array con todos los impuestos (id, nombre, descripcion, porcentaje, tipo, activo), ordenados DESC
- `$csrfToken` — token CSRF

---

### `store()` — Crear impuesto

```php
public function store(): void
{
    // 1. Solo acepta POST
    // 2. Valida CSRF
    // 3. Valida datos con validarDatosImpuesto($_POST)
    //    → El porcentaje se normaliza a 2 decimales: number_format($val, 2, '.', '')
    // 4. Crea en BD
    // 5. Responde JSON al modal

    $this->impuestoModel->crear($datos);
    $this->jsonExito('impuestos.index', 'Impuesto creado correctamente.');
}
```

---

### `toggleEstado()` — Activar/Desactivar

```php
$nuevoEstado = $estadoActual === 1 ? 0 : 1;
$this->impuestoModel->cambiarEstado($id, $nuevoEstado);

// Muestra flash y redirige (no usa JSON — es formulario inline)
```

**Estados:**
- `1` = Activo (aparece en `<select>` al crear/editar productos via `listarActivos()`)
- `0` = Inactivo (no aparece para nuevas selecciones; productos existentes conservan su impuesto)

---

### `destroy()` — Eliminación física con captura de FK

```php
public function destroy(): void
{
    try {
        $this->impuestoModel->eliminar($id);
        $this->guardarMensaje('success', 'Impuesto eliminado correctamente.');
    } catch (Throwable $error) {
        $this->guardarMensaje(
            'error',
            'No se puede eliminar este impuesto porque puede estar relacionado con productos.'
        );
    }

    $this->redireccionar('index.php?route=impuestos.index');
}
```

---

## Validación: `validarDatosImpuesto()`

```php
private function validarDatosImpuesto(array $input): ?array
{
    $nombre      = trim((string) ($input['nombre'] ?? ''));
    $descripcion = trim((string) ($input['descripcion'] ?? ''));
    $porcentaje  = trim((string) ($input['porcentaje'] ?? ''));
    $tipo        = trim((string) ($input['tipo'] ?? ''));
    $activo      = (int) ($input['activo'] ?? 1);

    // Obligatorio: nombre (≤ 80 chars), porcentaje (0-100), tipo (≤ 50 chars)
    // Opcional: descripcion

    $porcentajeDecimal = (float) $porcentaje;

    return [
        'nombre'      => $nombre,
        'descripcion' => $descripcion !== '' ? $descripcion : null,
        'porcentaje'  => number_format($porcentajeDecimal, 2, '.', ''),  // ej: "19.00"
        'tipo'        => $tipo,
        'activo'      => $activo === 1 ? 1 : 0,
    ];
}
```

**Reglas de validación:**

| Campo | Obligatorio | Validación extra |
|---|---|---|
| `nombre` | ✅ | No vacío, máximo 80 caracteres |
| `porcentaje` | ✅ | Debe ser número, entre 0 y 100. Se guarda con 2 decimales |
| `tipo` | ✅ | No vacío, máximo 50 caracteres (ej: "IVA", "INC", "Exento") |
| `descripcion` | ❌ | Se guarda como `null` si vacío |
| `activo` | ❌ | Default `1` (activo) |

> **Normalización del porcentaje:** `number_format(19.0, 2, '.', '')` → `"19.00"`. Esto garantiza que siempre se almacene con 2 decimales en la columna `DECIMAL(5,2)`.

---

## Modelo: `Impuesto.php`

### Tabla: `impuestos`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `nombre` | VARCHAR(80) | Nombre del impuesto (ej: "IVA 19%") |
| `descripcion` | TEXT | Descripción opcional |
| `porcentaje` | DECIMAL(5,2) | Tasa del impuesto (ej: 19.00, 5.00, 0.00) |
| `tipo` | VARCHAR(50) | Categoría del impuesto (ej: "IVA", "INC", "Exento") |
| `activo` | TINYINT | 1=activo, 0=inactivo |

> **No tiene** `deleted_at`. La eliminación es física (`DELETE`).

### Métodos del modelo

| Método | Descripción |
|---|---|
| `listar()` | Retorna todos los impuestos (activos e inactivos), orden DESC |
| `listarActivos()` | Retorna solo los activos (`activo = 1`), ordenados por nombre ASC — usado en el formulario de productos |
| `buscarPorId(int $id)` | Retorna un impuesto por ID o `null` |
| `crear(array $datos)` | INSERT, retorna el nuevo `id` |
| `actualizar(int $id, array $datos)` | UPDATE de todos los campos |
| `cambiarEstado(int $id, int $activo)` | Actualiza solo el campo `activo` |
| `eliminar(int $id)` | **DELETE físico** — puede fallar si hay FK activos en productos |

### Método `listarActivos()` — para el `<select>` de productos

```sql
SELECT id, nombre, porcentaje, tipo
FROM impuestos
WHERE activo = 1
ORDER BY nombre ASC
```

Este método lo usa `ProductoController` para poblar el select de impuesto al crear/editar productos.

---

## Vista: `impuestos/index.php`

**Funcionalidades de la vista:**
- Lista todos los impuestos con: nombre, tipo, porcentaje (formateado con `%`), descripción, estado, acciones
- Botón "Nuevo Impuesto" → `openModal('index.php?route=impuestos.create&ajax=1')`
- Botón "Editar" → `openModal('index.php?route=impuestos.edit&id=X&ajax=1')`
- Botón "Activar/Desactivar" → formulario POST inline con CSRF
- Botón "Eliminar" → formulario POST inline con confirmación JS
- Paginación cliente (10 registros por página)

---

## Ejemplos de impuestos típicos

| nombre | porcentaje | tipo |
|---|---|---|
| IVA 19% | 19.00 | IVA |
| IVA 5% | 5.00 | IVA |
| INC Cervezas 8% | 8.00 | INC |
| Exento | 0.00 | Exento |

---

## Flujo completo: Crear impuesto

```
1. Superadmin en impuestos.index hace clic "Nuevo Impuesto"
   ↓
2. openModal('index.php?route=impuestos.create&ajax=1')
   → GET: ImpuestoController::create()
   → Vista simple sin dependencias de BD
   ↓
3. Modal muestra: nombre*, porcentaje*, tipo*, descripción (opcional), activo
   ↓
4. Usuario ingresa "IVA 19%", porcentaje "19", tipo "IVA" → "Guardar"
   submitModalForm()
   → POST impuestos.store con X-Modal-Request: 1
   ↓
5. ImpuestoController::store()
   → Valida CSRF ✓
   → validarDatosImpuesto() → porcentaje "19" → (float) 19.0 → "19.00" ✓
   → impuestoModel->crear([...]) ✓
   → jsonExito('impuestos.index', 'Impuesto creado correctamente.')
   ↓
6. JS: cierra modal, toast, recarga impuestos.index
```

---

## Errores comunes y soluciones

| Error | Causa | Solución |
|---|---|---|
| "El nombre del impuesto es obligatorio" | Campo nombre vacío | Completar el campo |
| "El porcentaje debe ser un número válido" | Porcentaje vacío o con letras | Ingresar un número (ej: "19") |
| "El porcentaje debe estar entre 0 y 100" | Valor fuera de rango | Ingresar valor entre 0 y 100 |
| "El tipo de impuesto es obligatorio" | Campo tipo vacío | Ingresar tipo (ej: "IVA") |
| "No se puede eliminar este impuesto porque puede estar relacionado con productos" | FK violation — productos usan este impuesto | Desactivar el impuesto en lugar de eliminarlo |

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
