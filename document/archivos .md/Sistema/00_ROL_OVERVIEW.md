# ⚙️ Rol: Sistema — Visión General

> **Nivel jerárquico:** Técnico (no operativo)
> **Scope:** Global — acceso técnico al sistema, sin operaciones de negocio

---

## Descripción

El rol Sistema está diseñado para integraciones automatizadas, bots, scripts de mantenimiento o usuarios técnicos que necesitan acceso a funciones de infraestructura sin acceso a ventas, inventario ni datos de negocio. Sus permisos son puramente técnicos.

---

## Módulos definidos en permisos

| Módulo | Permiso(s) | Estado |
|---|---|---|
| Dashboard | `dashboard.view` | ✅ Vista placeholder (`dashboard/sistema.php`) |
| Notificaciones | `notificaciones.manage` | ⚠️ Pendiente de implementación |
| Backups | `backups.manage` | ⚠️ Pendiente de implementación |
| Alertas de inventario | `inventario.alerts` | ✅ Implementado |

---

## Estado actual

Solo existe una vista placeholder (`dashboard/sistema.php`) accesible desde `dashboard.sistema`. Los módulos de `notificaciones` y `backups` **no tienen controladores ni rutas en `web.php`**. Son funcionalidades de infraestructura pendientes de desarrollo.

El permiso `inventario.alerts` sí está implementado y le permite consultar alertas de stock bajo de todas las tiendas (sin `tienda_id` asignado, `tiendaIdPermitida()` retorna `null`).

---

## Permisos completos del rol

```
dashboard.view
notificaciones.manage
backups.manage
inventario.alerts
```

---

## Nota de seguridad

Este rol no debería asignarse a usuarios humanos en producción. Es para cuentas de servicio o integraciones técnicas. No tiene acceso a datos sensibles de ventas ni puede modificar inventario.

---

*Rol documentado: mayo 2026 — Ángel Nicolás Abril*
