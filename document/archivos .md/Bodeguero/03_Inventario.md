# 📊 Inventario — Bodeguero

> **Permisos:** `inventario.view · inventario.move · inventario.alerts`
> **Referencia base:** `Administrador_Tienda/03_Inventario.md`

---

## Acceso completo al inventario de su tienda

El Bodeguero tiene el mismo nivel de acceso a inventario que el Administrador de Tienda: puede ver, mover y consultar alertas. La restricción de tienda es total — `tiendaIdPermitida()` retorna su `int` en cada método.

| Acción | Disponible |
|---|---|
| `inventario.index` | ✅ Solo su tienda |
| `inventario.create` | ✅ Select de tienda muestra solo la suya |
| `inventario.store` | ✅ Con doble validación de acceso |
| `inventario.movimiento` | ✅ Solo ítems de su tienda — con filtros de fecha/tipo |
| `inventario.movimientos` | ✅ Solo movimientos de su tienda — con filtros de fecha/tipo |
| `inventario.alertas` | ✅ Solo alertas de su tienda |

---

## Filtros en el historial de movimientos

Tanto `inventario.movimiento` (de un ítem) como `inventario.movimientos` (global de la tienda) permiten filtrar el historial por:

- **Tipo:** entrada / salida / ajuste (o mostrar todos)
- **Desde:** fecha de inicio (formato `YYYY-MM-DD`)
- **Hasta:** fecha de fin

Los filtros se pasan vía GET y se mantienen en los botones "Filtrar" y "Limpiar" de la barra de filtros. La navegación se hace vía `loadContent(params, true)` — sin recargar la página.

> **Nota:** la vista `inventario/movimientos.php` (global) fue **creada** en mayo 2026. Anteriormente no existía y el acceso desde el dashboard del Bodeguero generaba un error 404.

---

## Tipos de movimiento disponibles

| Tipo | Efecto | Cuándo usarlo |
|---|---|---|
| `entrada` | Stock actual + cantidad | Mercancía nueva recibida |
| `salida` | Stock actual - cantidad (con validación) | Merma, muestra, pérdida |
| `ajuste` | Reemplaza el stock absoluto | Conteo físico de inventario |

---

## Validación doble en `store`

```php
// InventarioController::store():
$this->validarAccesoATienda((int) $datos['tienda_id']);
$this->inventarioModel->productoPerteneceATienda($productoId, $tiendaId);
// → El producto debe estar activo en tiendas_productos para su tienda
```

Cualquier manipulación del POST con otra `tienda_id` resulta en HTTP 403.

---

## Alertas de stock

`inventario.alertas` muestra los productos de su tienda donde `cantidad <= cantidad_minima`. El Bodeguero es el responsable principal de reaccionar a estas alertas y registrar entradas para reponer stock.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
