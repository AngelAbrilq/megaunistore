# 👷 Empleados — Administrador de Tienda

> **Permisos:** `empleados.view · empleados.manage`
> **Referencia base:** `Superadministrador/12_Empleados.md`

---

## Restricción de tienda

| Acción | Comportamiento |
|---|---|
| `empleados.index` | Solo empleados donde `empleados.tienda_id = su_tienda` |
| `empleados.create` | Select de tiendas muestra **solo su tienda** |
| `empleados.store` | `validarAccesoATienda($datos['tienda_id'])` → 403 si otra tienda |
| `empleados.edit` | Solo puede editar empleados de su tienda |
| `empleados.update` | Verifica acceso antes de actualizar |
| `empleados.destroy` | Solo puede desvincular empleados de su tienda |

---

## Select de tiendas en `create`

```php
// EmpleadoController::create():
$tiendas = $tiendaId === null
    ? $this->tiendaModel->listar()
    : [$this->tiendaModel->buscarPorId($tiendaId)];
// → Admin Tienda: una sola opción en el select — su tienda
```

---

## Restricciones de actualización

Igual que para el Superadmin: al editar un empleado, `tienda_id` y `usuario_id` son **inmutables**. `actualizar()` solo modifica `codigo`, `fecha_contratacion`, `salario` y `estado`. El vínculo Empleado↔Tienda↔Usuario no puede cambiarse por diseño.

---

## Validaciones de unicidad (idénticas)

Al crear un empleado el sistema verifica dos cosas para la tienda del Admin:

1. **Código duplicado en tienda** — `existeCodigoEnTienda($codigo, $tiendaId)` — el código debe ser único dentro de la tienda.
2. **Usuario ya empleado en tienda** — `usuarioYaEsEmpleadoEnTienda($usuarioId, $tiendaId)` — un usuario no puede tener dos registros de empleado en la misma tienda.

---

## Select de usuarios

El select de usuarios en `create` lista **todos los usuarios activos del sistema**, sin filtro de tienda. El Admin de Tienda puede vincular cualquier usuario como empleado de su tienda, siempre que ese usuario no sea ya empleado de ella.

---

## Eliminación lógica

`eliminarLogico()` establece `deleted_at = NOW()` y `estado = 'inactivo'`. El empleado desaparece del listado pero el registro permanece en BD para trazabilidad histórica (ventas, etc.).

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
