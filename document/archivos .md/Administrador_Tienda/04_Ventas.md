# 💰 Ventas — Administrador de Tienda

> **Permisos:** `ventas.view · ventas.create · ventas.cancel`
> **Referencia base:** `Superadministrador/09_Ventas.md`

---

## Restricción de tienda

Todas las ventas están filtradas por la tienda del Admin. El campo `tienda_id` de la venta se toma de la sesión, no del formulario.

> **Nota UI:** el botón "Ver" en `ventas/index.php` abre el comprobante en **modal** mediante `openModal('index.php?route=ventas.show&id=X&ajax=1')`, no como navegación SPA. Esto permite revisar el detalle sin perder el listado de ventas.

| Acción | Comportamiento |
|---|---|
| `ventas.index` | Solo ventas donde `ventas.tienda_id = su_tienda` |
| `ventas.create` | La tienda se fija automáticamente desde la sesión |
| `ventas.store` | `crearVenta()` usa el `tienda_id` de sesión |
| `ventas.show` | Solo puede ver ventas de su tienda |
| `ventas.anular` | Solo puede anular ventas de su tienda |

---

## Nueva venta (`create`)

El formulario de nueva venta se precarga con:
- `productosPorTienda[su_tienda]` — Solo productos con precio en `tiendas_productos` para su tienda
- `clientesPorTienda[su_tienda]` — Solo clientes asociados a su tienda

No aparecen productos ni clientes de otras tiendas.

---

## Prerrequisito de caja

Igual que para cualquier rol: **debe haber una caja abierta en su tienda** antes de registrar una venta. Si no la hay, `crearVenta()` lanza:
```
RuntimeException: 'No hay una caja abierta para esta tienda.'
```

---

## Anulación (`cancel`)

El Admin de Tienda tiene `ventas.cancel`, por lo que puede anular ventas. Las reglas son las mismas que para el Superadmin:
- La venta no debe estar ya anulada
- La caja debe seguir abierta (para registrar el egreso de reversión)
- La anulación revierte inventario y decrementa el cupón si había uno

---

## Diferencia respecto a Vendedor

| Capacidad | Admin Tienda | Vendedor |
|---|---|---|
| Ver ventas | ✅ Todas de su tienda | ✅ Solo las que él registró (o todas, según config) |
| Crear ventas | ✅ | ✅ |
| Anular ventas | ✅ `ventas.cancel` | ❌ No tiene el permiso |

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
