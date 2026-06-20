<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Auditoria
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =========================================================================
    // Listar registros con filtros y paginación
    // =========================================================================

    public function listar(
        ?int    $tiendaId   = null,
        ?int    $usuarioId  = null,
        ?string $tabla      = null,
        ?string $accion     = null,
        ?string $desde      = null,
        ?string $hasta      = null,
        int     $limit      = 50,
        int     $offset     = 0
    ): array {
        $sql = "
            SELECT
                al.id,
                al.tabla,
                al.accion,
                al.registro_id,
                al.datos_antes,
                al.datos_despues,
                al.ip_address,
                al.created_at,
                CONCAT(u.nombre, ' ', u.apellido) AS usuario_nombre,
                u.email                           AS usuario_email,
                t.nombre                             AS tienda_nombre
            FROM audit_log al
            LEFT JOIN usuarios u ON u.id = al.usuario_id
            LEFT JOIN tiendas  t ON t.id = al.tienda_id
            WHERE 1 = 1
        ";

        $params = [];

        if ($tiendaId !== null) {
            $sql .= " AND al.tienda_id = :tienda_id";
            $params[':tienda_id'] = $tiendaId;
        }

        if ($usuarioId !== null) {
            $sql .= " AND al.usuario_id = :usuario_id";
            $params[':usuario_id'] = $usuarioId;
        }

        if ($tabla !== null && $tabla !== '') {
            $sql .= " AND al.tabla = :tabla";
            $params[':tabla'] = $tabla;
        }

        if ($accion !== null && $accion !== '') {
            $sql .= " AND al.accion = :accion";
            $params[':accion'] = $accion;
        }

        if ($desde !== null && $desde !== '') {
            $sql .= " AND DATE(al.created_at) >= :desde";
            $params[':desde'] = $desde;
        }

        if ($hasta !== null && $hasta !== '') {
            $sql .= " AND DATE(al.created_at) <= :hasta";
            $params[':hasta'] = $hasta;
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // =========================================================================
    // Contar total (para paginación)
    // =========================================================================

    public function contar(
        ?int    $tiendaId  = null,
        ?int    $usuarioId = null,
        ?string $tabla     = null,
        ?string $accion    = null,
        ?string $desde     = null,
        ?string $hasta     = null
    ): int {
        $sql = "SELECT COUNT(*) FROM audit_log al WHERE 1 = 1";
        $params = [];

        if ($tiendaId !== null) {
            $sql .= " AND al.tienda_id = :tienda_id";
            $params[':tienda_id'] = $tiendaId;
        }
        if ($usuarioId !== null) {
            $sql .= " AND al.usuario_id = :usuario_id";
            $params[':usuario_id'] = $usuarioId;
        }
        if ($tabla !== null && $tabla !== '') {
            $sql .= " AND al.tabla = :tabla";
            $params[':tabla'] = $tabla;
        }
        if ($accion !== null && $accion !== '') {
            $sql .= " AND al.accion = :accion";
            $params[':accion'] = $accion;
        }
        if ($desde !== null && $desde !== '') {
            $sql .= " AND DATE(al.created_at) >= :desde";
            $params[':desde'] = $desde;
        }
        if ($hasta !== null && $hasta !== '') {
            $sql .= " AND DATE(al.created_at) <= :hasta";
            $params[':hasta'] = $hasta;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    // =========================================================================
    // Tablas distintas registradas (para filtro)
    // =========================================================================

    public function tablasDistintas(): array
    {
        $stmt = $this->db->query(
            "SELECT DISTINCT tabla FROM audit_log ORDER BY tabla ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
