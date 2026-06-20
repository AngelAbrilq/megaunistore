# 👑 Rol: Superadministrador — Visión General

> **Nivel jerárquico:** 1 (máximo del sistema)
> **Scope:** Global — todas las tiendas, sin restricción
> **Clave técnica:** `tiendaIdPermitida()` retorna `null` → sin filtro de tienda

---

## Descripción

El Superadministrador tiene acceso irrestricto a **todos los módulos, todas las tiendas y todas las operaciones** del sistema. Es el único rol que puede crear y configurar tiendas, gestionar usuarios con CRUD completo, asignar roles, y acceder al módulo de Setup del sistema.

A nivel de código, la distinción clave es que `tiendaIdPermitida()` retorna `null` para este rol. Todos los modelos interpretan `null` como "sin filtro de tienda":

```php
// En cualquier modelo:
$items = $modelo->listar(null);
// → SELECT * FROM tabla  (sin WHERE tienda_id)
```

---

## Módulos accesibles

| Módulo | Permiso(s) principales | Particularidad |
|---|---|---|
| Dashboard | `dashboard.view` | Datos de todas las tiendas en tiempo real |
| Tiendas | `tiendas.view/create/update/delete/toggle` | Único rol con CRUD de tiendas |
| Usuarios | `usuarios.view/create/update/delete/roles.assign` | CRUD completo + asignación de roles |
| Categorías | `categorias.view/create/update/delete` | Catálogo global |
| Unidades de Medida | `unidades.view/create/update/delete` | Catálogo global |
| Impuestos | `impuestos.view/create/update/delete` | Catálogo global |
| Productos | `productos.view/create/update/delete` | Catálogo global + precios por tienda |
| Inventario | `inventario.view/move/alerts` | Todas las tiendas |
| Ventas | `ventas.view/create/cancel` | Todas las tiendas |
| Caja | `caja.view/manage` | Todas las tiendas |
| Clientes | `clientes.view/manage` | Todos los clientes |
| Empleados | `empleados.view/manage` | Todos los empleados |
| Proveedores | `proveedores.view/manage` | Catálogo global |
| Cupones | `cupones.view/manage` | Cupones globales y por tienda |
| Devoluciones | `devoluciones.view/manage` | Todas las tiendas |
| Reportes | `reportes.view/export` | Todas las tiendas + exportación |
| Password Reset | — (gestión interna) | Único que puede aprobar solicitudes sin restricción de tienda |
| Setup | Acceso por ruta protegida | Configuración de sistema, roles base, permisos |

---

## Clave técnica: `tiendaIdPermitida()` = `null`

```php
// ControllerHelper::tiendaIdPermitida():
$tiendaId = $_SESSION['auth']['rol_principal']['tienda_id'] ?? null;
return $tiendaId !== null ? (int) $tiendaId : null;

// Para Superadmin: tienda_id en sesión = null → retorna null
```

Efecto en cascada:
- `listar(null)` → sin WHERE tienda_id
- `validarAccesoATienda()` → acepta cualquier tienda (no rechaza)
- `requerirPermiso($accion, null)` → verifica permiso global, no por tienda

---

## Sesión del Superadministrador

```php
$_SESSION['auth']['rol_principal'] = [
    'rol_nombre' => 'Superadministrador',
    'rol_nivel'  => 1,
    'tienda_id'  => null,  // ← sin tienda asignada
];
```

---

## Restricciones del rol

Aunque tiene acceso máximo, hay convenciones de seguridad:

1. **No puede impersonar otros usuarios** — no existe función de "actuar como" en el sistema
2. **Las eliminaciones son suaves (`deleted_at`)** — no se borran registros de la BD directamente desde UI
3. **Flujo D de contraseñas** — puede cambiar la contraseña de cualquier usuario con `password.admin.set`, pero queda log implícito en `updated_at`

---

## Archivos de documentación de este rol

| # | Archivo | Módulo |
|---|---|---|
| 00 | `00_ROL_OVERVIEW.md` | Este archivo |
| 01 | `01_Dashboard.md` | KPIs globales + gráficas multi-tienda |
| 02 | `02_Tiendas.md` | CRUD de tiendas |
| 03 | `03_Usuarios.md` | CRUD + asignación de roles |
| 04 | `04_Categorias.md` | Catálogo de categorías |
| 05 | `05_Unidades_Medida.md` | Unidades de medida |
| 06 | `06_Impuestos.md` | Tipos de impuesto |
| 07 | `07_Productos.md` | Catálogo + precios por tienda |
| 08 | `08_Inventario.md` | Stock + movimientos + alertas |
| 09 | `09_Ventas.md` | POS + historial + anulación |
| 10 | `10_Caja.md` | Cajas, turnos, movimientos |
| 11 | `11_Clientes.md` | Base de clientes global |
| 12 | `12_Empleados.md` | Empleados de todas las tiendas |
| 13 | `13_Proveedores.md` | Catálogo de proveedores |
| 14 | `14_Cupones.md` | Cupones globales y por tienda |
| 15 | `15_Devoluciones.md` | Devoluciones de todas las tiendas |
| 16 | `16_Reportes.md` | Reportes completos + exportación |
| 17 | `17_Setup.md` | Configuración del sistema |
| 18 | `18_Password_Reset.md` | Flujos A, C y D de contraseñas |

---

*Rol documentado: mayo 2026 — Ángel Nicolás Abril*
