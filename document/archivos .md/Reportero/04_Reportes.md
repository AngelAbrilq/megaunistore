# 📈 Reportes — Reportero

> **Permisos:** `reportes.view · reportes.export`
> **Referencia base:** `Administrador_Tienda/06_Reportes.md`

---

## Acceso completo — el único rol no-Superadmin con exportación

El Reportero es el único rol de tienda que tiene `reportes.export` además de `reportes.view`. Puede generar y descargar todos los sub-reportes.

| Sub-reporte | Disponible |
|---|---|
| Ventas (detalle) | ✅ |
| Ventas por Tienda | ✅ (sin filtro de tienda — ve todas) |
| Productos más vendidos | ✅ |
| Ventas por método de pago | ✅ |
| Inventario | ✅ |
| Stock bajo | ✅ |
| Movimientos de inventario | ✅ |
| Movimientos de caja | ✅ |

---

## Filtro de tienda según scope

Si el Reportero tiene `tienda_id` asignado en sesión, el patrón de override aplica igual que para el Admin de Tienda: todos los sub-reportes (excepto `ventasPorTienda`) quedan restringidos a su tienda. Si es global (`tiendaId = null`), ve datos de todas las tiendas sin restricción.

---

## Excepción: `ventasPorTienda()`

`reportes.ventasPorTienda` no aplica el override de `tiendaIdPermitida()`. Un Reportero con tienda asignada también vería todas las tiendas en este sub-reporte. Ver `Administrador_Tienda/06_Reportes.md` para el detalle técnico.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
