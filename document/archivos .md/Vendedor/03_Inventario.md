# 📊 Inventario — Vendedor

> **Permiso:** `inventario.view` (solo lectura)
> **Referencia base:** `Superadministrador/08_Inventario.md`

---

## Solo lectura

El Vendedor puede consultar el stock de su tienda pero **no puede registrar movimientos ni ver alertas**.

| Ruta | Permiso requerido | Vendedor |
|---|---|---|
| `inventario.index` | `inventario.view` | ✅ Solo su tienda |
| `inventario.create` | `inventario.move` | ❌ |
| `inventario.store` | `inventario.move` | ❌ |
| `inventario.movimiento` | `inventario.move` | ❌ |
| `inventario.movimientos` | `inventario.move` | ❌ |
| `inventario.alertas` | `inventario.alerts` | ❌ |

---

## Uso principal

El Vendedor consulta `inventario.index` para verificar **disponibilidad de stock** antes de registrar una venta. El listado está filtrado por su tienda (`tiendaIdPermitida()` retorna `int`), así que solo ve el stock de su propia tienda.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
