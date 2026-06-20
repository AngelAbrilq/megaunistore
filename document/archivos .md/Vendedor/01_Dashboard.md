# 🏠 Dashboard — Vendedor

> **Permiso:** `dashboard.view`
> **Referencia base:** `Superadministrador/01_Dashboard.md`

---

## Comportamiento

Igual que el Administrador de Tienda: llama a `Reporte::resumenGeneral($tiendaId)` con su `tienda_id` fijo. Todos los indicadores reflejan **únicamente su tienda**.

| Indicador | Valor mostrado |
|---|---|
| Ventas hoy | Solo las de su tienda |
| Ventas del mes | Solo las de su tienda |
| Stock bajo | Solo inventario de su tienda |
| Total productos | Solo los que tienen inventario en su tienda |

---

## Acceso rápido desde el dashboard

Botones visibles para el Vendedor:

- → Nueva Venta (`ventas.create`)
- → Abrir/Cerrar Caja (`caja.index`)

No ve: Gestionar Productos, Mover Inventario, Alertas, Reportes, Empleados, Setup.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
