# 🎟️ Módulo Cupones — Superadministrador

> **Rol:** `Superadministrador` (ve todos los cupones) · `Administrador de Tienda` (ve cupones de su tienda y los globales)
> **Permisos:** `cupones.view` · `cupones.manage`
> **Controlador:** `CuponController`
> **Modelos:** `Cupon` + `Tienda`
> **Vistas:** `resources/views/cupones/`

---

## Descripción

El módulo Cupones gestiona los códigos de descuento que se aplican durante una venta. Los cupones pueden ser **globales** (`tienda_id = NULL`) o **específicos de una tienda**. El ciclo de vida de un cupón termina cuando expira, agota sus usos o es eliminado. Al anular una venta, los usos del cupón se decrementan automáticamente.

### Conceptos clave

- **Cupones globales** — `tienda_id = NULL`: aplicables en cualquier tienda.
- **Cupones de tienda** — `tienda_id = X`: solo válidos en esa tienda.
- **`tipo_descuento`** — `'porcentaje'` (% del subtotal, con tope opcional) o `'fijo'` (monto fijo).
- **`usos_actuales`** — Contador de cuántas veces se ha aplicado el cupón. Se incrementa en `crearVenta()` y se decrementa en `anularVenta()`.
- **`validarCupon()`** — Método del modelo usado en tiempo real desde el formulario de nueva venta (endpoint `cupones.validar`).
- **Acceso especial:** roles con tienda ven sus propios cupones **más** los globales (`tienda_id IS NULL`).

---

## Rutas disponibles

| Ruta | Método | Permiso | Acción |
|---|---|---|---|
| `cupones.index` | GET | `cupones.view` | Listar cupones |
| `cupones.create` | GET | `cupones.manage` | Formulario nuevo cupón (modal) |
| `cupones.store` | POST | `cupones.manage` | Crear cupón |
| `cupones.edit` | GET | `cupones.manage` | Formulario editar cupón (modal) |
| `cupones.update` | POST | `cupones.manage` | Guardar cambios |
| `cupones.destroy` | POST | `cupones.manage` | Eliminación lógica |
| `cupones.validar` | POST | *(sin permiso — llamada AJAX desde nueva venta)* | Validar cupón en tiempo real |

---

## Controlador: `CuponController.php`

```php
final class CuponController
{
    use ControllerHelper;
    private Cupon  $cuponModel;
    private Tienda $tiendaModel;

    // Implementa sus propios métodos privados:
    // tiendaIdPermitida(), validarAccesoATienda(), denegarAcceso(),
    // usuarioIdActual(), generarCsrfToken(), validarCsrfToken(),
    // guardarMensaje(), redireccionar()
    // (no los hereda del trait — los define localmente)
}
```

> **Nota de arquitectura:** `CuponController` implementa sus propios métodos de sesión/CSRF/acceso en lugar de usar el `ControllerHelper` trait para todos ellos. La lógica es equivalente, pero está duplicada localmente.

---

### `index()` — Listar cupones

```php
public function index(): void
{
    $tiendaIdPermitida = $this->tiendaIdPermitida();
    $cupones = $this->cuponModel->listar($tiendaIdPermitida);
}
```

**Comportamiento de `listar()` según rol:**
- `null` (Superadmin): todos los cupones sin filtro.
- `int` (rol con tienda): cupones de esa tienda **O** globales (`tienda_id IS NULL`).

---

### `create()` — Formulario

```php
public function create(): void
{
    $tiendas = $tiendaIdPermitida === null
        ? $this->tiendaModel->listar()            // Superadmin: todas las tiendas
        : [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];  // Rol: solo la suya
}
```

---

### `store()` — Crear cupón

```php
public function store(): void
{
    $datos = $this->validarDatos($_POST);
    // → Si tienda_id > 0, también llama validarAccesoATienda() internamente

    try {
        $cuponId = $this->cuponModel->crear($datos);
        $this->jsonExito('cupones.index', 'Cupón creado correctamente.');
    } catch (Throwable $error) {
        // Captura excepciones del modelo (ej: código duplicado vía UNIQUE KEY)
        $this->guardarMensaje('error', $error->getMessage());
        $this->redireccionar('index.php?route=cupones.create');
    }
}
```

---

### `edit()` y `update()` — Acceso controlado por tienda

```php
// En edit() y update():
if ($cupon['tienda_id'] !== null) {
    $this->validarAccesoATienda((int) $cupon['tienda_id']);
}
// Los cupones globales (tienda_id = null) son accesibles por cualquier rol
```

---

### `destroy()` — Eliminación lógica con auditoría

```php
public function destroy(): void
{
    $this->cuponModel->eliminar($id, $this->usuarioIdActual());
    // → UPDATE cupones SET deleted_at = NOW(), updated_by = :usuario_id
}
```

---

### `validar()` — Endpoint AJAX para nueva venta

```php
public function validar(): void
{
    // Recibe: codigo, subtotal, tienda_id (vía POST)
    // Responde JSON puro — no usa CSRF ni sesión de usuario

    $resultado = $this->cuponModel->validarCupon($codigo, $subtotal, $tiendaId);
    echo json_encode($resultado);
    exit;
}
```

**Respuesta JSON posible:**
```json
// Válido:
{"valido": true, "cupon_id": 5, "descuento": 15000.00, "mensaje": "Cupón aplicado correctamente."}

// Inválido:
{"valido": false, "mensaje": "El cupón ha expirado."}
```

---

## Validación: `validarDatos()`

**Reglas de validación:**

| Campo | Obligatorio | Validación extra |
|---|---|---|
| `codigo` | ✅ | No vacío; unicidad no validada en PHP (la BD tiene UNIQUE KEY) |
| `tipo_descuento` | ✅ | Solo `'porcentaje'` o `'fijo'` |
| `valor_descuento` | ✅ | Numérico, `> 0`, normalizado a 2 decimales |
| `tienda_id` | ❌ | Si > 0: se valida acceso; si vacío o 0 → `null` (cupón global) |
| `descuento_maximo` | ❌ | Si se ingresa: numérico, normalizado a 2 decimales (solo aplica a tipo `'porcentaje'`) |
| `monto_minimo` | ❌ | Si se ingresa: numérico, normalizado a 2 decimales |
| `fecha_inicio` | ❌ | Si se ingresa: formato datetime del input HTML |
| `fecha_fin` | ❌ | Si se ingresa: formato datetime del input HTML |
| `usos_maximos` | ❌ | Si se ingresa: entero positivo |
| `activo` | ❌ | `0` o `1`; default `0` si no se envía |
| `descripcion` | ❌ | Guardado como `null` si vacío |

---

## Modelo: `Cupon.php`

### Tabla: `cupones`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `tienda_id` | INT FK / NULL | Tienda dueña del cupón; `NULL` = cupón global |
| `codigo` | VARCHAR UNIQUE | Código que escribe el cliente (ej: "PROMO20") |
| `descripcion` | VARCHAR / NULL | Descripción visible del descuento |
| `tipo_descuento` | ENUM('porcentaje','fijo') | Tipo de cálculo del descuento |
| `valor_descuento` | DECIMAL(10,2) | Porcentaje (ej: 20.00) o monto fijo (ej: 15000.00) |
| `descuento_maximo` | DECIMAL(10,2) / NULL | Tope máximo de descuento (solo para tipo `'porcentaje'`) |
| `monto_minimo` | DECIMAL(10,2) / NULL | Compra mínima requerida para aplicar el cupón |
| `fecha_inicio` | DATETIME / NULL | Desde cuándo es válido |
| `fecha_fin` | DATETIME / NULL | Hasta cuándo es válido |
| `usos_maximos` | INT / NULL | Límite total de usos; `NULL` = ilimitado |
| `usos_actuales` | INT | Contador de usos reales (incrementado por ventas) |
| `activo` | TINYINT | 1=activo, 0=inactivo |
| `deleted_at` | TIMESTAMP / NULL | Soft delete |
| `created_by` | INT FK | Usuario que lo creó |
| `updated_by` | INT FK | Último usuario que lo modificó |

### Métodos del modelo

| Método | Descripción |
|---|---|
| `listar(?int $tiendaId)` | Sin tiendaId: todos. Con tiendaId: de esa tienda + globales (`tienda_id IS NULL`). LEFT JOIN tiendas |
| `buscarPorId(int $id)` | Datos completos del cupón con nombre de tienda |
| `buscarPorCodigo(string $codigo, ?int $tiendaId)` | Busca cupón activo por código, filtrando por tienda o globales |
| `validarCupon(string $codigo, float $subtotal, ?int $tiendaId)` | Validación completa: existencia, fechas, usos, monto mínimo + cálculo del descuento |
| `crear(array $datos)` | INSERT con todos los campos incluyendo `created_by` y `updated_by` |
| `actualizar(int $id, array $datos)` | UPDATE de todos los campos (incluye `updated_by`, excluye `usos_actuales`) |
| `eliminar(int $id, ?int $usuarioId)` | Soft delete con auditoría: `deleted_at = NOW()`, `updated_by = usuario` |
| `incrementarUsos(int $cuponId)` | `usos_actuales = usos_actuales + 1` — llamado desde `crearVenta()` |
| `decrementarUsos(int $cuponId)` | `usos_actuales = GREATEST(0, usos_actuales - 1)` — llamado desde `anularVenta()` |

### Lógica de `validarCupon()` — paso a paso

```php
public function validarCupon(string $codigo, float $subtotal, ?int $tiendaId): array
{
    // 1. buscarPorCodigo($codigo, $tiendaId)
    //    → WHERE activo=1 AND (tienda_id=X OR tienda_id IS NULL)
    //    → null → {valido: false, mensaje: "El cupón no existe o no está activo."}

    // 2. Validar fecha_inicio (si existe):
    //    → NOW() < fecha_inicio → {valido: false, "El cupón aún no está vigente."}

    // 3. Validar fecha_fin (si existe):
    //    → NOW() > fecha_fin → {valido: false, "El cupón ha expirado."}

    // 4. Validar usos (si usos_maximos no es null):
    //    → usos_actuales >= usos_maximos → {valido: false, "Ha alcanzado su límite de usos."}

    // 5. Validar monto_minimo (si existe):
    //    → subtotal < monto_minimo → {valido: false, "El monto mínimo es $X"}

    // 6. Calcular descuento:
    if ($tipo === 'porcentaje') {
        $descuento = $subtotal * ($valor / 100);
        if ($descuento_maximo && $descuento > $descuento_maximo) {
            $descuento = $descuento_maximo;  // Aplica el tope
        }
    } else { // 'fijo'
        $descuento = $valor;
    }

    // → {valido: true, cupon_id: X, descuento: Y, mensaje: "Cupón aplicado correctamente."}
}
```

---

## Integración con el módulo Ventas

```
Formulario Nueva Venta:
1. Vendedor ingresa código de cupón → clic "Aplicar"
   → POST cupones.validar (AJAX)
   → Respuesta JSON → JS actualiza el total en pantalla

2. Vendedor confirma la venta → POST ventas.store
   → crearVenta() llama cuponModel->incrementarUsos($cuponId)
   → El descuento se registra en ventas.descuento_cupon

3. Si se anula la venta:
   → anularVenta() llama cuponModel->decrementarUsos($cuponId)
   → usos_actuales vuelve al valor anterior (mínimo 0 con GREATEST)
```

---

## Vista: `cupones/index.php`

**Funcionalidades:**
- Lista cupones con: código, tienda (o "Global"), tipo descuento, valor, tope, mínimo, fechas, usos (actuales/máximos), estado
- Botón "Nuevo Cupón" → `openModal('index.php?route=cupones.create&ajax=1')`
- Botón "Editar" → `openModal('index.php?route=cupones.edit&id=X&ajax=1')`
- Botón "Eliminar" → formulario POST inline con confirmación JS
- Paginación cliente (10 registros por página)

---

## Flujo completo: Aplicar cupón en una venta

```
1. Vendedor en nueva venta, subtotal = $100.000
   → Ingresa código "PROMO20" → clic "Aplicar"
   ↓
2. POST cupones.validar
   → codigo="PROMO20", subtotal=100000, tienda_id=1
   → validarCupon("PROMO20", 100000.0, 1)
       → buscarPorCodigo → cupón tipo='porcentaje', valor=20, descuento_maximo=15000
       → fechas: vigente ✓
       → usos: 3/50 ✓
       → monto_minimo: null ✓
       → descuento = 100000 * 0.20 = 20000 > 15000 → aplica tope → descuento = 15000
   → JSON: {valido: true, cupon_id: 7, descuento: 15000.00}
   ↓
3. JS actualiza: Total = $100.000 - $15.000 = $85.000
   → cupon_id=7 queda en hidden input del formulario
   ↓
4. Vendedor confirma → POST ventas.store
   → crearVenta(): ...
   → incrementarUsos(7) → usos_actuales = 4
   → ventas.descuento_cupon = 15000.00
```

---

## Errores comunes y soluciones

| Error | Causa | Solución |
|---|---|---|
| "El código del cupón es obligatorio" | Campo código vacío | Completar el código |
| "El tipo de descuento debe ser porcentaje o fijo" | Valor manipulado | Usar solo los selects del formulario |
| "El valor del descuento debe ser un número mayor a cero" | Valor <= 0 o no numérico | Ingresar valor positivo |
| "El cupón no existe o no está activo" | Código inválido o cupón desactivado | Verificar el código o activar el cupón |
| "El cupón aún no está vigente" | `fecha_inicio` en el futuro | Esperar la fecha de inicio |
| "El cupón ha expirado" | `fecha_fin` en el pasado | Actualizar la fecha de fin o crear uno nuevo |
| "Ha alcanzado su límite de usos" | `usos_actuales >= usos_maximos` | Aumentar `usos_maximos` o crear un nuevo cupón |
| "El monto mínimo para usar este cupón es $X" | Subtotal menor al mínimo requerido | Agregar más productos a la venta |

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
