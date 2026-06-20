# 📊 Inventario — Administrador de Tienda

> **Permisos:** `inventario.view · inventario.move · inventario.alerts`
> **Referencia base:** `Superadministrador/08_Inventario.md`

---

## Restricción de tienda: total

El inventario es el módulo con **mayor restricción de tienda** para este rol. `tiendaIdPermitida()` retorna el `int` de su tienda en cada método, sin excepción.

| Acción | Comportamiento |
|---|---|
| `inventario.index` | Solo ítems donde `inventario.tienda_id = su_tienda` |
| `inventario.create` | El select de tiendas muestra **solo su tienda** |
| `inventario.store` | `validarAccesoATienda()` rechaza con 403 si se envía otra tienda |
| `inventario.movimiento` | Solo puede acceder a ítems de su tienda — con filtros tipo/desde/hasta |
| `inventario.movimientos` | Solo movimientos de su tienda — con filtros tipo/desde/hasta |
| `inventario.alertas` | Solo alertas de su tienda |

---

## Formulario de entrada inicial (`create`)

```php
// En InventarioController::create():
$tiendas = [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];
// → El select de tiendas tiene UNA sola opción: la suya
// → No puede seleccionar otra tienda
```

---

## Validación doble de acceso en `store`

```php
// InventarioController::store():
$this->validarAccesoATienda((int) $datos['tienda_id']);
// → Si alguien manipula el POST con otra tienda_id → HTTP 403

$this->inventarioModel->productoPerteneceATienda($productoId, $tiendaId);
// → El producto debe estar activo en tiendas_productos para su tienda
```

---

## Tipos de movimiento disponibles

Igual que el Superadmin — sin restricción de tipo:

| Tipo | Efecto | Cuándo usarlo |
|---|---|---|
| `entrada` | Stock actual + cantidad | Mercancía nueva recibida |
| `salida` | Stock actual - cantidad (con validación) | Merma, muestra, pérdida |
| `ajuste` | Reemplaza el stock absoluto | Conteo físico de inventario |

---

## Alertas de stock

`inventario.alertas` muestra solo los productos de su tienda donde `cantidad <= cantidad_minima`. El Admin de Tienda es el responsable principal de reaccionar a estas alertas.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
