<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Rol
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function crear(array $datos): int
    {
        $sql = "
            INSERT INTO roles (
                nombre,
                descripcion,
                nivel,
                activo
            ) VALUES (
                :nombre,
                :descripcion,
                :nivel,
                :activo
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':nombre'      => trim($datos['nombre']),
            ':descripcion' => $datos['descripcion'] ?? null,
            ':nivel'       => $datos['nivel'] ?? 1,
            ':activo'      => $datos['activo'] ?? 1,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT
                id,
                nombre,
                descripcion,
                nivel,
                activo
            FROM roles
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id,
        ]);

        $rol = $stmt->fetch();

        return $rol ?: null;
    }

    public function buscarPorNombre(string $nombre): ?array
    {
        $sql = "
            SELECT
                id,
                nombre,
                descripcion,
                nivel,
                activo
            FROM roles
            WHERE nombre = :nombre
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => trim($nombre),
        ]);

        $rol = $stmt->fetch();

        return $rol ?: null;
    }

    public function listarActivos(): array
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

    public function asegurarRolesBase(): void
    {
        $rolesBase = [
            [
                'nombre'      => 'Superadministrador',
                'descripcion' => 'Usuario raíz con control total sobre la plataforma, tiendas, usuarios, roles y configuración global.',
                'nivel'       => 1,
            ],
            [
                'nombre'      => 'Administrador de Tienda',
                'descripcion' => 'Gestiona una tienda específica, su personal, productos, inventario, ventas y reportes.',
                'nivel'       => 2,
            ],
            [
                'nombre'      => 'Supervisor',
                'descripcion' => 'Supervisa operación, cumplimiento de procesos, transacciones y actividades de la tienda.',
                'nivel'       => 3,
            ],
            [
                'nombre'      => 'Nómina y RRHH',
                'descripcion' => 'Gestiona personal, novedades laborales, pagos, liquidaciones y reportes de productividad.',
                'nivel'       => 3,
            ],
            [
                'nombre'      => 'Vendedor',
                'descripcion' => 'Registra ventas, atiende clientes, gestiona carrito, pagos y devoluciones operativas.',
                'nivel'       => 4,
            ],
            [
                'nombre'      => 'Bodeguero',
                'descripcion' => 'Gestiona inventario, entradas, salidas, ajustes de stock y preparación de pedidos.',
                'nivel'       => 4,
            ],
            [
                'nombre'      => 'Reportero',
                'descripcion' => 'Consulta, genera y exporta reportes de ventas, inventarios y desempeño operativo.',
                'nivel'       => 4,
            ],
            [
                'nombre'      => 'Cliente',
                'descripcion' => 'Usuario final que navega, compra, paga, consulta historial y califica productos.',
                'nivel'       => 5,
            ],
            [
                'nombre'      => 'Sistema',
                'descripcion' => 'Actor lógico para automatizaciones, alertas, respaldos, validaciones y eventos internos.',
                'nivel'       => 1,
            ],
        ];

        foreach ($rolesBase as $rol) {
            if ($this->buscarPorNombre($rol['nombre']) === null) {
                $this->crear([
                    'nombre'      => $rol['nombre'],
                    'descripcion' => $rol['descripcion'],
                    'nivel'       => $rol['nivel'],
                    'activo'      => 1,
                ]);
            }
        }
    }

    public function asignarRolAUsuario(int $usuarioId, int $rolId, ?int $tiendaId = null): bool
    {
        if ($this->usuarioTieneRol($usuarioId, $rolId, $tiendaId)) {
            return true;
        }

        $sql = "
            INSERT INTO usuarios_roles (
                usuario_id,
                rol_id,
                tienda_id
            ) VALUES (
                :usuario_id,
                :rol_id,
                :tienda_id
            )
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':rol_id'     => $rolId,
            ':tienda_id'  => $tiendaId,
        ]);
    }

    public function usuarioTieneRol(int $usuarioId, int $rolId, ?int $tiendaId = null): bool
    {
        if ($tiendaId === null) {
            $sql = "
                SELECT COUNT(*) AS total
                FROM usuarios_roles
                WHERE usuario_id = :usuario_id
                  AND rol_id = :rol_id
                  AND tienda_id IS NULL
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $usuarioId,
                ':rol_id'     => $rolId,
            ]);
        } else {
            $sql = "
                SELECT COUNT(*) AS total
                FROM usuarios_roles
                WHERE usuario_id = :usuario_id
                  AND rol_id = :rol_id
                  AND tienda_id = :tienda_id
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $usuarioId,
                ':rol_id'     => $rolId,
                ':tienda_id'  => $tiendaId,
            ]);
        }

        $resultado = $stmt->fetch();

        return (int) $resultado['total'] > 0;
    }

    public function obtenerRolesDeUsuario(int $usuarioId): array
    {
        $sql = "
            SELECT
                ur.id AS usuario_rol_id,
                ur.usuario_id,
                ur.tienda_id,
                r.id AS rol_id,
                r.nombre AS rol_nombre,
                r.descripcion AS rol_descripcion,
                r.nivel AS rol_nivel,
                r.activo AS rol_activo
            FROM usuarios_roles ur
            INNER JOIN roles r ON r.id = ur.rol_id
            WHERE ur.usuario_id = :usuario_id
              AND r.activo = 1
            ORDER BY r.nivel ASC, r.nombre ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuarioId,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Devuelve todos los roles de un listado de usuarios en UNA sola query.
     * Evita el N+1 en UsuarioController::index().
     * Retorna array indexado por usuario_id: [ userId => [ ...roles ] ]
     */
    public function obtenerRolesDeUsuariosBatch(array $usuarioIds): array
    {
        if (empty($usuarioIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($usuarioIds), '?'));

        $stmt = $this->db->prepare("
            SELECT
                ur.usuario_id,
                ur.tienda_id,
                r.id AS rol_id,
                r.nombre AS rol_nombre,
                r.nivel AS rol_nivel
            FROM usuarios_roles ur
            INNER JOIN roles r ON r.id = ur.rol_id
            WHERE ur.usuario_id IN ($placeholders)
              AND r.activo = 1
            ORDER BY r.nivel ASC
        ");
        $stmt->execute(array_values($usuarioIds));
        $rows = $stmt->fetchAll();

        $resultado = [];
        foreach ($rows as $row) {
            $resultado[(int) $row['usuario_id']][] = $row;
        }
        return $resultado;
    }

    public function obtenerRolPrincipalDeUsuario(int $usuarioId): ?array
    {
        $sql = "
            SELECT
                ur.id AS usuario_rol_id,
                ur.usuario_id,
                ur.tienda_id,
                r.id AS rol_id,
                r.nombre AS rol_nombre,
                r.descripcion AS rol_descripcion,
                r.nivel AS rol_nivel,
                r.activo AS rol_activo
            FROM usuarios_roles ur
            INNER JOIN roles r ON r.id = ur.rol_id
            WHERE ur.usuario_id = :usuario_id
              AND r.activo = 1
            ORDER BY r.nivel ASC, r.id ASC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuarioId,
        ]);

        $rol = $stmt->fetch();

        return $rol ?: null;
    }
}
