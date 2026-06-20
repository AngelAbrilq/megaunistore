# 🏧 Módulo Caja — Superadministrador

> **Rol:** `Superadministrador` (ve todas las tiendas) · `Vendedor`, `Administrador de Tienda` (solo su tienda)
> **Permisos:** `caja.view` · `caja.manage`
> **Controlador:** `CajaController`
> **Modelos:** `Caja` + `Tienda`
> **Vistas:** `resources/views/caja/`

---

## Descripción

El módulo Caja gestiona las cajas registradoras de cada tienda y su ciclo de vida: apertura → operación → cierre. Es un prerrequisito para el módulo de Ventas: **sin caja abierta no se puede registrar ninguna venta**.

### Conceptos clave

- **`cajas`** — Registro físico de una caja (nombre, tienda, estado habilitado/deshabilitado)
- **`cajas_movimientos`** — Historial de todos los eventos de la caja: apertura, ingreso, egreso, cierre
- **Estado "abierta"** — La caja NO tiene campo booleano de estado abierta; se determina dinámicamente por el último movimiento: si el último movimiento NO es de tipo `'cierre'`, la caja está abierta
- **Saldo actual** — Se calcula sumando todos los movimientos desde la última apertura: `apertura + ingresos - egresos`
- **Tipos de movimiento:** `apertura`, `ingreso`, `egreso`, `cierre`

---

## Rutas disponibles

| Ruta | Método | Permiso | Acción |
|---|---|---|---|
| `caja.index` | GET | `caja.view` | Listar cajas con estado y saldo |
| `caja.create` | GET | `caja.manage` | Formulario nueva caja |
| `caja.store` | POST | `caja.manage` | Crear nueva caja |
| `caja.apertura` | GET | `caja.manage` | Formulario de apertura de caja |
| `caja.abrir` | POST | `caja.manage` | Registrar apertura |
| `caja.cierre` | GET | `caja.manage` | Formulario de cierre de caja |
| `caja.cerrar` | POST | `caja.manage` | Registrar cierre |
| `caja.movimiento` | GET | `caja.manage` | Formulario de movimiento manual |
| `caja.guardar_movimiento` | POST | `caja.manage` | Registrar ingreso/egreso manual |
| `caja.movimientos` | GET | `caja.view` | Listado global de movimientos de caja |

---

## Controlador: `CajaController.php`

```php
final class CajaController
{
    use ControllerHelper;
    private Caja   $cajaModel;
    private Tienda $tiendaModel;
}
```

---

### `index()` — Listar cajas

```php
public function index(): void
{
    $tiendaIdPermitida = $this->tiendaIdPermitida();  // null = Superadmin
    $cajas             = $this->cajaModel->listar($tiendaIdPermitida);
    $csrfToken         = $this->generarCsrfToken();
}
```

**Variables para la vista:** cada caja en `$cajas` viene enriquecida con:
- `abierta` — bool: si el último movimiento no es `'cierre'`
- `saldo_actual` — float: saldo calculado desde la última apertura
- `ultimo_movimiento` — array o null con el último movimiento registrado

---

### `store()` — Crear caja

```php
public function store(): void
{
    // Valida CSRF, tienda_id, nombre (obligatorio, ≤ 100 chars)
    // Soporta modal: isModalRequest() → jsonSuccess/jsonError
    // Si no es modal: flash + redirect

    $this->cajaModel->crear([
        'tienda_id'   => $tiendaId,
        'nombre'      => $nombre,
        'descripcion' => $descripcion ?: null,
        'estado'      => 1,
    ]);
}
```

---

### `abrir()` — Registrar apertura

```php
public function abrir(): void
{
    // Valida CSRF, caja_id, acceso a la tienda
    // Valida monto_inicial: numérico, >= 0

    $this->cajaModel->registrarApertura($cajaId, $montoInicial, $descripcion);
    // → Lanza RuntimeException si la caja ya está abierta
}
```

---

### `cerrar()` — Registrar cierre

```php
public function cerrar(): void
{
    // Valida CSRF, caja_id, acceso a la tienda
    // Valida monto_real: numérico, >= 0

    $this->cajaModel->registrarCierre($cajaId, $montoReal, $descripcion);
    // → Calcula diferencia: monto_real - saldo_sistema
    // → Lanza RuntimeException si la caja NO está abierta
}
```

---

### `guardarMovimiento()` — Ingreso/Egreso manual

```php
public function guardarMovimiento(): void
{
    // Valida CSRF, caja_id, tipo (ingreso|egreso), monto (> 0)
    // Para egreso: valida que monto <= saldo_actual

    if ($tipo === 'ingreso') {
        $this->cajaModel->registrarIngresoManual($cajaId, $monto, $descripcion);
    } else {
        $this->cajaModel->registrarEgresoManual($cajaId, $monto, $descripcion);
    }
}
```

---

### `movimientos()` — Listado global de movimientos (con filtros)

```php
public function movimientos(): void
{
    $tiendaIdPermitida = $this->tiendaIdPermitida();

    // Para roles globales (Superadmin) el tienda_id viene del GET;
    // para roles acotados se fuerza el de sesión.
    $filtroTienda = $tiendaIdPermitida !== null
        ? $tiendaIdPermitida
        : (($_GET['tienda_id'] ?? '') !== '' ? (int) $_GET['tienda_id'] : null);

    $filtroTipo  = trim((string) ($_GET['tipo']  ?? ''));
    $filtroDesde = trim((string) ($_GET['desde'] ?? ''));
    $filtroHasta = trim((string) ($_GET['hasta'] ?? ''));

    $movimientos = $this->cajaModel->listarMovimientos(
        null,
        $filtroTienda,
        $filtroTipo  !== '' ? $filtroTipo  : null,
        $filtroDesde !== '' ? $filtroDesde : null,
        $filtroHasta !== '' ? $filtroHasta : null
    );

    // Lista de tiendas para el selector (sólo roles globales)
    $tiendas = $tiendaIdPermitida === null ? $this->tiendaModel->listar() : [];
}
```

**Filtros soportados vía GET:**

| Parámetro | Tipo | Descripción |
|---|---|---|
| `tienda_id` | int | Filtrar por tienda (solo roles globales) |
| `tipo` | string | `apertura` / `ingreso` / `egreso` / `cierre` |
| `desde` | date | Fecha mínima (`YYYY-MM-DD`) |
| `hasta` | date | Fecha máxima (`YYYY-MM-DD`) |

**Ejemplo de llamada desde el SPA:**
```js
loadContent('caja.movimientos&tipo=egreso&desde=2026-05-01&hasta=2026-05-31', true)
```

---

## Modelo: `Caja.php`

### Tabla: `cajas`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `tienda_id` | INT FK | Tienda a la que pertenece |
| `nombre` | VARCHAR(100) | Nombre de la caja (ej: "Caja Principal", "Caja 2") |
| `descripcion` | TEXT / NULL | Descripción opcional |
| `estado` | TINYINT | 1=habilitada, 0=deshabilitada |

> El campo `estado` es para habilitar/deshabilitar la caja en el sistema. Es diferente de si está "abierta" o "cerrada" — eso se determina por los movimientos.

### Tabla: `cajas_movimientos`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `caja_id` | INT FK | Caja afectada |
| `empleado_id` | INT FK / NULL | Empleado que realizó el movimiento |
| `tipo` | ENUM('apertura','ingreso','egreso','cierre') | Tipo de movimiento |
| `monto` | DECIMAL(10,2) | Monto del sistema (o monto inicial en apertura) |
| `monto_real` | DECIMAL(10,2) / NULL | Solo en cierre: dinero contado físicamente |
| `diferencia` | DECIMAL(10,2) / NULL | Solo en cierre: `monto_real - monto_sistema` |
| `descripcion` | VARCHAR | Descripción del movimiento |
| `venta_id` | INT FK / NULL | Venta asociada (para ingresos automáticos por venta) |
| `created_at` | TIMESTAMP | Fecha y hora del movimiento |

### Métodos del modelo

| Método | Descripción |
|---|---|
| `listar(?int $tiendaId)` | Lista cajas con `abierta`, `saldo_actual` y `ultimo_movimiento` enriquecidos |
| `buscarPorId(int $id)` | Retorna una caja con datos enriquecidos |
| `crear(array $datos)` | INSERT en `cajas` |
| `cambiarEstado(int $id, int $estado)` | Habilita/deshabilita la caja |
| `listarMovimientos(?int $cajaId, ?int $tiendaId, ?string $tipo, ?string $desde, ?string $hasta)` | Movimientos con filtros opcionales por tipo, rango de fechas y tienda; máximo 500 registros, orden DESC |
| `obtenerUltimoMovimiento(int $cajaId)` | El movimiento más reciente de una caja |
| `estaAbierta(int $cajaId)` | `true` si el último movimiento != 'cierre' |
| `buscarCajaAbiertaPorTienda(int $tiendaId)` | Devuelve la primera caja abierta de la tienda (usada por Ventas) |
| `registrarApertura(int $cajaId, float $monto, ?string $desc)` | Valida que no esté abierta; inserta movimiento tipo 'apertura' |
| `registrarCierre(int $cajaId, float $montoReal, ?string $desc)` | Valida que esté abierta; calcula diferencia; inserta movimiento tipo 'cierre' |
| `registrarIngresoManual(int $cajaId, float $monto, ?string $desc)` | Valida que esté abierta; inserta movimiento tipo 'ingreso' |
| `registrarEgresoManual(int $cajaId, float $monto, ?string $desc)` | Valida abierta + saldo suficiente; inserta movimiento tipo 'egreso' |
| `registrarIngresoVenta(int $cajaId, int $ventaId, float $monto)` | Ingreso automático desde una venta (llamado por `Venta::crearVenta()`) |
| `calcularSaldoActual(int $cajaId)` | Suma desde la última apertura: `apertura + ingresos - egresos` |

### Cálculo del saldo actual

```sql
-- Desde el ID de la última apertura hasta el movimiento más reciente:
SELECT COALESCE(SUM(
    CASE
        WHEN tipo = 'apertura' THEN monto
        WHEN tipo = 'ingreso'  THEN monto
        WHEN tipo = 'egreso'   THEN -monto
        ELSE 0               -- 'cierre' no suma ni resta
    END
), 0) AS saldo
FROM cajas_movimientos
WHERE caja_id = :caja_id
  AND id >= :apertura_id   -- Solo desde la última apertura
```

### Diferencia en el cierre

```
diferencia = monto_real - saldo_sistema
> 0 → sobrante (hay más dinero físico del esperado)
< 0 → faltante (hay menos dinero físico del esperado)
= 0 → cuadre perfecto
```

---

## Vistas del módulo Caja

### `caja/index.php`
- Lista cajas con: tienda, nombre, descripción, estado habilitado, estado abierta (badge), saldo actual, último movimiento
- Botón "Nueva Caja" → `loadContent('caja.create', true)` (SPA)
- Botón "Abrir" (si cerrada) → `loadContent('caja.apertura&id=X', true)`
- Botón "Cerrar" (si abierta) → `loadContent('caja.cierre&id=X', true)`
- Botón "Movimiento" → `loadContent('caja.movimiento&id=X', true)`
- Botón "Ver movimientos" → `loadContent('caja.movimientos', true)`

### `caja/apertura.php`
- Formulario con: monto inicial*, descripción (opcional)
- POST a `caja.abrir` con `caja_id` oculto

### `caja/cierre.php`
- Muestra el saldo del sistema calculado
- Campo "Monto real contado" (físico)
- POST a `caja.cerrar` con `caja_id` oculto
- Al guardar muestra la diferencia resultante

### `caja/movimiento.php`
- Muestra el saldo actual de la caja
- Formulario: tipo (radio ingreso/egreso), monto*, descripción
- POST a `caja.guardar_movimiento`

### `caja/movimientos.php`
- **Barra de filtros** (novedad): selector de tienda (solo roles globales), selector de tipo, fecha "Desde" y fecha "Hasta" + botones Filtrar/Limpiar
  - Filtrar recarga el contenido via `loadContent('caja.movimientos&tipo=...&desde=...', true)`
  - Limpiar recarga sin parámetros
- Tabla con todos los movimientos (badge por tipo: verde=apertura/ingreso, rojo=egreso, naranja=cierre)
- Columnas: fecha, tienda, caja, tipo, monto sistema, monto real, diferencia, venta asociada, descripción
- Paginación cliente (10 por página)

---

## Flujo completo: Apertura → Ventas → Cierre

```
MAÑANA — Apertura de caja:
1. Vendedor va a Caja → clic "Abrir" en "Caja Principal"
   ↓
2. loadContent('caja.apertura&id=5', true)
   → Formulario: monto_inicial=50000 (base del día)
   ↓
3. POST caja.abrir
   → cajaModel->registrarApertura(5, 50000, null)
      → estaAbierta(5) → false ✓
      → INSERT cajas_movimientos (tipo='apertura', monto=50000.00)
   → flash 'success', redirecciona a caja.index
   → Caja aparece como "Abierta" ✅

DURANTE EL DÍA — Ventas automáticas:
- Cada venta registrada llama a registrarMovimientoCaja(tipo='ingreso', monto=total_venta)
- El saldo_actual va aumentando con cada venta

TARDE — Egreso manual (gasto):
4. Vendedor: Caja → "Movimiento" → tipo=egreso, monto=20000, descripción="Papelería"
   → registrarEgresoManual(5, 20000, 'Papelería')

NOCHE — Cierre de caja:
5. Vendedor cuenta el dinero: $350.000 físicos
   → loadContent('caja.cierre&id=5', true)
   → Formulario muestra: saldo_sistema=$345.000
   → Vendedor ingresa: monto_real=350000
   ↓
6. POST caja.cerrar
   → registrarCierre(5, 350000, null)
      → saldo_sistema = calcularSaldoActual(5) = $345.000
      → diferencia = 350000 - 345000 = +$5.000 (sobrante)
      → INSERT cajas_movimientos (tipo='cierre', monto=345000, monto_real=350000, diferencia=5000)
   → Caja aparece como "Cerrada" 🔒
```

---

## Errores comunes y soluciones

| Error | Causa | Solución |
|---|---|---|
| "La caja ya está abierta." | Intento de apertura sobre una caja ya abierta | Cerrar la caja primero |
| "La caja no está abierta." | Intento de cierre o movimiento en caja cerrada | Abrir la caja primero |
| "La caja debe estar abierta para registrar ingresos/egresos." | Movimiento manual en caja cerrada | Abrir la caja primero |
| "El egreso no puede superar el saldo actual de la caja." | Intento de egresar más del saldo disponible | Ingresar monto menor |
| "No hay una caja abierta para esta tienda." | Al intentar hacer una venta sin caja abierta | Ir a Caja → Abrir una caja |

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
