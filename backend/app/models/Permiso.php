<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Permiso
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function crear(array $datos): int
    {
        $sql = "
            INSERT INTO permisos (
                nombre,
                modulo,
                accion,
                descripcion
            ) VALUES (
                :nombre,
                :modulo,
                :accion,
                :descripcion
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':nombre' => trim($datos['nombre']),
            ':modulo' => trim($datos['modulo']),
            ':accion' => trim($datos['accion']),
            ':descripcion' => $datos['descripcion'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function buscarPorNombre(string $nombre): ?array
    {
        $sql = "
            SELECT
                id,
                nombre,
                modulo,
                accion,
                descripcion
            FROM permisos
            WHERE nombre = :nombre
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => trim($nombre),
        ]);

        $permiso = $stmt->fetch();

        return $permiso ?: null;
    }

    public function buscarPorAccion(string $accion): ?array
    {
        $sql = "
            SELECT
                id,
                nombre,
                modulo,
                accion,
                descripcion
            FROM permisos
            WHERE accion = :accion
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':accion' => trim($accion),
        ]);

        $permiso = $stmt->fetch();

        return $permiso ?: null;
    }

    public function listar(): array
    {
        $sql = "
            SELECT
                id,
                nombre,
                modulo,
                accion,
                descripcion
            FROM permisos
            ORDER BY modulo ASC, accion ASC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function asignarPermisoARol(int $rolId, int $permisoId): bool
    {
        if ($this->rolTienePermiso($rolId, $permisoId)) {
            return true;
        }

        $sql = "
            INSERT INTO roles_permisos (
                rol_id,
                permiso_id
            ) VALUES (
                :rol_id,
                :permiso_id
            )
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':rol_id' => $rolId,
            ':permiso_id' => $permisoId,
        ]);
    }

    public function rolTienePermiso(int $rolId, int $permisoId): bool
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM roles_permisos
            WHERE rol_id = :rol_id
              AND permiso_id = :permiso_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':rol_id' => $rolId,
            ':permiso_id' => $permisoId,
        ]);

        $resultado = $stmt->fetch();

        return (int) $resultado['total'] > 0;
    }

    public function obtenerPermisosDeRol(int $rolId): array
    {
        $sql = "
            SELECT
                p.id,
                p.nombre,
                p.modulo,
                p.accion,
                p.descripcion
            FROM roles_permisos rp
            INNER JOIN permisos p ON p.id = rp.permiso_id
            WHERE rp.rol_id = :rol_id
            ORDER BY p.modulo ASC, p.accion ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':rol_id' => $rolId,
        ]);

        return $stmt->fetchAll();
    }

    public function asegurarPermisosBase(): void
    {
        $permisos = $this->permisosBase();

        foreach ($permisos as $permiso) {
            if ($this->buscarPorAccion($permiso['accion']) === null) {
                $this->crear($permiso);
            }
        }
    }

    public function sincronizarPermisosRolesBase(): void
    {
        $this->asegurarPermisosBase();

        $roles = $this->obtenerRoles();
        $permisos = $this->listar();

        $rolesPorNombre = [];

        foreach ($roles as $rol) {
            $rolesPorNombre[$rol['nombre']] = $rol;
        }

        $permisosPorAccion = [];

        foreach ($permisos as $permiso) {
            $permisosPorAccion[$permiso['accion']] = $permiso;
        }

        $matriz = $this->matrizRolesPermisos();

        foreach ($matriz as $rolNombre => $acciones) {
            if (!isset($rolesPorNombre[$rolNombre])) {
                continue;
            }

            $rolId = (int) $rolesPorNombre[$rolNombre]['id'];

            foreach ($acciones as $accion) {
                if (!isset($permisosPorAccion[$accion])) {
                    continue;
                }

                $permisoId = (int) $permisosPorAccion[$accion]['id'];
                $this->asignarPermisoARol($rolId, $permisoId);
            }
        }
    }

    public function usuarioTienePermiso(int $usuarioId, string $accion, ?int $tiendaId = null): bool
    {
        if ($tiendaId === null) {
            $sql = "
                SELECT COUNT(*) AS total
                FROM usuarios_roles ur
                INNER JOIN roles_permisos rp ON rp.rol_id = ur.rol_id
                INNER JOIN permisos p ON p.id = rp.permiso_id
                INNER JOIN roles r ON r.id = ur.rol_id
                WHERE ur.usuario_id = :usuario_id
                  AND p.accion = :accion
                  AND r.activo = 1
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $usuarioId,
                ':accion' => $accion,
            ]);
        } else {
            $sql = "
                SELECT COUNT(*) AS total
                FROM usuarios_roles ur
                INNER JOIN roles_permisos rp ON rp.rol_id = ur.rol_id
                INNER JOIN permisos p ON p.id = rp.permiso_id
                INNER JOIN roles r ON r.id = ur.rol_id
                WHERE ur.usuario_id = :usuario_id
                  AND p.accion = :accion
                  AND r.activo = 1
                  AND (
                        ur.tienda_id IS NULL
                        OR ur.tienda_id = :tienda_id
                  )
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $usuarioId,
                ':accion' => $accion,
                ':tienda_id' => $tiendaId,
            ]);
        }

        $resultado = $stmt->fetch();

        return (int) $resultado['total'] > 0;
    }






    public function obtenerAccionesDeUsuario(int $usuarioId): array
{
    $sql = "
        SELECT DISTINCT
            p.accion
        FROM usuarios_roles ur
        INNER JOIN roles r ON r.id = ur.rol_id
        INNER JOIN roles_permisos rp ON rp.rol_id = r.id
        INNER JOIN permisos p ON p.id = rp.permiso_id
        WHERE ur.usuario_id = :usuario_id
          AND r.activo = 1
        ORDER BY p.accion ASC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        ':usuario_id' => $usuarioId,
    ]);

    return array_column($stmt->fetchAll(), 'accion');
}





    private function obtenerRoles(): array
    {
        $sql = "
            SELECT
                id,
                nombre,
                descripcion,
                nivel,
                activo
            FROM roles
            WHERE activo = 1
            ORDER BY nivel ASC, nombre ASC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    private function permisosBase(): array
    {
        return [
            [
                'nombre' => 'Ver dashboard',
                'modulo' => 'dashboard',
                'accion' => 'dashboard.view',
                'descripcion' => 'Permite acceder al panel principal del sistema.',
            ],

            [
                'nombre' => 'Ver tiendas',
                'modulo' => 'tiendas',
                'accion' => 'tiendas.view',
                'descripcion' => 'Permite consultar tiendas registradas.',
            ],
            [
                'nombre' => 'Crear tiendas',
                'modulo' => 'tiendas',
                'accion' => 'tiendas.create',
                'descripcion' => 'Permite crear nuevas tiendas.',
            ],
            [
                'nombre' => 'Editar tiendas',
                'modulo' => 'tiendas',
                'accion' => 'tiendas.update',
                'descripcion' => 'Permite actualizar información de tiendas.',
            ],
            [
                'nombre' => 'Cambiar estado de tiendas',
                'modulo' => 'tiendas',
                'accion' => 'tiendas.toggle',
                'descripcion' => 'Permite activar o desactivar tiendas.',
            ],
            [
                'nombre' => 'Eliminar tiendas',
                'modulo' => 'tiendas',
                'accion' => 'tiendas.delete',
                'descripcion' => 'Permite eliminar tiendas de forma lógica.',
            ],

            [
                'nombre' => 'Ver usuarios',
                'modulo' => 'usuarios',
                'accion' => 'usuarios.view',
                'descripcion' => 'Permite consultar usuarios registrados.',
            ],
            [
                'nombre' => 'Crear usuarios',
                'modulo' => 'usuarios',
                'accion' => 'usuarios.create',
                'descripcion' => 'Permite crear usuarios administrativos.',
            ],
            [
                'nombre' => 'Editar usuarios',
                'modulo' => 'usuarios',
                'accion' => 'usuarios.update',
                'descripcion' => 'Permite actualizar información de usuarios.',
            ],
            [
                'nombre' => 'Asignar roles a usuarios',
                'modulo' => 'usuarios',
                'accion' => 'usuarios.roles.assign',
                'descripcion' => 'Permite asignar roles globales o por tienda.',
            ],
            [
                'nombre' => 'Cambiar estado de usuarios',
                'modulo' => 'usuarios',
                'accion' => 'usuarios.toggle',
                'descripcion' => 'Permite activar o desactivar usuarios.',
            ],
            [
                'nombre' => 'Eliminar usuarios',
                'modulo' => 'usuarios',
                'accion' => 'usuarios.delete',
                'descripcion' => 'Permite eliminar usuarios de forma lógica.',
            ],

            [
                'nombre' => 'Ver productos',
                'modulo' => 'productos',
                'accion' => 'productos.view',
                'descripcion' => 'Permite consultar productos.',
            ],
            [
                'nombre' => 'Crear productos',
                'modulo' => 'productos',
                'accion' => 'productos.create',
                'descripcion' => 'Permite registrar productos.',
            ],
            [
                'nombre' => 'Editar productos',
                'modulo' => 'productos',
                'accion' => 'productos.update',
                'descripcion' => 'Permite actualizar productos.',
            ],
            [
                'nombre' => 'Eliminar productos',
                'modulo' => 'productos',
                'accion' => 'productos.delete',
                'descripcion' => 'Permite eliminar productos de forma lógica.',
            ],

            [
                'nombre' => 'Ver inventario',
                'modulo' => 'inventario',
                'accion' => 'inventario.view',
                'descripcion' => 'Permite consultar existencias de inventario.',
            ],
            [
                'nombre' => 'Mover inventario',
                'modulo' => 'inventario',
                'accion' => 'inventario.move',
                'descripcion' => 'Permite registrar entradas, salidas y ajustes de inventario.',
            ],
            [
                'nombre' => 'Ver alertas de stock',
                'modulo' => 'inventario',
                'accion' => 'inventario.alerts',
                'descripcion' => 'Permite consultar alertas de stock mínimo.',
            ],

            [
                'nombre' => 'Ver ventas',
                'modulo' => 'ventas',
                'accion' => 'ventas.view',
                'descripcion' => 'Permite consultar ventas.',
            ],
            [
                'nombre' => 'Crear ventas',
                'modulo' => 'ventas',
                'accion' => 'ventas.create',
                'descripcion' => 'Permite registrar ventas.',
            ],
            [
                'nombre' => 'Anular ventas',
                'modulo' => 'ventas',
                'accion' => 'ventas.cancel',
                'descripcion' => 'Permite anular ventas bajo reglas del sistema.',
            ],

            [
                'nombre' => 'Ver caja',
                'modulo' => 'caja',
                'accion' => 'caja.view',
                'descripcion' => 'Permite consultar caja y movimientos.',
            ],
            [
                'nombre' => 'Gestionar caja',
                'modulo' => 'caja',
                'accion' => 'caja.manage',
                'descripcion' => 'Permite abrir, cerrar y registrar movimientos de caja.',
            ],

            [
                'nombre' => 'Ver reportes',
                'modulo' => 'reportes',
                'accion' => 'reportes.view',
                'descripcion' => 'Permite consultar reportes.',
            ],
            [
                'nombre' => 'Exportar reportes',
                'modulo' => 'reportes',
                'accion' => 'reportes.export',
                'descripcion' => 'Permite exportar reportes en PDF, Excel o CSV.',
            ],

            [
                'nombre' => 'Ver empleados',
                'modulo' => 'rrhh',
                'accion' => 'empleados.view',
                'descripcion' => 'Permite consultar empleados.',
            ],
            [
                'nombre' => 'Gestionar empleados',
                'modulo' => 'rrhh',
                'accion' => 'empleados.manage',
                'descripcion' => 'Permite crear y actualizar empleados.',
            ],
            [
                'nombre' => 'Ver nómina',
                'modulo' => 'nomina',
                'accion' => 'nomina.view',
                'descripcion' => 'Permite consultar nómina.',
            ],
            [
                'nombre' => 'Gestionar nómina',
                'modulo' => 'nomina',
                'accion' => 'nomina.manage',
                'descripcion' => 'Permite procesar y administrar nómina.',
            ],

            [
                'nombre' => 'Ver catálogo cliente',
                'modulo' => 'catalogo',
                'accion' => 'catalogo.view',
                'descripcion' => 'Permite consultar catálogo público o de cliente.',
            ],
            [
                'nombre' => 'Gestionar pedidos propios',
                'modulo' => 'pedidos',
                'accion' => 'pedidos.own.manage',
                'descripcion' => 'Permite al cliente gestionar sus propios pedidos.',
            ],
            [
                'nombre' => 'Gestionar perfil propio',
                'modulo' => 'perfil',
                'accion' => 'perfil.own.manage',
                'descripcion' => 'Permite al usuario gestionar su propio perfil.',
            ],
            [
                'nombre' => 'Calificar productos',
                'modulo' => 'feedback',
                'accion' => 'feedback.create',
                'descripcion' => 'Permite crear reseñas o calificaciones.',
            ],

            [
                'nombre' => 'Ver auditoría',
                'modulo' => 'auditoria',
                'accion' => 'auditoria.view',
                'descripcion' => 'Permite consultar trazabilidad y logs del sistema.',
            ],
            [
                'nombre' => 'Gestionar notificaciones',
                'modulo' => 'notificaciones',
                'accion' => 'notificaciones.manage',
                'descripcion' => 'Permite gestionar alertas y notificaciones internas.',
            ],
            [
                'nombre' => 'Gestionar respaldos',
                'modulo' => 'sistema',
                'accion' => 'backups.manage',
                'descripcion' => 'Permite gestionar respaldos y tareas automáticas.',
            ],
        ];
    }

    private function matrizRolesPermisos(): array
    {
        $todos = array_column($this->permisosBase(), 'accion');

        return [
            'Superadministrador' => $todos,

            'Sistema' => [
                'dashboard.view',
                'notificaciones.manage',
                'backups.manage',
                'inventario.alerts',
            ],

            'Administrador de Tienda' => [
                'dashboard.view',
                'productos.view',
                'productos.create',
                'productos.update',
                'productos.delete',
                'inventario.view',
                'inventario.move',
                'inventario.alerts',
                'ventas.view',
                'ventas.create',
                'ventas.cancel',
                'caja.view',
                'caja.manage',
                'reportes.view',
                'reportes.export',
                'empleados.view',
                'empleados.manage',
                'usuarios.view',
            ],

            'Supervisor' => [
                'dashboard.view',
                'productos.view',
                'inventario.view',
                'inventario.alerts',
                'ventas.view',
                'ventas.create',
                'ventas.cancel',
                'caja.view',
                'caja.manage',
                'reportes.view',
            ],

            'Vendedor' => [
                'dashboard.view',
                'productos.view',
                'inventario.view',
                'ventas.view',
                'ventas.create',
                'caja.view',
                'caja.manage',
            ],

            'Bodeguero' => [
                'dashboard.view',
                'productos.view',
                'inventario.view',
                'inventario.move',
                'inventario.alerts',
            ],

            'Reportero' => [
                'dashboard.view',
                'reportes.view',
                'reportes.export',
                'ventas.view',
                'inventario.view',
            ],

            'Nómina y RRHH' => [
                'dashboard.view',
                'empleados.view',
                'empleados.manage',
                'nomina.view',
                'nomina.manage',
                'reportes.view',
            ],

            'Cliente' => [
                'dashboard.view',
                'catalogo.view',
                'pedidos.own.manage',
                'perfil.own.manage',
                'feedback.create',
            ],
        ];
    }
}