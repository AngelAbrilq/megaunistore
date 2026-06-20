<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Notificacion
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =========================================================================
    // Listar con filtros y paginación
    // =========================================================================

    public function listar(
        ?int    $usuarioId = null,
        ?int    $tiendaId  = null,
        ?string $tipo      = null,
        ?int    $leida     = null,   // null=todas, 0=no leídas, 1=leídas
        int     $limit     = 50,
        int     $offset    = 0
    ): array {
        $sql = "
            SELECT
                n.id,
                n.titulo,
                n.mensaje,
                n.tipo,
                n.leida,
                n.url_accion,
                n.created_at,
                CONCAT(u.nombre, ' ', u.apellido) AS usuario_nombre,
                u.email                           AS usuario_email,
                t.nombre                          AS tienda_nombre
            FROM notificaciones n
            LEFT JOIN usuarios u ON u.id = n.usuario_id
            LEFT JOIN tiendas  t ON t.id = n.tienda_id
            WHERE 1 = 1
        ";
        $params = [];

        if ($usuarioId !== null) {
            $sql .= " AND n.usuario_id = :usuario_id";
            $params[':usuario_id'] = $usuarioId;
        }
        if ($tiendaId !== null) {
            $sql .= " AND n.tienda_id = :tienda_id";
            $params[':tienda_id'] = $tiendaId;
        }
        if ($tipo !== null && $tipo !== '') {
            $sql .= " AND n.tipo = :tipo";
            $params[':tipo'] = $tipo;
        }
        if ($leida !== null) {
            $sql .= " AND n.leida = :leida";
            $params[':leida'] = $leida;
        }

        $sql .= " ORDER BY n.created_at DESC LIMIT :limit OFFSET :offset";

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
    // Contar (para paginación)
    // =========================================================================

    public function contar(
        ?int    $usuarioId = null,
        ?int    $tiendaId  = null,
        ?string $tipo      = null,
        ?int    $leida     = null
    ): int {
        $sql    = "SELECT COUNT(*) FROM notificaciones n WHERE 1 = 1";
        $params = [];

        if ($usuarioId !== null) { $sql .= " AND n.usuario_id = :usuario_id"; $params[':usuario_id'] = $usuarioId; }
        if ($tiendaId  !== null) { $sql .= " AND n.tienda_id = :tienda_id";   $params[':tienda_id']  = $tiendaId; }
        if ($tipo !== null && $tipo !== '') { $sql .= " AND n.tipo = :tipo";   $params[':tipo']       = $tipo; }
        if ($leida !== null) { $sql .= " AND n.leida = :leida";               $params[':leida']      = $leida; }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    // =========================================================================
    // Contar no leídas (para el badge del menú)
    // =========================================================================

    public function contarNoLeidas(?int $usuarioId = null, ?int $tiendaId = null): int
    {
        return $this->contar($usuarioId, $tiendaId, null, 0);
    }

    // =========================================================================
    // Tipos distintos (para filtro)
    // =========================================================================

    public function tiposDistintos(): array
    {
        $stmt = $this->db->query(
            "SELECT DISTINCT tipo FROM notificaciones ORDER BY tipo ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // =========================================================================
    // Marcar una como leída
    // =========================================================================

    public function marcarLeida(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE notificaciones SET leida = 1 WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
    }

    // =========================================================================
    // Marcar todas como leídas (filtrado por usuario/tienda)
    // =========================================================================

    public function marcarTodasLeidas(?int $usuarioId = null, ?int $tiendaId = null): int
    {
        $sql    = "UPDATE notificaciones SET leida = 1 WHERE leida = 0";
        $params = [];

        if ($usuarioId !== null) { $sql .= " AND usuario_id = :usuario_id"; $params[':usuario_id'] = $usuarioId; }
        if ($tiendaId  !== null) { $sql .= " AND tienda_id = :tienda_id";   $params[':tienda_id']  = $tiendaId; }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    // =========================================================================
    // Eliminar una notificación
    // =========================================================================

    public function eliminar(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM notificaciones WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    // =========================================================================
    // Eliminar las leídas (limpiar historial)
    // =========================================================================

    public function eliminarLeidas(?int $tiendaId = null): int
    {
        $sql    = "DELETE FROM notificaciones WHERE leida = 1";
        $params = [];

        if ($tiendaId !== null) {
            $sql .= " AND tienda_id = :tienda_id";
            $params[':tienda_id'] = $tiendaId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    // =========================================================================
    // Datos para exportación
    // =========================================================================

    public function datosExportacion(?int $tiendaId = null): array
    {
        $sql = "
            SELECT
                n.id,
                n.titulo,
                n.mensaje,
                n.tipo,
                IF(n.leida = 1, 'Leída', 'No leída') AS estado,
                n.created_at,
                CONCAT(u.nombre, ' ', u.apellido)     AS usuario,
                t.nombre                              AS tienda
            FROM notificaciones n
            LEFT JOIN usuarios u ON u.id = n.usuario_id
            LEFT JOIN tiendas  t ON t.id = n.tienda_id
            WHERE 1 = 1
        ";
        $params = [];

        if ($tiendaId !== null) {
            $sql .= " AND n.tienda_id = :tienda_id";
            $params[':tienda_id'] = $tiendaId;
        }

        $sql .= " ORDER BY n.created_at DESC LIMIT 500";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
