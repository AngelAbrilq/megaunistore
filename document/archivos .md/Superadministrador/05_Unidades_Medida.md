# 📏 Módulo Unidades de Medida — Superadministrador

> **Rol:** `Superadministrador` (y `Administrador de Tienda`)
> **Permisos:** `productos.view` · `productos.create` · `productos.update` · `productos.delete`
> **Controlador:** `UnidadMedidaController`
> **Modelo:** `UnidadMedida`
> **Vistas:** `resources/views/unidades_medida/`

---

## Descripción

El módulo Unidades de Medida permite definir las unidades con las que se mide y vende cada producto: unidad, kilogramo, litro, metro, caja, etc. Es un catálogo auxiliar requerido para crear productos.

A diferencia de otros módulos, las unidades de medida:
- **No tienen soft delete** — el `destroy()` ejecuta un `DELETE` físico
- **No tienen campo `activo`** — todas las unidades listadas están activas
- Si la unidad está referenciada por productos, el intento de eliminación falla con una excepción de integridad referencial que el controlador captura con `try/catch`

---

## Rutas disponibles

| Ruta | Método | Permiso | Acción |
|---|---|---|---|
| `unidades.index` | GET | `productos.view` | Listar todas las unidades |
| `unidades.create` | GET | `productos.create` | Formulario nueva unidad (modal) |
| `unidades.store` | POST | `productos.create` | Guardar nueva unidad |
| `unidades.edit` | GET | `productos.update` | Formulario editar unidad (modal) |
| `unidades.update` | POST | `productos.update` | Guardar cambios |
| `unidades.destroy` | POST | `productos.delete` | Eliminación física (con protección FK) |

---

## Controlador: `UnidadMedidaController.php`

```php
final class UnidadMedidaController
{
    use ControllerHelper;
    private UnidadMedida $unidadModel;

    public function __construct()
    {
        $this->unidadModel = new UnidadMedida();
    }
}
```

---

### `index()` — Listar unidades

```php
public function index(): void
{
    $unidades  = $this->unidadModel->listar();
    $csrfToken = $this->generarCsrfToken();

    require __DIR__ . '/../../resources/views/unidades_medida/index.php';
}
```

**Variables para la vista:**
- `$unidades` — array con todos los registros (id, nombre, simbolo, tipo), ordenados por nombre ASC
- `$csrfToken` — token CSRF para los formularios inline

---

### `create()` — Formulario nueva unidad

```php
public function create(): void
{
    $csrfToken = $this->generarCsrfToken();

    require __DIR__ . '/../../resources/views/unidades_medida/create.php';
}
```

> No requiere datos previos de BD (no hay selects dependientes).

---

### `store()` — Crear unidad

```php
public function store(): void
{
    // 1. Solo acepta POST
    // 2. Valida CSRF
    // 3. Valida datos con validarDatosUnidad($_POST)
    // 4. Crea en BD
    // 5. Responde JSON al modal

    $this->unidadModel->crear($datos);

    $this->jsonExito('unidades.index', 'Unidad de medida creada correctamente.');
}
```

---

### `update()` — Editar unidad

```php
public function update(): void
{
    // Valida CSRF, ID y existencia
    // Valida datos con validarDatosUnidad($_POST)

    $this->unidadModel->actualizar($id, $datos);

    $this->jsonExito('unidades.index', 'Unidad de medida actualizada correctamente.');
}
```

---

### `destroy()` — Eliminación física con captura de FK

```php
public function destroy(): void
{
    $id = (int) ($_POST['id'] ?? 0);

    try {
        $this->unidadModel->eliminar($id);
        $this->guardarMensaje('success', 'Unidad de medida eliminada correctamente.');
    } catch (Throwable $error) {
        // PDOException por violación de foreign key (productos referenciando esta unidad)
        $this->guardarMensaje(
            'error',
            'No se puede eliminar esta unidad porque puede estar relacionada con productos.'
        );
    }

    $this->redireccionar('index.php?route=unidades.index');
}
```

> **Diferencia crítica respecto a otros módulos:** no usa `eliminarLogico()` sino `eliminar()` que ejecuta un `DELETE FROM` real. Si hay productos vinculados, MySQL lanza una excepción de foreign key que el `try/catch` atrapa para mostrar un mensaje amigable.

---

## Validación: `validarDatosUnidad()`

```php
private function validarDatosUnidad(array $input): ?array
{
    $nombre  = trim((string) ($input['nombre'] ?? ''));
    $simbolo = trim((string) ($input['simbolo'] ?? ''));
    $tipo    = trim((string) ($input['tipo'] ?? ''));

    // nombre: obligatorio, máx 80 caracteres
    // simbolo: obligatorio, máx 10 caracteres
    // tipo: opcional, máx 50 caracteres

    return [
        'nombre'  => $nombre,
        'simbolo' => $simbolo,
        'tipo'    => $tipo !== '' ? $tipo : null,
    ];
}
```

**Reglas de validación:**

| Campo | Obligatorio | Validación extra |
|---|---|---|
| `nombre` | ✅ | No vacío, máximo 80 caracteres |
| `simbolo` | ✅ | No vacío, máximo 10 caracteres |
| `tipo` | ❌ | Se guarda como `null` si vacío; máximo 50 caracteres si se ingresa |

---

## Modelo: `UnidadMedida.php`

### Tabla: `unidades_medida`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `nombre` | VARCHAR(80) | Nombre completo (ej: "Kilogramo", "Litro") |
| `simbolo` | VARCHAR(10) | Símbolo corto (ej: "kg", "L", "und") |
| `tipo` | VARCHAR(50) | Categoría opcional (ej: "masa", "volumen", "conteo") |

> **No tiene** `deleted_at`, `activo`, `created_at`, `updated_at`. Es la tabla más simple del sistema.

### Métodos del modelo

| Método | Descripción |
|---|---|
| `listar()` | Retorna todos los registros ordenados por nombre ASC |
| `buscarPorId(int $id)` | Retorna una unidad por ID o `null` |
| `crear(array $datos)` | INSERT, retorna el nuevo `id` |
| `actualizar(int $id, array $datos)` | UPDATE de nombre, símbolo y tipo |
| `eliminar(int $id)` | **DELETE físico** — puede lanzar excepción si hay FK activos |

### SQL del `eliminar()` — DELETE real

```sql
DELETE FROM unidades_medida WHERE id = :id
```

Si un producto tiene `unidad_medida_id = $id`, MySQL lanza:
```
SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row
```
El controlador captura esto con `catch (Throwable $error)`.

---

## Vista: `unidades_medida/index.php`

**Funcionalidades de la vista:**
- Lista todas las unidades con: nombre, símbolo, tipo, acciones
- Botón "Nueva Unidad" → `openModal('index.php?route=unidades.create&ajax=1')`
- Botón "Editar" → `openModal('index.php?route=unidades.edit&id=X&ajax=1')`
- Botón "Eliminar" → formulario POST inline con confirmación JS
- No tiene botón de Activar/Desactivar (no existe campo `activo`)
- Paginación cliente (10 registros por página)

---

## Ejemplos de unidades de medida por tipo

| nombre | simbolo | tipo |
|---|---|---|
| Unidad | und | conteo |
| Kilogramo | kg | masa |
| Gramo | g | masa |
| Litro | L | volumen |
| Mililitro | mL | volumen |
| Metro | m | longitud |
| Caja | caja | conteo |
| Docena | doc | conteo |
| Par | par | conteo |

---

## Flujo completo: Crear unidad de medida

```
1. Superadmin en unidades.index hace clic "Nueva Unidad"
   ↓
2. openModal('index.php?route=unidades.create&ajax=1')
   → GET: UnidadMedidaController::create()
   → Vista simple sin dependencias de BD
   ↓
3. Modal muestra formulario: nombre*, símbolo*, tipo (opcional)
   ↓
4. Usuario completa y hace clic "Guardar"
   submitModalForm()
   → POST unidades.store con header X-Modal-Request: 1
   ↓
5. UnidadMedidaController::store()
   → Valida CSRF ✓
   → validarDatosUnidad($_POST) ✓
   → unidadModel->crear([...]) ✓
   → jsonExito('unidades.index', 'Unidad de medida creada correctamente.')
   ↓
6. JS: cierra modal, toast "Unidad de medida creada correctamente."
   loadContent('unidades.index', true)
```

---

## Errores comunes y soluciones

| Error | Causa | Solución |
|---|---|---|
| "El nombre de la unidad es obligatorio" | Campo nombre vacío | Completar el campo |
| "El nombre no puede superar 80 caracteres" | Nombre demasiado largo | Abreviar el nombre |
| "El símbolo de la unidad es obligatorio" | Campo símbolo vacío | Ingresar símbolo (ej: "kg") |
| "El símbolo no puede superar 10 caracteres" | Símbolo muy largo | Usar abreviatura estándar |
| "No se puede eliminar esta unidad porque puede estar relacionada con productos" | FK violation — productos usan esta unidad | Editar los productos para cambiar su unidad, luego eliminar; o simplemente no eliminarla |

---

*Módulo documentado: mayo 2026 — Ángel Nicolás Abril*
