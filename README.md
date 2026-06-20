# Mega Uni Store v3

Sistema de gestión multitienda desarrollado en PHP vanilla con arquitectura MVC. Permite administrar inventario, ventas, caja, empleados, clientes y reportes para múltiples tiendas desde un único panel centralizado con control de acceso basado en roles.

**Autor:** Ángel Nicolás Abril  
**Versión:** 3.0  
**Stack:** PHP 8.1+ · MySQL 8 · Laragon · Chart.js 4.4.0  

---

## Requisitos

- **PHP** 8.1 o superior
- **MySQL** 8.0 o superior
- **Laragon** (recomendado) o XAMPP / WAMP
- **Composer** (opcional — no se usa actualmente)
- **Node.js** (solo para scripts de generación de documentos `.docx`)

---

## Instalación con Laragon

1. Clonar o copiar el proyecto en `C:\Laragon\www\Mega_Uni_Store_v3`
2. Iniciar Laragon (Apache + MySQL)
3. Crear la base de datos:
   ```sql
   CREATE DATABASE mega_uni_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
4. Importar el esquema:
   ```
   phpMyAdmin → Importar → document/Docmentos y archivos UML/mega_uni_store.sql
   ```
5. Copiar el archivo de entorno:
   ```
   backend/.env.example → backend/.env
   ```
6. Editar `backend/.env` con los datos de conexión:
   ```env
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=mega_uni_store
   DB_USER=root
   DB_PASSWORD=
   SETUP_KEY=clave_segura_aqui
   APP_URL=http://mega-uni-store-v3.test
   ```
7. Acceder a: `http://mega-uni-store-v3.test/index.php?route=setup&key=clave_segura_aqui`  
   → Esto crea los roles base y permisos en la BD.
8. Registrar el primer usuario y asignarle el rol Superadministrador desde phpMyAdmin.

---

## Estructura del proyecto

```
Mega_Uni_Store_v3/
├── backend/
│   ├── app/
│   │   ├── controllers/     → Un controller por módulo (InventarioController, VentaController, etc.)
│   │   ├── models/          → Un model por entidad (Venta, Inventario, Usuario, etc.)
│   │   ├── Helpers/         → ControllerHelper trait (compartido por todos los controllers)
│   │   └── services/        → Mailer.php (envío de emails)
│   ├── config/
│   │   └── database.php     → Singleton PDO
│   ├── database/
│   │   ├── FASE0_fix_bd.sql         → Fixes iniciales de BD
│   │   ├── fase3_migracion.sql      → Migración Fase 3 (cupones, devoluciones)
│   │   └── migrations/
│   │       └── 004_password_module.sql → Tablas del módulo de contraseñas
│   ├── resources/
│   │   └── views/           → Vistas PHP por módulo y rol
│   ├── routes/
│   │   └── web.php          → Front Controller — único punto de entrada de rutas
│   └── .env / .env.example
├── document/
│   ├── archivos .md/        → Documentación técnica por rol y módulo
│   └── Docmentos y archivos UML/   → Diagramas, SQL, Word, Excel
├── frontend/                → (En preparación — no activo)
├── mobile/                  → (En preparación — no activo)
└── index.php                → Entry point público (redirige a backend/routes/web.php)
```

---

## Arquitectura

### Front Controller (SPA-lite)

Todas las peticiones pasan por `index.php` → `backend/routes/web.php`. El parámetro `?route=X` determina qué controller y método se ejecuta.

Las vistas se cargan en dos modos:
- **Navegación directa** (sin `?ajax=1`): incluye el layout completo con sidebar, navbar y Chart.js
- **SPA (con `?ajax=1`)**: solo retorna el contenido del panel principal — `loadContent(route, true)` lo inyecta en `#dynamicContent`

```javascript
// En cualquier vista que cargue en SPA:
const $isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
if (!$isAjax) { return; } // carga solo cuando viene de loadContent()
```

### Sistema de permisos

Basado en `roles → rol_permisos → permisos`. Cada ruta en `web.php` llama a `requerirPermiso('modulo.accion')` antes de instanciar el controller. El array `$_SESSION['auth']['permisos']` se carga en login y contiene todas las acciones permitidas del usuario.

### Restricción de tienda

`tiendaIdPermitida()` retorna `null` (Superadmin — sin filtro) o `int` (todos los demás roles — solo su tienda). Todos los models interpretan `null` como "todas las tiendas".

### Seguridad (Junio 2026)

- **Cabeceras HTTP**: `app/Middlewares/SecurityHeaders.php` aplica CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy y HSTS (solo HTTPS) en todas las respuestas.
- **Anti fuerza bruta**: `app/Middlewares/RateLimiter.php` limita login (5 intentos / 5 min por IP), registro (3 / 10 min) y solicitudes de reset de contraseña (3 / 10 min). Contadores en `backend/storage/ratelimit/`.
- **CSRF**: el login y el registro ahora validan token CSRF; el token se rota en cada inicio de sesión.
- **Setup**: la clave de setup se compara con `hash_equals()` (timing-safe).

---

## Módulos nuevos (Junio 2026)

| Módulo | Rutas | Cubre |
|---|---|---|
| **Compras a proveedores** | `compras.*` | BD-COM-001 · al recibir una orden, el inventario se actualiza automáticamente (CF-INT-013) |
| **Gastos operacionales** | `gastos.*` | CF-CON-006, REQ-7.8.2 · estados pendiente/pagado/anulado |
| **Contabilidad** | `contabilidad.*` | CF-CON-001..011 · plan de cuentas (PUC base auto-sembrado), asientos con partida doble, períodos, centros de costo, libro mayor, Balance General y Estado de Resultados |
| **RRHH avanzado** | `rrhh.*` | NR-NOM-005/006 · horas extra con recargos legales (25/75/100/150 %) y vacaciones/ausencias con detección de solapamientos |

> ⚠️ **Importante tras actualizar:** vuelve a ejecutar la ruta de setup
> (`index.php?route=setup&key=TU_SETUP_KEY`) para sembrar los nuevos permisos
> (`compras.*`, `gastos.*`, `contabilidad.*`, `rrhh.*`) y asignarlos a los roles.

---

## Roles del sistema

| Rol | Nivel | Scope | Descripción |
|---|---|---|---|
| Superadministrador | 1 | Global | Acceso total |
| Administrador de Tienda | 2 | 1 tienda | Gestión operativa completa |
| Supervisor | 3 | 1 tienda | Supervisión de ventas, caja y reportes |
| Vendedor | 4 | 1 tienda | POS — registra ventas |
| Bodeguero | 5 | 1 tienda | Inventario — entradas y ajustes |
| Reportero | 6 | Global | Solo lectura + exportación de reportes |
| Nómina y RRHH | 7 | Global | Reportes y empleados — sin exportación |
| Cliente | 8 | Global | Portal de autoservicio (en desarrollo) |
| Sistema | 9 | Global | Actor automatizado (en desarrollo) |

---

## Variables de entorno (.env)

| Variable | Descripción | Ejemplo |
|---|---|---|
| `DB_HOST` | Host de MySQL | `127.0.0.1` |
| `DB_PORT` | Puerto de MySQL | `3306` |
| `DB_NAME` | Nombre de la base de datos | `mega_uni_store` |
| `DB_USER` | Usuario de MySQL | `root` |
| `DB_PASSWORD` | Contraseña de MySQL | (vacío en local) |
| `SETUP_KEY` | Clave para la ruta de setup inicial | `clave_segura` |
| `APP_URL` | URL base de la aplicación | `http://mega-uni-store-v3.test` |
| `MAIL_*` | Configuración de SMTP para emails | Ver `.env.example` |

---

## Documentación completa

Ver `document/archivos .md/00_SISTEMA_OVERVIEW.md` para la visión general del sistema.  
Ver `document/archivos .md/00_Auth_Login.md` para los flujos de autenticación.  
Cada rol tiene su carpeta con un archivo por módulo en `document/archivos .md/`.

---

*Proyecto académico — mayo 2026 — Ángel Nicolás Abril*
