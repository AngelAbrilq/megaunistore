# 🗂️ Módulo Categorías — Superadministrador

> **Rol:** `Superadministrador` (y `Administrador de Tienda`)
> **Permisos:** `productos.view` · `productos.create` · `productos.update` · `productos.delete`
> **Controlador:** `CategoriaController`
> **Modelo:** `Categoria`
> **Vistas:** `resources/views/categorias/`

---

## Descripción

El módulo Categorías permite organizar los productos en una estructura jerárquica de dos niveles: **categoría padre** y **subcategoría**. Toda categoría puede opcionalmente tener una categoría padre, lo que permite agrupar subcategorías bajo un tema común (ej: "Electrónica" → "Celulares", "Laptops").

Las categorías se usan al crear y editar productos para clasificarlos. Sin al menos una categoría activa no es posible crear productos.

---

## Rutas disponibles

| Ruta | Método | Permiso | Acción |
|---|---|---|---|
| `categorias.index` | GET | `productos.view` | Listar todas las categorías |
| `categorias.create` | GET | `productos.create` | Formulario nueva categoría (modal) |
| `categorias.store` | POST | `productos.create` | Guardar nueva categoría |
| `categorias.edit` | GET | `productos.update` | Formulario editar categoría (modal) |
| `categorias.update` | POST | `productos.update` | Guardar cambios |
| `categorias.toggle` | POST | `productos.update` | Activar / desactivar categoría |
| `categorias.destroy` | POST | `productos.delete` | Eliminación lógica |

---

## Controlador: `CategoriaController.php`

```php
final class CategoriaController
{
    use ControllerHelper;
    private Categoria $categoriaModel;

    public function __construct()
    {
        $this->categoriaModel = new Categoria();
    }
}
```

---

### `index()` — Listar categorías

```php
public function index(): void
{
    $categorias = $this->categoriaModel->listar();
    $csrfToken  = $this->generarCsrfToken();

    require __DIR__ . '/../../resources/views/categorias/index.php';
}
```

**Variables para la vista:**
- `$categorias` — array de categorías con `categoria_padre_nombre` incluido (JOIN en el modelo)
- `$csrfToken` — token CSRF

---

### `create()` — Formulario nueva categoría

```php
public function create(): void
{
    $categoriasPadre = $this->categoriaModel->listarActivasParaSelect();
    $csrfToken       = $this->generarCsrfToken();

    require __DIR__ . '/../../resources/views/categorias/create.php';
}
```

**Variables para la vista:**
- `$categoriasPadre` — categorías activas para el `<select>` de "Categoría padre" (sin filtro de exclusión al crear)

---

### `store()` — Crear categoría

```php
public function store(): void
{
    // 1. Solo acepta POST
    // 2. Valida CSRF
    // 3. Valida datos con validarDatosCategoria($_POST)
    // 4. Crea en BD
    // 5. Responde JSON al modal

    $this->categoriaModel->crear($datos);

    $this->jsonExito('categorias.index', 'Categoría creada correctamente.');
}
```

---

### `edit()` — Formulario editar categoría

```php
public function edit(): void
{
    $id       = (int) ($_GET['id'] ?? 0);
    $categoria = $this->categoriaModel->buscarPorId($id);

    // En el select de "Categoría padre" se excluye la propia categoría
    // para evitar que se asigne a sí misma como padre
    $categoriasPadre = $this->categoriaModel->listarActivasParaSelect($id);
    $csrfToken       = $this->generarCsrfToken();

    require __DIR__ . '/../../resources/views/categorias/edit.php';
}
```

**Variables para la vista:**
- `$categoria` — datos actuales de la categoría a editar
- `$categoriasPadre` — categorías activas **excluyendo la categoría actual** (`id <> $id`)

---

### `update()` — Guardar edición

```php
public function update(): void
{
    // Valida CSRF, ID y existencia de la categoría
    // Valida datos con validarDatosCategoria($_POST, $id)
    //   → $id pasado para validar que no sea padre de sí misma

    $this->categoriaModel->actualizar($id, $datos);

    $this->jsonExito('categorias.index', 'Categoría actualizada correctamente.');
}
```

---

### `toggleEstado()` — Activar/Desactivar

```php
$nuevoEstado = $estadoActual === 1 ? 0 : 1;
$this->categoriaModel->cambiarEstado($id, $nuevoEstado);

// Responde con redirección (no JSON) porque es un formulario inline
// Muestra flash 'success' con el mensaje correspondiente
```

**Estados:**
- `1` = Activa (aparece en el `<select>` al crear/editar productos)
- `0` = Inactiva (oculta para nuevas selecciones; productos existentes no se ven afectados)

---

### `destroy()` — Eliminación lógica

```php
$this->categoriaModel->eliminarLogico($id);
// Marca deleted_at = NOW() y activo = 0
// Los productos ya vinculados a esta categoría mantienen la referencia en BD
```

---

## Validación: `validarDatosCategoria()`

```php
private function validarDatosCategoria(array $input, ?int $categoriaIdActual = null): ?array
{
    $nombre           = trim((string) ($input['nombre'] ?? ''));
    $descripcion      = trim((string) ($input['descripcion'] ?? ''));
    $imagenUrl        = trim((string) ($input['imagen_url'] ?? ''));
    $categoriaPadreId = (int) ($input['categoria_padre_id'] ?? 0);
    $activo           = (int) ($input['activo'] ?? 1);

    // Regla 1: nombre obligatorio
    // Regla 2: nombre máximo 100 caracteres
    // Regla 3: no puede ser su propio padre (solo en edición)
    // Regla 4: si hay padre, debe existir en BD

    return [
        'nombre'             => $nombre,
        'descripcion'        => $descripcion !== '' ? $descripcion : null,
        'categoria_padre_id' => $categoriaPadreId > 0 ? $categoriaPadreId : null,
        'imagen_url'         => $imagenUrl !== '' ? $imagenUrl : null,
        'activo'             => $activo === 1 ? 1 : 0,
    ];
}
```

**Reglas de validación:**

| Campo | Obligatorio | Validación extra |
|---|---|---|
| `nombre` | ✅ | No vacío, máximo 100 caracteres |
| `descripcion` | ❌ | Se guarda como `null` si vacío |
| `categoria_padre_id` | ❌ | Si presente, debe existir en BD; no puede ser la misma categoría (en edición) |
| `imagen_url` | ❌ | Se guarda como `null` si vacío |
| `activo` | ❌ | Default `1` (activa) |

---

## Modelo: `Categoria.php`

### Tabla: `categorias`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `nombre` | VARCHAR(100) | Nombre de la categoría |
| `descripcion` | TEXT | Descripción opcional |
| `categoria_padre_id` | INT FK / NULL | ID de la categoría padre (jerarquía de 2 niveles) |
| `imagen_url` | VARCHAR | URL de imagen de la categoría (opcional) |
| `activo` | TINYINT | 1=activa, 0=inactiva |
| `deleted_at` | TIMESTAMP | NULL = no eliminada; fecha = soft delete |

### Métodos del modelo

| Método | Descripción |
|---|---|
| `listar()` | Retorna todas las categorías no eliminadas con JOIN a su categoría padre (`categoria_padre_nombre`) |
| `listarActivasParaSelect(?int $excluirId)` | Retorna categorías activas para `<select>`. Si se pasa `$excluirId`, excluye esa categoría (evita auto-referencia) |
| `buscarPorId(int $id)` | Retorna una categoría por ID o `null` |
| `crear(array $datos)` | INSERT en la tabla, retorna el nuevo `id` |
| `actualizar(int $id, array $datos)` | UPDATE de todos los campos editables |
| `cambiarEstado(int $id, int $activo)` | Actualiza solo el campo `activo` |
| `eliminarLogico(int $id)` | Marca `deleted_at = NOW()` y `activo = 0` |

### Detalle del `listar()` — JOIN con padre

```sql
SELECT
    c.id,
    c.nombre,
    c.descripcion,
    c.categoria_padre_id,
    c.imagen_url,
    c.activo,
    c.deleted_at,
    cp.nombre AS categoria_padre_nombre    -- Nombre de la categoría padre
FROM categorias c
LEFT JOIN categorias cp ON cp.id = c.categoria_padre_id
WHERE c.deleted_at IS NULL
ORDER BY c.id DESC
```

---

## Vista: `categorias/index.php`

**Funcionalidades de la vista:**
- Lista todas las categorías con: nombre, categoría padre, descripción, estado, acciones
- Botón "Nueva Categoría" → `openModal('index.php?route=categorias.create&ajax=1')`
- Botón "Editar" → `openModal('index.php?route=categorias.edit&id=X&ajax=1')`
- Botón "Activar/Desactivar" → formulario POST inline con CSRF
- Botón "Eliminar" → formulario POST inline con confirmación JS
- Paginación cliente (10 registros por página)
- Las categorías sin padre muestran "—" o "Raíz" en la columna Padre

---

## Flujo completo: Crear categoría nueva

```
1. Superadmin en categorias.index hace clic "Nueva Categoría"
   ↓
2. openModal('index.php?route=categorias.create&ajax=1')
   → GET: CategoriaController::create()
   → Vista recibe $categoriasPadre para el <select>
   ↓
3. Modal muestra formulario: nombre*, descripción, categoría padre (opcional), imagen URL, activo
   ↓
4. Usuario completa y hace clic "Guardar"
   submitModalForm()
   → POST categorias.store con header X-Modal-Request: 1
   ↓
5. CategoriaController::store()
   → Valida CSRF ✓
   → validarDatosCategoria($_POST) ✓
   → categoriaModel->crear([...]) ✓
   → jsonExito('categorias.index', 'Categoría creada correctamente.')
   ↓
6. JS: cierra modal, toast "Categoría creada correctamente."
   loadContent('categorias.index', true)
```

---

## Jerarquía de categorías — Ejemplo práctico

```
Electrónica          (id=1, categoria_padre_id=NULL)
├── Celulares        (id=2, categoria_padre_id=1)
├── Laptops          (id=3, categoria_padre_id=1)
└── Accesorios       (id=4, categoria_padre_id=1)

Ropa                 (id=5, categoria_padre_id=NULL)
├── Camisetas        (id=6, categoria_padre_id=5)
└── Pantalones       (id=7, categoria_padre_id=5)
```

> El sistema soporta **2 niveles**: padre → hijo. No se valida ni soporta profundidad mayor de 2.

---

## Errores comunes y soluciones

| Error | Causa | Solución |
|---|---|---|
| "El nombre de la categoría es obligatorio" | Campo nombre vacío | Completar el campo nombre |
| "El nombre no puede superar 100 caracteres" | Nombre demasiado largo | Abreviar el nombre |
| "Una categoría no puede ser padre de sí misma" | Se seleccionó la misma categoría como padre al editar | Seleccionar otra categoría o dejar vacío |
| "La categoría padre seleccionada no existe" | ID de padre manipulado | El select solo muestra categorías activas válidas |
| Categoría no aparece en productos | La categoría está inactiva | Activarla desde el listado |

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
