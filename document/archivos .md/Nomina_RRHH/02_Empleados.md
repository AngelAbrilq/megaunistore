# 👷 Empleados — Nómina y RRHH

> **Permisos:** `empleados.view · empleados.manage`
> **Referencia base:** `Superadministrador/12_Empleados.md`

---

## Acceso global a empleados

A diferencia del Administrador de Tienda (que solo ve empleados de su tienda), el rol de Nómina y RRHH generalmente **no tiene `tienda_id` asignado** en sesión, por lo que `tiendaIdPermitida()` retorna `null`. Esto significa que puede ver y gestionar empleados de **todas las tiendas**.

| Comportamiento | Nomina_RRHH | Admin Tienda |
|---|---|---|
| `tiendaIdPermitida()` | `null` (global) | `int` (solo su tienda) |
| `empleados.index` | Todos los empleados | Solo su tienda |
| `empleados.create` | Select de todas las tiendas | Solo su tienda |
| `empleados.store` | Puede crear en cualquier tienda | Solo su tienda (validado) |

---

## Operaciones disponibles

Tiene `empleados.manage`, por lo que puede crear, editar y dar de baja empleados:

- Crear empleado vinculado a cualquier tienda y usuario
- Editar `codigo`, `fecha_contratacion`, `salario`, `estado`
- Eliminación lógica (`deleted_at + estado = inactivo`)

Las reglas de unicidad (código por tienda, usuario ya empleado en tienda) aplican igual que para el Superadmin.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
