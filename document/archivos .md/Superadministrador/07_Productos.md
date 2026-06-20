# 📦 Módulo Productos — Superadministrador

> **Rol:** `Superadministrador` (y `Administrador de Tienda`)
> **Permisos:** `productos.view` · `productos.create` · `productos.update` · `productos.delete`
> **Controlador:** `ProductoController`
> **Modelos:** `Producto` + `Categoria` + `UnidadMedida` + `Impuesto` + `Tienda`
> **Vistas:** `resources/views/productos/`

---

## Descripción

El módulo Productos es el núcleo del inventario. Gestiona los artículos que se venden en la plataforma. Tiene complejidad mayor que otros módulos porque un producto se relaciona simultáneamente con:

- **Una categoría** (opcional)
- **Una unidad de medida** (opcional)
- **Uno o varios impuestos** — tabla pivote `productos_impuestos`
- **Una o varias tiendas** — tabla pivote `tiendas_productos`, con precio de venta y precio de compra **por tienda**

Todas las operaciones de creación y actualización usan **transacciones PDO** para garantizar que el producto, sus impuestos y sus precios por tienda se guarden o reviertan de forma atómica.

---

## Rutas disponibles

| Ruta | Método | Permiso | Acción |
|---|---|---|---|
| `productos.index` | GET | `productos.view` | Listar todos los productos |
| `productos.create` | GET | `productos.create` | Formulario nuevo producto (modal) |
| `productos.store` | POST | `productos.create` | Guardar nuevo producto |
| `productos.edit` | GET | `productos.update` | Formulario editar producto (modal) |
| `productos.update` | POST | `productos.update` | Guardar cambios |
| `productos.toggle` | POST | `productos.update` | Activar / desactivar producto |
| `productos.destroy` | POST | `productos.delete` | Eliminación lógica (con cascada) |

---

## Controlador: `ProductoController.php`

```php
final class ProductoController
{
    use ControllerHelper;
    private Producto    $productoModel;
    private Categoria   $categoriaModel;
    private UnidadMedida $unidadModel;
    private Impuesto    $impuestoModel;
    private Tienda      $tiendaModel;
}
```

---

### `create()` — Formulario nuevo producto

```php
public function create(): void
{
    $categorias = $this->categoriaModel->listarActivasParaSelect();
    $unidades   = $this->unidadModel->listar();
    $impuestos  = $this->impuestoModel->listarActivos();
    $tiendas    = $this->tiendaModel->listar();
    $csrfToken  = $this->generarCsrfToken();

    require __DIR__ . '/../../resources/views/productos/create.php';
}
```

**Variables para la vista:**
- `$categorias` — para el `<select>` de categoría
- `$unidades` — para el `<select>` de unidad de medida
- `$impuestos` — para los `<checkbox>` de impuestos (múltiple selección)
- `$tiendas` — para mostrar un bloque de precio por tienda (checkboxes + inputs)

---

### `store()` — Crear producto (transaccional)

```php
public function store(): void
{
    // 1. Valida CSRF
    // 2. validarDatosProducto($_POST) → datos básicos del producto
    // 3. Verifica unicidad del código de barras (si se ingresó)
    // 4. validarTiendasProducto($_POST) → array de {tienda_id, precio_venta, precio_compra}
    // 5. Crea el producto, impuestos y tiendas en una sola transacción:

    $this->productoModel->crearCompleto(
        $datosProducto,        // datos de la tabla productos
        $impuestosIds,         // array de int (IDs de impuestos seleccionados)
        $tiendasProductos      // array de {tienda_id, precio_venta, precio_compra}
    );

    $this->jsonExito('productos.index', 'Producto creado correctamente.');
}
```

---

### `edit()` — Formulario editar producto

```php
public function edit(): void
{
    $producto          = $this->productoModel->buscarPorId($id);
    $categorias        = $this->categoriaModel->listarActivasParaSelect();
    $unidades          = $this->unidadModel->listar();
    $impuestos         = $this->impuestoModel->listarActivos();
    $tiendas           = $this->tiendaModel->listar();
    $impuestosProducto = $this->productoModel->obtenerImpuestosProducto($id); // array de IDs
    $tiendasProducto   = $this->productoModel->obtenerTiendasProducto($id);   // array indexado por tienda_id
    $csrfToken         = $this->generarCsrfToken();
}
```

**Variables adicionales para la vista de edición:**
- `$impuestosProducto` — array de IDs de impuestos activos para pre-marcar checkboxes
- `$tiendasProducto` — array `[tienda_id => ['precio_venta', 'precio_compra', 'estado']]` para pre-llenar precios

---

### `update()` — Guardar edición (transaccional)

```php
public function update(): void
{
    // Valida CSRF, ID, existencia
    // Valida código de barras: si cambió, no debe existir en OTRO producto
    //   → existeCodigoBarras($codigo, $idActual) → excluye el producto actual

    $this->productoModel->actualizarCompleto(
        $id,
        $datosProducto,
        $impuestosIds,
        $tiendasProductos
    );

    $this->jsonExito('productos.index', 'Producto actualizado correctamente.');
}
```

---

### `destroy()` — Eliminación lógica en cascada

```php
public function destroy(): void
{
    $this->productoModel->eliminarLogico($id, $this->usuarioIdActual());
    // Dentro de una transacción:
    // 1. UPDATE productos SET deleted_at=NOW(), estado=0
    // 2. UPDATE tiendas_productos SET estado=0 WHERE producto_id=:id
    // 3. UPDATE productos_impuestos SET activo=0 WHERE producto_id=:id
}
```

---

## Validación: `validarDatosProducto()`

```php
private function validarDatosProducto(array $input): ?array
{
    // Obligatorio: nombre (≤ 200 chars)
    // Opcional: descripcion, codigo_barras (≤ 50 chars), imagen_url
    // Opcional: categoria_id (si se envía, debe existir en BD)
    // Opcional: unidad_medida_id (si se envía, debe existir en BD)
}
```

**Reglas de validación — datos básicos:**

| Campo | Obligatorio | Validación extra |
|---|---|---|
| `nombre` | ✅ | No vacío, máximo 200 caracteres |
| `codigo_barras` | ❌ | Máximo 50 caracteres; debe ser único globalmente |
| `descripcion` | ❌ | Se guarda como `null` si vacío |
| `imagen_url` | ❌ | Se guarda como `null` si vacío |
| `categoria_id` | ❌ | Si se envía, debe existir en BD |
| `unidad_medida_id` | ❌ | Si se envía, debe existir en BD |

---

## Validación: `validarTiendasProducto()`

```php
private function validarTiendasProducto(array $input): ?array
{
    // Entrada desde el formulario:
    // $_POST['tiendas'][]        → array de tienda_id seleccionados
    // $_POST['precio_venta'][$tiendaId]  → precio por tienda
    // $_POST['precio_compra'][$tiendaId] → precio por tienda (opcional)

    // Reglas:
    // - Al menos una tienda debe estar seleccionada
    // - precio_venta es obligatorio para cada tienda seleccionada
    // - precio_venta >= 0
    // - precio_compra es opcional, pero si se ingresa debe ser numérico >= 0
    // - Ambos precios se guardan con 2 decimales: number_format(val, 2, '.', '')

    return [
        ['tienda_id' => 1, 'precio_venta' => '25000.00', 'precio_compra' => '18000.00'],
        ['tienda_id' => 3, 'precio_venta' => '27000.00', 'precio_compra' => null],
    ];
}
```

**Reglas de validación — tiendas y precios:**

| Campo | Obligatorio | Validación extra |
|---|---|---|
| `tiendas[]` | ✅ | Al menos una tienda seleccionada y existente |
| `precio_venta[$id]` | ✅ por tienda | Numérico, `>= 0`, normalizado a 2 decimales |
| `precio_compra[$id]` | ❌ por tienda | Si se ingresa: numérico, `>= 0` |

---

## Modelo: `Producto.php`

### Tablas involucradas

**Tabla principal: `productos`**

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `nombre` | VARCHAR(200) | Nombre del producto |
| `descripcion` | TEXT | Descripción opcional |
| `codigo_barras` | VARCHAR(50) UNIQUE | Código de barras (único global) |
| `imagen_url` | VARCHAR | URL de imagen opcional |
| `categoria_id` | INT FK / NULL | Categoría asignada |
| `unidad_medida_id` | INT FK / NULL | Unidad de medida |
| `estado` | TINYINT | 1=activo, 0=inactivo |
| `deleted_at` | TIMESTAMP | Soft delete |
| `created_by` | INT FK | Usuario que lo creó |
| `updated_by` | INT FK | Último usuario que lo modificó |
| `created_at` | TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | Última modificación |

**Tabla pivote: `productos_impuestos`**

| Campo | Tipo | Descripción |
|---|---|---|
| `producto_id` | INT FK | Producto |
| `impuesto_id` | INT FK | Impuesto aplicado |
| `activo` | TINYINT | 1=activo, 0=desactivado (no se borra) |

**Tabla pivote: `tiendas_productos`**

| Campo | Tipo | Descripción |
|---|---|---|
| `tienda_id` | INT FK | Tienda |
| `producto_id` | INT FK | Producto |
| `precio_venta` | DECIMAL(10,2) | Precio de venta en esta tienda |
| `precio_compra` | DECIMAL(10,2) / NULL | Precio de compra opcional |
| `estado` | TINYINT | 1=activo, 0=inactivo |

### Métodos del modelo

| Método | Descripción |
|---|---|
| `listar()` | SELECT con JOINs a categoría, unidad, impuestos (GROUP_CONCAT) y tiendas+precios (GROUP_CONCAT) |
| `buscarPorId(int $id)` | Retorna datos básicos del producto (sin JOINs) |
| `existeCodigoBarras(string $codigo, ?int $excluirId)` | Verifica unicidad; con `$excluirId` ignora el producto actual |
| `crearCompleto(array $prod, array $impIds, array $tiendas)` | Transacción: INSERT en productos + sincronizarImpuestos() + sincronizarTiendasProducto() |
| `actualizarCompleto(int $id, array $prod, array $impIds, array $tiendas)` | Transacción: UPDATE + sincronizarImpuestos() + sincronizarTiendasProducto() |
| `cambiarEstado(int $id, int $estado, ?int $updatedBy)` | Solo actualiza el campo `estado` |
| `eliminarLogico(int $id, ?int $updatedBy)` | Transacción: soft delete del producto + desactiva tiendas_productos + desactiva productos_impuestos |
| `obtenerImpuestosProducto(int $productoId)` | Retorna array de IDs de impuestos activos del producto |
| `obtenerTiendasProducto(int $productoId)` | Retorna array indexado por tienda_id con precio_venta, precio_compra, estado |

### Estrategia de sincronización (upsert)

`sincronizarImpuestos()` y `sincronizarTiendasProducto()` usan el patrón **desactivar todo, luego reactivar los seleccionados**:

```sql
-- Paso 1: desactivar todo
UPDATE productos_impuestos SET activo = 0 WHERE producto_id = :id

-- Paso 2: reactivar/insertar los seleccionados
INSERT INTO productos_impuestos (producto_id, impuesto_id, activo)
VALUES (:pid, :iid, 1)
ON DUPLICATE KEY UPDATE activo = 1
```

Esto preserva el historial y evita duplicados con `ON DUPLICATE KEY UPDATE`.

---

## Vista: `productos/index.php`

**Funcionalidades de la vista:**
- Lista todos los productos con: nombre, código de barras, categoría, unidad, impuestos (badges), tiendas y precios, estado
- Botón "Nuevo Producto" → `openModal('index.php?route=productos.create&ajax=1')`
- Botón "Editar" → `openModal('index.php?route=productos.edit&id=X&ajax=1')`
- Botón "Activar/Desactivar" → formulario POST inline con CSRF
- Botón "Eliminar" → formulario POST inline con confirmación JS
- Paginación cliente (10 registros por página)

---

## Flujo completo: Crear producto nuevo

```
1. Superadmin en productos.index hace clic "Nuevo Producto"
   ↓
2. openModal('index.php?route=productos.create&ajax=1')
   → GET: ProductoController::create()
   → Vista recibe categorias, unidades, impuestos, tiendas
   ↓
3. Modal muestra formulario:
   - nombre* (text)
   - codigo_barras (text, opcional)
   - descripcion, imagen_url
   - categoria_id (select)
   - unidad_medida_id (select)
   - impuestos[] (checkboxes múltiples)
   - Para cada tienda:
       checkbox "Incluir en tienda X"
       precio_venta[tienda_id]* (input numérico)
       precio_compra[tienda_id] (input numérico, opcional)
   ↓
4. Usuario completa y hace clic "Guardar"
   submitModalForm()
   → POST productos.store con X-Modal-Request: 1
   ↓
5. ProductoController::store()
   → Valida CSRF ✓
   → validarDatosProducto() ✓
   → existeCodigoBarras() → false ✓
   → validarTiendasProducto() → [{tienda_id:1, precio_venta:'25000.00', precio_compra:'18000.00'}] ✓
   → productoModel->crearCompleto(datos, impuestosIds, tiendasProductos)
       → beginTransaction()
       → INSERT INTO productos → $productoId = 42
       → sincronizarImpuestos(42, [1, 3])    ← IVA 19% e INC
       → sincronizarTiendasProducto(42, [...])
       → commit()
   → jsonExito('productos.index', 'Producto creado correctamente.')
   ↓
6. JS: cierra modal, toast "Producto creado correctamente."
   loadContent('productos.index', true)
```

---

## Errores comunes y soluciones

| Error | Causa | Solución |
|---|---|---|
| "El nombre del producto es obligatorio" | Campo nombre vacío | Completar el nombre |
| "El código de barras ya está registrado" | Código duplicado al crear | Verificar que no exista o no ingresarlo |
| "El código de barras ya está registrado en otro producto" | Código duplicado al editar | Cambiar el código o dejarlo vacío |
| "La categoría seleccionada no existe" | ID manipulado | El select solo muestra categorías activas válidas |
| "Debes asociar el producto al menos a una tienda" | Ninguna tienda seleccionada | Marcar al menos una tienda |
| "El precio de venta es obligatorio para cada tienda" | Tienda marcada sin precio | Ingresar precio de venta para cada tienda marcada |
| "El precio de venta no puede ser negativo" | Precio negativo | Ingresar precio >= 0 |
| Error 500 al guardar | Rollback de transacción | Revisar logs del servidor; puede ser FK o timeout |

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
