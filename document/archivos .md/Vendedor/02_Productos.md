# 📦 Productos — Vendedor

> **Permiso:** `productos.view` (solo lectura)
> **Referencia base:** `Superadministrador/07_Productos.md`

---

## Solo lectura

El Vendedor puede consultar el catálogo de productos pero **no puede crear, editar ni eliminar**. El router bloquea cualquier intento de acceder a rutas CRUD con 403:

| Ruta | Permiso requerido | Vendedor |
|---|---|---|
| `productos.index` | `productos.view` | ✅ |
| `productos.create` | `productos.create` | ❌ |
| `productos.store` | `productos.create` | ❌ |
| `productos.edit` | `productos.update` | ❌ |
| `productos.update` | `productos.update` | ❌ |
| `productos.destroy` | `productos.delete` | ❌ |

---

## Uso principal

El Vendedor consulta `productos.index` para verificar **precios** y **categorías** antes de registrar una venta. La tabla `productos` es global, por lo que ve todos los productos del sistema, pero al crear una venta solo aparecen los que tienen precio en `tiendas_productos` para su tienda.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
