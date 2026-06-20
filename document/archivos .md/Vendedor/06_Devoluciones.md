# 🔄 Devoluciones — Vendedor

> **Permisos:** ninguno directo — el Vendedor **inicia** la devolución desde el comprobante de venta
> **Referencia base:** `Superadministrador/15_Devoluciones.md`

---

## Acceso del Vendedor al módulo

El Vendedor **no tiene** `devoluciones.view` ni `devoluciones.manage` de forma directa en la matriz de permisos. Su punto de entrada es el comprobante de venta (`ventas.show`), donde el botón "Devolución" llama a `_ventaShowDevolucion(id)`.

```javascript
// En ventas/show.php — disponible para Vendedor:
function _ventaShowDevolucion(id) {
    if (typeof closeModal === 'function') closeModal();
    loadContent('devoluciones.create&venta_id=' + id, true);
}
```

Si la venta fue abierta en modal, el modal se cierra primero; luego carga `devoluciones.create` en el panel principal de la SPA.

---

## Rutas disponibles para Vendedor

| Ruta | Acceso | Condición |
|---|---|---|
| `devoluciones.create?venta_id=X` | ✅ Vía ventas.show | La venta debe ser de su tienda y no estar anulada |
| `devoluciones.store` | ✅ POST desde create | Venta y usuario de su tienda |
| `devoluciones.show` | ✅ Tras store exitoso | Solo devoluciones de su tienda |
| `devoluciones.index` | ❌ No accede directamente | Requiere permiso separado no asignado al Vendedor |

---

## Validaciones en `DevolucionController::create()`

```php
// 1. venta_id > 0 — si no → redirect ventas.index
// 2. $venta = Venta::buscarPorId($ventaId) — si null → error
// 3. validarAccesoATienda($venta['tienda_id']) — si otra tienda → 403
// 4. $venta['estado'] === 'anulada' → error flash + redirect ventas.show
// Si pasa todo → muestra devoluciones/create.php con el detalle de la venta
```

---

## Formulario de devolución (`create`)

El formulario muestra el detalle completo de la venta: cada línea con su producto, cantidad vendida y precio. El Vendedor marca la cantidad a devolver por ítem (puede ser parcial — puede dejar en 0 los ítems que no devuelve).

```
POST devoluciones.store:
  - venta_id     (hidden)
  - motivo       (text, obligatorio)
  - producto_id[]  (array de los productos del detalle)
  - cantidad[]     (array de cantidades a devolver, 0 = no devuelve)
  - csrf_token   (validación CSRF)
```

---

## Proceso interno al guardar (`store`)

`Devolucion::crearDevolucion()` ejecuta en una sola transacción:

```
1. Verificar que la venta exista y no esté anulada
2. Verificar que no hayan pasado más de 15 días desde la venta
3. Buscar caja abierta en la tienda — si no existe → RuntimeException
4. Por cada ítem a devolver:
   ├─ Validar cantidad contra ventas_detalle (no puede devolver más de lo vendido)
   └─ Reingresa stock al inventario de la tienda
5. INSERT INTO devoluciones (venta_id, motivo, usuario_id, tienda_id, total)
6. INSERT INTO devoluciones_detalle (por cada ítem)
7. INSERT movimiento de caja tipo 'egreso' (reversión del dinero)
```

Si cualquier paso falla → rollback completo, sin cambios en inventario ni caja.

---

## Restricción de tienda

`validarAccesoATienda()` compara el `tienda_id` de la venta contra el `tienda_id` del rol principal del Vendedor en sesión. Si no coinciden → HTTP 403. El Vendedor no puede procesar devoluciones de ventas de otra tienda aunque tenga la URL.

---

## Prerrequisito de caja

Igual que en ventas: debe haber una **caja abierta** en la tienda antes de procesar la devolución. Si no la hay, `crearDevolucion()` lanza:
```
RuntimeException: 'No hay una caja abierta para esta tienda.'
```

---

## Diferencia respecto a Administrador de Tienda

| Capacidad | Admin Tienda | Vendedor |
|---|---|---|
| Ver listado `devoluciones.index` | ✅ | ❌ |
| Iniciar devolución desde venta | ✅ | ✅ (vía ventas.show) |
| Ver comprobante `devoluciones.show` | ✅ | ✅ (solo la que él registró en esa sesión) |

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
