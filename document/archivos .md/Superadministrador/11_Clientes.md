# 👥 Módulo Clientes — Superadministrador

> **Rol:** `Superadministrador` (ve todos los clientes) · `Administrador de Tienda`, `Vendedor` (solo clientes de su tienda)
> **Permisos:** `clientes.view` · `clientes.manage`
> **Controlador:** `ClienteController`
> **Modelos:** `Cliente` + `Tienda`
> **Vistas:** `resources/views/clientes/`

---

## Descripción

El módulo Clientes gestiona el padrón de clientes de la plataforma. Su característica principal es el modelo **multitienda con tabla pivote**: un cliente es una entidad global única (por documento o email), pero su relación con cada tienda se administra a través de `tiendas_clientes`, donde también se acumulan sus **puntos de fidelidad por tienda**.

### Conceptos clave

- **`clientes`** — Tabla global. Un cliente existe una sola vez sin importar en cuántas tiendas compre.
- **`tiendas_clientes`** — Pivote que vincula un cliente a una tienda. Contiene `puntos_fidelidad` y `activo`.
- **Documento duplicado** — Si se intenta crear un cliente con el mismo `tipo_documento` + `numero_documento`, el sistema NO crea un duplicado: simplemente asocia el cliente existente a la nueva tienda.
- **Superadmin** — Ve todos los clientes sin filtro de tienda (`tiendaId = null`).
- **Roles con tienda** — Solo ven los clientes asociados a su tienda vía `INNER JOIN tiendas_clientes`.

---

## Rutas disponibles

| Ruta | Método | Permiso | Acción |
|---|---|---|---|
| `clientes.index` | GET | `clientes.view` | Listar clientes |
| `clientes.create` | GET | `clientes.manage` | Formulario nuevo cliente (modal) |
| `clientes.store` | POST | `clientes.manage` | Crear o asociar cliente |
| `clientes.edit` | GET | `clientes.manage` | Formulario editar cliente (modal) |
| `clientes.update` | POST | `clientes.manage` | Guardar cambios |
| `clientes.destroy` | POST | `clientes.manage` | Eliminación lógica |

---

## Controlador: `ClienteController.php`

```php
final class ClienteController
{
    use ControllerHelper;
    private Cliente $clienteModel;
    private Tienda  $tiendaModel;
}
```

---

### `index()` — Listar clientes

```php
public function index(): void
{
    $tiendaId = $this->tiendaIdPermitida();  // null = Superadmin
    $clientes  = $this->clienteModel->listar($tiendaId);
    $csrfToken = $this->generarCsrfToken();
}
```

**Comportamiento según rol:**
- `$tiendaId === null` → Superadmin: SELECT directo en `clientes`, sin JOIN a `tiendas_clientes`. No incluye `puntos_fidelidad`.
- `$tiendaId === int` → Rol con tienda: INNER JOIN a `tiendas_clientes` filtrando por tienda. Incluye `puntos_fidelidad` y `activo_tienda`.

---

### `store()` — Crear o asociar cliente

Este método tiene lógica especial para evitar duplicados por documento:

```php
public function store(): void
{
    // 1. Valida CSRF
    // 2. validarDatos($_POST) → datos básicos del cliente
    // 3. Si tiene tipo_documento + numero_documento:
    //    → buscarPorDocumento(tipo, numero)
    //    → Si ya existe: asociarATienda(existente['id'], tiendaId) y retorna éxito
    // 4. Si tiene email:
    //    → existeEmail(email) → si existe: flash error, redirige
    // 5. Si tiendaId > 0:
    //    → crearYAsociar(datos, tiendaId)  ← transacción
    //    → Si no hay tiendaId: crear(datos)  (cliente sin tienda)

    $this->jsonExito('clientes.index', 'Cliente registrado correctamente.');
}
```

**Flujo de decisión en `store()`:**

```
¿Tiene tipo_documento + numero_documento?
    ↓ SÍ
    buscarPorDocumento() → ¿existe?
        ↓ SÍ → asociarATienda(existente.id, tiendaId)
               → jsonExito("Cliente ya registrado. Asociado a la tienda.")
        ↓ NO → continuar

¿Tiene email?
    ↓ SÍ → existeEmail() → ¿existe?
               ↓ SÍ → flash error "El correo ya está registrado" + redirect
               ↓ NO → continuar

¿tiendaId > 0?
    ↓ SÍ  → crearYAsociar(datos, tiendaId)  ← transacción
    ↓ NO  → crear(datos)                      ← cliente global sin tienda
```

---

### `update()` — Actualizar cliente

```php
public function update(): void
{
    // Valida CSRF, id, existencia
    // validarDatos($_POST)
    // Si tiene email: existeEmail(email, $id)
    //   → excluye el cliente actual de la búsqueda de duplicados

    $this->clienteModel->actualizar($id, $datos);
    $this->jsonExito('clientes.index', 'Cliente actualizado correctamente.');
}
```

---

### `destroy()` — Eliminación lógica

```php
public function destroy(): void
{
    $this->clienteModel->eliminarLogico($id);
    // → UPDATE clientes SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL
    // No elimina de tiendas_clientes — solo marca el cliente como eliminado

    $this->guardarMensaje('success', 'Cliente eliminado correctamente.');
    $this->redireccionar('index.php?route=clientes.index');
}
```

> **Nota:** `destroy()` redirige con flash (no usa JSON). El formulario de eliminación es inline en la vista.

---

## Validación: `validarDatos()`

```php
private function validarDatos(array $input): ?array
{
    $nombre = trim((string) ($input['nombre'] ?? ''));
    // → Obligatorio; si vacío: flash error, return null

    $email = strtolower(trim((string) ($input['email'] ?? '')));
    // → Opcional; si se ingresa: FILTER_VALIDATE_EMAIL

    return [
        'nombre'           => $nombre,
        'apellido'         => trim($input['apellido'] ?? ''),
        'email'            => $email,               // lowercase
        'telefono'         => trim($input['telefono'] ?? ''),
        'tipo_documento'   => trim($input['tipo_documento'] ?? ''),
        'numero_documento' => trim($input['numero_documento'] ?? ''),
        'direccion'        => trim($input['direccion'] ?? ''),
    ];
}
```

**Reglas de validación:**

| Campo | Obligatorio | Validación extra |
|---|---|---|
| `nombre` | ✅ | No vacío |
| `apellido` | ❌ | Se guarda vacío si no se ingresa |
| `email` | ❌ | Si se ingresa: formato válido + único (global) |
| `telefono` | ❌ | Sin validación de formato |
| `tipo_documento` | ❌ | Se usa junto con `numero_documento` para deduplicar |
| `numero_documento` | ❌ | Se usa junto con `tipo_documento` para deduplicar |
| `direccion` | ❌ | Sin validación de longitud |

---

## Modelo: `Cliente.php`

### Tabla: `clientes`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `nombre` | VARCHAR | Nombre del cliente |
| `apellido` | VARCHAR | Apellido (opcional) |
| `email` | VARCHAR | Email único (opcional) |
| `telefono` | VARCHAR | Teléfono (opcional) |
| `tipo_documento` | VARCHAR | CC, NIT, CE, Pasaporte, etc. |
| `numero_documento` | VARCHAR | Número del documento |
| `direccion` | VARCHAR | Dirección (opcional) |
| `deleted_at` | TIMESTAMP / NULL | Soft delete |
| `created_at` | TIMESTAMP | Fecha de registro |

### Tabla pivote: `tiendas_clientes`

| Campo | Tipo | Descripción |
|---|---|---|
| `tienda_id` | INT FK | Tienda |
| `cliente_id` | INT FK | Cliente |
| `puntos_fidelidad` | INT | Puntos acumulados en esta tienda |
| `activo` | TINYINT | 1=activo, 0=inactivo en esta tienda |

> La combinación `(tienda_id, cliente_id)` es **única**. El método `asociarATienda()` usa `ON DUPLICATE KEY UPDATE activo = 1` para reactivar relaciones existentes.

### Métodos del modelo

| Método | Descripción |
|---|---|
| `listar(?int $tiendaId)` | Sin tiendaId: todos los clientes. Con tiendaId: INNER JOIN a tiendas_clientes, incluye puntos_fidelidad |
| `buscarPorId(int $id)` | Retorna datos básicos del cliente (sin JOIN a tiendas) |
| `buscarPorDocumento(string $tipo, string $numero)` | Busca cliente activo por combinación tipo+número de documento |
| `existeEmail(string $email, ?int $excluirId)` | Verifica unicidad de email; con excluirId ignora el cliente actual |
| `crear(array $datos)` | INSERT en `clientes`; retorna el nuevo `id` |
| `actualizar(int $id, array $datos)` | UPDATE de todos los campos del cliente |
| `eliminarLogico(int $id)` | UPDATE `deleted_at = NOW()` |
| `asociarATienda(int $clienteId, int $tiendaId)` | INSERT en `tiendas_clientes` con `ON DUPLICATE KEY UPDATE activo = 1` |
| `crearYAsociar(array $datos, int $tiendaId)` | Transacción: `crear()` + `asociarATienda()` |
| `listarParaSelect(?int $tiendaId)` | Lista simplificada (id, nombre, apellido, numero_documento) para `<select>` en Ventas |

### Detalle: `listar()` — dos comportamientos

**Sin tienda (Superadmin):**
```sql
SELECT c.id, c.nombre, c.apellido, c.email, c.telefono,
       c.tipo_documento, c.numero_documento, c.direccion, c.created_at
FROM clientes c
WHERE c.deleted_at IS NULL
ORDER BY c.id DESC
```

**Con tienda (roles con acceso restringido):**
```sql
SELECT c.id, c.nombre, c.apellido, c.email, c.telefono,
       c.tipo_documento, c.numero_documento, c.direccion, c.created_at,
       tc.puntos_fidelidad, tc.activo AS activo_tienda
FROM clientes c
INNER JOIN tiendas_clientes tc ON tc.cliente_id = c.id
WHERE c.deleted_at IS NULL
  AND tc.tienda_id = :tienda_id
  AND tc.activo    = 1
ORDER BY c.nombre ASC, c.apellido ASC
```

### Detalle: `crearYAsociar()` — transacción atómica

```php
public function crearYAsociar(array $datos, int $tiendaId): int
{
    $this->db->beginTransaction();
    try {
        $clienteId = $this->crear($datos);
        $this->asociarATienda($clienteId, $tiendaId);
        $this->db->commit();
        return $clienteId;
    } catch (Throwable $e) {
        $this->db->rollBack();
        throw $e;
    }
}
```

---

## Vistas del módulo Clientes

### `clientes/index.php`
- Lista clientes con: nombre completo, email, teléfono, tipo+número de documento, dirección, fecha de registro
- Si el rol tiene tienda asignada: también muestra `puntos_fidelidad`
- Botón "Nuevo Cliente" → `openModal('index.php?route=clientes.create&ajax=1')`
- Botón "Editar" → `openModal('index.php?route=clientes.edit&id=X&ajax=1')`
- Botón "Eliminar" → formulario POST inline con confirmación JS
- Paginación cliente (10 registros por página)

### `clientes/create.php`
- Formulario modal: nombre*, apellido, email, teléfono, tipo_documento, numero_documento, dirección
- Select de tienda (para asociar el cliente al crearlos)
- POST a `clientes.store` con `X-Modal-Request: 1`

### `clientes/edit.php`
- Mismo formulario que create, pre-llenado con datos del cliente
- No permite cambiar la tienda (la asociación es gestionada por `tiendas_clientes`)

---

## Flujo completo: Crear cliente nuevo con tienda

```
1. Admin en clientes.index hace clic "Nuevo Cliente"
   ↓
2. openModal('index.php?route=clientes.create&ajax=1')
   → GET: ClienteController::create()
   → Vista recibe: todas las tiendas (para el select)
   ↓
3. Modal muestra formulario:
   - nombre* (text)
   - apellido, email, teléfono
   - tipo_documento (select: CC/NIT/CE/Pasaporte)
   - numero_documento
   - dirección
   - tienda_id (select: a qué tienda asociar)
   ↓
4. Usuario completa: nombre="Juan Pérez", tipo_documento="CC", numero_documento="1234567", tienda=1
   submitModalForm()
   → POST clientes.store con X-Modal-Request: 1
   ↓
5. ClienteController::store()
   → Valida CSRF ✓
   → validarDatos() → nombre no vacío ✓
   → buscarPorDocumento('CC', '1234567') → null (no existe) ✓
   → existeEmail(null) → no aplica (email vacío) ✓
   → tiendaId = 1 > 0 ✓
   → crearYAsociar(datos, 1)
       → beginTransaction()
       → INSERT INTO clientes → $clienteId = 15
       → INSERT INTO tiendas_clientes (tienda_id=1, cliente_id=15, puntos_fidelidad=0, activo=1)
       → commit()
   → jsonExito('clientes.index', 'Cliente registrado correctamente.')
   ↓
6. JS: cierra modal, toast "Cliente registrado correctamente.", recarga clientes.index
```

## Flujo: Documento duplicado → asociación automática

```
1. Se intenta crear cliente con CC=1234567, tienda_id=2
   ↓
2. ClienteController::store()
   → buscarPorDocumento('CC', '1234567') → {id: 15, nombre: "Juan Pérez"} (ya existe)
   ↓
3. asociarATienda(15, 2)
   → INSERT INTO tiendas_clientes (tienda_id=2, cliente_id=15, puntos_fidelidad=0, activo=1)
      ON DUPLICATE KEY UPDATE activo = 1
   ↓
4. jsonExito('clientes.index', 'Cliente ya registrado. Asociado a la tienda.')
   → Juan Pérez ahora está asociado tanto a tienda 1 como a tienda 2
```

---

## Errores comunes y soluciones

| Error | Causa | Solución |
|---|---|---|
| "El nombre es obligatorio" | Campo nombre vacío | Completar el nombre |
| "El correo electrónico no tiene un formato válido" | Email con formato incorrecto | Verificar formato (ej: usuario@dominio.com) |
| "El correo electrónico ya está registrado" | Email duplicado al crear | Usar otro email o dejarlo vacío |
| "El correo electrónico ya está asignado a otro cliente" | Email duplicado al editar | Cambiar el email |
| "Cliente ya registrado. Asociado a la tienda." | Documento duplicado — no es error | Comportamiento esperado: el cliente existente fue vinculado a la nueva tienda |

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
