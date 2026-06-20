# 📋 Casos de Uso — Mega_Uni_Store v3

> **Notación:** Actor | Precondición | Flujo principal | Flujo alternativo | Postcondición

---

## CU-01: Iniciar Sesión

| Campo | Detalle |
|---|---|
| **Actor principal** | Cualquier usuario (todos los roles) |
| **Precondición** | El usuario NO está autenticado |
| **Ruta** | `?route=login` → `?route=login.post` |

**Flujo principal:**
1. El usuario accede a `index.php` (ruta `login`)
2. El sistema muestra el formulario de inicio de sesión
3. El usuario ingresa su email y contraseña
4. El sistema valida el formato del email
5. El sistema busca el usuario activo en la BD por email
6. El sistema verifica la contraseña con `password_verify()`
7. El sistema crea la sesión con datos del usuario, rol principal y permisos
8. El sistema redirige al dashboard correspondiente según el rol

**Flujo alternativo:**
- Email/contraseña vacíos → error "El correo y la contraseña son obligatorios"
- Email con formato inválido → error de formato
- Usuario no encontrado → error "Credenciales incorrectas" (sin revelar cuál es incorrecto)
- Contraseña incorrecta → error "Credenciales incorrectas"
- Usuario inactivo → no se encuentra en la búsqueda `buscarActivoPorEmail()`

**Postcondición:** `$_SESSION['auth']` contiene usuario_id, nombre, email, rol_principal, roles y permisos

---

## CU-02: Cerrar Sesión

| Campo | Detalle |
|---|---|
| **Actor** | Cualquier usuario autenticado |
| **Precondición** | Usuario autenticado |
| **Ruta** | `?route=logout` |

**Flujo:** Destruye `$_SESSION`, redirige a `login`

---

## CU-03: Recuperar Contraseña

| Campo | Detalle |
|---|---|
| **Actor** | Cualquier usuario |
| **Ruta** | `password.request` → `password.request.post` → email → `password.reset` → `password.reset.post` |

**Flujo principal:**
1. Usuario solicita reset con su email
2. Sistema genera token único y registra en BD con expiración
3. Sistema envía email con enlace de reset (`Mailer.php`)
4. Usuario accede al enlace, ingresa nueva contraseña
5. Sistema valida el token y su expiración
6. Sistema actualiza el hash de la contraseña
7. Sistema invalida el token usado

**Flujo alternativo:**
- Email no registrado → se muestra mensaje genérico (no revela si existe)
- Token expirado → error "El enlace ha expirado"
- Token ya usado → error de validación

---

## CU-04: Registrar Nueva Tienda

| Campo | Detalle |
|---|---|
| **Actor** | Superadministrador |
| **Permiso** | `tiendas.create` |
| **Ruta** | `tiendas.create` → `tiendas.store` |

**Flujo principal:**
1. Actor accede al listado de tiendas (`tiendas.index`)
2. Hace clic en "Nueva Tienda" (abre modal o navega a formulario)
3. Completa: nombre, dirección, teléfono, estado
4. Sistema valida campos obligatorios
5. Sistema guarda la tienda en BD
6. Sistema muestra mensaje de éxito y recarga listado

**Flujo alternativo:**
- Nombre vacío → error de validación
- Petición modal → responde JSON `{ok: true, ruta: 'tiendas.index', mensaje: '...'}`

---

## CU-05: Registrar Usuario y Asignar Rol

| Campo | Detalle |
|---|---|
| **Actor** | Superadministrador / Administrador de Tienda |
| **Permisos** | `usuarios.create` + `usuarios.roles.assign` |
| **Ruta** | `usuarios.create` → `usuarios.store` → `usuarios.asignar_rol` → `usuarios.guardar_rol` |

**Flujo principal:**
1. Actor crea el usuario con nombre, email y contraseña inicial
2. Sistema hashea la contraseña con `password_hash()`
3. Sistema guarda el usuario activo en BD
4. Actor accede a "Asignar Rol" del usuario
5. Selecciona rol y opcionalmente la tienda asignada
6. Sistema registra el rol en `usuario_roles`

**Flujo alternativo:**
- Email duplicado → error "El email ya está registrado"
- Contraseña débil → validación de longitud mínima

---

## CU-06: Registrar Producto

| Campo | Detalle |
|---|---|
| **Actor** | Superadministrador / Administrador de Tienda |
| **Permiso** | `productos.create` |
| **Ruta** | `productos.create` → `productos.store` |

**Flujo principal:**
1. Actor abre formulario de nuevo producto (modal o página)
2. Completa: nombre, código de barras, categoría, unidad de medida, impuesto, precio de costo, precio de venta, estado
3. Sistema valida campos obligatorios y unicidad del código de barras
4. Sistema guarda el producto en BD

**Flujo alternativo:**
- Código de barras duplicado → error de unicidad
- Precio negativo → validación numérica

---

## CU-07: Registrar Entrada de Inventario

| Campo | Detalle |
|---|---|
| **Actor** | Bodeguero / Administrador de Tienda / Superadministrador |
| **Permiso** | `inventario.move` |
| **Ruta** | `inventario.create` → `inventario.store` |

**Flujo principal:**
1. Actor selecciona producto, tienda y cantidad a ingresar
2. Indica motivo (compra, ajuste, etc.)
3. Sistema registra el movimiento tipo `entrada` en `inventario_movimientos`
4. Sistema actualiza el stock actual en `inventario`

**Flujo alternativo:**
- Cantidad cero o negativa → error de validación
- Producto no registrado en esa tienda → sistema crea el registro con stock inicial

---

## CU-08: Registrar Movimiento de Inventario (Ajuste / Salida)

| Campo | Detalle |
|---|---|
| **Actor** | Bodeguero / Administrador |
| **Permiso** | `inventario.move` |
| **Ruta** | `inventario.movimiento` → `inventario.guardar_movimiento` |

**Flujo principal:**
1. Actor selecciona tipo: `entrada` / `salida` / `ajuste`
2. Selecciona producto, tienda y cantidad
3. Sistema registra el movimiento y actualiza el stock

**Flujo alternativo:**
- Salida mayor al stock disponible → error "Stock insuficiente"

---

## CU-09: Registrar Nueva Venta (POS)

| Campo | Detalle |
|---|---|
| **Actor** | Vendedor / Administrador de Tienda |
| **Permiso** | `ventas.create` |
| **Ruta** | `ventas.create` → `ventas.store` |

**Flujo principal:**
1. Vendedor abre la pantalla de nueva venta
2. Busca y agrega productos al carrito (con cantidad)
3. Opcionalmente selecciona un cliente
4. Opcionalmente aplica un cupón de descuento (`cupones.validar`)
5. Selecciona método de pago
6. Sistema calcula subtotal, descuentos, impuestos y total
7. Sistema registra la venta, sus detalles, el movimiento de caja y descuenta inventario
8. Sistema muestra comprobante de venta

**Flujo alternativo:**
- Stock insuficiente → error por producto
- Cupón inválido / expirado → error de validación
- Caja cerrada → no permite registrar ventas

---

## CU-10: Registrar Devolución

| Campo | Detalle |
|---|---|
| **Actor** | Vendedor / Supervisor / Administrador |
| **Permiso** | `ventas.cancel` |
| **Ruta** | `devoluciones.create` → `devoluciones.store` |

**Flujo principal:**
1. Actor busca la venta original por ID
2. Selecciona los productos a devolver y sus cantidades
3. Indica el motivo de la devolución
4. Sistema registra la devolución
5. Sistema reingresa el stock de los productos devueltos
6. Sistema registra el egreso en caja (si aplica reembolso)

**Flujo alternativo:**
- Venta ya devuelta → error de validación
- Cantidad a devolver mayor a la vendida → error

---

## CU-11: Apertura de Caja

| Campo | Detalle |
|---|---|
| **Actor** | Vendedor / Administrador |
| **Permiso** | `caja.manage` |
| **Ruta** | `caja.apertura` → `caja.abrir` |

**Flujo principal:**
1. Actor selecciona la caja a abrir
2. Ingresa el monto inicial en efectivo
3. Sistema verifica que la caja no esté ya abierta
4. Sistema registra la apertura con fecha/hora y usuario
5. Caja queda en estado `abierta`

**Flujo alternativo:**
- Caja ya abierta → error "La caja ya está en uso"
- Monto negativo → validación

---

## CU-12: Cierre de Caja

| Campo | Detalle |
|---|---|
| **Actor** | Vendedor / Administrador |
| **Permiso** | `caja.manage` |
| **Ruta** | `caja.cierre` → `caja.cerrar` |

**Flujo principal:**
1. Actor selecciona la caja abierta
2. Ingresa el monto real contado al cierre
3. Sistema calcula diferencia entre monto esperado y monto real
4. Sistema registra el cierre con fecha/hora, usuario y diferencia
5. Caja queda en estado `cerrada`

---

## CU-13: Registrar Movimiento Manual de Caja

| Campo | Detalle |
|---|---|
| **Actor** | Administrador / Vendedor |
| **Permiso** | `caja.manage` |
| **Ruta** | `caja.movimiento` → `caja.guardar_movimiento` |

**Flujo:** Actor registra un ingreso o egreso manual en la caja activa (gastos, retiros, etc.) con su descripción.

---

## CU-14: Aplicar Cupón de Descuento

| Campo | Detalle |
|---|---|
| **Actor** | Vendedor (durante una venta) |
| **Permiso** | `ventas.create` |
| **Ruta** | `cupones.validar` (POST) |

**Flujo principal:**
1. Vendedor ingresa el código del cupón en la pantalla de venta
2. Sistema valida el cupón: existe, está activo, no expiró, no superó el límite de usos
3. Sistema devuelve el tipo de descuento (porcentaje o monto fijo) y el valor
4. Sistema aplica el descuento al total de la venta

**Flujo alternativo:**
- Cupón no encontrado → JSON `{ok: false, error: 'Cupón inválido'}`
- Cupón expirado → error con fecha de expiración
- Límite de usos alcanzado → error

---

## CU-15: Consultar Reportes

| Campo | Detalle |
|---|---|
| **Actor** | Superadministrador / Administrador / Supervisor / Reportero |
| **Permiso** | Varía según el reporte |
| **Ruta** | `reportes.index` y sub-rutas |

**Flujo principal:**
1. Actor accede al módulo de reportes (menú lateral)
2. Selecciona el tipo de reporte
3. Configura filtros: rango de fechas, tienda
4. Sistema ejecuta la consulta SQL y muestra resultados
5. La tabla tiene paginación cliente (10 registros por página)

**Reportes disponibles:**
- Ventas por período (diario)
- Ventas por tienda (comparativo)
- Productos más vendidos (Top N)
- Ventas por método de pago (con % visual)
- Estado del inventario
- Productos con stock bajo (alerta)
- Movimientos de inventario
- Movimientos de caja

---

## CU-16: Ver Alertas de Stock Bajo

| Campo | Detalle |
|---|---|
| **Actor** | Bodeguero / Administrador / Superadministrador |
| **Permiso** | `inventario.alerts` |
| **Ruta** | `inventario.alertas` |

**Flujo:** Sistema consulta todos los productos donde `stock_actual <= stock_minimo` y los muestra organizados por tienda y déficit. Color rojo indica urgencia.

---

## CU-17: Gestionar Empleados

| Campo | Detalle |
|---|---|
| **Actor** | Nómina y RRHH / Administrador de Tienda / Superadministrador |
| **Permisos** | `empleados.view` / `empleados.manage` |
| **Ruta** | `empleados.*` |

**Flujo:** CRUD completo de empleados. Un empleado puede estar vinculado a un usuario del sistema.

---

## CU-18: Crear/Editar Categorías, Unidades e Impuestos

| Campo | Detalle |
|---|---|
| **Actor** | Superadministrador / Administrador de Tienda |
| **Permiso** | `productos.*` |
| **Ruta** | `categorias.*` / `unidades.*` / `impuestos.*` |

**Flujo:** Formulario vía modal. El sistema devuelve JSON `{ok: true/false}`. Al crear, los nuevos registros quedan disponibles inmediatamente en el formulario de productos.

---

## CU-19: Registrar Proveedor

| Campo | Detalle |
|---|---|
| **Actor** | Superadministrador / Administrador de Tienda |
| **Permiso** | `productos.*` |
| **Ruta** | `proveedores.*` |

**Flujo:** CRUD de proveedores. El proveedor puede estar activo/inactivo (`toggleEstado`). Se vincula a productos para trazabilidad de compras.

---

## CU-20: Setup Inicial del Sistema

| Campo | Detalle |
|---|---|
| **Actor** | Técnico / Desarrollador |
| **Permiso** | Ninguno (ruta sin protección) |
| **Ruta** | `setup` |

**Flujo:**
1. Técnico accede a `index.php?route=setup` solo durante la instalación inicial
2. Sistema crea todas las tablas de la BD
3. Sistema inserta datos iniciales (roles, permisos, usuario superadmin, tienda demo)
4. Sistema muestra confirmación

> ⚠️ **IMPORTANTE:** Esta ruta debe deshabilitarse en producción eliminando el `case 'setup'` del router o protegerla con una clave.

---

*Documento generado: mayo 2026 — Ángel Nicolás Abril*
