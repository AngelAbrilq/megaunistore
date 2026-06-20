# 💰 Módulo Ventas (POS) — Superadministrador

> **Rol:** `Superadministrador` (ve todas las tiendas) · `Vendedor`, `Administrador de Tienda` (solo su tienda)
> **Permisos:** `ventas.view` · `ventas.create` · `ventas.cancel`
> **Controlador:** `VentaController`
> **Modelos:** `Venta` + `Tienda` + `MetodoPago` + `Cliente` + `Cupon` (interno)
> **Vistas:** `resources/views/ventas/`

---

## Descripción

El módulo Ventas es el **corazón del POS**. Registra transacciones de venta con múltiples productos, calcula subtotales, impuestos (sumando todos los impuestos activos de cada producto) y aplica descuentos de cupones.

### Lo que hace `crearVenta()` en una sola transacción:

1. Valida que haya una **caja abierta** en la tienda — sin caja no hay venta
2. Calcula totales y valida stock para cada producto
3. Inserta el registro en `ventas`
4. Inserta cada ítem en `ventas_detalle`
5. Descuenta el inventario (`inventario` y `movimientos_inventario`)
6. Registra el pago en `pagos`
7. Registra el ingreso en `cajas_movimientos`
8. Si hay cupón: incrementa su contador de usos (`cupones.usos_actuales`)

Si cualquier paso falla → `rollBack()` total.

---

## Rutas disponibles

| Ruta | Método | Permiso | Acción |
|---|---|---|---|
| `ventas.index` | GET | `ventas.view` | Listar ventas |
| `ventas.create` | GET | `ventas.create` | POS — formulario de nueva venta |
| `ventas.store` | POST | `ventas.create` | Registrar la venta |
| `ventas.show` | GET | `ventas.view` | Ver detalle/comprobante de venta |
| `ventas.anular` | POST | `ventas.cancel` | Anular una venta |

---

## Controlador: `VentaController.php`

```php
final class VentaController
{
    use ControllerHelper;
    private Venta      $ventaModel;
    private Tienda     $tiendaModel;
    private MetodoPago $metodoPagoModel;
    private Cliente    $clienteModel;
}
```

---

### `index()` — Listar ventas

```php
public function index(): void
{
    $tiendaIdPermitida = $this->tiendaIdPermitida();  // null = Superadmin
    $ventas            = $this->ventaModel->listar($tiendaIdPermitida);
    $csrfToken         = $this->generarCsrfToken();
}
```

**Variables para la vista:** `$ventas` — array con: id, tienda_nombre, caja_nombre, cliente_nombre, cliente_apellido, fecha, subtotal, descuento, impuesto, total, estado, creado_por_email.

---

### `create()` — POS / Formulario de nueva venta

```php
public function create(): void
{
    $tiendaIdPermitida = $this->tiendaIdPermitida();

    // Superadmin: todas las tiendas | Otros roles: solo su tienda
    $tiendas     = $tiendaIdPermitida === null
        ? $this->tiendaModel->listar()
        : [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];

    $metodosPago = $this->metodoPagoModel->listarActivos();

    // Para cada tienda: productos disponibles y clientes
    $productosPorTienda = [];
    $clientesPorTienda  = [];

    foreach ($tiendas as $tienda) {
        $tid = (int) $tienda['id'];
        $productosPorTienda[$tid] = $this->ventaModel->productosVendiblesPorTienda($tid);
        $clientesPorTienda[$tid]  = $this->clienteModel->listarParaSelect($tid);
    }
}
```

**Variables para la vista:**
- `$tiendas` — tiendas disponibles para el selector de tienda
- `$metodosPago` — métodos de pago activos
- `$productosPorTienda[$tiendaId]` — productos activos de esa tienda con precio y stock
- `$clientesPorTienda[$tiendaId]` — clientes de esa tienda para selección opcional

---

### `store()` — Registrar venta

```php
public function store(): void
{
    // Valida CSRF
    // Valida tienda_id, metodo_pago_id
    // validarAccesoATienda($tiendaId) → 403 si es rol ajeno
    // validarItemsVenta($_POST) → array [{producto_id, cantidad}, ...]
    // Captura el cupon_id y descuento_cupon ya validado desde el frontend

    $ventaId = $this->ventaModel->crearVenta($venta, $items, $pago);

    // Respuesta modal-compatible (JSON):
    $this->jsonExito('ventas.index', 'Venta #' . $ventaId . ' registrada correctamente.');
    // → Errores de validación usan $this->jsonError(mensaje, 'ventas.create')
    // → En caso de excepción: $this->jsonError($error->getMessage(), 'ventas.create')
    // NOTA: tras el éxito navega a ventas.index (no ventas.show) porque el modal
    // cierra y luego recarga la ruta activa del SPA.
}
```

---

### `show()` — Comprobante de venta

```php
public function show(): void
{
    $venta   = $this->ventaModel->buscarPorId($id);
    $detalle = $this->ventaModel->obtenerDetalle($id);  // líneas de la venta
    $pagos   = $this->ventaModel->obtenerPagos($id);    // formas de pago
    $csrfToken = $this->generarCsrfToken();
}
```

---

### `anular()` — Anular venta

```php
public function anular(): void
{
    // Valida CSRF, ID y existencia
    // validarAccesoATienda() → 403

    $this->ventaModel->anularVenta($id, $this->usuarioIdActual());
    // En transacción:
    // 1. Verifica que no esté ya anulada
    // 2. Verifica que la caja asociada esté abierta
    // 3. Reingresa el stock de cada ítem al inventario
    // 4. Registra egreso en cajas_movimientos
    // 5. UPDATE ventas SET estado = 'anulada'
    // 6. UPDATE pagos SET estado = 'rechazado'
    // 7. Si había cupón → decrementa usos

    $this->redireccionar('index.php?route=ventas.show&id=' . $id);
}
```

---

## Validación: `validarItemsVenta()`

```php
// Entrada del formulario:
// $_POST['producto_id'][] = [5, 12, 8]
// $_POST['cantidad'][]    = [2, 1, 3]

// Reglas:
// - Al menos un ítem en el carrito
// - producto_id > 0
// - cantidad numérica > 0
```

**Reglas de validación — ítems del carrito:**

| Campo | Obligatorio | Validación |
|---|---|---|
| `producto_id[]` | ✅ | Al menos uno, `> 0` |
| `cantidad[]` | ✅ por ítem | Numérico, `> 0` |
| `tienda_id` | ✅ | `> 0`, accesible para el rol |
| `metodo_pago_id` | ✅ | Debe existir y estar activo |
| `cliente_id` | ❌ | Opcional (venta anónima) |
| `cupon_id` | ❌ | Opcional (pre-validado desde JS) |
| `descuento_cupon` | ❌ | `>= 0` (viene calculado del frontend) |

---

## Modelo: `Venta.php`

### Tablas involucradas

**`ventas`** — Cabecera de la venta

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `tienda_id` | INT FK | Tienda donde se realizó la venta |
| `cliente_id` | INT FK / NULL | Cliente (opcional) |
| `empleado_id` | INT FK / NULL | Empleado que realizó la venta (auto-detectado por usuario) |
| `caja_id` | INT FK | Caja abierta al momento de la venta |
| `cupon_id` | INT FK / NULL | Cupón aplicado |
| `fecha` | TIMESTAMP | Fecha/hora de la venta |
| `subtotal` | DECIMAL(10,2) | Suma de subtotales sin impuesto |
| `descuento` | DECIMAL(10,2) | Descuento del cupón |
| `impuesto` | DECIMAL(10,2) | Suma de impuestos |
| `total` | DECIMAL(10,2) | `subtotal + impuesto - descuento` |
| `estado` | ENUM('completada','anulada') | Estado de la venta |
| `deleted_at` | TIMESTAMP | Soft delete |
| `created_by` | INT FK | Usuario que la registró |
| `updated_by` | INT FK | Último que la modificó |

**`ventas_detalle`** — Líneas de la venta

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `venta_id` | INT FK | Venta a la que pertenece |
| `producto_id` | INT FK | Producto vendido |
| `cantidad` | DECIMAL(10,2) | Cantidad vendida |
| `precio_unitario` | DECIMAL(10,2) | Precio al momento de la venta |
| `descuento` | DECIMAL(10,2) | Descuento por línea (actualmente `0.00`) |
| `subtotal` | DECIMAL(10,2) | `precio_unitario * cantidad` |

**`pagos`** — Registro de pago

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador |
| `venta_id` | INT FK | Venta pagada |
| `metodo_pago_id` | INT FK | Método de pago utilizado |
| `monto` | DECIMAL(10,2) | Monto pagado |
| `referencia` | VARCHAR / NULL | Número de referencia (tarjeta, transferencia, etc.) |
| `estado` | ENUM('aprobado','rechazado') | Estado del pago |

### Métodos del modelo

| Método | Descripción |
|---|---|
| `listar(?int $tiendaId)` | Lista ventas con JOINs a tienda, caja, cliente y usuario |
| `buscarPorId(int $id)` | Cabecera detallada de una venta |
| `obtenerDetalle(int $ventaId)` | Líneas de la venta con nombre del producto |
| `obtenerPagos(int $ventaId)` | Pagos de la venta con nombre del método |
| `productosVendiblesPorTienda(int $tiendaId)` | Productos activos en `tiendas_productos`, con precio y stock de inventario |
| `crearVenta(array $venta, array $items, array $pago)` | **Transacción completa** — ver flujo abajo |
| `anularVenta(int $ventaId, ?int $usuarioId)` | **Transacción completa** de anulación con reingreso de stock |

### Cálculo de totales por ítem (privado)

```php
private function calcularTotalesYValidarStock(int $tiendaId, array $items): array
{
    foreach ($items as $item) {
        // 1. Verifica que el producto esté activo en tiendas_productos de la tienda
        // 2. Verifica que tenga inventario registrado
        // 3. Valida stock suficiente: si cantidad > stock_actual → RuntimeException
        // 4. Calcula:
        $precioUnitario     = (float) $producto['precio_venta'];
        $subtotalItem       = $precioUnitario * $cantidad;
        $porcentajeImpuesto = SUM(impuestos activos del producto);  // de productos_impuestos
        $impuestoItem       = $subtotalItem * ($porcentajeImpuesto / 100);

        // Acumula subtotal e impuesto del carrito
    }

    // Total final aplicado en crearVenta():
    $totalFinal = max(0.0, $subtotal + $impuesto - $descuentoCupon);
}
```

### Verificación de caja abierta

```sql
-- Una caja está "abierta" si:
-- 1. estado = 1 (no desactivada)
-- 2. Tiene al menos un movimiento de tipo 'apertura'
-- 3. El último movimiento NO es de tipo 'cierre'
SELECT c.id, c.nombre
FROM cajas c
WHERE c.tienda_id = :tienda_id
  AND c.estado = 1
  AND EXISTS (SELECT 1 FROM cajas_movimientos WHERE caja_id = c.id AND tipo = 'apertura')
  AND (SELECT tipo FROM cajas_movimientos WHERE caja_id = c.id ORDER BY id DESC LIMIT 1) <> 'cierre'
ORDER BY c.id ASC
LIMIT 1
```

---

## Vista: `ventas/index.php`

**Funcionalidades:**
- Lista de ventas con: ID, tienda, cliente, fecha, total, estado (badge: verde=completada, rojo=anulada), usuario que registró
- Botón "Nueva Venta" → `openModal('index.php?route=ventas.create&ajax=1')` — abre el POS en modal
- Botón "Ver" → `openModal('index.php?route=ventas.show&id=X&ajax=1')` — abre el comprobante en modal
- Paginación cliente (10 registros por página)

> **Nota de implementación:** el botón "Ver" fue cambiado de `loadContent()` a `openModal()` para mantener el contexto del listado al volver. El modal de detalle/show detecta este contexto mediante `esPeticionModal()` y adapta los botones internos.

## Vista: `ventas/create.php`

**Funcionalidades (POS):**
- Si hay múltiples tiendas: selector de tienda que filtra productos y clientes disponibles
- Tabla de productos seleccionados (carrito): agregar/quitar líneas dinámicamente con JS
- Campo de búsqueda de producto por nombre o código de barras
- Selector de cliente (opcional)
- Campo de código de cupón con botón "Validar" → `fetch('cupones.validar', POST)`
- Resumen: subtotal, impuesto, descuento cupón, **total**
- Selector de método de pago
- Campo de referencia (para pagos con tarjeta/transferencia)
- Botón "Registrar Venta" → POST ventas.store

## Vista: `ventas/show.php`

**Funcionalidades:**
- Comprobante con cabecera de la venta (tienda, caja, cliente, fecha, estado)
- Tabla detalle de ítems (producto, cantidad, precio unitario, subtotal)
- Resumen de totales (subtotal, impuesto, descuento, total)
- Pago(s) realizados (método, monto, referencia)
- Botón "Anular Venta" (solo si estado = 'completada') → POST `ventas.anular`
- Botón "Volver" y botón "Nueva Devolución" son **contexto-dependientes**:
  - Si la vista se abre desde el modal (`esPeticionModal()` = true) → los botones llaman a `_ventaShowVolver()` y `_ventaShowDevolucion(id)` que cierran el modal y navegan apropiadamente
  - Si se accede directamente en el SPA → los botones usan `loadContent()` directamente

**JS helpers:**
```javascript
function _ventaShowVolver() {
    if (typeof closeModal === 'function' && document.getElementById('globalModal')?.style.display !== 'none') {
        closeModal();
    } else {
        loadContent('ventas.index', true);
    }
}
function _ventaShowDevolucion(id) {
    if (typeof closeModal === 'function') closeModal();
    loadContent('devoluciones.create&venta_id=' + id, true);
}
```

---

## Flujo completo: Registrar venta POS

```
1. Vendedor en ventas.index hace clic "Nueva Venta"
   ↓
2. loadContent('ventas.create', true)
   → GET: VentaController::create()
   → Vista recibe tiendas, metodosPago, productosPorTienda, clientesPorTienda
   ↓
3. Vendedor busca y agrega productos al carrito:
   - Busca "Camisa" → aparece en la tabla
   - Agrega 3 unidades de "Camisa Blanca" ($25.000)
   - Agrega 1 unidad de "Pantalón" ($45.000)
   - Total calculado en JS: subtotal=$120.000, IVA 19%=$22.800, total=$142.800
   ↓
4. Vendedor ingresa cupón "PROMO10" → botón "Validar"
   → fetch POST cupones.validar → {ok:true, tipo:'porcentaje', valor:10, descuento:14280}
   → total ajustado: $142.800 - $14.280 = $128.520
   ↓
5. Vendedor selecciona: Método de pago "Efectivo" → "Registrar Venta"
   POST ventas.store (desde modal — header X-Modal-Request: 1)
   ↓
6. VentaController::store()
   → Valida CSRF, tienda, método de pago ✓
   → validarItemsVenta() → [{prod:5, cant:3}, {prod:12, cant:1}] ✓
   → ventaModel->crearVenta(...)
      → beginTransaction()
      → buscarCajaAbiertaPorTienda(3) → caja_id=7 ✓
      → calcularTotalesYValidarStock() → stock OK, totales calculados ✓
      → INSERT INTO ventas → venta_id=142
      → insertarDetalleVenta(142, items)
         → Descuenta inventario de cada producto + registra movimiento_inventario tipo='salida'
      → registrarPago(142, metodo_pago_id=1, monto=128.520.00)
      → registrarMovimientoCaja(caja_id=7, tipo='ingreso', monto=128520)
      → cupon->incrementarUsos(cupon_id)
      → commit()
   → jsonExito('ventas.index', 'Venta #142 registrada correctamente.')
      → responde JSON {ok:true, redirect:'ventas.index', mensaje:'...'}
   ↓
7. Modal se cierra y el SPA navega a ventas.index mostrando el flash de éxito
```

---

## Errores comunes y soluciones

| Error | Causa | Solución |
|---|---|---|
| "No hay una caja abierta para esta tienda. Abre una caja antes de registrar ventas." | Ninguna caja activa/abierta en la tienda | Ir a Caja → Apertura y abrir una caja |
| "Stock insuficiente para el producto: X" | Se intenta vender más de lo disponible | Verificar stock en inventario o reducir cantidad |
| "El producto seleccionado no está disponible para esta tienda" | Producto no en `tiendas_productos` activo | Editar el producto y agregarlo a la tienda |
| "El producto no tiene inventario registrado: X" | Producto en tienda pero sin registro de inventario | Ir a Inventario → Registrar Entrada |
| "Debes agregar al menos un producto a la venta" | Carrito vacío | Agregar al menos un producto |
| "No se puede anular la venta porque la caja asociada ya está cerrada." | La caja ya fue cerrada tras hacer la venta | No se puede anular; crear devolución en cambio |
| "La venta ya está anulada." | Intento de anular una venta ya anulada | La venta ya fue procesada |

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
