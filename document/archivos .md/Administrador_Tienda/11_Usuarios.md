# 👥 Usuarios — Administrador de Tienda

> **Permiso:** `usuarios.view` (solo lectura)
> **Referencia base:** `Superadministrador/05_Usuarios.md`

---

## Acceso exclusivamente de lectura

El Admin de Tienda solo tiene `usuarios.view`. El router protege cada ruta con el permiso exacto:

| Ruta | Permiso requerido | Admin Tienda |
|---|---|---|
| `usuarios.index` | `usuarios.view` | ✅ Puede acceder |
| `usuarios.create` | `usuarios.create` | ❌ Bloqueado (403) |
| `usuarios.store` | `usuarios.create` | ❌ Bloqueado (403) |
| `usuarios.edit` | `usuarios.update` | ❌ Bloqueado (403) |
| `usuarios.update` | `usuarios.update` | ❌ Bloqueado (403) |
| `usuarios.asignar_rol` | `usuarios.roles.assign` | ❌ Bloqueado (403) |

---

## Listado sin filtro de tienda

`UsuarioController::index()` llama a `$this->usuarioModel->listar()` sin `tiendaIdPermitida`. Los usuarios son una entidad global (no tienen `tienda_id` directo), por lo que el Admin de Tienda **ve todos los usuarios del sistema** en el listado — igual que el Superadmin.

La restricción opera solo a nivel de permisos: puede ver, pero no crear ni modificar.

---

## Caso de uso típico

El Admin de Tienda consulta el listado de usuarios para conocer los datos de un empleado antes de vincularlo como empleado de su tienda en el módulo **Empleados** (`empleados.create`). Desde Usuarios no puede realizar ninguna acción.

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
