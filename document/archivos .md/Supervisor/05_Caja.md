# 🏦 Caja — Supervisor

> **Permisos:** `caja.view · caja.manage`
> **Referencia base:** `Administrador_Tienda/05_Caja.md`

---

## Comportamiento idéntico al Administrador de Tienda

El Supervisor tiene pleno acceso operativo a la caja de su tienda: puede crear cajas, abrirlas, cerrarlas y registrar movimientos manuales. Todas las operaciones están restringidas a su tienda por `validarAccesoATienda()`.

| Ruta | Acceso | Descripción |
|---|---|---|
| `caja.index` | ✅ | Listado de cajas de su tienda |
| `caja.create / store` | ✅ | Crear nueva caja |
| `caja.abrir` | ✅ | Apertura de turno con monto inicial |
| `caja.cerrar` | ✅ | Cierre de turno con resumen |
| `caja.movimiento` | ✅ | Ingreso / egreso manual con descripción |
| `caja.historial` | ✅ | Historial de movimientos de la caja |

---

## Flujo típico del Supervisor en caja

```
Inicio de turno:
1. caja.index → seleccionar caja activa o crear nueva
2. caja.abrir → ingresar monto inicial en efectivo
   → CajaController::abrir() → INSERT apertura con timestamp

Durante el día:
3. Las ventas registran automáticamente movimientos de tipo 'ingreso'
4. Las devoluciones/anulaciones registran 'egreso'
5. El Supervisor puede registrar movimientos manuales (caja.movimiento)
   → tipo: 'ingreso' o 'egreso', descripción obligatoria

Cierre de turno:
6. caja.cerrar → registra el monto final contado en físico
   → Sistema calcula diferencia vs. total teórico
   → Cierre queda registrado con timestamp
```

---

## Restricción de tienda

`tiendaIdPermitida()` retorna el `int` de su tienda. El Supervisor solo puede operar cajas de su tienda — no puede abrir ni cerrar cajas de otras tiendas aunque conozca el ID.

---

## Prerrequisito para ventas y devoluciones

Antes de registrar cualquier venta o devolución, debe existir una caja abierta. Si no la hay, `VentaController::crearVenta()` y `DevolucionController::crearDevolucion()` lanzan `RuntimeException`. El Supervisor debe abrir la caja primero.

---

Ver `Administrador_Tienda/05_Caja.md` para el detalle completo del modelo y las consultas SQL.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
