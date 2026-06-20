# ⚙️ Dashboard — Sistema

> **Permiso:** `dashboard.view` (rol: `Sistema`)
> **Ruta:** `dashboard.sistema`

---

## Descripción del rol

El rol **Sistema** es un actor lógico automatizado — no corresponde a un usuario humano. Está diseñado para ser usado por scripts de automatización, validaciones de integridad, alertas programadas y respaldos internos que necesitan autenticarse en el sistema con permisos controlados.

```
Tipo:    Actor no-humano (bot / daemon / script)
Scope:   Global (tienda_id = null en sesión)
Nivel:   Igual o superior a Superadmin en jerarquía técnica (solo lectura de todo)
```

---

## Ruta y renderizado

```php
// web.php:
case 'dashboard.sistema':
    $authController->requerirRol(['Sistema']);
    if (!$isAjax) {
        require layout/dashboard_layout.php;
    } else {
        require views/dashboard/sistema.php;
    }
```

---

## Widgets del dashboard

```
┌─────────────────┬──────────────────────┬─────────────────┐
│  ⚙️ Rol activo  │  🤖 Tipo              │  🔒 Acceso      │
│  Sistema        │  Actor automatizado   │  Solo lectura   │
└─────────────────┴──────────────────────┴─────────────────┘
```

---

## Accesos rápidos (vista del dashboard)

| Ícono | Acción | Ruta |
|---|---|---|
| 🏪 | Estado de tiendas | `tiendas.index` |
| 📊 | Inventario global | `inventario.index` |
| 🚨 | Alertas de stock | `inventario.alertas` |

Estos accesos reflejan las tareas más comunes de un actor de tipo Sistema: monitorear el estado operativo de las tiendas y reaccionar a alertas de inventario.

---

## Propósito de diseño

El rol Sistema existe para:

1. **Automatizaciones futuras** — scripts cron que necesiten leer datos sin credenciales de usuario real
2. **Validaciones programadas** — chequeo nocturno de integridad de inventario vs. ventas
3. **Respaldos controlados** — lectura de datos para backup sin acceso administrativo completo
4. **Webhooks entrantes** — procesamiento de notificaciones externas que requieren contexto de sesión

---

## Estado actual

El rol Sistema está **implementado estructuralmente** (rutas, dashboard, sesión funcional), pero **no se usa en producción actualmente**. No hay scripts automatizados conectados. Es una base lista para expansión futura.

---

## Restricciones de seguridad

- No puede crear, modificar ni eliminar registros desde la UI
- No tiene permisos de escritura en la matriz base de `rol_permisos`
- Si se asigna a un usuario humano por error, ese usuario solo verá el dashboard de Sistema (sin módulos operativos)

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
