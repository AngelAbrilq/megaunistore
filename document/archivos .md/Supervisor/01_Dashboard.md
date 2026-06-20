# 🏠 Dashboard — Supervisor

> **Permiso:** `dashboard.view`
> **Ruta:** `?route=dashboard.supervisor`
> **Vista:** `resources/views/dashboard/supervisor.php`
> **Referencia base:** `Administrador_Tienda/01_Dashboard.md`

---

## Comportamiento

Llama a `Reporte::resumenGeneral($tiendaId)` con su `tienda_id` fijo. Todos los indicadores y la gráfica son de su tienda.

---

## KPIs mostrados

Los mismos 4 KPIs que el Admin de Tienda (ventas del día, clientes atendidos, productos con alerta, caja activa), filtrados a su tienda.

---

## Gráfica de ventas

Muestra un gráfico de línea con ventas de los últimos 7 días de su tienda. Si no hay ventas en ese período se muestra un estado vacío con icono 📊 en lugar del canvas.

> **Chart.js se carga globalmente** en el `<head>` de `dashboard_layout.php`. La vista del supervisor **no incluye** `<script src="chart.js">` — solo el código `new Chart(...)` dentro de un bloque PHP condicional:
> ```php
> <?php if (!empty($labels7)): ?>
> <script>(function(){
>     new Chart(document.getElementById('chartVentas7'), { ... });
> })();</script>
> <?php endif; ?>
> ```

---

## Acceso rápido desde el dashboard

Las tarjetas usan navegación SPA (`onclick="loadContent(..., true)"`):

| Icono | Título | Ruta |
|---|---|---|
| 💰 | Ventas | `ventas.index` |
| 🔄 | Devoluciones | `devoluciones.index` |
| 📊 | Inventario | `inventario.index` |
| 💵 | Movimientos de caja | `caja.movimientos` |
| 👥 | Clientes | `clientes.index` |
| 📈 | Reportes | `reportes.ventas` |

No ve: Gestionar Productos (crear/editar), Mover Inventario (solo supervisar), Empleados, Setup.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
