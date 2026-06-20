# 📊 Inventario — Supervisor

> **Permisos:** `inventario.view · inventario.alerts`
> **Referencia base:** `Superadministrador/08_Inventario.md`

---

## Ver y alertas, sin movimientos

El Supervisor puede consultar el stock y ver las alertas de su tienda, pero **no puede registrar movimientos** (entradas, salidas, ajustes).

| Ruta | Permiso requerido | Supervisor |
|---|---|---|
| `inventario.index` | `inventario.view` | ✅ Solo su tienda |
| `inventario.alertas` | `inventario.alerts` | ✅ Solo su tienda |
| `inventario.create` | `inventario.move` | ❌ |
| `inventario.store` | `inventario.move` | ❌ |
| `inventario.movimiento` | `inventario.move` | ❌ |
| `inventario.movimientos` | `inventario.move` | ❌ |

---

## Uso principal

El Supervisor consulta el inventario para tomar decisiones de supervisión: verificar si hay stock suficiente antes de autorizar una venta grande, o revisar las alertas para escalarlas al Bodeguero o al Administrador de Tienda.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
