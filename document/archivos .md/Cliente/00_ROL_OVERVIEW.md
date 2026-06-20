# 🛍️ Rol: Cliente — Visión General

> **Nivel jerárquico:** 6 (usuario final)
> **Scope:** Propio (`own`) — solo puede ver y gestionar sus propios datos

---

## Descripción

El Cliente es el rol del usuario final del sistema de e-commerce o portal de clientes. Sus permisos son de naturaleza `own` (propios): solo puede gestionar sus propios pedidos y perfil, ver el catálogo y dejar feedback. No tiene acceso a ningún módulo administrativo.

---

## Módulos definidos en permisos

| Módulo | Permiso(s) | Estado |
|---|---|---|
| Dashboard | `dashboard.view` | ✅ Vista placeholder (`dashboard/cliente.php`) |
| Catálogo | `catalogo.view` | ⚠️ Pendiente de implementación |
| Pedidos propios | `pedidos.own.manage` | ⚠️ Pendiente de implementación |
| Perfil propio | `perfil.own.manage` | ⚠️ Pendiente de implementación |
| Feedback | `feedback.create` | ⚠️ Pendiente de implementación |

---

## Estado actual

Solo existe una vista de dashboard placeholder (`dashboard/cliente.php`) accesible desde `dashboard.cliente`. Los módulos de catálogo, pedidos, perfil y feedback **no tienen controladores ni rutas en `web.php`**. Son el alcance del portal de clientes que está pendiente de desarrollo.

---

## Permisos completos del rol

```
dashboard.view
catalogo.view
pedidos.own.manage
perfil.own.manage
feedback.create
```

---

*Rol documentado: mayo 2026 — Ángel Nicolás Abril*
