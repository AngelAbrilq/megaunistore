# 📈 Reportes — Nómina y RRHH

> **Permiso:** `reportes.view` (sin exportación)
> **Referencia base:** `Superadministrador/16_Reportes.md`

---

## Acceso de lectura global

Al ser un rol sin `tienda_id` asignado (scope global), `tiendaIdPermitida()` retorna `null`. El rol de Nómina ve reportes de **todas las tiendas** sin restricción.

Puede consultar todos los sub-reportes pero **no puede exportarlos** (no tiene `reportes.export`).

| Ruta | Nómina/RRHH | Notas |
|---|---|---|
| `reportes.index` | ✅ | Panel de reportes con selector de tienda |
| `reportes.ventas` | ✅ | Ventas por período — todas las tiendas |
| `reportes.ventas_por_tienda` | ✅ | Comparativa entre tiendas |
| `reportes.productos_mas_vendidos` | ✅ | Ranking de productos |
| `reportes.inventario` | ✅ | Estado de stock por tienda |
| `reportes.caja` | ✅ | Movimientos de caja por período |
| `reportes.empleados` | ✅ | Listado de empleados por tienda |
| `reportes.export` | ❌ | Requiere `reportes.export` — no asignado |

---

## Filtros disponibles en cada reporte

Desde cada vista de reporte el rol Nómina puede filtrar por:

| Filtro | Disponible |
|---|---|
| Fecha inicio / fin | ✅ |
| Tienda | ✅ (ve todas) |
| Tipo (en movimientos caja) | ✅ |
| Límite de resultados | ✅ (en ranking de productos) |

---

## Reportes de mayor relevancia para Nómina/RRHH

**`reportes.empleados`** — el más usado por este rol:
- Listado de empleados activos por tienda con datos de contacto y cargo
- Base para calcular comisiones por ventas, antigüedad y ausentismo

**`reportes.ventas_por_tienda`** — para cálculo de comisiones:
- Total de ventas de cada tienda en el período → base de cálculo de comisión por rendimiento

**`reportes.ventas`** con filtro de vendedor (si aplica):
- Ventas atribuidas a un empleado específico → métricas individuales

---

## Sin exportación: cómo obtener datos

El rol Nómina no puede generar archivos CSV/Excel directamente desde los reportes. Opciones:

1. Solicitar exportación al **Superadministrador** o a un **Reportero**
2. Copiar los datos de la tabla visible en pantalla manualmente
3. (Futuro) Solicitar al Superadmin que le asigne también el permiso `reportes.export`

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
