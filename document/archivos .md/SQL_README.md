# 🗄️ Archivos SQL — Guía de referencia

> Qué es cada archivo SQL del proyecto, cuándo usarlo y cuál es el canónico.

---

## Archivo canónico: `mega_uni_store.sql`

**Ruta:** `document/Docmentos y archivos UML/mega_uni_store.sql`

Este es el **dump completo de la base de datos en producción**. Incluye:
- Estructura de todas las tablas
- Datos semilla de catálogos (roles, permisos, unidades de medida, categorías base)
- Es el archivo a importar en una instalación nueva

**Cuándo usarlo:**
```
Instalación desde cero → importar este archivo en phpMyAdmin
```

---

## Backup: `backup.sql`

**Ruta:** `document/Docmentos y archivos UML/backup.sql`

Copia de seguridad del esquema en un punto previo al desarrollo de Fase 3 (cupones, devoluciones, reset de contraseñas). **No usar para instalaciones nuevas** — le faltan tablas y columnas agregadas en fases posteriores.

**Cuándo usarlo:**
```
Solo si necesitas restaurar a un estado anterior específico.
Para uso cotidiano, siempre usa mega_uni_store.sql.
```

---

## Migración Fase 0: `FASE0_fix_bd.sql`

**Ruta:** `backend/database/FASE0_fix_bd.sql`

Contiene fixes de estructura aplicados al esquema inicial durante la fase 0 del proyecto:
- Columnas faltantes en tablas existentes (ej: `cantidad_minima`, `imagen_url`)
- Ajustes de tipos de dato
- Índices faltantes

**Cuándo usarlo:**
```
Ya está aplicado en mega_uni_store.sql.
Solo ejecutar si estás trabajando con un backup muy antiguo
y ves errores de columnas faltantes.
```

---

## Migración Fase 3: `fase3_migracion.sql`

**Ruta:** `backend/database/fase3_migracion.sql`

Migración que introduce los módulos de Fase 3:
- Tabla `cupones`
- Tabla `devoluciones` y `devoluciones_detalle`
- Columnas de cupón en `ventas` (`cupon_id`, `descuento`)
- Datos semilla de cupones de ejemplo

**Cuándo usarlo:**
```
Ya está aplicado en mega_uni_store.sql.
Solo ejecutar manualmente si se está migrando desde un backup de Fase 2.
```

---

## Migración módulo Password: `004_password_module.sql`

**Ruta:** `backend/database/migrations/004_password_module.sql`

Crea las dos tablas del módulo de reset de contraseñas:
- `password_resets` — tokens de reset por email (Flujo A)
- `solicitudes_cambio_contrasena` — solicitudes con aprobación (Flujo C)

**Importante:** Estas tablas también se crean automáticamente en runtime via `CREATE TABLE IF NOT EXISTS` en el constructor del modelo `PasswordReset`. Por tanto, **no es obligatorio ejecutar este archivo** — las tablas aparecen solas en la primera ejecución del flujo de contraseñas.

**Cuándo usarlo:**
```
Si quieres pre-crear las tablas antes del primer uso del módulo.
O si ves el error: "Table 'password_resets' doesn't exist" y quieres
resolverlo manualmente en phpMyAdmin.
```

---

## Resumen rápido

| Archivo | Usar para... | Estado |
|---|---|---|
| `mega_uni_store.sql` | Instalación nueva — **canónico** | ✅ Actualizado |
| `backup.sql` | Restaurar estado pre-Fase3 | ⚠️ Desactualizado |
| `FASE0_fix_bd.sql` | Fix de esquema inicial | ✅ Ya incluido en canónico |
| `fase3_migracion.sql` | Migrar desde Fase 2 | ✅ Ya incluido en canónico |
| `004_password_module.sql` | Pre-crear tablas de passwords | ✅ Auto-creadas en runtime |

---

## ¿Qué hacer si hay un error de tabla o columna faltante?

1. Verificar primero si la tabla se auto-crea (modelos con `CREATE TABLE IF NOT EXISTS` en constructor)
2. Si no: ejecutar el script de migración correspondiente
3. Si el error persiste después de un backup: re-importar `mega_uni_store.sql` completo

---

*Documentado: mayo 2026 — Ángel Nicolás Abril*
