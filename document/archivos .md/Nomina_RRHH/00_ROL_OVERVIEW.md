# 💼 Rol: Nómina y RRHH — Visión General

> **Nivel jerárquico:** 4
> **Scope:** Global (sin restricción de tienda en la mayoría de módulos)

---

## Descripción

El rol de Nómina y RRHH gestiona el talento humano de la empresa: empleados en todas las tiendas y el módulo de nómina. Tiene acceso de lectura a reportes generales. No tiene acceso a ventas, caja ni inventario.

---

## Módulos accesibles

| Módulo | Permiso(s) | Estado |
|---|---|---|
| Dashboard | `dashboard.view` | ✅ Implementado |
| Empleados | `empleados.view · empleados.manage` | ✅ Implementado |
| Reportes | `reportes.view` | ✅ Implementado |
| Nómina | `nomina.view · nomina.manage` | ⚠️ **Pendiente de implementación** |

## Módulos NO accesibles

| Módulo | Razón |
|---|---|
| Ventas, Caja | Sin permisos operativos |
| Inventario | Sin permisos |
| Productos (CRUD) | Sin permisos |
| Tiendas, Setup, Usuarios | Sin permisos |

---

## Estado del módulo Nómina

Los permisos `nomina.view` y `nomina.manage` están definidos en la matriz base (`Permiso::permisosBase()`), pero **no existe aún un `NominaController` ni rutas de nómina** en `web.php`. Solo existe una vista placeholder (`dashboard/nomina.php`) accesible desde la ruta `dashboard.nomina`. El módulo está reservado para desarrollo futuro.

---

## Permisos completos del rol

```
dashboard.view
empleados.view · empleados.manage
nomina.view · nomina.manage
reportes.view
```

---

## Archivos de este rol

| Archivo | Módulo |
|---|---|
| `01_Dashboard.md` | Dashboard general |
| `02_Empleados.md` | Gestión de empleados (global) |
| `03_Reportes.md` | Reportes de lectura |
| `04_Nomina.md` | Módulo pendiente |

---

*Rol documentado: mayo 2026 — Ángel Nicolás Abril*
