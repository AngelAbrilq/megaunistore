# 🔄 Devoluciones — Administrador de Tienda

> **Permisos:** `devoluciones.view · devoluciones.manage` (heredados de ventas en la matriz base)
> **Referencia base:** `Superadministrador/15_Devoluciones.md`

---

## Restricción de tienda

| Acción | Comportamiento |
|---|---|
| `devoluciones.index` | Solo devoluciones donde `tienda_id = su_tienda` |
| `devoluciones.create` | `validarAccesoATienda($venta['tienda_id'])` — la venta origen debe ser de su tienda |
| `devoluciones.store` | La venta a devolver debe pertenecer a su tienda |
| `devoluciones.show` | Solo puede ver devoluciones de su tienda |

---

## Proceso idéntico al Superadmin

Las 5 etapas de `crearDevolucion()` aplican igual para el Admin de Tienda:

```
1. Buscar la venta y verificar que no esté anulada
2. Verificar que no hayan pasado más de 15 días desde la venta
3. Buscar caja abierta en su tienda
4. Por cada ítem: validar contra ventas_detalle → reingresar al inventario
5. INSERT devoluciones + detalle + movimiento de caja (egreso)
```

El `tienda_id` de la devolución se toma de la venta original, no del formulario.

---

## Pre-bloqueo en `create`

Si el Admin intenta procesar una devolución sobre una venta ya anulada, el controlador bloquea antes de mostrar el formulario:

```php
// DevolucionController::create():
$this->validarAccesoATienda((int) $venta['tienda_id']);
// + check previo: si $venta['estado'] === 'anulada' → error flash + redirect
```

---

## Prerrequisito de caja

Igual que para ventas: debe existir una caja abierta en su tienda para procesar la devolución. La caja registrará el egreso de la reversión.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
