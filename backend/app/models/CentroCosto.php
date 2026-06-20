<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

/**
 * Modelo CentroCosto — centros de costo por tienda.
 * Tabla: centros_costo. Historia: CF-CON-007.
 */
final class CentroCosto
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function listar(?int $tiendaId = null): array
    {
        $sql = "
            SELECT cc.id, cc.tienda_id, cc.codigo, cc.nombre, cc.activo,
                   t.nombre AS tienda_nombre,
                   CONCAT(u.nombre, ' ', COALESCE(u.apellido, '')) AS responsable_nombre
            FROM centros_costo cc
            INNER JOIN tiendas t ON t.id = cc.tienda_id
            LEFT JOIN empleados e ON e.id = cc.responsable_id
            LEFT JOIN usuarios u  ON u.id = e.usuario_id
            WHERE 1 = 1
        ";

        $parametros = [];

        if ($tiendaId !== null) {
            $sql .= " AND cc.tienda_id = :tienda_id";
            $parametros[':tienda_id'] = $tiendaId;
        }

        $sql .= " ORDER BY cc.codigo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM centros_costo WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO centros_costo (tienda_id, codigo, nombre, responsable_id, activo)
            VALUES (:tienda_id, :codigo, :nombre, :responsable_id, :activo)
        ");
        $stmt->execute([
            ':tienda_id'      => $datos['tienda_id'],
            ':codigo'         => $datos['codigo'],
            ':nombre'         => $datos['nombre'],
            ':responsable_id' => $datos['responsable_id'],
            ':activo'         => $datos['activo'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $stmt = $this->db->prepare("
            UPDATE centros_costo
            SET codigo = :codigo, nombre = :nombre, responsable_id = :responsable_id
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id'             => $id,
            ':codigo'         => $datos['codigo'],
            ':nombre'         => $datos['nombre'],
            ':responsable_id' => $datos['responsable_id'],
        ]);
    }

    public function cambiarEstado(int $id, int $activo): bool
    {
        $stmt = $this->db->prepare("UPDATE centros_costo SET activo = :activo WHERE id = :id");

        return $stmt->execute([':id' => $id, ':activo' => $activo]);
    }
}
