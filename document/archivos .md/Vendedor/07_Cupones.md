# 🎟️ Cupones — Vendedor

> **Permiso:** `cupones.validar` (solo validación en POS — no gestión)
> **Referencia base:** `Superadministrador/14_Cupones.md`

---

## Rol del Vendedor frente a cupones

El Vendedor **no gestiona cupones** (no puede crear, editar ni eliminar). Su única interacción con el módulo es **validar un código de cupón** durante el proceso de creación de una venta en el POS.

| Ruta | Vendedor |
|---|---|
| `cupones.index` | ❌ No accede |
| `cupones.create / store` | ❌ No tiene permiso |
| `cupones.edit / update` | ❌ No tiene permiso |
| `cupones.destroy` | ❌ No tiene permiso |
| `cupones.validar` | ✅ Llamada AJAX desde el formulario de nueva venta |

---

## Validación AJAX de cupón (en POS)

Cuando el Vendedor escribe un código de cupón en el formulario de nueva venta, el sistema llama a `cupones.validar` vía POST asíncrono:

```javascript
// En ventas/create.php — campo cupón:
// Al escribir el código, se dispara fetch POST a:
// index.php?route=cupones.validar
// Body: { codigo: 'DESC10', subtotal: 150.00, tienda_id: 3 }
```

**Respuesta JSON exitosa:**
```json
{
  "valido": true,
  "cupon_id": 7,
  "tipo_descuento": "porcentaje",
  "valor_descuento": "10.00",
  "descuento_calculado": 15.00,
  "total_con_descuento": 135.00,
  "mensaje": "Cupón aplicado: 10% de descuento"
}
```

**Respuesta JSON de error:**
```json
{
  "valido": false,
  "mensaje": "El cupón ya alcanzó su límite de usos."
}
```

---

## Lógica de validación (`CuponController::validar()`)

```php
POST cupones.validar:
  - codigo    (string, obligatorio)
  - subtotal  (float > 0)
  - tienda_id (int, tienda actual del vendedor)

→ Cupon::validarCupon($codigo, $subtotal, $tiendaId)
   ├─ Busca cupón por código (activo = 1)
   ├─ Verifica fecha_inicio ≤ hoy ≤ fecha_fin (si están definidas)
   ├─ Verifica usos_actuales < usos_maximos (si está definido)
   ├─ Verifica monto_minimo ≤ subtotal (si está definido)
   ├─ Verifica que el cupón sea de la tienda del vendedor (o global si tienda_id = null)
   └─ Calcula descuento según tipo: 'porcentaje' o 'fijo'
      └─ Si tipo = 'porcentaje': min(subtotal * valor / 100, descuento_maximo)
```

---

## Tipos de cupón que puede usar

| tipo_descuento | Cálculo | Ejemplo |
|---|---|---|
| `porcentaje` | `subtotal × valor / 100` (respeta `descuento_maximo`) | 10% → $15 de $150 |
| `fijo` | `valor_descuento` directo | $20 fijo sobre cualquier monto |

---

## Al finalizar la venta

Si el cupón fue validado y aplicado, el `cupon_id` se incluye en el POST de `ventas.store`. `Venta::crearVenta()` registra el uso del cupón:

```php
// Al insertar la venta:
// ventas.cupon_id = $cuponId (si se usó)
// ventas.descuento = $descuentoAplicado
// Cupon::incrementarUso($cuponId) → usos_actuales + 1
```

Si la venta se anula posteriormente, el uso del cupón se revierte: `usos_actuales - 1`.

---

## Lo que el Vendedor NO puede hacer

- No puede ver el listado de cupones ni sus condiciones completas (solo el resultado de validar)
- No puede crear cupones para su tienda — debe pedírselo al Administrador de Tienda o Superadmin
- No puede desactivar ni eliminar cupones vencidos

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
