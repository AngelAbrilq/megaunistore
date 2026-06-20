# 🏪 Mega_Uni_Store v3 — Visión General del Sistema

> **Versión documentada:** v3 (mayo 2026)
> **Autor:** Ángel Nicolás Abril
> **Stack:** PHP 8+ · MySQL · Vanilla JS · SPA-lite

---

## 1. ¿Qué es Mega_Uni_Store?

Sistema de gestión de punto de venta (POS) **multi-tienda** desarrollado en PHP puro sin frameworks.
Permite administrar ventas, inventario, caja, clientes, empleados y reportes desde una sola aplicación
con roles y permisos granulares.

---

## 2. Arquitectura General

```
Mega_Uni_Store_v3/
├── backend/                    ← Toda la lógica del servidor
│   ├── index.php               ← Punto de entrada único (Front Controller)
│   ├── routes/
│   │   └── web.php             ← Router principal (switch por ?route=)
│   ├── app/
│   │   ├── controllers/        ← Un controlador por módulo
│   │   ├── models/             ← Un modelo por entidad de BD
│   │   ├── Helpers/
│   │   │   └── ControllerHelper.php  ← Trait compartido por todos los controladores
│   │   └── services/
│   │       └── Mailer.php      ← Servicio de envío de correos
│   ├── resources/
│   │   ├── views/              ← Vistas PHP por módulo
│   │   │   ├── layout/         ← Shell SPA (dashboard_layout.php)
│   │   │   ├── auth/           ← login.php, register.php
│   │   │   ├── dashboard/      ← Un dashboard por rol
│   │   │   ├── productos/
│   │   │   ├── ventas/
│   │   │   ├── caja/
│   │   │   └── reportes/
│   │   └── errors/             ← 403.php, 404.php, 500.php
│   └── config/
│       └── database.php        ← Conexión PDO a MySQL
├── document/
│   ├── archivos .md/           ← Documentación técnica (este directorio)
│   └── Docmentos y archivos UML/ ← Documentos Word, PDF, SQL, diagramas
├── frontend/                   ← Assets estáticos (CSS, JS globales)
├── mobile/                     ← App móvil (proyecto aparte)
└── README.md
```

---

## 3. Patrón MVC + Front Controller

### Flujo de una petición

```
Navegador
  │
  ▼
backend/index.php          ← Inicia sesión, carga config, requiere web.php
  │
  ▼
backend/routes/web.php     ← Lee $_GET['route'], ejecuta switch()
  │                          Verifica autenticación y permisos
  ▼
XxxController->metodo()    ← Lógica de negocio
  │
  ▼
Modelo (PDO)               ← Consultas a MySQL
  │
  ▼
Vista PHP (.php)           ← Genera HTML / JSON
  │
  ▼
Respuesta al navegador
```

### Ejemplo concreto — ruta `ventas.index`

```php
// web.php
case 'ventas.index':
    $authController->requerirPermiso('ventas.view');   // 1. Verificar permiso
    $ventaController = new VentaController();
    $ventaController->index();                          // 2. Ejecutar acción
    break;

// VentaController.php
public function index(): void
{
    $ventas = $this->ventaModel->listar($tiendaIdPermitida);
    require __DIR__ . '/../../resources/views/ventas/index.php';
}
```

---

## 4. Sistema SPA-lite (Single Page Application ligera)

El frontend carga **una sola vez** el shell `dashboard_layout.php` y luego
actualiza el contenido central sin recargar la página completa.

### Cómo funciona

```
Usuario hace clic en "Productos" del sidebar
      │
      ▼
loadContent('productos.index', true)   ← Función global en JS del layout
      │
      ▼
fetch('index.php?route=productos.index&ajax=1')   ← Agrega &ajax=1
      │
      ▼
PHP detecta $isAjax = true
Vista devuelve SOLO el fragmento HTML (sin <html><body>)
      │
      ▼
document.getElementById('dynamicContent').innerHTML = fragmento
history.pushState({}, '', '?route=productos.index')
```

### Guard `$isAjax` en todas las vistas

Cada vista SPA-aware debe comenzar así:

```php
<?php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) {
    require __DIR__ . '/../layout/dashboard_layout.php';
    return;
}
?>
<!-- Solo el fragmento HTML va aquí -->
```

**Regla crítica:** ninguna vista SPA debe contener `</body>` ni `</html>`
porque cuando se inyectan en el DOM via `innerHTML`, el navegador mueve el
contenido fuera del DOM activo → página en blanco.

### Función `loadContent` con parámetros extra

```javascript
// Uso básico
loadContent('productos.index', true);

// Con parámetros extra (ej: filtros de reporte)
loadContent('reportes.ventas&fecha_inicio=2025-01-01&fecha_fin=2025-12-31', true);
// ↑ Se divide en el primer '&' para que no se codifique como %26
```

---

## 5. Sistema de Autenticación y Sesiones

### Estructura de `$_SESSION['auth']`

```php
$_SESSION['auth'] = [
    'usuario_id'   => 5,
    'nombre'       => 'Juan Pérez',
    'email'        => 'juan@ejemplo.com',
    'rol_principal' => [
        'rol'       => 'Vendedor',
        'tienda_id' => 3,           // null si es Superadministrador
    ],
    'roles'        => ['Vendedor', 'Bodeguero'],   // Todos los roles del usuario
    'permisos'     => [
        'ventas.view', 'ventas.create', 'caja.view', 'caja.manage', ...
    ],
];
```

### Métodos de AuthController

| Método | Descripción |
|---|---|
| `requerirAutenticacion()` | Redirige a login si no hay sesión |
| `requerirRol(['Vendedor'])` | 403 si el rol del usuario no coincide |
| `requerirPermiso('ventas.create')` | 403 si el usuario no tiene ese permiso |
| `estaAutenticado()` | Retorna `bool` |
| `redireccionarSegunRolPrincipal()` | Lleva al dashboard correcto según rol |

---

## 6. Sistema de Roles y Permisos

### Roles del sistema

| Rol | Descripción |
|---|---|
| `Superadministrador` | Acceso total a todo el sistema |
| `Administrador de Tienda` | Acceso completo filtrado a su tienda |
| `Supervisor` | Supervisión y reportes de su tienda |
| `Vendedor` | Ventas, clientes, caja (su turno) |
| `Bodeguero` | Inventario, productos, alertas de stock |
| `Reportero` | Solo acceso a reportes |
| `Nómina y RRHH` | Empleados y nómina |
| `Cliente` | Dashboard propio, sin acceso administrativo |
| `Sistema` | Rol técnico/automatizado |

### Permisos disponibles

```
tiendas.view / tiendas.create / tiendas.update / tiendas.toggle / tiendas.delete
usuarios.view / usuarios.create / usuarios.update / usuarios.toggle / usuarios.delete / usuarios.roles.assign
productos.view / productos.create / productos.update / productos.delete
inventario.view / inventario.move / inventario.alerts
ventas.view / ventas.create / ventas.cancel
caja.view / caja.manage
empleados.view / empleados.manage
```

### Filtro por tienda

Los roles que tienen `tienda_id` en sesión **solo ven datos de su tienda**.
Los controladores usan `$this->tiendaIdPermitida()` del `ControllerHelper`:

```php
private function tiendaIdPermitida(): ?int
{
    $tiendaId = $_SESSION['auth']['rol_principal']['tienda_id'] ?? null;
    return $tiendaId !== null ? (int) $tiendaId : null;
}
// Retorna null → Superadministrador (ve todo)
// Retorna int  → usuario de tienda (solo ve su tienda)
```

---

## 7. Trait `ControllerHelper`

Trait importado por **todos** los controladores. Centraliza las operaciones repetidas.

```php
// Métodos disponibles en cualquier controlador:

$this->redireccionar('index.php?route=ventas.index');
$this->guardarMensaje('success', 'Venta registrada.');
$this->guardarMensaje('error', 'Error al procesar.');
$this->generarCsrfToken();
$this->validarCsrfToken();
$this->usuarioIdActual();        // int
$this->tiendaIdPermitida();      // ?int
$this->validarAccesoATienda(3);
$this->esPeticionModal();        // bool
$this->jsonExito('ventas.index', 'Venta guardada.');
$this->jsonError('Error X', 'ventas.create');
$this->denegarAcceso('Sin permisos.');
```

---

## 8. Sistema de Modales (AJAX)

Los formularios de creación/edición se abren en un modal global sin salir del SPA.

### Flujo del modal

```
1. Usuario hace clic en "Nueva Categoría"
         │
         ▼
2. openModal('index.php?route=categorias.create&ajax=1')
         │
         ▼
3. fetch() trae el formulario como fragmento HTML
   El modal global muestra el formulario
         │
         ▼
4. Usuario llena y envía el formulario
   submitModalForm() — agrega header X-Modal-Request: 1
         │
         ▼
5. Controlador detecta: esPeticionModal() → true
   Responde con JSON: { ok: true, ruta: 'categorias.index', mensaje: '...' }
                 o  : { ok: false, error: 'Mensaje de error' }
         │
         ▼
6. JS procesa JSON:
   - ok: true  → cierra modal, muestra toast, recarga contenido
   - ok: false → muestra error dentro del modal sin cerrarlo
```

---

## 9. Sistema de Mensajes Flash

```php
// Controlador guarda el mensaje
$_SESSION['flash'] = ['type' => 'success', 'message' => 'Guardado correctamente.'];

// El layout lo muestra una sola vez al siguiente render
// y luego lo elimina de la sesión (flash = mostrar una vez)
```

Tipos: `success` (verde) · `error` (rojo) · `warning` (amarillo) · `info` (azul)

---

## 10. Protección CSRF

Todos los formularios POST incluyen un token CSRF:

```html
<!-- En la vista -->
<input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

<!-- El controlador genera y valida -->
$csrfToken = $this->generarCsrfToken();   // En GET (mostrar formulario)
$this->validarCsrfToken();                // En POST (procesar formulario)
```

Si el token es inválido → HTTP 419 + mensaje de error.

---

## 11. Conexión a Base de Datos

```php
// backend/config/database.php
// PDO con MySQL, charset UTF-8, modo error con excepciones

$pdo = new PDO(
    'mysql:host=localhost;dbname=mega_uni_store;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
```

Los modelos reciben el PDO por constructor o lo instancian directamente.

---

## 12. Módulos implementados

| Módulo | Controlador | Vistas |
|---|---|---|
| Autenticación | `AuthController` | login, register |
| Contraseñas | `PasswordController` + `PasswordReset` | request, reset (Flujo A por email) · change, solicitudes, aprobar/rechazar (Flujo C con aprobación admin) |
| Dashboard | `DashboardController` | superadmin, admin_tienda, vendedor, bodeguero, supervisor, reportero, nomina, cliente, sistema |
| Tiendas | `TiendaController` | index, create, edit |
| Usuarios | `UsuarioController` | index, create, edit |
| Categorías | `CategoriaController` | index, create, edit |
| Unidades de Medida | `UnidadMedidaController` | index, create, edit |
| Impuestos | `ImpuestoController` | index, create, edit |
| Productos | `ProductoController` | index, create, edit |
| Inventario | `InventarioController` | index, movimiento (con filtros), movimientos (global, con filtros), alertas |
| Ventas | `VentaController` | index, create, show (detalle en modal) |
| Caja | `CajaController` | index, apertura, cierre, movimiento, movimientos |
| Clientes | `ClienteController` | index, create, edit |
| Empleados | `EmpleadoController` | index, create, edit |
| Proveedores | `ProveedorController` | index, create, edit |
| Cupones | `CuponController` | index, create, edit, validar |
| Devoluciones | `DevolucionController` | index, create, show |
| Reportes | `ReporteController` | index + 6 sub-reportes |
| Setup | `SetupController` | setup inicial de BD |

---

---

## 13. Correcciones aplicadas (mayo 2026)

Las siguientes correcciones de bugs se aplicaron en las sesiones de depuración de mayo 2026:

### 13.1 Chart.js — Gráficas en blanco en dashboards

**Causa raíz:** `executeScripts()` del layout inyecta `<script src="cdn.chart.js">` de forma asíncrona, pero el código inline `new Chart(...)` se ejecutaba antes de que CDN terminara de cargar → `Chart` indefinido.

**Solución:** Chart.js se carga **una sola vez** en el `<head>` de `dashboard_layout.php`, de forma síncrona, antes de cualquier contenido dinámico. Cada vista de dashboard elimina su `<script src>` local y simplemente usa el global.

```html
<!-- dashboard_layout.php <head> -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
```

**Dashboards afectados:** `superadmin.php`, `admin_tienda.php`, `supervisor.php`

Además, se añadió manejo de estado vacío en cada dashboard:
```php
<?php if (empty($labels7)): ?>
    <div class="chart-empty"><span>📊</span><span>Sin ventas en los últimos 7 días</span></div>
<?php else: ?>
    <canvas id="chartVentas7"></canvas>
<?php endif; ?>
```

---

### 13.2 Ventas — Botón "Ver" abría en SPA en lugar de modal

**Causa raíz:** El botón "Ver" en `ventas/index.php` usaba `loadContent()` (navegación SPA) en lugar de `openModal()`, perdiendo el contexto del listado y no mostrando el comprobante en overlay.

**Solución:**
```php
// Antes:
onclick="loadContent('ventas.show&id=<?= $id ?>', true)"

// Después:
onclick="openModal('index.php?route=ventas.show&id=<?= $id ?>&ajax=1')"
```

La vista `ventas/show.php` detecta si está dentro de un modal y adapta sus botones "Volver" y "Nueva Devolución" en consecuencia.

---

### 13.3 Inventario — Filtros en historial de movimientos

**Causa raíz:** `inventario/movimiento.php` no tenía filtros y `Inventario::listarMovimientos()` no los aceptaba.

**Solución:**
- Firma extendida del modelo: `listarMovimientos(?int $inventarioId, ?int $tiendaId, ?string $tipo, ?string $desde, ?string $hasta)`
- Controlador: `movimiento()` y `movimientos()` leen `$_GET['tipo']`, `$_GET['desde']`, `$_GET['hasta']` y los pasan al modelo
- Vista `movimiento.php`: barra de filtros con select de tipo, inputs de fecha, botones "Filtrar" y "Limpiar"
- Vista `movimientos.php`: **creada** (no existía — el dashboard del Bodeguero la referenciaba y causaba 404)

---

### 13.4 Reportero y Nómina — Dashboards usaban `<a href>` en lugar de SPA

**Causa raíz:** Las tarjetas de acceso rápido en `reportero.php` y `nomina.php` usaban `<a href="index.php?route=...">` causando recargas completas de página y perdiendo el estado de sesión SPA.

**Solución:** Reemplazadas por `<div onclick="loadContent('ruta', true)">` con la misma apariencia visual.

---

### 13.5 PasswordReset — Fatal error al acceder a solicitudes

**Causa raíz:** La tabla `solicitudes_cambio_contrasena` no existía en bases de datos nuevas. Además, las consultas usaban `ur.es_principal = 1` — columna que **no existe** en `usuarios_roles` — y `r.rol_nombre` que tampoco existe en `roles` (el campo correcto es `r.nombre`).

**Solución:**
1. **Auto-creación de tablas:** `PasswordReset::__construct()` llama a `crearTablasIfNotExist()` que ejecuta `CREATE TABLE IF NOT EXISTS` para ambas tablas.
2. **Subquery de rol principal:** reemplazado `ur.es_principal = 1` por `sqlUsuarioRolPrincipal()` — subquery con `NOT EXISTS` que determina el rol de menor `nivel` (el mismo patrón de `Rol::obtenerRolPrincipalDeUsuario()`).
3. **Columna correcta:** `r.rol_nombre` → `r.nombre AS rol_nombre` (la columna de la tabla `roles` se llama `nombre`).

---

*Documento generado: mayo 2026 — Ángel Nicolás Abril*
