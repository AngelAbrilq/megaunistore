# 🏠 Dashboard — Administrador de Tienda

> **Permiso:** `dashboard.view`
> **Referencia base:** `Superadministrador/01_Dashboard.md`

---

## Diferencias vs Superadmin

El dashboard del Administrador de Tienda llama a `Reporte::resumenGeneral($tiendaId)` con su `tienda_id` fijo, en lugar de `null`. Esto significa que **todos los indicadores reflejan únicamente su tienda**.

| Indicador | Superadmin | Admin Tienda |
|---|---|---|
| Ventas hoy | Todas las tiendas | Solo su tienda |
| Ventas del mes | Todas las tiendas | Solo su tienda |
| Productos con stock bajo | Global | Solo inventario de su tienda |
| Total de productos | Todos en BD | Solo los que tienen inventario en su tienda |

---

## Widgets visibles

```
┌─────────────────┬─────────────────┐
│  Ventas HOY     │  Ventas MES     │
│  (su tienda)    │  (su tienda)    │
├─────────────────┼─────────────────┤
│  Stock Bajo     │  Total Productos│
│  (su tienda)    │  (su tienda)    │
└─────────────────┴─────────────────┘
```

---

## Acceso rápido desde el dashboard

Botones de acceso rápido que el Admin de Tienda ve en su dashboard. Todos usan navegación SPA (`onclick="loadContent(..., true)"`):

- → Nueva Venta (`ventas.create`)
- → Abrir/Cerrar Caja (`caja.index`)
- → Alertas de Stock (`inventario.alertas`)
- → Ver Reportes (`reportes.index`)

No ve: "Gestionar Tiendas", "Gestionar Usuarios (CRUD)", "Setup".

---

## Gráficas del dashboard

El dashboard incluye dos gráficas:
- **Ventas últimos 7 días** (línea) — de su tienda
- **Top 5 productos más vendidos** (barras horizontal) — de su tienda

Si no hay datos en el período se muestra un estado vacío con icono y texto "Sin ventas en los últimos 7 días" / "Sin datos de productos". Chart.js se carga globalmente en el layout, no por vista.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
