# 🏠 Dashboard — Reportero

> **Permiso:** `dashboard.view`
> **Ruta:** `?route=dashboard.reportero`
> **Vista:** `resources/views/dashboard/reportero.php`
> **Referencia base:** `Superadministrador/01_Dashboard.md`

---

## Scope según asignación de tienda

`Reporte::resumenGeneral($tiendaId)` recibe `null` si el Reportero es global, o el `int` de su tienda si está asignado a una:

| Situación | `tiendaIdPermitida()` | Datos del dashboard |
|---|---|---|
| Reportero sin tienda asignada | `null` | Todas las tiendas |
| Reportero con tienda asignada | `int` | Solo su tienda |

---

## Acceso rápido desde el dashboard

El dashboard muestra 8 accesos directos a reportes mediante tarjetas SPA:

| Icono | Título | Ruta |
|---|---|---|
| 📈 | Reportes generales | `reportes.index` |
| 💰 | Reporte de ventas | `reportes.ventas` |
| 🏪 | Ventas por tienda | `reportes.ventas_por_tienda` |
| 🏆 | Productos más vendidos | `reportes.productos_mas_vendidos` |
| 💳 | Ventas por método de pago | `reportes.ventas_por_metodo_pago` |
| 📦 | Inventario | `reportes.inventario` |
| 🚨 | Stock bajo | `reportes.stock_bajo` |
| 💵 | Movimientos de caja | `reportes.movimientos_caja` |

Todas las tarjetas usan **navegación SPA** (`onclick="loadContent('ruta', true)"`), no `<a href>` — esto evita recargar la página completa y mantiene la sesión en el shell.

No ve: Nueva Venta, Caja (gestión), Inventario (mover), Empleados, Setup.

---

## Implementación de la vista

```php
// reportero.php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { return; }

// Cada acción usa onclick="loadContent(route, true)" — navegación SPA interna
foreach ($actions as $action):
?>
<div class="db-action" onclick="loadContent('<?= htmlspecialchars($action['route']) ?>', true)">
    ...
</div>
```

> **Corrección aplicada (mayo 2026):** Las tarjetas originalmente usaban `<a href="index.php?route=...">` causando recargas completas de página. Se corrigieron a `<div onclick="loadContent(...)">` para mantener la experiencia SPA.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
