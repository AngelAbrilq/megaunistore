# 📈 Reportes — Supervisor

> **Permiso:** `reportes.view` (sin exportación)
> **Referencia base:** `Administrador_Tienda/06_Reportes.md`

---

## Lectura sin exportación

El Supervisor puede consultar todos los sub-reportes de su tienda pero **no puede exportarlos**. El router protege `reportes.export` con ese permiso específico.

| Capacidad | Supervisor | Admin Tienda |
|---|---|---|
| Ver reportes de su tienda | ✅ | ✅ |
| Exportar reportes | ❌ | ✅ |

---

## Filtro de tienda

Idéntico al del Administrador de Tienda: en cada sub-reporte, `tiendaIdPermitida()` sobreescribe el `tienda_id` del GET con el de la sesión. El Supervisor nunca ve datos de otras tiendas.

---

## Excepción: `ventasPorTienda()`

Igual que para el Admin de Tienda, `reportes.ventasPorTienda` no aplica el filtro de tienda y mostraría datos de todas las tiendas. Ver `Administrador_Tienda/06_Reportes.md` para el detalle de esta excepción.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
