# 🏠 Dashboard — Nómina y RRHH

> **Permiso:** `dashboard.view`
> **Ruta:** `?route=dashboard.nomina`
> **Vista:** `resources/views/dashboard/nomina.php`

---

## Comportamiento

El rol de Nómina no tiene `tienda_id` asignado en la mayoría de casos (es un rol global de RRHH). `resumenGeneral(null)` muestra indicadores de todas las tiendas.

---

## Acceso rápido desde el dashboard

Las tarjetas de acceso rápido usan **navegación SPA** (`onclick="loadContent(..., true)"`):

| Icono | Título | Ruta |
|---|---|---|
| 👥 | Empleados | `empleados.index` |
| 👤 | Usuarios del sistema | `usuarios.index` |
| 📈 | Reportes de ventas | `reportes.ventas` |

No ve: Nueva Venta, Caja, Inventario (mover), Setup.

> **Corrección aplicada (mayo 2026):** Las tarjetas originalmente usaban `<a href="index.php?route=...">` causando recargas completas. Se corrigieron a `<div onclick="loadContent(...)">` para mantener la experiencia SPA.

---

## Módulo Nómina

El módulo de nómina es actualmente un **placeholder** (`dashboard.nomina`). Los empleados se gestionan a través del módulo `empleados.index` que el rol de Nómina y RRHH tiene permiso para ver y editar.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
