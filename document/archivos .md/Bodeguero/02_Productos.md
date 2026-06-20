# 📦 Productos — Bodeguero

> **Permiso:** `productos.view` (solo lectura)
> **Referencia base:** `Vendedor/02_Productos.md`

---

## Solo lectura

Idéntico al Vendedor: el Bodeguero puede consultar el catálogo global de productos para identificar referencias, categorías y unidades de medida al registrar movimientos de inventario. No puede crear, editar ni eliminar productos.

| Ruta | Vendedor | Bodeguero |
|---|---|---|
| `productos.index` | ✅ | ✅ |
| `productos.create/store/edit/update/destroy` | ❌ | ❌ |

---

## Uso principal

Consultar nombre, código de barras y unidad de medida de un producto antes de registrar una entrada o ajuste de inventario en `inventario.create`.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
