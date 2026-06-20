# 💰 Ventas — Vendedor

> **Permisos:** `ventas.view · ventas.create`
> **Referencia base:** `Superadministrador/09_Ventas.md` · `Administrador_Tienda/04_Ventas.md`

---

## Diferencia clave vs Administrador de Tienda

El Vendedor **no tiene `ventas.cancel`**. No puede anular ventas. Si necesita revertir una venta, debe pedírselo a su Administrador de Tienda o Supervisor.

| Capacidad | Vendedor | Admin Tienda |
|---|---|---|
| Ver ventas | ✅ Su tienda | ✅ Su tienda |
| Crear ventas | ✅ | ✅ |
| Anular ventas | ❌ | ✅ |

---

## Restricción de tienda

Todas las ventas están filtradas por la tienda del Vendedor. El `tienda_id` se toma de la sesión, no del formulario.

| Acción | Comportamiento |
|---|---|
| `ventas.index` | Solo ventas de su tienda |
| `ventas.create` | Tienda fijada desde sesión |
| `ventas.store` | `crearVenta()` usa el `tienda_id` de sesión |
| `ventas.show` | Solo puede ver ventas de su tienda |
| `ventas.anular` | 403 — no tiene el permiso |

---

## Prerrequisito de caja

Debe existir una caja abierta en su tienda antes de registrar cualquier venta. Si no la hay, `crearVenta()` lanza:
```
RuntimeException: 'No hay una caja abierta para esta tienda.'
```

---

## Productos y clientes disponibles en el POS

- **Productos:** solo los que tienen precio en `tiendas_productos` para su tienda.
- **Clientes:** solo los asociados a su tienda (INNER JOIN `tiendas_clientes`).

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
