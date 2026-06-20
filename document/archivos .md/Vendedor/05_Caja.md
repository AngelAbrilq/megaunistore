# 🏦 Caja — Vendedor

> **Permisos:** `caja.view · caja.manage`
> **Referencia base:** `Administrador_Tienda/05_Caja.md`

---

## Comportamiento idéntico al Administrador de Tienda

El Vendedor tiene los mismos permisos de caja que el Admin de Tienda. Todas las operaciones están disponibles y restringidas a su propia tienda:

| Operación | Disponible |
|---|---|
| Ver listado de cajas de su tienda | ✅ |
| Crear nueva caja | ✅ |
| Abrir caja (`registrarApertura`) | ✅ |
| Cerrar caja (`registrarCierre`) | ✅ |
| Ingreso manual | ✅ |
| Egreso manual | ✅ |
| Ver historial de movimientos | ✅ |

---

## Restricción de tienda

`validarAccesoATienda()` se aplica en cada operación. El Vendedor solo puede operar cajas de su tienda. El select de tiendas en `create` muestra únicamente la suya.

---

## Razón de diseño

El Vendedor necesita `caja.manage` para poder abrir la caja al inicio de su turno y cerrarla al final, sin depender del Administrador para cada apertura/cierre operativo.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
