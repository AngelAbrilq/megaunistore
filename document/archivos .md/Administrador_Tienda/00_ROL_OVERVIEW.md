# 🏪 Rol: Administrador de Tienda — Visión General

> **Nivel jerárquico:** 2 (por debajo de Superadministrador)
> **Scope:** Una sola tienda (`tienda_id` fijo en `$_SESSION['auth']['rol_principal']['tienda_id']`)
> **Clave técnica:** `tiendaIdPermitida()` retorna `int` (nunca `null`)

---

## Descripción

El Administrador de Tienda gestiona **todos los aspectos operativos de su tienda asignada**: productos, inventario, ventas, caja, empleados, clientes, cupones y reportes. No puede ver ni afectar datos de otras tiendas.

A nivel de código, **usa exactamente los mismos controladores y modelos que el Superadministrador**. La restricción no está en el código del módulo sino en `tiendaIdPermitida()`: cuando retorna un `int`, todos los `listar()`, `registrar()` y `validarAccesoATienda()` aplican el filtro automáticamente.

---

## Módulos accesibles

| Módulo | Permiso(s) | Diferencia vs Superadmin |
|---|---|---|
| Dashboard | `dashboard.view` | Solo datos de su tienda |
| Productos | `productos.view/create/update/delete` | Ve productos globales; puede crearlos para su tienda |
| Inventario | `inventario.view/move/alerts` | Solo stock de su tienda |
| Ventas | `ventas.view/create/cancel` | Solo ventas de su tienda |
| Caja | `caja.view/manage` | Solo cajas de su tienda |
| Reportes | `reportes.view/export` | Solo datos de su tienda |
| Empleados | `empleados.view/manage` | Solo empleados de su tienda |
| Clientes | `clientes.view/manage` | Ve clientes de su tienda + globales |
| Cupones | `cupones.view/manage` | Ve sus cupones + cupones globales |
| Devoluciones | `devoluciones.view/manage` | Solo devoluciones de su tienda |
| Usuarios | `usuarios.view` | Solo lectura — no puede crear ni gestionar |

## Módulos NO accesibles

| Módulo | Razón |
|---|---|
| Tiendas (CRUD) | No tiene `tiendas.create/update/delete/toggle` |
| Usuarios (CRUD) | Solo tiene `usuarios.view`, no `usuarios.create/update/delete` |
| Setup | Exclusivo del Superadministrador / acceso por clave |
| Categorías, Unidades de Medida, Impuestos | Sin permisos en la matriz base |

---

## Clave técnica: `tiendaIdPermitida()`

```php
// En todos los controladores:
$tiendaIdPermitida = $this->tiendaIdPermitida();
// → Superadmin: null   (sin restricción)
// → Admin Tienda: 5    (solo tienda con ID 5)

// Efecto en cada módulo:
$items = $modelo->listar($tiendaIdPermitida);
// → Si null:  SELECT ... (sin WHERE tienda_id)
// → Si int:   SELECT ... WHERE tienda_id = 5
```

---

## Permisos completos del rol

```
dashboard.view
productos.view · productos.create · productos.update · productos.delete
inventario.view · inventario.move · inventario.alerts
ventas.view · ventas.create · ventas.cancel
caja.view · caja.manage
reportes.view · reportes.export
empleados.view · empleados.manage
usuarios.view
```

> Los módulos de clientes, cupones y devoluciones usan los permisos de ventas/caja — no tienen permisos propios en la matriz base.

---

## Archivos de este rol

| Archivo | Módulo |
|---|---|
| `01_Dashboard.md` | Dashboard filtrado por tienda |
| `02_Productos.md` | CRUD completo, precios por tienda |
| `03_Inventario.md` | Stock, movimientos y alertas |
| `04_Ventas.md` | POS, anulación |
| `05_Caja.md` | Apertura, cierre, movimientos |
| `06_Reportes.md` | Todos los reportes de su tienda |
| `07_Empleados.md` | Alta y gestión de empleados |
| `08_Clientes.md` | Clientes de la tienda |
| `09_Cupones.md` | Cupones propios + globales |
| `10_Devoluciones.md` | Procesar devoluciones |
| `11_Usuarios.md` | Solo lectura — sin CRUD |

---

*Rol documentado: mayo 2026 — Ángel Nicolás Abril*
