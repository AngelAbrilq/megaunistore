# 📊 Inventario — Reportero

> **Permiso:** `inventario.view` (solo lectura)

---

## Solo lectura

El Reportero puede consultar el stock filtrado por su scope pero **no puede registrar movimientos ni ver alertas**. Solo lectura de los niveles actuales.

| Ruta | Reportero | Notas |
|---|---|---|
| `inventario.index` | ✅ | Listado de stock actual con filtros |
| `inventario.movimientos` | ✅ | Historial global de movimientos (con filtros) |
| `inventario.create / store` | ❌ 403 | Requiere `inventario.move` |
| `inventario.movimiento` | ❌ 403 | Requiere `inventario.move` |
| `inventario.alertas` | ❌ 403 | Requiere `inventario.alerts` |

---

## Scope del Reportero en inventario

Al igual que en ventas, si `tiendaIdPermitida()` retorna `null` (rol sin tienda asignada), el Reportero ve el inventario de **todas las tiendas**. Si tiene tienda asignada, solo ve la suya.

```php
// InventarioController::index():
$tiendaIdPermitida = $this->tiendaIdPermitida();
$items = $this->inventarioModel->listar($tiendaIdPermitida);
// null → todas las tiendas
```

---

## Filtros disponibles en `inventario.index`

| Filtro | Descripción |
|---|---|
| Tienda | Dropdown de tiendas (si scope global) |
| Producto | Búsqueda por nombre o código |
| Stock bajo | Checkbox: solo mostrar ítems en alerta |
| Categoría | Filtro por categoría de producto |

---

## Historial de movimientos (`inventario.movimientos`)

El Reportero puede acceder al historial global de movimientos de inventario:

```php
// InventarioController::movimientos():
// Filtros disponibles:
// - tipo: 'entrada' | 'salida' | 'ajuste'
// - desde: fecha inicio
// - hasta: fecha fin
// - tienda_id: si tiene scope global
```

Este historial es clave para auditorías: permite cruzar movimientos de inventario con ventas y devoluciones del mismo período.

---

## Uso principal para el Reportero

- Consultar stock actual antes de generar un reporte de inventario
- Verificar movimientos de un período para detectar discrepancias
- Complementar `reportes.inventario` con el detalle de cada movimiento registrado

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
