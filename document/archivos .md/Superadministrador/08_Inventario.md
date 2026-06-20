# 📊 Módulo Inventario — Superadministrador

> **Rol:** `Superadministrador` (ve todas las tiendas) · `Administrador de Tienda`, `Bodeguero` (solo su tienda)
> **Permisos:** `inventario.view` · `inventario.move` · `inventario.alerts`
> **Controlador:** `InventarioController`
> **Modelos:** `Inventario` + `Tienda`
> **Vistas:** `resources/views/inventario/`

---

## Descripción

El módulo Inventario gestiona el stock de cada producto en cada tienda. Su diseño es multitienda con **filtro de acceso automático**: el Superadministrador ve inventario de todas las tiendas, mientras que roles con `tienda_id` en sesión solo ven y gestionan su propia tienda.

### Conceptos clave

- **`inventario`** — Una fila por combinación `(tienda_id, producto_id)`. Almacena el stock actual, mínimo, máximo y ubicación.
- **`movimientos_inventario`** — Historial de cada cambio de stock. Nunca se modifica; solo se inserta.
- **Tipos de movimiento:** `entrada` (suma), `salida` (resta, con validación de stock), `ajuste` (reemplaza el stock absoluto)
- **Alerta de stock:** se activa cuando `cantidad <= cantidad_minima`
- **`crearOActualizar()`** — Upsert manual: si ya existe la combinación tienda+producto, hace UPDATE; si no, hace INSERT.
- **`registrarMovimiento()`** — Opera en una transacción: actualiza `inventario.cantidad` + inserta en `movimientos_inventario`.

---

## Rutas disponibles

| Ruta | Método | Permiso | Acción |
|---|---|---|---|
| `inventario.index` | GET | `inventario.view` | Listar inventario (stock actual por tienda) |
| `inventario.create` | GET | `inventario.move` | Formulario entrada inicial de stock |
| `inventario.store` | POST | `inventario.move` | Guardar/actualizar stock inicial |
| `inventario.movimiento` | GET | `inventario.move` | Ver historial y formulario de movimiento para un ítem |
| `inventario.guardar_movimiento` | POST | `inventario.move` | Registrar movimiento (entrada/salida/ajuste) |
| `inventario.movimientos` | GET | `inventario.view` | Listado global de movimientos |
| `inventario.alertas` | GET | `inventario.alerts` | Ver productos con stock bajo |

---

## Controlador: `InventarioController.php`

```php
final class InventarioController
{
    use ControllerHelper;
    private Inventario $inventarioModel;
    private Tienda     $tiendaModel;
}
```

---

### `index()` — Listar inventario

```php
public function index(): void
{
    $tiendaIdPermitida = $this->tiendaIdPermitida();  // null = Superadmin
    $inventarios       = $this->inventarioModel->listar($tiendaIdPermitida);
    $csrfToken         = $this->generarCsrfToken();

    require __DIR__ . '/../../resources/views/inventario/index.php';
}
```

**Variables para la vista:**
- `$inventarios` — array con: id, tienda_nombre, producto_nombre, codigo_barras, categoria_nombre, unidad_nombre, unidad_simbolo, cantidad, cantidad_minima, cantidad_maxima, ubicacion, updated_at

---

### `alertas()` — Stock bajo

```php
public function alertas(): void
{
    $tiendaIdPermitida = $this->tiendaIdPermitida();
    $alertas           = $this->inventarioModel->listarAlertas($tiendaIdPermitida);

    require __DIR__ . '/../../resources/views/inventario/alertas.php';
}
```

**Variables para la vista:**
- `$alertas` — mismos campos que index, pero filtrados por `cantidad <= cantidad_minima`

---

### `create()` — Formulario de entrada inicial

```php
public function create(): void
{
    $tiendaIdPermitida = $this->tiendaIdPermitida();

    // Superadmin: ve todas las tiendas
    // Otro rol: solo su tienda
    $tiendas  = $tiendaIdPermitida === null
        ? $this->tiendaModel->listar()
        : [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];

    // Solo productos activos asociados a tiendas activas
    $productos = $this->inventarioModel->productosAsociadosATiendas($tiendaIdPermitida);
    $csrfToken = $this->generarCsrfToken();
}
```

---

### `store()` — Guardar/actualizar stock inicial

```php
public function store(): void
{
    // 1. Valida CSRF
    // 2. validarDatosInventario() → tienda_id, producto_id, cantidades
    // 3. validarAccesoATienda() → 403 si el rol no tiene acceso a esa tienda
    // 4. Verifica que el producto pertenezca a la tienda en tiendas_productos
    // 5. crearOActualizar() → INSERT si no existe, UPDATE si ya existe

    $this->inventarioModel->crearOActualizar($datos);

    // Respuesta modal-compatible (JSON):
    $this->jsonExito('inventario.index', 'Inventario registrado correctamente.');
    // → Errores usan $this->jsonError(mensaje, 'inventario.create')
}
```

> **Nota:** `store()` responde JSON mediante `jsonExito()/jsonError()` del trait `ControllerHelper`. El formulario se abre vía `openModal()` y se envía a través del sistema de modal de la SPA.

---

### `movimiento()` — Ver historial + formulario de movimiento

```php
public function movimiento(): void
{
    $id        = (int) ($_GET['id'] ?? 0);
    $inventario = $this->inventarioModel->buscarPorId($id);

    $this->validarAccesoATienda((int) $inventario['tienda_id']);

    // Filtros opcionales vía GET
    $filtroTipo  = trim((string) ($_GET['tipo']  ?? ''));
    $filtroDesde = trim((string) ($_GET['desde'] ?? ''));
    $filtroHasta = trim((string) ($_GET['hasta'] ?? ''));

    $movimientos = $this->inventarioModel->listarMovimientos(
        $id,
        null,
        $filtroTipo  !== '' ? $filtroTipo  : null,
        $filtroDesde !== '' ? $filtroDesde : null,
        $filtroHasta !== '' ? $filtroHasta : null
    );
    $csrfToken   = $this->generarCsrfToken();

    require __DIR__ . '/../../resources/views/inventario/movimiento.php';
}
```

**Variables para la vista:**
- `$inventario` — datos actuales del ítem (tienda, producto, stock actual)
- `$movimientos` — historial de movimientos del ítem, con filtros aplicados si se pasaron
- `$filtroTipo`, `$filtroDesde`, `$filtroHasta` — valores actuales de los filtros (para pre-rellenar la barra)

---

### `guardarMovimiento()` — Registrar movimiento

```php
public function guardarMovimiento(): void
{
    // Valida: inventario_id, tipo (entrada|salida|ajuste), cantidad (numérico ≥ 0)

    $this->inventarioModel->registrarMovimiento(
        $inventarioId,
        $tipo,           // 'entrada' | 'salida' | 'ajuste'
        $cantidad,
        $motivo,         // string|null
        null,            // empleado_id (null en movimientos manuales)
        null,            // ref_id (ID de venta/devolución si es automático)
        null             // ref_tipo ('venta'|'devolucion'|null)
    );
    // Si hay error (ej: salida > stock) → el modelo lanza RuntimeException
    // El controlador la captura y muestra el mensaje
}
```

---

### `movimientos()` — Listado global de movimientos

```php
public function movimientos(): void
{
    $tiendaIdPermitida = $this->tiendaIdPermitida();

    // Filtros opcionales vía GET
    $filtroTipo  = trim((string) ($_GET['tipo']  ?? ''));
    $filtroDesde = trim((string) ($_GET['desde'] ?? ''));
    $filtroHasta = trim((string) ($_GET['hasta'] ?? ''));

    $movimientos = $this->inventarioModel->listarMovimientos(
        null,              // null = todos los ítems (global)
        $tiendaIdPermitida,
        $filtroTipo  !== '' ? $filtroTipo  : null,
        $filtroDesde !== '' ? $filtroDesde : null,
        $filtroHasta !== '' ? $filtroHasta : null
    );
}
```

---

## Filtro de acceso por tienda

```php
private function validarAccesoATienda(int $tiendaId): void
{
    $tiendaIdPermitida = $this->tiendaIdPermitida();

    if ($tiendaIdPermitida !== null && $tiendaIdPermitida !== $tiendaId) {
        // Usuario de tienda intenta acceder al inventario de otra tienda → 403
        $this->denegarAcceso();
    }
    // Si $tiendaIdPermitida === null → Superadmin → pasa sin restricciones
}
```

---

## Validación: `validarDatosInventario()`

**Reglas de validación — entrada inicial:**

| Campo | Obligatorio | Validación extra |
|---|---|---|
| `tienda_id` | ✅ | `> 0` |
| `producto_id` | ✅ | `> 0`; además el producto debe pertenecer a la tienda |
| `cantidad` | ✅ | Numérico, `>= 0`, normalizado a 2 decimales |
| `cantidad_minima` | ✅ | Numérico, `>= 0` |
| `cantidad_maxima` | ❌ | Si se ingresa: numérico, `>= 0`, y `>= cantidad_minima` |
| `ubicacion` | ❌ | Se guarda como `null` si vacío |

---

## Modelo: `Inventario.php`

### Tabla: `inventario`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `tienda_id` | INT FK | Tienda a la que pertenece el stock |
| `producto_id` | INT FK | Producto del que se lleva el stock |
| `cantidad` | DECIMAL(10,2) | Stock actual |
| `cantidad_minima` | DECIMAL(10,2) | Umbral de alerta (stock bajo cuando `cantidad <= cantidad_minima`) |
| `cantidad_maxima` | DECIMAL(10,2) / NULL | Capacidad máxima opcional |
| `ubicacion` | VARCHAR | Ubicación física en bodega (opcional) |
| `updated_at` | TIMESTAMP | Última actualización del stock |

> La combinación `(tienda_id, producto_id)` es **única** por diseño (UNIQUE KEY implícita — el modelo usa `buscarPorTiendaProducto` para el upsert manual).

### Tabla: `movimientos_inventario`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `inventario_id` | INT FK | Ítem de inventario afectado |
| `tipo` | ENUM('entrada','salida','ajuste') | Tipo de movimiento |
| `cantidad` | DECIMAL(10,2) | Cantidad del movimiento |
| `motivo` | VARCHAR | Descripción del motivo (opcional) |
| `empleado_id` | INT FK / NULL | Empleado relacionado (opcional) |
| `ref_id` | INT / NULL | ID de la referencia (venta, devolución) |
| `ref_tipo` | VARCHAR / NULL | Tipo de referencia ('venta', 'devolucion') |
| `created_at` | TIMESTAMP | Fecha y hora del movimiento |

### Métodos del modelo

| Método | Descripción |
|---|---|
| `listar(?int $tiendaId)` | Lista todo el inventario. Si `$tiendaId=null` → todas las tiendas; si `int` → solo esa tienda |
| `listarAlertas(?int $tiendaId)` | Lista ítems con `cantidad <= cantidad_minima`. Mismo filtro de tienda |
| `buscarPorId(int $id)` | Retorna un ítem de inventario con JOINs a tienda, producto y unidad |
| `buscarPorTiendaProducto(int $tiendaId, int $productoId)` | Busca el registro exacto por la clave compuesta |
| `productoPerteneceATienda(int $productoId, int $tiendaId)` | Verifica que el producto esté activo en `tiendas_productos` para esa tienda |
| `productosAsociadosATiendas(?int $tiendaId)` | Lista productos activos asociados a tiendas para el `<select>` |
| `crearOActualizar(array $datos)` | Upsert manual: UPDATE si existe, INSERT si no. Retorna el `id` del registro |
| `registrarMovimiento(...)` | Transacción: actualiza `inventario.cantidad` + inserta en `movimientos_inventario` |
| `listarMovimientos(?int $inventarioId, ?int $tiendaId, ?string $tipo, ?string $desde, ?string $hasta)` | Lista movimientos. Con `$inventarioId` → de un ítem; con `$tiendaId` → de una tienda. Acepta filtros opcionales: `tipo` (entrada/salida/ajuste), `desde` y `hasta` (fechas). Máximo 200 registros |

### Lógica interna de `registrarMovimiento()` — cálculo de stock

```php
$cantidadActual = (float) $inventario['cantidad'];

match ($tipo) {
    'entrada' => $nuevaCantidad = $cantidadActual + $cantidad,
    'salida'  => [
        // Validación: no puede salir más del disponible
        if ($cantidad > $cantidadActual) throw new RuntimeException('La salida no puede superar la cantidad disponible.'),
        $nuevaCantidad = $cantidadActual - $cantidad,
    ],
    'ajuste'  => $nuevaCantidad = $cantidad,  // Reemplaza el stock absoluto
};

// 1. UPDATE inventario SET cantidad = $nuevaCantidad WHERE id = $inventarioId
// 2. INSERT INTO movimientos_inventario (...)
// → Todo en una transacción
```

---

## Vista: `inventario/index.php`

**Funcionalidades de la vista:**
- Lista el inventario completo con columnas: tienda, producto, código de barras, categoría, unidad, stock actual, mínimo, máximo, ubicación, última actualización
- Stock en **rojo** cuando `cantidad <= cantidad_minima`
- Botón "Registrar inventario" → `openModal('index.php?route=inventario.create&ajax=1')` — abre el formulario en modal
- Botón "Movimiento" por fila → `loadContent('inventario.movimiento&id=X', true)` (navegación SPA)
- Botón "Ver Alertas" → `loadContent('inventario.alertas', true)`
- Paginación cliente (10 registros por página)

---

## Vista: `inventario/movimiento.php`

**Funcionalidades:**
- Muestra datos del ítem: tienda, producto, stock actual
- Formulario de nuevo movimiento: tipo (entrada/salida/ajuste), cantidad, motivo
- **Barra de filtros** sobre la tabla de historial:
  - Tipo (select: todas/entrada/salida/ajuste)
  - Desde (date input)
  - Hasta (date input)
  - Botones "Filtrar" y "Limpiar" — navegan vía `loadContent` con parámetros GET
- Tabla de "Historial de movimientos" del ítem (badge de color por tipo: verde=entrada, rojo=salida, azul=ajuste)
- Si no hay movimientos se muestra estado vacío con mensaje "Sin movimientos registrados"
- Botón "Volver al inventario" → `loadContent('inventario.index', true)`

**JS helpers en la vista:**
```javascript
// Aplica los filtros navegando con SPA
function _movFiltrar(inventarioId) {
    var tipo  = document.getElementById('movFiltroTipo').value;
    var desde = document.getElementById('movFiltroDesde').value;
    var hasta = document.getElementById('movFiltroHasta').value;
    var params = 'inventario.movimiento&id=' + inventarioId;
    if (tipo)  params += '&tipo='  + encodeURIComponent(tipo);
    if (desde) params += '&desde=' + encodeURIComponent(desde);
    if (hasta) params += '&hasta=' + encodeURIComponent(hasta);
    loadContent(params, true);
}
// Limpia los filtros
function _movLimpiar(inventarioId) {
    loadContent('inventario.movimiento&id=' + inventarioId, true);
}
```

---

## Vista: `inventario/movimientos.php` *(global)*

Vista de historial global de movimientos, accedida desde el dashboard del Bodeguero (ruta `inventario.movimientos`).

**Funcionalidades:**
- Página completa con todos los movimientos (filtrados por tienda si el rol tiene `tienda_id`)
- Misma barra de filtros que `movimiento.php` (tipo/desde/hasta) con helpers `_movsFiltrar()` y `_movsLimpiar()`
- Tabla con columnas: Fecha, Tienda, Producto, Tipo, Cantidad, Motivo
- Botón "← Volver al inventario" → `loadContent('inventario.index', true)`

---

## Flujo completo: Registrar entrada de mercancía

```
1. Superadmin en inventario.index hace clic "Registrar Entrada"
   ↓
2. loadContent('inventario.create', true)
   → GET: InventarioController::create()
   → Vista recibe tiendas (todas para Superadmin) y productos asociados
   ↓
3. Vista muestra formulario:
   - tienda_id (select con todas las tiendas)
   - producto_id (select con productos activos en tiendas_productos)
   - cantidad*, cantidad_minima*, cantidad_maxima, ubicacion
   ↓
4. Usuario completa → POST inventario.store
   ↓
5. InventarioController::store()
   → Valida CSRF, datos y acceso a tienda
   → productoPerteneceATienda(productoId, tiendaId) ✓
   → crearOActualizar($datos)
      → buscarPorTiendaProducto() → existe → UPDATE (ajusta cantidades)
      → o no existe → INSERT (primer registro)
   → flash 'success', redirecciona a inventario.index
```

---

## Flujo: Registrar movimiento manual (entrada/salida/ajuste)

```
1. En inventario.index, clic "Movimiento" en la fila del producto
   ↓
2. loadContent('inventario.movimiento&id=42', true)
   → GET: InventarioController::movimiento(id=42)
   → Valida acceso a la tienda del ítem
   → Vista muestra ítem + historial + formulario
   ↓
3. Usuario selecciona: tipo="salida", cantidad=5, motivo="Muestra de cliente"
   → POST inventario.guardar_movimiento
   ↓
4. InventarioController::guardarMovimiento()
   → Valida CSRF, tipo y cantidad
   → registrarMovimiento(42, 'salida', 5.0, 'Muestra de cliente', null, null, null)
      → beginTransaction()
      → stock actual = 20 → nueva cantidad = 20 - 5 = 15 ✓
      → UPDATE inventario SET cantidad = 15.00 WHERE id = 42
      → INSERT INTO movimientos_inventario (tipo='salida', cantidad=5, motivo='Muestra...')
      → commit()
   → flash 'success', redirecciona a inventario.movimiento&id=42
```

---

## Errores comunes y soluciones

| Error | Causa | Solución |
|---|---|---|
| "Debes seleccionar una tienda" | Select tienda vacío | Seleccionar una tienda del listado |
| "El producto seleccionado no está asociado a la tienda" | Producto no vinculado en tiendas_productos | Editar el producto y agregarlo a esa tienda |
| "La salida no puede superar la cantidad disponible" | Intento de sacar más del stock actual | Ingresar una cantidad <= stock disponible |
| "Tipo de movimiento inválido" | Valor manipulado fuera de los 3 tipos | Solo usar los radios del formulario: entrada, salida, ajuste |
| "No tienes permisos para gestionar inventario de esta tienda" | Rol con tienda asignada intentó acceder a inventario de otra tienda | Cada rol solo gestiona inventario de su tienda |
| "La cantidad máxima no puede ser menor que la cantidad mínima" | Máximo < mínimo | Ingresar máximo ≥ mínimo |

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
