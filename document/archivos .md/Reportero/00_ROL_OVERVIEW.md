# 📊 Rol: Reportero — Visión General

> **Nivel jerárquico:** 5
> **Scope:** Global o por tienda según configuración — depende de `tiendaIdPermitida()`
> **Nota:** Si el Reportero está asignado a una tienda, ve solo esa tienda. Si es global (sin tienda_id), ve todas.

---

## Descripción

El Reportero es el rol de análisis. Tiene acceso de **solo lectura** a ventas, inventario y reportes completos (incluida exportación). No puede registrar ni modificar ningún dato operativo: no vende, no mueve inventario, no gestiona caja ni empleados.

---

## Módulos accesibles

| Módulo | Permiso(s) | Alcance |
|---|---|---|
| Dashboard | `dashboard.view` | Según scope (tienda o global) |
| Ventas | `ventas.view` | Solo lectura — sin crear ni anular |
| Inventario | `inventario.view` | Solo lectura — sin movimientos ni alertas |
| Reportes | `reportes.view · reportes.export` | Lectura y exportación |

## Módulos NO accesibles

| Módulo | Razón |
|---|---|
| Ventas (crear/anular) | Solo `ventas.view` |
| Caja | Sin permisos de caja |
| Inventario (mover/alertas) | Solo `inventario.view` |
| Empleados, Usuarios | Sin permisos |
| Productos CRUD | Sin permiso `productos.view` |
| Tiendas, Setup | Exclusivos de Superadmin |

---

## Permisos completos del rol

```
dashboard.view
ventas.view
inventario.view
reportes.view · reportes.export
```

---

## Archivos de este rol

| Archivo | Módulo |
|---|---|
| `01_Dashboard.md` | Dashboard según scope |
| `02_Ventas.md` | Historial — solo lectura |
| `03_Inventario.md` | Stock — solo lectura |
| `04_Reportes.md` | Reportes completos con exportación |

---

*Rol documentado: mayo 2026 — Ángel Nicolás Abril*
