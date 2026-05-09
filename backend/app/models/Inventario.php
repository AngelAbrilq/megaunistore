<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Inventario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function listar(?int $tiendaId = null): array
    {
        if ($tiendaId === null) {
            $sql = "
                SELECT
                    i.id,
                    i.tienda_id,
                    i.producto_id,
                    i.cantidad,
                    i.cantidad_minima,
                    i.cantidad_maxima,
                    i.ubicacion,
                    i.updated_at,
                    t.nombre AS tienda_nombre,
                    p.nombre AS producto_nombre,
                    p.codigo_barras,
                    p.estado AS producto_estado,
                    c.nombre AS categoria_nombre,
                    u.nombre AS unidad_nombre,
                    u.simbolo AS unidad_simbolo
                FROM inventario i
                INNER JOIN tiendas t ON t.id = i.tienda_id
                INNER JOIN productos p ON p.id = i.producto_id
                LEFT JOIN categorias c ON c.id = p.categoria_id
                LEFT JOIN unidades_medida u ON u.id = p.unidad_medida_id
                WHERE p.deleted_at IS NULL
                ORDER BY t.nombre ASC, p.nombre ASC
            ";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        }

        $sql = "
            SELECT
                i.id,
                i.tienda_id,
                i.producto_id,
                i.cantidad,
                i.cantidad_minima,
                i.cantidad_maxima,
                i.ubicacion,
                i.updated_at,
                t.nombre AS tienda_nombre,
                p.nombre AS producto_nombre,
                p.codigo_barras,
                p.estado AS producto_estado,
                c.nombre AS categoria_nombre,
                u.nombre AS unidad_nombre,
                u.simbolo AS unidad_simbolo
            FROM inventario i
            INNER JOIN tiendas t ON t.id = i.tienda_id
            INNER JOIN productos p ON p.id = i.producto_id
            LEFT JOIN categorias c ON c.id = p.categoria_id
            LEFT JOIN unidades_medida u ON u.id = p.unidad_medida_id
            WHERE p.deleted_at IS NULL
              AND i.tienda_id = :tienda_id
            ORDER BY p.nombre ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tienda_id' => $tiendaId,
        ]);

        return $stmt->fetchAll();
    }

    public function listarAlertas(?int $tiendaId = null): array
    {
        if ($tiendaId === null) {
            $sql = "
                SELECT
                    i.id,
                    i.tienda_id,
                    i.producto_id,
                    i.cantidad,
                    i.cantidad_minima,
                    i.cantidad_maxima,
                    i.ubicacion,
                    i.updated_at,
                    t.nombre AS tienda_nombre,
                    p.nombre AS producto_nombre,
                    u.simbolo AS unidad_simbolo
                FROM inventario i
                INNER JOIN tiendas t ON t.id = i.tienda_id
                INNER JOIN productos p ON p.id = i.producto_id
                LEFT JOIN unidades_medida u ON u.id = p.unidad_medida_id
                WHERE p.deleted_at IS NULL
                  AND i.cantidad <= i.cantidad_minima
                ORDER BY t.nombre ASC, p.nombre ASC
            ";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        }

        $sql = "
            SELECT
                i.id,
                i.tienda_id,
                i.producto_id,
                i.cantidad,
                i.cantidad_minima,
                i.cantidad_maxima,
                i.ubicacion,
                i.updated_at,
                t.nombre AS tienda_nombre,
                p.nombre AS producto_nombre,
                u.simbolo AS unidad_simbolo
            FROM inventario i
            INNER JOIN tiendas t ON t.id = i.tienda_id
            INNER JOIN productos p ON p.id = i.producto_id
            LEFT JOIN unidades_medida u ON u.id = p.unidad_medida_id
            WHERE p.deleted_at IS NULL
              AND i.cantidad <= i.cantidad_minima
              AND i.tienda_id = :tienda_id
            ORDER BY p.nombre ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tienda_id' => $tiendaId,
        ]);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT
                i.id,
                i.tienda_id,
                i.producto_id,
                i.cantidad,
                i.cantidad_minima,
                i.cantidad_maxima,
                i.ubicacion,
                i.updated_at,
                t.nombre AS tienda_nombre,
                p.nombre AS producto_nombre,
                p.codigo_barras,
                u.simbolo AS unidad_simbolo
            FROM inventario i
            INNER JOIN tiendas t ON t.id = i.tienda_id
            INNER JOIN productos p ON p.id = i.producto_id
            LEFT JOIN unidades_medida u ON u.id = p.unidad_medida_id
            WHERE i.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id,
        ]);

        $inventario = $stmt->fetch();

        return $inventario ?: null;
    }

    public function buscarPorTiendaProducto(int $tiendaId, int $productoId): ?array
    {
        $sql = "
            SELECT
                id,
                tienda_id,
                producto_id,
                cantidad,
                cantidad_minima,
                cantidad_maxima,
                ubicacion,
                updated_at
            FROM inventario
            WHERE tienda_id = :tienda_id
              AND producto_id = :producto_id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tienda_id' => $tiendaId,
            ':producto_id' => $productoId,
        ]);

        $inventario = $stmt->fetch();

        return $inventario ?: null;
    }

    public function productoPerteneceATienda(int $productoId, int $tiendaId): bool
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM tiendas_productos tp
            INNER JOIN productos p ON p.id = tp.producto_id
            INNER JOIN tiendas t ON t.id = tp.tienda_id
            WHERE tp.producto_id = :producto_id
              AND tp.tienda_id = :tienda_id
              AND tp.estado = 1
              AND p.deleted_at IS NULL
              AND p.estado = 1
              AND t.deleted_at IS NULL
              AND t.estado = 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':producto_id' => $productoId,
            ':tienda_id' => $tiendaId,
        ]);

        $resultado = $stmt->fetch();

        return (int) $resultado['total'] > 0;
    }

public function productosAsociadosATiendas(?int $tiendaId = null): array
{
    if ($tiendaId === null) {
        $sql = "
            SELECT DISTINCT
                p.id,
                p.nombre,
                p.codigo_barras
            FROM productos p
            INNER JOIN tiendas_productos tp ON tp.producto_id = p.id
            WHERE p.deleted_at IS NULL
              AND p.estado = 1
              AND tp.estado = 1
            ORDER BY p.nombre ASC
        ";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    $sql = "
        SELECT DISTINCT
            p.id,
            p.nombre,
            p.codigo_barras
        FROM productos p
        INNER JOIN tiendas_productos tp ON tp.producto_id = p.id
        WHERE p.deleted_at IS NULL
          AND p.estado = 1
          AND tp.estado = 1
          AND tp.tienda_id = :tienda_id
        ORDER BY p.nombre ASC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        ':tienda_id' => $tiendaId,
    ]);

    return $stmt->fetchAll();
}

    public function crearOActualizar(array $datos): int
    {
        $existente = $this->buscarPorTiendaProducto(
            (int) $datos['tienda_id'],
            (int) $datos['producto_id']
        );

        if ($existente !== null) {
            $sql = "
                UPDATE inventario
                SET
                    cantidad = :cantidad,
                    cantidad_minima = :cantidad_minima,
                    cantidad_maxima = :cantidad_maxima,
                    ubicacion = :ubicacion
                WHERE id = :id
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id' => (int) $existente['id'],
                ':cantidad' => $datos['cantidad'],
                ':cantidad_minima' => $datos['cantidad_minima'],
                ':cantidad_maxima' => $datos['cantidad_maxima'],
                ':ubicacion' => $datos['ubicacion'],
            ]);

            return (int) $existente['id'];
        }

        $sql = "
            INSERT INTO inventario (
                tienda_id,
                producto_id,
                cantidad,
                cantidad_minima,
                cantidad_maxima,
                ubicacion
            ) VALUES (
                :tienda_id,
                :producto_id,
                :cantidad,
                :cantidad_minima,
                :cantidad_maxima,
                :ubicacion
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tienda_id' => $datos['tienda_id'],
            ':producto_id' => $datos['producto_id'],
            ':cantidad' => $datos['cantidad'],
            ':cantidad_minima' => $datos['cantidad_minima'],
            ':cantidad_maxima' => $datos['cantidad_maxima'],
            ':ubicacion' => $datos['ubicacion'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function registrarMovimiento(
        int $inventarioId,
        string $tipo,
        float $cantidad,
        ?string $motivo = null,
        ?int $empleadoId = null,
        ?int $refId = null,
        ?string $refTipo = null
    ): bool {
        $this->db->beginTransaction();

        try {
            $inventario = $this->buscarPorId($inventarioId);

            if ($inventario === null) {
                throw new RuntimeException('El inventario no existe.');
            }

            $cantidadActual = (float) $inventario['cantidad'];

            if ($tipo === 'entrada') {
                $nuevaCantidad = $cantidadActual + $cantidad;
            } elseif ($tipo === 'salida') {
                if ($cantidad > $cantidadActual) {
                    throw new RuntimeException('La salida no puede superar la cantidad disponible.');
                }

                $nuevaCantidad = $cantidadActual - $cantidad;
            } elseif ($tipo === 'ajuste') {
                $nuevaCantidad = $cantidad;
            } else {
                throw new RuntimeException('Tipo de movimiento inválido.');
            }

            $sqlUpdate = "
                UPDATE inventario
                SET cantidad = :cantidad
                WHERE id = :id
            ";

            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $stmtUpdate->execute([
                ':id' => $inventarioId,
                ':cantidad' => number_format($nuevaCantidad, 2, '.', ''),
            ]);

            $sqlMovimiento = "
                INSERT INTO movimientos_inventario (
                    inventario_id,
                    tipo,
                    cantidad,
                    motivo,
                    empleado_id,
                    ref_id,
                    ref_tipo
                ) VALUES (
                    :inventario_id,
                    :tipo,
                    :cantidad,
                    :motivo,
                    :empleado_id,
                    :ref_id,
                    :ref_tipo
                )
            ";

            $stmtMovimiento = $this->db->prepare($sqlMovimiento);
            $stmtMovimiento->execute([
                ':inventario_id' => $inventarioId,
                ':tipo' => $tipo,
                ':cantidad' => number_format($cantidad, 2, '.', ''),
                ':motivo' => $motivo,
                ':empleado_id' => $empleadoId,
                ':ref_id' => $refId,
                ':ref_tipo' => $refTipo,
            ]);

            $this->db->commit();

            return true;
        } catch (Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    public function listarMovimientos(?int $inventarioId = null, ?int $tiendaId = null): array
    {
        $where = [];
        $params = [];

        if ($inventarioId !== null) {
            $where[] = 'mi.inventario_id = :inventario_id';
            $params[':inventario_id'] = $inventarioId;
        }

        if ($tiendaId !== null) {
            $where[] = 'i.tienda_id = :tienda_id';
            $params[':tienda_id'] = $tiendaId;
        }

        $whereSql = '';

        if (!empty($where)) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        $sql = "
            SELECT
                mi.id,
                mi.inventario_id,
                mi.tipo,
                mi.cantidad,
                mi.motivo,
                mi.empleado_id,
                mi.ref_id,
                mi.ref_tipo,
                mi.created_at,
                i.tienda_id,
                t.nombre AS tienda_nombre,
                p.nombre AS producto_nombre
            FROM movimientos_inventario mi
            INNER JOIN inventario i ON i.id = mi.inventario_id
            INNER JOIN tiendas t ON t.id = i.tienda_id
            INNER JOIN productos p ON p.id = i.producto_id
            $whereSql
            ORDER BY mi.created_at DESC, mi.id DESC
            LIMIT 200
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}