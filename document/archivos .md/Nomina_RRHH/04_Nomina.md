# 💼 Nómina — Nómina y RRHH

> **Permisos:** `nomina.view · nomina.manage`
> **Estado: ⚠️ PENDIENTE DE IMPLEMENTACIÓN**

---

## Estado actual

Los permisos `nomina.view` y `nomina.manage` están **definidos en la matriz base** (`Permiso::permisosBase()`), pero el módulo no ha sido implementado aún:

- No existe `NominaController.php`
- No hay rutas de nómina en `backend/routes/web.php`
- Solo existe una vista placeholder: `backend/resources/views/dashboard/nomina.php` accesible desde `dashboard.nomina`

---

## Lo que está reservado

Cuando se implemente, el módulo de nómina debería cubrir:

- Listado de liquidaciones por empleado y período
- Cálculo de salarios, deducciones y bonificaciones
- Historial de pagos
- Integración con el módulo de Empleados (salario base en `empleados.salario`)

---

## Campo `salario` en Empleados

El modelo `Empleado` ya tiene el campo `salario` (decimal) que sirve de base para futuros cálculos de nómina. Es el único punto de contacto actual entre el módulo de empleados y el módulo de nómina pendiente.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
