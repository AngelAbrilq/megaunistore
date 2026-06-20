# 🔄 Módulo Devoluciones — Superadministrador

> **Rol:** `Superadministrador` (ve todas las tiendas) · `Administrador de Tienda`, `Vendedor` (solo su tienda)
> **Permisos:** `devoluciones.view` · `devoluciones.manage`
> **Controlador:** `DevolucionController`
> **Modelos:** `Devolucion` + `Venta`
> **Vistas:** `resources/views/devoluciones/`

---

## Descripción

El módulo Devoluciones permite procesar la devolución parcial o total de los ítems de una venta. Es uno de los módulos más complejos porque su operación central (`crearDevolucion()`) ejecuta una **transacción de 5 pasos** que afecta a cuatro tablas distintas y requiere condiciones previas estrictas.

### Conceptos clave

- **Devolución sobre ítems, no sobre la venta completa** — Se devuelven productos específicos con cantidades específicas, no la venta entera (para eso existe la anulación).
- **Límite de 15 días** — No se puede devolver si la venta tiene más de 15 días.
- **Requiere caja abierta** — El monto devuelto se registra como egreso de caja.
- **Reingresa inventario** — Cada ítem devuelto incrementa el stock y deja rastro en `movimientos_inventario`.
- **Solo ventas no anuladas** — No se puede devolver sobre una venta ya anulada.
- **Estado siempre `'completada'`** — Al insertar la devolución, el estado es `'completada'` directamente.

---

## Rutas disponibles

| Ruta | Método | Permiso | Acción |
|---|---|---|---|
| `devoluciones.index` | GET | `devoluciones.view` | Listar devoluciones |
| `devoluciones.create` | GET | `devoluciones.manage` | Formulario nueva devolución (requiere `?venta_id=X`) |
| `devoluciones.store` | POST | `devoluciones.manage` | Procesar devolución |
| `devoluciones.show` | GET | `devoluciones.view` | Ver detalle de una devolución |

---

## Controlador: `DevolucionController.php`

```php
final class DevolucionController
{
    use ControllerHelper;
    private Devolucion $devolucionModel;
    private Venta      $ventaModel;

    // Como CuponController, implementa sus propios métodos privados de sesión/acceso
}
```

---

### `index()` — Listar devoluciones

```php
public function index(): void
{
    $tiendaIdPermitida = $this->tiendaIdPermitida();
    $devoluciones = $this->devolucionModel->listar($tiendaIdPermitida);
    // null → todas las tiendas; int → solo esa tienda
}
```

---

### `create()` — Formulario de devolución

```php
public function create(): void
{
    $ventaId = (int) ($_GET['venta_id'] ?? 0);
    $venta   = $this->ventaModel->buscarPorId($ventaId);

    $this->validarAccesoATienda((int) $venta['tienda_id']);

    // Bloqueo previo:
    if ($venta['estado'] === 'anulada') {
        // → flash error, redirect a ventas.show
    }

    $detalle = $this->ventaModel->obtenerDetalle($ventaId);
    // Vista muestra los ítems de la venta para seleccionar qué devolver
}
```

**El formulario se accede desde `ventas.show`**, no desde la navegación principal. El link es:
```
loadContent('devoluciones.create&venta_id=X', true)
```

---

### `store()` — Procesar devolución

```php
public function store(): void
{
    // 1. Valida CSRF
    // 2. Valida venta_id y existencia de la venta
    // 3. validarAccesoATienda(venta['tienda_id'])
    // 4. Valida motivo (no vacío)
    // 5. validarItemsDevolucion($_POST)
    //    → Lee producto_id[] y cantidad[] en paralelo
    //    → Valida que cada item tenga producto_id > 0 y cantidad > 0
    // 6. crearDevolucion(ventaId, items, motivo, usuarioId)
    //    → Lanza RuntimeException en cualquier error
    // 7. En éxito: flash + redirect a devoluciones.show&id=X
    // 8. En error: flash + redirect a devoluciones.create&venta_id=X

    // No usa JSON — siempre redirect
}
```

---

### `show()` — Ver detalle

```php
public function show(): void
{
    $devolucion = $this->devolucionModel->buscarPorId($id);
    $this->validarAccesoATienda((int) $devolucion['tienda_id']);
    $detalle = $this->devolucionModel->obtenerDetalle($id);
    // Vista muestra: datos de la devolución + tabla de ítems devueltos
}
```

---

## Validación: `validarItemsDevolucion()` — controlador

```php
private function validarItemsDevolucion(array $input): ?array
{
    $productos  = $input['producto_id'] ?? [];  // array paralelo (hidden fields — siempre presentes)
    $cantidades = $input['cantidad'] ?? [];      // array paralelo (el usuario puede dejar en 0 los que no quiere devolver)

    // Lógica:
    // 1. Itera todos los ítems de la venta (presentes en el formulario)
    // 2. Si cantidad <= 0 → SKIP (el usuario no quiere devolver ese producto)
    // 3. Si cantidad > 0 pero producto_id inválido → error
    // 4. Al final, si ningún ítem tiene cantidad > 0 → error "debes ingresar al menos uno"

    // IMPORTANTE: los campos producto_id[] son hidden (siempre tienen valor) —
    // no se puede usar "saltar si ambos son vacíos". La lógica correcta
    // es saltar basándose únicamente en cantidad <= 0.

    return [
        ['producto_id' => 5, 'cantidad' => 2.0],
        ['producto_id' => 7, 'cantidad' => 1.0],
        // Los ítems con cantidad=0 no aparecen en el resultado
    ];
}
```

> **Bug corregido (mayo 2026):** La versión anterior lanzaba error "cantidad debe ser mayor que cero" cuando el usuario dejaba un producto en 0 (no quería devolverlo). Esto ocurría porque `producto_id` es un campo `hidden` que siempre tiene valor, así que la condición "omitir si ambos vacíos" nunca se cumplía. La corrección fue cambiar el chequeo a: `if ($cantidad <= 0) { continue; }` — simplemente omite el ítem sin error.

---

## Modelo: `Devolucion.php`

### Tablas involucradas

**Tabla principal: `devoluciones`**

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `venta_id` | INT FK | Venta sobre la que se hace la devolución |
| `tienda_id` | INT FK | Tienda (heredada de la venta) |
| `motivo` | VARCHAR | Razón de la devolución (obligatorio) |
| `monto_devuelto` | DECIMAL(10,2) | Suma de los subtotales de los ítems devueltos |
| `estado` | VARCHAR | Siempre `'completada'` al crear |
| `deleted_at` | TIMESTAMP / NULL | Soft delete |
| `created_by` | INT FK | Usuario que procesó la devolución |
| `updated_by` | INT FK | Último usuario que modificó |
| `created_at` | TIMESTAMP | Fecha de creación |

**Tabla detalle: `devoluciones_detalle`**

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `devolucion_id` | INT FK | Devolución a la que pertenece |
| `producto_id` | INT FK | Producto devuelto |
| `cantidad` | DECIMAL(10,2) | Unidades devueltas |
| `precio_unitario` | DECIMAL(10,2) | Precio del producto en la venta original |
| `subtotal` | DECIMAL(10,2) | `precio_unitario × cantidad` |

### Métodos públicos del modelo

| Método | Descripción |
|---|---|
| `listar(?int $tiendaId)` | INNER JOIN con ventas, tiendas y LEFT JOIN usuarios. Sin filtro: todas. Con filtro: por tienda |
| `buscarPorId(int $id)` | Datos de la devolución + datos de venta y tienda vinculados |
| `obtenerDetalle(int $devolucionId)` | INNER JOIN con productos; retorna todos los ítems de la devolución |
| `crearDevolucion(int $ventaId, array $items, string $motivo, int $usuarioId)` | Transacción completa — ver detalle abajo |

---

## Transacción `crearDevolucion()` — paso a paso

```
1. beginTransaction()

2. buscarVenta(ventaId)
   → Si null → RuntimeException('La venta no existe.')
   → Si estado='anulada' → RuntimeException('No se puede devolver una venta anulada.')

3. Validar antigüedad:
   → días = ceil((NOW - venta.created_at) / 86400)
   → Si días > 15 → RuntimeException('La venta tiene más de 15 días de antigüedad (X días).')

4. buscarCajaAbiertaPorTienda(tiendaId)
   → Si null → RuntimeException('No hay una caja abierta para esta tienda.')

5. validarItemsDevolucion(items, detalleVenta, tiendaId)
   Para cada ítem:
   a. Verifica que el producto esté en el detalle de la venta
   b. Verifica que cantidad_a_devolver <= cantidad_vendida
   c. buscarInventario(tiendaId, productoId)
      → Si null → RuntimeException('No existe inventario para devolver: producto X')
   d. nueva_cantidad = inventario.cantidad + cantidad_devolver
      → UPDATE inventario SET cantidad = nueva_cantidad
      → INSERT movimientos_inventario (tipo='entrada', ref_tipo='devoluciones')
   e. subtotal = precio_unitario_original × cantidad_devolver

6. INSERT INTO devoluciones → $devolucionId
   (estado='completada', monto_devuelto=SUM(subtotales))

7. INSERT INTO devoluciones_detalle (por cada ítem)

8. INSERT INTO cajas_movimientos
   (tipo='egreso', monto=monto_devuelto, descripcion='Devolución #X de venta #Y')

9. commit()
   → retorna $devolucionId

   En cualquier excepción:
   → rollBack()
   → re-lanza la excepción
```

---

## Vista: `devoluciones/index.php`

**Funcionalidades:**
- Lista devoluciones con: ID, venta ID, tienda, motivo, monto devuelto, estado, fecha, creado por
- Botón "Ver" → `loadContent('devoluciones.show&id=X', true)`
- Paginación cliente (10 registros por página)
- No hay botón "Nueva Devolución" aquí — se accede desde `ventas.show`

## Vista: `devoluciones/create.php`

**Funcionalidades:**
- Muestra datos de la venta de origen (total, fecha, cliente)
- Por cada ítem de la venta: nombre del producto, cantidad vendida, input de cantidad a devolver
- Campo motivo* (textarea obligatorio)
- Al enviar: POST a `devoluciones.store`

## Vista: `devoluciones/show.php`

**Funcionalidades:**
- Muestra datos de la devolución: venta vinculada, tienda, motivo, monto devuelto, estado, fecha
- Tabla de ítems devueltos: producto, cantidad, precio unitario, subtotal
- Botón "Volver al listado" → `loadContent('devoluciones.index', true)`

---

## Flujo completo: Procesar devolución

```
1. Admin en ventas.show (venta #42) clic "Devolución"
   ↓
2. loadContent('devoluciones.create&venta_id=42', true)
   → GET: DevolucionController::create(venta_id=42)
   → Valida: venta existe ✓, no anulada ✓, acceso a tienda ✓
   → Vista muestra ítems de la venta #42:
     - [✓] Producto A — vendido: 3 | devolver: [_2_]
     - [✓] Producto B — vendido: 1 | devolver: [_1_]
   → motivo: "Producto defectuoso"
   ↓
3. POST devoluciones.store
   → Valida CSRF ✓
   → venta encontrada ✓, no anulada ✓
   → motivo no vacío ✓
   → validarItemsDevolucion() → [{prod_id:5, cant:2}, {prod_id:8, cant:1}] ✓
   ↓
4. devolucionModel->crearDevolucion(42, items, "Producto defectuoso", userId)
   → beginTransaction()
   → venta existe ✓, estado='completada' (no anulada) ✓
   → días = 3 ≤ 15 ✓
   → caja abierta en tienda 1: caja_id=5 ✓
   → validarItems():
     → Producto A: cantidad_vendida=3 ≥ 2 ✓
       → inventario: id=12, cantidad=10 → nueva=12
       → UPDATE inventario SET cantidad=12
       → INSERT movimientos_inventario (entrada, ref_tipo='devoluciones')
       → subtotal = 25000 × 2 = 50000
     → Producto B: cantidad_vendida=1 ≥ 1 ✓
       → inventario: id=20, cantidad=5 → nueva=6
       → subtotal = 15000 × 1 = 15000
   → monto_devuelto = 50000 + 15000 = 65000
   → INSERT devoluciones → devolucion_id=7
   → INSERT devoluciones_detalle (2 filas)
   → INSERT cajas_movimientos (tipo='egreso', monto=65000, descripcion='Devolución #7 de venta #42')
   → commit()
   → retorna 7
   ↓
5. flash 'success', redirect a devoluciones.show&id=7
```

---

## Errores comunes y soluciones

| Error | Causa | Solución |
|---|---|---|
| "No se puede hacer devolución de una venta anulada" | La venta ya fue anulada | No se puede devolver — la anulación ya revirtió todo |
| "La venta tiene más de 15 días de antigüedad (X días)" | Venta demasiado antigua | El límite es 15 días desde la fecha de la venta |
| "No hay una caja abierta para esta tienda" | Caja cerrada al momento de la devolución | Ir a Caja → abrir una caja primero |
| "El producto no pertenece a esta venta" | producto_id manipulado | El formulario solo muestra ítems reales de la venta |
| "La cantidad a devolver excede la cantidad vendida para: X" | Intentar devolver más de lo vendido | Ingresar cantidad ≤ cantidad vendida |
| "No existe inventario para devolver el producto: X" | El registro en `inventario` fue eliminado | Requiere intervención manual en BD |
| "Debes especificar el motivo de la devolución" | Campo motivo vacío | Completar el motivo |
| "Debes agregar al menos un producto válido" | No se marcó ningún ítem para devolver | Seleccionar al menos un producto con cantidad > 0 |

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
