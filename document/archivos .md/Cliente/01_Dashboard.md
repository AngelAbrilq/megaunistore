# 🛒 Dashboard — Cliente

> **Permiso:** `dashboard.view` (rol: `Cliente`)
> **Ruta:** `dashboard.cliente`

---

## Descripción del rol

El rol **Cliente** se asigna automáticamente a cualquier usuario que se registra desde el formulario público (`register.php`). Es el rol de menor jerarquía en la pirámide del sistema.

```php
// AuthController::registrar():
$rolCliente = $this->rolModel->buscarPorNombre('Cliente');
$this->rolModel->asignarRolAUsuario($usuarioId, $rolCliente['id'], null);
// tienda_id = null → sin tienda asignada (scope global)
```

---

## Ruta y renderizado

```php
// web.php:
case 'dashboard.cliente':
    $authController->requerirRol(['Cliente']);
    if (!$isAjax) {
        require layout/dashboard_layout.php;
    } else {
        require views/dashboard/cliente.php;
    }
```

---

## Widgets del dashboard

```
┌──────────────┬─────────────────┬──────────────┐
│  👤 Rol       │  📧 Correo       │  🎫 Estado   │
│  Cliente      │  (su email)      │  Activo      │
└──────────────┴─────────────────┴──────────────┘
```

---

## Accesos rápidos

| Ícono | Título | Ruta |
|---|---|---|
| 🛒 | Mis compras | `ventas.index` |
| 🎫 | Mis cupones | `cupones.index` |

> **Nota:** Estos accesos rápidos apuntan a rutas que pueden requerir permisos adicionales (`ventas.view`, `cupones.view`). En la implementación actual, el rol Cliente accede a esas rutas si tiene los permisos asignados en la tabla `rol_permisos`. Si no los tiene, recibirá HTTP 403.

---

## Estado actual del rol

El rol Cliente está **preparado estructuralmente** (dashboard, vistas) pero sus módulos de autoservicio (historial de compras propio, perfil, cupones personales) están **pendientes de implementación completa**. Las vistas existen pero el flujo de "solo ver mis propias compras" no ha sido implementado a nivel de modelo.

---

## Lo que el Cliente NO puede hacer

- No puede ver ventas de otros clientes
- No puede acceder a ningún módulo administrativo
- No puede registrar movimientos de inventario
- No puede abrir ni cerrar caja
- No puede gestionar productos, tiendas ni usuarios

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
