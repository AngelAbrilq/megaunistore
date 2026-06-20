# 🔍 Rol: Supervisor — Visión General

> **Nivel jerárquico:** 3
> **Scope:** Una sola tienda (`tienda_id` fijo en sesión)
> **Clave técnica:** `tiendaIdPermitida()` retorna `int` (nunca `null`)

---

## Descripción

El Supervisor supervisa la operación de ventas de su tienda. Puede ver y crear ventas, **y también anularlas** (a diferencia del Vendedor). Tiene acceso a reportes de lectura y puede ver alertas de stock, pero no puede mover inventario ni gestionar empleados o usuarios.

---

## Módulos accesibles

| Módulo | Permiso(s) | Alcance |
|---|---|---|
| Dashboard | `dashboard.view` | Solo datos de su tienda |
| Productos | `productos.view` | Solo lectura |
| Inventario | `inventario.view · inventario.alerts` | Ver stock y alertas — sin movimientos |
| Ventas | `ventas.view · ventas.create · ventas.cancel` | Completo — **puede anular** |
| Caja | `caja.view · caja.manage` | Apertura, cierre y movimientos de su tienda |
| Reportes | `reportes.view` | Lectura — sin exportar |

## Módulos NO accesibles

| Módulo | Razón |
|---|---|
| Inventario (mover) | No tiene `inventario.move` |
| Reportes (exportar) | No tiene `reportes.export` |
| Empleados, Usuarios | Sin permisos |
| Productos CRUD | Solo `productos.view` |
| Tiendas, Setup | Exclusivos de Superadmin |

---

## Diferencias clave vs otros roles

| Capacidad | Supervisor | Vendedor | Admin Tienda |
|---|---|---|---|
| Anular ventas | ✅ | ❌ | ✅ |
| Ver reportes | ✅ | ❌ | ✅ |
| Exportar reportes | ❌ | ❌ | ✅ |
| Mover inventario | ❌ | ❌ | ✅ |
| CRUD Productos | ❌ | ❌ | ✅ |
| Gestionar empleados | ❌ | ❌ | ✅ |

---

## Permisos completos del rol

```
dashboard.view
productos.view
inventario.view · inventario.alerts
ventas.view · ventas.create · ventas.cancel
caja.view · caja.manage
reportes.view
```

---

## Archivos de este rol

| Archivo | Módulo |
|---|---|
| `01_Dashboard.md` | Dashboard de su tienda |
| `02_Productos.md` | Catálogo — solo consulta |
| `03_Inventario.md` | Stock y alertas — sin movimientos |
| `04_Ventas.md` | POS completo con anulación |
| `05_Caja.md` | Operación de caja |
| `06_Reportes.md` | Reportes de solo lectura |

---

*Rol documentado: mayo 2026 — Ángel Nicolás Abril*
