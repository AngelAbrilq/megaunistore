# 🗺️ Rutas y Enrutamiento — Mega_Uni_Store v3

> **Archivo fuente:** `backend/routes/web.php`
> **Punto de entrada:** `backend/index.php`
> **Mecanismo:** `$_GET['route']` → `switch()` → Controlador → Vista

---

## Cómo funciona el router

```
URL: http://localhost/Mega_Uni_Store_v3/backend/index.php?route=ventas.index
                                                                    ↑
                                              $route = $_GET['route'] ?? 'login'
```

Todas las peticiones llegan a **`index.php`**, que carga `web.php`.
El router lee `$route` del query string y ejecuta el `case` correspondiente.

**Petición SPA:** agrega `&ajax=1` → la vista devuelve solo el fragmento HTML.

---

## 1. Autenticación y Contraseñas

| Ruta (`?route=`) | Método | Permiso requerido | Controlador → Método |
|---|---|---|---|
| `login` *(default)* | GET | Ninguno | `AuthController→mostrarLogin()` |
| `login.post` | POST | Ninguno | `AuthController→login()` |
| `register` | GET | Ninguno | `AuthController→mostrarRegistro()` |
| `register.post` | POST | Ninguno | `AuthController→registrar()` |
| `logout` | GET | Autenticado | `AuthController→logout()` |
| `password.request` | GET | Ninguno | `PasswordController→mostrarFormularioReset()` |
| `password.request.post` | POST | Ninguno | `PasswordController→enviarLinkReset()` |
| `password.reset` | GET | Ninguno | `PasswordController→mostrarFormularioNuevoPassword()` |
| `password.reset.post` | POST | Ninguno | `PasswordController→aplicarNuevoPassword()` |
| `password.change` | GET | Autenticado | `PasswordController→mostrarFormularioCambio()` |
| `password.change.post` | POST | Autenticado | `PasswordController→procesarCambio()` |
| `password.requests` | GET | `Superadministrador` | `PasswordController→listarSolicitudes()` |
| `password.approve` | POST | `Superadministrador` | `PasswordController→aprobarSolicitud()` |
| `password.deny` | POST | `Superadministrador` | `PasswordController→rechazarSolicitud()` |
| `password.admin.set` | POST | `Superadministrador` | `PasswordController→adminSetPassword()` |

---

## 2. Dashboards (por rol)

| Ruta | Rol requerido | Vista |
|---|---|---|
| `dashboard` | Autenticado (redirección automática) | Redirige al dashboard del rol activo |
| `dashboard.superadmin` | `Superadministrador` | `dashboard/superadmin.php` |
| `dashboard.admin_tienda` | `Administrador de Tienda` | `dashboard/admin_tienda.php` |
| `dashboard.supervisor` | `Supervisor` | `dashboard/supervisor.php` |
| `dashboard.vendedor` | `Vendedor` | `dashboard/vendedor.php` |
| `dashboard.bodeguero` | `Bodeguero` | `dashboard/bodeguero.php` |
| `dashboard.reportero` | `Reportero` | `dashboard/reportero.php` |
| `dashboard.nomina` | `Nómina y RRHH` | `dashboard/nomina.php` |
| `dashboard.cliente` | `Cliente` | `dashboard/cliente.php` |
| `dashboard.sistema` | `Sistema` | `dashboard/sistema.php` |

> Todos los dashboards cargan el shell SPA si no es ajax, o devuelven el fragmento si `ajax=1`.

---

## 3. Módulo Tiendas

| Ruta | Método | Permiso | Controlador → Método |
|---|---|---|---|
| `tiendas.index` | GET | `tiendas.view` | `TiendaController→index()` |
| `tiendas.create` | GET | `tiendas.create` | `TiendaController→create()` |
| `tiendas.store` | POST | `tiendas.create` | `TiendaController→store()` |
| `tiendas.edit` | GET | `tiendas.update` | `TiendaController→edit()` |
| `tiendas.update` | POST | `tiendas.update` | `TiendaController→update()` |
| `tiendas.toggle` | POST | `tiendas.toggle` | `TiendaController→toggleEstado()` |
| `tiendas.destroy` | POST | `tiendas.delete` | `TiendaController→destroy()` |

---

## 4. Módulo Usuarios

| Ruta | Método | Permiso | Controlador → Método |
|---|---|---|---|
| `usuarios.index` | GET | `usuarios.view` | `UsuarioController→index()` |
| `usuarios.create` | GET | `usuarios.create` | `UsuarioController→create()` |
| `usuarios.store` | POST | `usuarios.create` | `UsuarioController→store()` |
| `usuarios.edit` | GET | `usuarios.update` | `UsuarioController→edit()` |
| `usuarios.update` | POST | `usuarios.update` | `UsuarioController→update()` |
| `usuarios.asignar_rol` | GET | `usuarios.roles.assign` | `UsuarioController→asignarRol()` |
| `usuarios.guardar_rol` | POST | `usuarios.roles.assign` | `UsuarioController→guardarRol()` |
| `usuarios.toggle` | POST | `usuarios.toggle` | `UsuarioController→toggleEstado()` |
| `usuarios.destroy` | POST | `usuarios.delete` | `UsuarioController→destroy()` |

---

## 5. Módulo Categorías

| Ruta | Método | Permiso | Controlador → Método |
|---|---|---|---|
| `categorias.index` | GET | `productos.view` | `CategoriaController→index()` |
| `categorias.create` | GET | `productos.create` | `CategoriaController→create()` |
| `categorias.store` | POST | `productos.create` | `CategoriaController→store()` |
| `categorias.edit` | GET | `productos.update` | `CategoriaController→edit()` |
| `categorias.update` | POST | `productos.update` | `CategoriaController→update()` |
| `categorias.toggle` | POST | `productos.update` | `CategoriaController→toggleEstado()` |
| `categorias.destroy` | POST | `productos.delete` | `CategoriaController→destroy()` |

---

## 6. Módulo Unidades de Medida

| Ruta | Método | Permiso | Controlador → Método |
|---|---|---|---|
| `unidades.index` | GET | `productos.view` | `UnidadMedidaController→index()` |
| `unidades.create` | GET | `productos.create` | `UnidadMedidaController→create()` |
| `unidades.store` | POST | `productos.create` | `UnidadMedidaController→store()` |
| `unidades.edit` | GET | `productos.update` | `UnidadMedidaController→edit()` |
| `unidades.update` | POST | `productos.update` | `UnidadMedidaController→update()` |
| `unidades.destroy` | POST | `productos.delete` | `UnidadMedidaController→destroy()` |

---

## 7. Módulo Impuestos

| Ruta | Método | Permiso | Controlador → Método |
|---|---|---|---|
| `impuestos.index` | GET | `productos.view` | `ImpuestoController→index()` |
| `impuestos.create` | GET | `productos.create` | `ImpuestoController→create()` |
| `impuestos.store` | POST | `productos.create` | `ImpuestoController→store()` |
| `impuestos.edit` | GET | `productos.update` | `ImpuestoController→edit()` |
| `impuestos.update` | POST | `productos.update` | `ImpuestoController→update()` |
| `impuestos.toggle` | POST | `productos.update` | `ImpuestoController→toggleEstado()` |
| `impuestos.destroy` | POST | `productos.delete` | `ImpuestoController→destroy()` |

---

## 8. Módulo Productos

| Ruta | Método | Permiso | Controlador → Método |
|---|---|---|---|
| `productos.index` | GET | `productos.view` | `ProductoController→index()` |
| `productos.create` | GET | `productos.create` | `ProductoController→create()` |
| `productos.store` | POST | `productos.create` | `ProductoController→store()` |
| `productos.edit` | GET | `productos.update` | `ProductoController→edit()` |
| `productos.update` | POST | `productos.update` | `ProductoController→update()` |
| `productos.toggle` | POST | `productos.update` | `ProductoController→toggleEstado()` |
| `productos.destroy` | POST | `productos.delete` | `ProductoController→destroy()` |

---

## 9. Módulo Inventario

| Ruta | Método | Permiso | Controlador → Método |
|---|---|---|---|
| `inventario.index` | GET | `inventario.view` | `InventarioController→index()` |
| `inventario.create` | GET | `inventario.move` | `InventarioController→create()` |
| `inventario.store` | POST | `inventario.move` | `InventarioController→store()` |
| `inventario.movimiento` | GET | `inventario.move` | `InventarioController→movimiento()` |
| `inventario.guardar_movimiento` | POST | `inventario.move` | `InventarioController→guardarMovimiento()` |
| `inventario.movimientos` | GET | `inventario.view` | `InventarioController→movimientos()` |
| `inventario.alertas` | GET | `inventario.alerts` | `InventarioController→alertas()` |

---

## 10. Módulo Ventas

| Ruta | Método | Permiso | Controlador → Método |
|---|---|---|---|
| `ventas.index` | GET | `ventas.view` | `VentaController→index()` |
| `ventas.create` | GET | `ventas.create` | `VentaController→create()` |
| `ventas.store` | POST | `ventas.create` | `VentaController→store()` |
| `ventas.show` | GET | `ventas.view` | `VentaController→show()` |
| `ventas.anular` | POST | `ventas.cancel` | `VentaController→anular()` |

---

## 11. Módulo Caja

| Ruta | Método | Permiso | Controlador → Método |
|---|---|---|---|
| `caja.index` | GET | `caja.view` | `CajaController→index()` |
| `caja.create` | GET | `caja.manage` | `CajaController→create()` |
| `caja.store` | POST | `caja.manage` | `CajaController→store()` |
| `caja.apertura` | GET | `caja.manage` | `CajaController→apertura()` |
| `caja.abrir` | POST | `caja.manage` | `CajaController→abrir()` |
| `caja.cierre` | GET | `caja.manage` | `CajaController→cierre()` |
| `caja.cerrar` | POST | `caja.manage` | `CajaController→cerrar()` |
| `caja.movimiento` | GET | `caja.manage` | `CajaController→movimiento()` |
| `caja.guardar_movimiento` | POST | `caja.manage` | `CajaController→guardarMovimiento()` |
| `caja.movimientos` | GET | `caja.view` | `CajaController→movimientos()` |

---

## 12. Módulo Clientes

| Ruta | Método | Permiso | Controlador → Método |
|---|---|---|---|
| `clientes.index` | GET | `ventas.view` | `ClienteController→index()` |
| `clientes.create` | GET | `ventas.create` | `ClienteController→create()` |
| `clientes.store` | POST | `ventas.create` | `ClienteController→store()` |
| `clientes.edit` | GET | `ventas.create` | `ClienteController→edit()` |
| `clientes.update` | POST | `ventas.create` | `ClienteController→update()` |
| `clientes.destroy` | POST | `ventas.cancel` | `ClienteController→destroy()` |

---

## 13. Módulo Empleados

| Ruta | Método | Permiso | Controlador → Método |
|---|---|---|---|
| `empleados.index` | GET | `empleados.view` | `EmpleadoController→index()` |
| `empleados.create` | GET | `empleados.manage` | `EmpleadoController→create()` |
| `empleados.store` | POST | `empleados.manage` | `EmpleadoController→store()` |
| `empleados.edit` | GET | `empleados.manage` | `EmpleadoController→edit()` |
| `empleados.update` | POST | `empleados.manage` | `EmpleadoController→update()` |
| `empleados.destroy` | POST | `empleados.manage` | `EmpleadoController→destroy()` |

---

## 14. Módulo Proveedores

| Ruta | Método | Permiso | Controlador → Método |
|---|---|---|---|
| `proveedores.index` | GET | `productos.view` | `ProveedorController→index()` |
| `proveedores.create` | GET | `productos.create` | `ProveedorController→create()` |
| `proveedores.store` | POST | `productos.create` | `ProveedorController→store()` |
| `proveedores.edit` | GET | `productos.update` | `ProveedorController→edit()` |
| `proveedores.update` | POST | `productos.update` | `ProveedorController→update()` |
| `proveedores.toggle` | POST | `productos.update` | `ProveedorController→toggleEstado()` |
| `proveedores.destroy` | POST | `productos.delete` | `ProveedorController→destroy()` |

---

## 15. Módulo Cupones

| Ruta | Método | Permiso | Controlador → Método |
|---|---|---|---|
| `cupones.index` | GET | `ventas.view` | `CuponController→index()` |
| `cupones.create` | GET | `ventas.create` | `CuponController→create()` |
| `cupones.store` | POST | `ventas.create` | `CuponController→store()` |
| `cupones.edit` | GET | `ventas.create` | `CuponController→edit()` |
| `cupones.update` | POST | `ventas.create` | `CuponController→update()` |
| `cupones.destroy` | POST | `ventas.cancel` | `CuponController→destroy()` |
| `cupones.validar` | POST | `ventas.create` | `CuponController→validar()` |

---

## 16. Módulo Devoluciones

| Ruta | Método | Permiso | Controlador → Método |
|---|---|---|---|
| `devoluciones.index` | GET | `ventas.view` | `DevolucionController→index()` |
| `devoluciones.create` | GET | `ventas.cancel` | `DevolucionController→create()` |
| `devoluciones.store` | POST | `ventas.cancel` | `DevolucionController→store()` |
| `devoluciones.show` | GET | `ventas.view` | `DevolucionController→show()` |

---

## 17. Módulo Reportes

| Ruta | Método | Permiso | Vista |
|---|---|---|---|
| `reportes.index` | GET | Autenticado | Menú de reportes |
| `reportes.ventas` | GET | Autenticado | Ventas por período |
| `reportes.ventas_por_tienda` | GET | Autenticado | Ventas por tienda |
| `reportes.productos_mas_vendidos` | GET | Autenticado | Top productos |
| `reportes.ventas_por_metodo_pago` | GET | Autenticado | Métodos de pago |
| `reportes.inventario` | GET | `inventario.view` | Estado del inventario |
| `reportes.stock_bajo` | GET | `inventario.alerts` | Productos stock bajo |
| `reportes.movimientos_inventario` | GET | `inventario.view` | Movimientos de inventario |
| `reportes.movimientos_caja` | GET | `caja.view` | Movimientos de caja |

---

## 18. Setup

| Ruta | Método | Permiso | Controlador → Método |
|---|---|---|---|
| `setup` | GET | Ninguno (solo en instalación inicial) | `SetupController→ejecutar()` |

> ⚠️ La ruta `setup` crea las tablas y datos iniciales de la BD. Debe estar protegida o eliminada en producción.

---

## Resumen de permisos por grupo

| Grupo de permisos | Qué controla |
|---|---|
| `tiendas.*` | CRUD completo de tiendas + toggle de estado |
| `usuarios.*` | CRUD de usuarios + asignación de roles |
| `productos.*` | Productos, categorías, unidades, impuestos, proveedores |
| `inventario.*` | Ver stock, registrar movimientos, ver alertas |
| `ventas.*` | Ventas, clientes, cupones, devoluciones |
| `caja.*` | Apertura/cierre de caja y movimientos |
| `empleados.*` | Ver y gestionar empleados |

---

## Convención de nomenclatura de rutas

```
módulo.acción

Ejemplos:
  ventas.index           → Lista de ventas
  ventas.create          → Formulario de nueva venta
  ventas.store           → Procesar nueva venta (POST)
  ventas.show            → Ver detalle de venta
  ventas.anular          → Anular venta (POST)
  
  caja.apertura          → Formulario apertura de caja
  caja.abrir             → Procesar apertura (POST)
```

---

*Documento generado: mayo 2026 — Ángel Nicolás Abril*
