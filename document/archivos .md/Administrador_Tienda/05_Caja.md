# 🏦 Caja — Administrador de Tienda

> **Permisos:** `caja.view · caja.manage`
> **Referencia base:** `Superadministrador/10_Caja.md`

---

## Restricción de tienda

Todas las operaciones de caja están filtradas por la tienda del Admin. `validarAccesoATienda()` se llama en cada endpoint que recibe un `caja_id`, garantizando que solo opere sobre cajas que pertenecen a su tienda.

| Acción | Comportamiento |
|---|---|
| `caja.index` | Solo cajas donde `cajas.tienda_id = su_tienda` |
| `caja.create` | El select de tiendas muestra **solo su tienda** |
| `caja.store` | `validarAccesoATienda($tiendaId)` → 403 si otra tienda |
| `caja.apertura` / `abrir` | Verifica que la caja es de su tienda antes de abrir |
| `caja.cierre` / `cerrar` | Verifica que la caja es de su tienda antes de cerrar |
| `caja.movimiento` / `guardarMovimiento` | Verifica que la caja es de su tienda |
| `caja.movimientos` | Solo movimientos de cajas de su tienda |

---

## Select de tiendas en `create`

```php
// CajaController::create():
$tiendas = $tiendaIdPermitida === null
    ? $this->tiendaModel->listar()
    : [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];
// → Admin Tienda: array de UN solo elemento — su tienda
// → No puede seleccionar otra tienda en el formulario
```

---

## Operaciones disponibles

Las operaciones son idénticas a las del Superadmin, solo restringidas a su tienda:

| Operación | Método modelo | Validación previa |
|---|---|---|
| Abrir caja | `registrarApertura($cajaId, $montoInicial)` | Lanza excepción si la caja ya está abierta |
| Cerrar caja | `registrarCierre($cajaId, $montoReal)` | Lanza excepción si la caja no está abierta |
| Ingreso manual | `registrarIngresoManual($cajaId, $monto)` | Caja debe estar abierta |
| Egreso manual | `registrarEgresoManual($cajaId, $monto)` | Caja abierta + monto ≤ saldo actual |

---

## Cierre: cálculo de diferencia

Al cerrar caja, el modelo calcula automáticamente la diferencia entre el saldo del sistema y el conteo físico:

```
diferencia = montoReal (conteo físico) - saldoSistema (calculado)
```

Si hay diferencia positiva o negativa, queda registrada en el movimiento de cierre. El Admin de Tienda ve esta diferencia en el historial.

---

## Egreso: protección de saldo negativo

`registrarEgresoManual()` lanza una excepción si el monto supera el saldo actual:
```
RuntimeException: 'El egreso no puede superar el saldo actual de la caja.'
```
Esto previene saldos negativos en caja.

---

## `listar()` enriquecida

El listado de cajas incluye datos calculados por el modelo para cada caja:

| Campo extra | Origen |
|---|---|
| `abierta` | `estaAbierta(cajaId)` — bool |
| `saldo_actual` | `calcularSaldoActual(cajaId)` — suma de movimientos |
| `ultimo_movimiento` | `obtenerUltimoMovimiento(cajaId)` |

---

## Prerrequisito para ventas y devoluciones

Para que el Admin de Tienda pueda registrar ventas o devoluciones, **debe existir una caja abierta en su tienda**. `buscarCajaAbiertaPorTienda(tiendaId)` busca en `listar($tiendaId)` la primera caja con `estado = 1` y `abierta = true`.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
