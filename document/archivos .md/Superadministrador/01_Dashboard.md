# 🏠 Dashboard — Superadministrador

> **Rol:** `Superadministrador`
> **Ruta:** `?route=dashboard.superadmin`
> **Controlador:** `DashboardController→superadmin()`
> **Vista:** `resources/views/dashboard/superadmin.php`

---

## Descripción

El dashboard del Superadministrador es la pantalla principal que ve el usuario con el rol de mayor jerarquía del sistema. Muestra KPIs globales de todas las tiendas y una gráfica de ventas comparativa.

---

## Flujo en el router (`web.php`)

```php
case 'dashboard.superadmin':
    $authController->requerirRol(['Superadministrador']);
    if (!$isAjax) {
        // Carga el shell SPA completo (primera vez)
        require __DIR__ . '/../resources/views/layout/dashboard_layout.php';
    } else {
        // Solo devuelve el fragmento del dashboard (SPA navigation)
        $dashCtrl  = new DashboardController();
        $dashData  = $dashCtrl->superadmin();
        $kpis      = $dashData['kpis'];
        $chartData = $dashData['chartData'];
        require __DIR__ . '/../resources/views/dashboard/superadmin.php';
    }
    break;
```

**Nota SPA:** La primera vez que el usuario accede, PHP carga el shell completo (`dashboard_layout.php`). Ese layout, al iniciar, llama automáticamente a `loadContent('dashboard.superadmin', false)` para poblar el área dinámica con el dashboard del rol actual.

---

## Datos que prepara el controlador

```php
// DashboardController::superadmin()
$dashData = $dashCtrl->superadmin();

// Retorna:
$dashData['kpis'] = [
    'total_tiendas'        => int,   // Número de tiendas activas
    'total_ventas_hoy'     => float, // Suma de ventas del día actual
    'total_productos'      => int,   // Productos registrados en el sistema
    'total_usuarios'       => int,   // Usuarios activos
];

$dashData['chartData'] = [
    // Datos para la gráfica de ventas (últimos 7-30 días por tienda)
    'labels'   => ['Tienda A', 'Tienda B', ...],
    'datasets' => [
        ['label' => 'Ene', 'data' => [1200, 800, ...]],
        ...
    ],
];
```

---

## Variables disponibles en la vista

| Variable | Tipo | Descripción |
|---|---|---|
| `$kpis` | array | KPIs globales del sistema |
| `$chartData` | array | Datos para Chart.js (ventas comparativas) |

---

## Redirección automática de rol

Cuando un usuario autenticado accede a `?route=dashboard` (sin especificar el sub-dashboard), el sistema ejecuta `redirigirDashboardPrincipal()` que lee `$_SESSION['auth']['rol_principal']['rol']` y redirige al dashboard correcto:

```php
// AuthController
private function redirigirDashboardPrincipal(AuthController $auth, bool $soloFragmento = false): void
{
    $rol = $_SESSION['auth']['rol_principal']['rol'] ?? '';
    $mapa = [
        'Superadministrador'    => 'dashboard.superadmin',
        'Administrador de Tienda' => 'dashboard.admin_tienda',
        'Vendedor'              => 'dashboard.vendedor',
        // ...
    ];
    $ruta = $mapa[$rol] ?? 'login';
    // redirige o carga el fragmento según $soloFragmento
}
```

---

## Acceso desde el Sidebar

El sidebar del layout verifica el rol en sesión y muestra el enlace correspondiente:

```php
// En dashboard_layout.php (sidebar)
// El link "Dashboard" llama a:
onclick="loadContent('dashboard.superadmin', true)"
```

---

## Permisos requeridos

| Acción | Verificación |
|---|---|
| Ver el dashboard | `requerirRol(['Superadministrador'])` |

El Superadministrador NO necesita un permiso específico de `dashboard.view`; su rol en sí da acceso total.

---

## Notas de implementación

- Los KPIs son consultas en tiempo real (sin caché) sobre todas las tiendas.
- La gráfica de ventas usa **Chart.js** cargado en el layout global.
- El layout SPA solo se renderiza una vez; las navegaciones posteriores solo cargan fragmentos.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
