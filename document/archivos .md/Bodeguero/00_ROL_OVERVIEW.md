# 📦 Rol: Bodeguero — Visión General

> **Nivel jerárquico:** 5
> **Scope:** Una sola tienda (`tienda_id` fijo en sesión)
> **Clave técnica:** `tiendaIdPermitida()` retorna `int` (nunca `null`)

---

## Descripción

El Bodeguero es el especialista de almacén. Su función es **gestionar el inventario físico** de su tienda: registrar entradas de mercancía, ajustar stock por conteo físico, registrar salidas por merma o pérdida, y monitorear alertas de stock bajo. No tiene acceso a ventas, caja ni reportes.

---

## Módulos accesibles

| Módulo | Permiso(s) | Alcance |
|---|---|---|
| Dashboard | `dashboard.view` | Solo datos de su tienda |
| Productos | `productos.view` | Solo lectura — sin CRUD |
| Inventario | `inventario.view · inventario.move · inventario.alerts` | Completo para su tienda |

## Módulos NO accesibles

| Módulo | Razón |
|---|---|
| Ventas | Sin permisos de ventas |
| Caja | Sin permisos de caja |
| Reportes | Sin permisos de reportes |
| Empleados, Usuarios | Sin permisos |
| Clientes, Cupones, Devoluciones | Sin permisos |
| Productos CRUD | Solo `productos.view` |

---

## Permisos completos del rol

```
dashboard.view
productos.view
inventario.view
inventario.move
inventario.alerts
```

---

## Archivos de este rol

| Archivo | Módulo |
|---|---|
| `01_Dashboard.md` | Dashboard de su tienda |
| `02_Productos.md` | Catálogo — solo consulta |
| `03_Inventario.md` | Stock completo: ver, mover y alertas |

---

*Rol documentado: mayo 2026 — Ángel Nicolás Abril*
