# 💰 Ventas — Reportero

> **Permiso:** `ventas.view` (solo lectura)

---

## Solo lectura

El Reportero puede consultar el historial completo de ventas pero **no puede crear ni anular** ninguna.

| Ruta | Reportero | Notas |
|---|---|---|
| `ventas.index` | ✅ | Listado con filtros por fecha, tienda, estado |
| `ventas.show` | ✅ | Comprobante completo en modal |
| `ventas.create / store` | ❌ 403 | Requiere `ventas.create` |
| `ventas.anular` | ❌ 403 | Requiere `ventas.cancel` |

---

## Scope del Reportero

El Reportero generalmente no tiene `tienda_id` asignado (`null`), por lo que `tiendaIdPermitida()` retorna `null` y ve **ventas de todas las tiendas**. Si se le asigna una tienda específica, verá solo esa.

```php
// VentaController::index():
$tiendaIdPermitida = $this->tiendaIdPermitida();
$ventas = $this->ventaModel->listar($tiendaIdPermitida);
// Reportero sin tienda → listar(null) → todas las tiendas
```

---

## Filtros disponibles en el listado

Desde `ventas/index.php` el Reportero puede filtrar por:

| Filtro | Tipo | Descripción |
|---|---|---|
| Fecha inicio / fin | `date` | Rango de fecha de la venta |
| Tienda | `select` | Solo si tiene scope global |
| Estado | `select` | `completada / anulada` |
| Cliente | `text` | Búsqueda por nombre de cliente |

---

## Ver comprobante (`ventas.show`)

El botón "Ver" en el listado abre el comprobante en **modal**:
```javascript
onclick="openModal('index.php?route=ventas.show&id=X&ajax=1')"
```
Permite revisar ítems, total, método de pago y datos del cliente sin salir del listado.

---

## Relación con Reportes

El módulo Ventas es la **fuente de datos primaria** para los reportes que el Reportero genera:
- `reportes.ventas` → agrupa ventas por período
- `reportes.ventas_por_tienda` → compara rendimiento entre tiendas
- `reportes.productos_mas_vendidos` → ranking de productos

El Reportero cruza el detalle de `ventas.show` con los totales en `reportes.*` para validar cifras antes de exportar.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
