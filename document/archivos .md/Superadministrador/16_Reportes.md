# 📊 Módulo Reportes — Superadministrador

> **Rol:** `Superadministrador` (ve todas las tiendas) · `Administrador de Tienda`, `Supervisor`, `Reportero` (solo su tienda)
> **Permisos:** `reportes.view`
> **Controlador:** `ReporteController`
> **Modelos:** `Reporte` + `Tienda`
> **Vistas:** `resources/views/reportes/`

---

## Descripción

El módulo Reportes es **solo lectura** — no realiza escrituras. Agrupa 8 sub-reportes organizados en tres categorías: ventas, inventario y caja. Todos los reportes respetan el filtro de tienda: el Superadmin puede ver cualquier tienda o todas a la vez; los roles con tienda asignada solo ven la suya.

### Conceptos clave

- **Filtro universal de tienda** — El parámetro `tienda_id` de los GET se ignora si el rol tiene `tiendaIdPermitida !== null`; en ese caso se fuerza al valor de la sesión.
- **Defaults de fecha** — Todos los reportes con período usan como default el mes actual: `fecha_inicio = Y-m-01`, `fecha_fin = Y-m-d`.
- **Solo ventas `'completada'`** — Todos los reportes de ventas excluyen ventas anuladas.
- **`resumenGeneral()`** — Usado por el dashboard (no tiene ruta propia en el módulo Reportes).
- **Sin paginación servidor** — Los resultados se devuelven completos; la paginación es del lado del cliente en la vista.

---

## Rutas disponibles

| Ruta | Método | Permiso | Acción |
|---|---|---|---|
| `reportes.index` | GET | `reportes.view` | Panel de acceso a todos los reportes |
| `reportes.ventas` | GET | `reportes.view` | Ventas por día en un período |
| `reportes.ventas_tienda` | GET | `reportes.view` | Ventas totales comparadas por tienda |
| `reportes.productos_vendidos` | GET | `reportes.view` | Productos más vendidos (ranking) |
| `reportes.metodo_pago` | GET | `reportes.view` | Ventas agrupadas por método de pago |
| `reportes.inventario` | GET | `reportes.view` | Inventario actual por tienda |
| `reportes.stock_bajo` | GET | `reportes.view` | Productos con stock ≤ mínimo |
| `reportes.movimientos_inventario` | GET | `reportes.view` | Historial de movimientos de inventario |
| `reportes.caja` | GET | `reportes.view` | Movimientos de caja por período |

---

## Controlador: `ReporteController.php`

```php
final class ReporteController
{
    use ControllerHelper;
    private Reporte $reporteModel;
    private Tienda  $tiendaModel;

    // tiendaIdPermitida() — implementación local (no del trait):
    //   return (int) $_SESSION['auth']['rol_principal']['tienda_id'] ?? null
}
```

**Patrón repetido en todos los métodos con filtro de tienda:**
```php
$tiendaId = (int) ($_GET['tienda_id'] ?? 0);
$tiendaIdPermitida = $this->tiendaIdPermitida();

if ($tiendaIdPermitida !== null) {
    $tiendaId = $tiendaIdPermitida;  // Override: ignora el GET
}

// Para el select de tiendas en la vista:
$tiendas = $tiendaIdPermitida === null
    ? $this->tiendaModel->listar()             // Superadmin: todas
    : [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];  // Rol: solo la suya
```

---

## Sub-reportes del controlador

### `index()` — Panel de acceso

```php
public function index(): void
{
    // Solo muestra la vista con links a todos los reportes
    // Pasa $tiendas para mostrarlo como contexto en la vista
}
```

---

### `ventas()` — Ventas por día

```php
public function ventas(): void
{
    // GET: fecha_inicio, fecha_fin, tienda_id
    $ventas = $this->reporteModel->ventasPorPeriodo($fechaInicio, $fechaFin, $tiendaId);
    // Agrupa por día: total_ventas, subtotal, descuento, impuesto, total
}
```

---

### `ventasPorTienda()` — Comparativo por tienda

```php
public function ventasPorTienda(): void
{
    // GET: fecha_inicio, fecha_fin (sin tienda_id — siempre todas las tiendas)
    $ventas = $this->reporteModel->ventasPorTienda($fechaInicio, $fechaFin);
    // Solo accesible por Superadmin (no tiene filtro de tienda en el modelo)
}
```

> **Nota:** Este reporte no filtra por tienda. Si un rol con tienda lo accede, verá todas las tiendas (es el único reporte sin restricción de tienda en el modelo).

---

### `productosMasVendidos()` — Ranking de productos

```php
public function productosMasVendidos(): void
{
    // GET: fecha_inicio, fecha_fin, tienda_id, limite (default 10)
    $productos = $this->reporteModel->productosMasVendidos($fechaInicio, $fechaFin, $tiendaId, $limite);
    // Ordenado por cantidad_vendida DESC, limitado
}
```

---

### `ventasPorMetodoPago()` — Por método de pago

```php
public function ventasPorMetodoPago(): void
{
    // GET: fecha_inicio, fecha_fin, tienda_id
    $ventas = $this->reporteModel->ventasPorMetodoPago($fechaInicio, $fechaFin, $tiendaId);
    // Agrupa por metodo_pago: total_ventas, total_monto
}
```

---

### `inventario()` — Inventario actual

```php
public function inventario(): void
{
    // GET: tienda_id (sin período — snapshot del estado actual)
    $inventario = $this->reporteModel->inventarioPorTienda($tiendaId);
    // Con estado_stock: 'Bajo' / 'Alto' / 'Normal'
}
```

---

### `stockBajo()` — Stock bajo

```php
public function stockBajo(): void
{
    // GET: tienda_id
    $productos = $this->reporteModel->productosStockBajo($tiendaId);
    // WHERE cantidad <= cantidad_minima, orden por cantidad ASC (los más críticos primero)
}
```

---

### `movimientosInventario()` — Historial de movimientos

```php
public function movimientosInventario(): void
{
    // GET: fecha_inicio, fecha_fin, tienda_id
    $movimientos = $this->reporteModel->movimientosInventario($fechaInicio, $fechaFin, $tiendaId);
    // Incluye: tipo (entrada/salida/ajuste), motivo, ref_tipo (venta/devolucion)
}
```

---

### `movimientosCaja()` — Movimientos de caja

```php
public function movimientosCaja(): void
{
    // GET: fecha_inicio, fecha_fin, tienda_id
    $movimientos = $this->reporteModel->movimientosCaja($fechaInicio, $fechaFin, $tiendaId);
    // Incluye: tipo (apertura/ingreso/egreso/cierre), monto, descripcion, venta_id
}
```

---

## Modelo: `Reporte.php`

### Métodos públicos

| Método | Filtros | Descripción |
|---|---|---|
| `ventasPorPeriodo(inicio, fin, ?tiendaId)` | Período + tienda opcional | Agrupado por `DATE(v.fecha)`. Solo ventas `'completada'` |
| `ventasPorTienda(inicio, fin)` | Solo período | LEFT JOIN tiendas → todas las tiendas incluso sin ventas |
| `productosMasVendidos(inicio, fin, ?tiendaId, limite)` | Período + tienda + límite | ORDER BY `cantidad_vendida DESC LIMIT X` |
| `ventasPorMetodoPago(inicio, fin, ?tiendaId)` | Período + tienda | JOIN con `pagos` (estado='aprobado') y `metodos_pago` |
| `inventarioPorTienda(?tiendaId)` | Solo tienda | Snapshot actual con `estado_stock` calculado |
| `productosStockBajo(?tiendaId)` | Solo tienda | `WHERE cantidad <= cantidad_minima` |
| `movimientosInventario(inicio, fin, ?tiendaId)` | Período + tienda | JOIN: movimientos → inventario → tienda + producto |
| `movimientosCaja(inicio, fin, ?tiendaId)` | Período + tienda | JOIN: cajas_movimientos → cajas → tiendas |
| `resumenGeneral(?tiendaId)` | Solo tienda | Usado por dashboard: ventas hoy, ventas mes, stock bajo, total productos |

### Métodos privados (solo usados por `resumenGeneral`)

| Método | Descripción |
|---|---|
| `obtenerTotalVentas(inicio, fin, ?tiendaId)` | Retorna `{total_ventas, total_ingresos}` del período |
| `contarProductos(?tiendaId)` | Sin tienda: `COUNT(*)` en productos. Con tienda: `COUNT(DISTINCT inventario.producto_id)` |

---

## Detalle de consultas SQL clave

### `ventasPorPeriodo()` — construcción dinámica

```sql
SELECT DATE(v.fecha) AS fecha,
       COUNT(v.id)   AS total_ventas,
       SUM(v.subtotal) AS subtotal,
       SUM(v.descuento) AS descuento,
       SUM(v.impuesto)  AS impuesto,
       SUM(v.total)     AS total
FROM ventas v
WHERE v.deleted_at IS NULL
  AND v.estado = 'completada'
  AND DATE(v.fecha) BETWEEN :fecha_inicio AND :fecha_fin
  [AND v.tienda_id = :tienda_id]    ← solo si tiendaId != null
GROUP BY DATE(v.fecha)
ORDER BY fecha DESC
```

### `inventarioPorTienda()` — estado_stock calculado en SQL

```sql
CASE
    WHEN i.cantidad <= i.cantidad_minima THEN 'Bajo'
    WHEN i.cantidad_maxima IS NOT NULL AND i.cantidad >= i.cantidad_maxima THEN 'Alto'
    ELSE 'Normal'
END AS estado_stock
```

### `ventasPorTienda()` — LEFT JOIN para incluir tiendas sin ventas

```sql
FROM tiendas t
LEFT JOIN ventas v ON v.tienda_id = t.id
    AND v.deleted_at IS NULL
    AND v.estado = 'completada'
    AND DATE(v.fecha) BETWEEN :fecha_inicio AND :fecha_fin
WHERE t.deleted_at IS NULL
GROUP BY t.id, t.nombre
ORDER BY total_ingresos DESC
```

### `resumenGeneral()` — usado por el dashboard

```php
public function resumenGeneral(?int $tiendaId = null): array
{
    $hoy       = date('Y-m-d');
    $inicioMes = date('Y-m-01');

    return [
        'ventas_hoy'           => $this->obtenerTotalVentas($hoy, $hoy, $tiendaId),
        'ventas_mes'           => $this->obtenerTotalVentas($inicioMes, $hoy, $tiendaId),
        'productos_stock_bajo' => count($this->productosStockBajo($tiendaId)),
        'total_productos'      => $this->contarProductos($tiendaId),
    ];
}
```

---

## Vistas del módulo Reportes

### `reportes/index.php`
- Panel con tarjetas/links a cada sub-reporte
- No muestra datos — es navegación
- **Acceso desde el sidebar:** botón "Reportes Generales" → `loadContent('reportes.index', true)` (visible para todos los roles con permiso `reportes.view`)

### `reportes/ventas.php`
- Tabla con columnas: fecha, nº ventas, subtotal, descuento, impuesto, total
- Formulario de filtro: fecha_inicio, fecha_fin, tienda_id (select)
- Totales al pie

### `reportes/ventas_por_tienda.php`
- Tabla comparativa: tienda, nº ventas, total ingresos
- Solo accessible a Superadmin (sin filtro de tienda)

### `reportes/productos_mas_vendidos.php`
- Tabla: producto, código de barras, cantidad vendida, total ventas, nº de ventas
- Filtro: fecha_inicio, fecha_fin, tienda_id, límite (5/10/20/50)

### `reportes/ventas_por_metodo_pago.php`
- Tabla: método de pago, nº de ventas, monto total
- Filtro: fecha_inicio, fecha_fin, tienda_id

### `reportes/inventario.php`
- Tabla: tienda, producto, código de barras, stock actual, mínimo, máximo, unidad, estado_stock
- Stock Bajo en rojo, Alto en amarillo, Normal en verde
- Filtro: tienda_id

### `reportes/stock_bajo.php`
- Tabla: tienda, producto, código, stock actual, mínimo, unidad
- Ordenado por stock ascendente (más crítico primero)
- Filtro: tienda_id

### `reportes/movimientos_inventario.php`
- Tabla: fecha, tienda, producto, tipo, cantidad, motivo, referencia (venta/devolución)
- Filtro: fecha_inicio, fecha_fin, tienda_id

### `reportes/movimientos_caja.php`
- Tabla: fecha, tienda, caja, tipo, monto, descripción, venta ID
- Filtro: fecha_inicio, fecha_fin, tienda_id

---

## Resumen de filtros por reporte

| Reporte | fecha_inicio | fecha_fin | tienda_id | limite |
|---|---|---|---|---|
| Ventas por período | ✅ (default mes) | ✅ | ✅ | ❌ |
| Ventas por tienda | ✅ | ✅ | ❌ (ignora) | ❌ |
| Productos más vendidos | ✅ | ✅ | ✅ | ✅ (default 10) |
| Ventas por método de pago | ✅ | ✅ | ✅ | ❌ |
| Inventario actual | ❌ | ❌ | ✅ | ❌ |
| Stock bajo | ❌ | ❌ | ✅ | ❌ |
| Movimientos inventario | ✅ | ✅ | ✅ | ❌ |
| Movimientos caja | ✅ | ✅ | ✅ | ❌ |

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
