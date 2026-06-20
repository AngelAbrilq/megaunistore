# 💰 Ventas — Supervisor

> **Permisos:** `ventas.view · ventas.create · ventas.cancel`
> **Referencia base:** `Administrador_Tienda/04_Ventas.md`

---

## Acceso completo de ventas (igual que Admin Tienda)

El Supervisor tiene los mismos permisos de ventas que el Administrador de Tienda: puede ver, crear **y anular** ventas de su tienda. Esta es la diferencia principal respecto al Vendedor.

> **Nota UI:** el botón "Ver" en `ventas/index.php` abre el comprobante en **modal** mediante `openModal()`, no como navegación SPA. Esto permite revisar el detalle de la venta sin perder el listado.

| Capacidad | Supervisor | Vendedor |
|---|---|---|
| Ver ventas de su tienda | ✅ | ✅ |
| Crear ventas | ✅ | ✅ |
| Anular ventas | ✅ | ❌ |

---

## Restricción de tienda

Todas las operaciones están filtradas a su tienda. El `tienda_id` se toma de la sesión.

---

## Prerrequisito de caja y reglas de anulación

Idénticos a los del Administrador de Tienda. Ver `Administrador_Tienda/04_Ventas.md` para el detalle completo.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
