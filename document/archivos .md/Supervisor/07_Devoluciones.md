# 🔄 Devoluciones — Supervisor

> **Permisos:** `devoluciones.view · devoluciones.manage`
> **Referencia base:** `Superadministrador/15_Devoluciones.md`

---

## Restricción de tienda

El Supervisor siempre opera con su `tienda_id` fijo. `validarAccesoATienda()` en `DevolucionController` rechaza con HTTP 403 cualquier intento de acceder a devoluciones de otra tienda.

| Acción | Comportamiento |
|---|---|
| `devoluciones.index` | Solo devoluciones donde `tienda_id = su_tienda` |
| `devoluciones.create?venta_id=X` | La venta origen debe pertenecer a su tienda |
| `devoluciones.store` | Transacción completa en su tienda |
| `devoluciones.show` | Solo puede ver devoluciones de su tienda |

---

## Diferencia vs Vendedor

El Supervisor tiene `devoluciones.view` completo, por lo que puede acceder directamente a `devoluciones.index` sin necesidad de pasar por `ventas.show`:

| Capacidad | Supervisor | Vendedor |
|---|---|---|
| `devoluciones.index` (listado) | ✅ Directo | ❌ No accede |
| Iniciar devolución desde venta | ✅ | ✅ |
| `devoluciones.show` | ✅ Toda su tienda | ✅ Solo la que registró |

---

## Proceso de devolución

Idéntico al Superadmin y al Admin de Tienda. `Devolucion::crearDevolucion()` en transacción:

```
1. Verificar venta activa (no anulada)
2. Verificar plazo ≤ 15 días desde la venta
3. Verificar caja abierta en su tienda
4. Por cada ítem: validar contra ventas_detalle → reingresar al inventario
5. INSERT devoluciones + devoluciones_detalle
6. Movimiento de caja tipo 'egreso' (reversión del dinero)
```

---

## Pre-bloqueo en `create`

```php
// DevolucionController::create():
$this->validarAccesoATienda((int) $venta['tienda_id']); // → 403 si otra tienda
if ($venta['estado'] === 'anulada') {
    // flash error + redirect ventas.show
}
```

El formulario nunca aparece para ventas anuladas.

---

## Prerrequisito de caja

Debe existir una caja abierta en su tienda al momento de procesar la devolución. Si no la hay:
```
RuntimeException: 'No hay una caja abierta para esta tienda.'
```

El movimiento de egreso se registra en la caja actual de la tienda.

---

## Formulario de devolución (`create`)

```
POST devoluciones.store:
  - venta_id        (hidden, la venta a devolver)
  - motivo          (text, obligatorio)
  - producto_id[]   (array, todos los ítems del detalle)
  - cantidad[]      (array, cantidad a devolver por ítem — 0 = no devuelve)
  - csrf_token
```

Devolución parcial disponible: el Supervisor puede devolver solo algunos ítems o cantidades menores a las vendidas.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
