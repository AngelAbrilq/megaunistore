# 📈 Reportes — Administrador de Tienda

> **Permisos:** `reportes.view · reportes.export`
> **Referencia base:** `Superadministrador/16_Reportes.md`

---

## Patrón de filtro por tienda

En casi todos los sub-reportes, el controlador sobreescribe el `tienda_id` del GET con el de la sesión si el rol tiene tienda asignada:

```php
$tiendaId = (int) ($_GET['tienda_id'] ?? 0);
$tiendaIdPermitida = $this->tiendaIdPermitida();

if ($tiendaIdPermitida !== null) {
    $tiendaId = $tiendaIdPermitida; // ← GET ignorado, se impone la tienda de sesión
}
```

El select de tiendas en el filtro también se reduce a una sola opción (la suya).

---

## Sub-reportes disponibles y su comportamiento

| Sub-reporte | Ruta | Filtra por tienda |
|---|---|---|
| Ventas (detalle) | `reportes.ventas` | ✅ Forzado a su tienda |
| Ventas por Tienda | `reportes.ventasPorTienda` | ❌ **Sin filtro** (ver nota abajo) |
| Productos más vendidos | `reportes.productosMasVendidos` | ✅ Forzado a su tienda |
| Ventas por método de pago | `reportes.ventasPorMetodoPago` | ✅ Forzado a su tienda |
| Inventario | `reportes.inventario` | ✅ Forzado a su tienda |
| Stock bajo | `reportes.stockBajo` | ✅ Forzado a su tienda |
| Movimientos de inventario | `reportes.movimientosInventario` | ✅ Forzado a su tienda |
| Movimientos de caja | `reportes.movimientosCaja` | ✅ Forzado a su tienda |

---

## ⚠️ Excepción: `ventasPorTienda()`

`reportes.ventasPorTienda` **no aplica el filtro de `tiendaIdPermitida`**. El método del controlador no tiene la lógica de override:

```php
public function ventasPorTienda(): void
{
    $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
    $fechaFin    = $_GET['fecha_fin']    ?? date('Y-m-d');

    $ventas = $this->reporteModel->ventasPorTienda($fechaInicio, $fechaFin);
    // ← Sin tiendaIdPermitida(), muestra TODAS las tiendas
    ...
}
```

Este sub-reporte muestra el comparativo entre todas las tiendas del sistema. Si se quiere restringirlo al Admin de Tienda, se debería agregar el mismo patrón de override que tienen los demás sub-reportes.

---

## Dashboard y `resumenGeneral()`

El dashboard del Admin de Tienda llama a `Reporte::resumenGeneral($tiendaId)` con su `tienda_id` fijo. Ver `01_Dashboard.md` para el detalle de indicadores.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
