# 🛒 Rol: Vendedor — Visión General

> **Nivel jerárquico:** 4
> **Scope:** Una sola tienda (`tienda_id` fijo en sesión)
> **Clave técnica:** `tiendaIdPermitida()` retorna `int` (nunca `null`)

---

## Descripción

El Vendedor es el rol operativo de punto de venta. Su función principal es **registrar ventas** en su tienda asignada. Tiene acceso de solo lectura a productos e inventario (para consultar stock y precios antes de vender), y puede operar la caja (apertura, cierre y movimientos manuales). No puede anular ventas ni acceder a reportes.

---

## Módulos accesibles

| Módulo | Permiso(s) | Alcance |
|---|---|---|
| Dashboard | `dashboard.view` | Solo datos de su tienda |
| Productos | `productos.view` | Solo lectura — sin CRUD |
| Inventario | `inventario.view` | Solo lectura — sin movimientos ni alertas |
| Ventas | `ventas.view · ventas.create` | Ver y crear — **no puede anular** |
| Caja | `caja.view · caja.manage` | Apertura, cierre y movimientos manuales de su tienda |

## Módulos NO accesibles

| Módulo | Razón |
|---|---|
| Inventario (mover/alertas) | No tiene `inventario.move` ni `inventario.alerts` |
| Ventas (anular) | No tiene `ventas.cancel` |
| Reportes | Sin permisos de reportes |
| Empleados, Usuarios | Sin permisos |
| Clientes, Cupones, Devoluciones | Sin permisos propios |
| Productos CRUD | Solo `productos.view` |
| Tiendas, Setup | Exclusivos de otros roles |

---

## Permisos completos del rol

```
dashboard.view
productos.view
inventario.view
ventas.view · ventas.create
caja.view · caja.manage
```

---

## Archivos de este rol

| Archivo | Módulo |
|---|---|
| `01_Dashboard.md` | Dashboard de su tienda |
| `02_Productos.md` | Catálogo — solo consulta |
| `03_Inventario.md` | Stock — solo consulta |
| `04_Ventas.md` | POS — crear ventas, sin anulación |
| `05_Caja.md` | Operación de caja |

---

*Rol documentado: mayo 2026 — Ángel Nicolás Abril*
