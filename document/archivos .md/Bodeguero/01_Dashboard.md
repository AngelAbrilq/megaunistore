# 🏠 Dashboard — Bodeguero

> **Permiso:** `dashboard.view`
> **Referencia base:** `Superadministrador/01_Dashboard.md`

---

## Comportamiento

Llama a `Reporte::resumenGeneral($tiendaId)` con su `tienda_id` fijo. Los indicadores reflejan únicamente su tienda.

| Indicador | Valor mostrado |
|---|---|
| Ventas hoy | Su tienda (dato visible, aunque no puede vender) |
| Ventas del mes | Su tienda |
| Stock bajo | Solo inventario de su tienda |
| Total productos | Solo los que tienen inventario en su tienda |

---

## Acceso rápido desde el dashboard

El Bodeguero ve principalmente los accesos relacionados con inventario. Todos usan SPA:

| Acceso rápido | Ruta SPA | Permiso |
|---|---|---|
| Alertas de Stock | `inventario.alertas` | `inventario.alerts` |
| Ver Inventario | `inventario.index` | `inventario.view` |

No ve: Nueva Venta, Abrir/Cerrar Caja, Reportes, Empleados, Cupones.

---

## Gráficas del dashboard

El dashboard incluye las mismas gráficas que otros roles, pero filtradas a su tienda:

- **Ventas últimos 7 días** (gráfica de línea) — solo su tienda
- **Top 5 productos más vendidos** (barras horizontal) — solo su tienda

> Estas gráficas muestran datos de ventas que el Bodeguero no genera (no puede vender). Son informativos para que conozca qué productos rotan más y anticipe las necesidades de inventario.

**Estado vacío:** si no hay ventas en el período, se muestra el mensaje "Sin ventas en los últimos 7 días" y "Sin datos de productos". Chart.js está cargado globalmente en `dashboard_layout.php` — no por vista.

---

## Widgets de KPI

```
┌─────────────────┬─────────────────┐
│  Ventas HOY     │  Ventas MES     │
│  (su tienda)    │  (su tienda)    │
├─────────────────┼─────────────────┤
│  Stock Bajo     │  Total Productos│
│  (su tienda)    │  (su tienda)    │
└─────────────────┴─────────────────┘
```

El widget "Stock Bajo" es el más relevante para el Bodeguero — muestra el número de productos en alerta. Al hacer clic en él navega directamente a `inventario.alertas`.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
