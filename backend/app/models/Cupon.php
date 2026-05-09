<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Cupon
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =========================================================================
    // Consultas públicas
    // =========================================================================

    public function listar(?int $tiendaId = null): array
    {
        if ($tiendaId === null) {
            $sql = "
                SELECT
                    c.id,
                    c.codigo,
                    c.descripcion,
                    c.tipo_descuento,
                    c.valor_descuento,
                    c.descuento_maximo,
                    c.monto_minimo,
                    c.fecha_inicio,
                    c.fecha_fin,
                    c.usos_maximos,
                    c.usos_actuales,
                    c.activo,
                    c.created_at,
                    t.nombre AS tienda_nombre
                FROM cupones c
                LEFT JOIN tiendas t ON t.id = c.tienda_id
                WHERE c.deleted_at IS NULL
                ORDER BY c.id DESC
            ";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        }

        $sql = "
            SELECT
                c.id,
                c.codigo,
                c.descripcion,
                c.tipo_descuento,
                c.valor_descuento,
                c.descuento_maximo,
                c.monto_minimo,
                c.fecha_inicio,
                c.fecha_fin,
                c.usos_maximos,
                c.usos_actuales,
                c.activo,
                c.created_at,
                t.nombre AS tienda_nombre
            FROM cupones c
            LEFT JOIN tiendas t ON t.id = c.tienda_id
            WHERE c.deleted_at IS NULL
              AND (c.tienda_id = :tienda_id OR c.tienda_id IS NULL)
            ORDER BY c.id DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tienda_id' => $tiendaId]);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT
                c.id,
                c.tienda_id,
                c.codigo,
                c.descripcion,
                c.tipo_descuento,
                c.valor_descuento,
                c.descuento_maximo,
                c.monto_minimo,
                c.fecha_inicio,
                c.fecha_fin,
                c.usos_maximos,
                c.usos_actuales,
                c.activo,
                c.deleted_at,
                c.created_at,
                c.updated_at,
                t.nombre AS tienda_nombre
            FROM cupones c
            LEFT JOIN tiendas t ON t.id = c.tienda_id
            WHERE c.id = :id
              AND c.deleted_at IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        $cupon = $stmt->fetch();

        return $cupon ?: null;
    }

    public function buscarPorCodigo(string $codigo, ?int $tiendaId = null): ?array
    {
        $sql = "
            SELECT
                c.id,
                c.tienda_id,
                c.codigo,
                c.descripcion,
                c.tipo_descuento,
                c.valor_descuento,
                c.descuento_maximo,
                c.monto_minimo,
                c.fecha_inicio,
                c.fecha_fin,
                c.usos_maximos,
                c.usos_actuales,
                c.activo
            FROM cupones c
            WHERE c.codigo = :codigo
              AND c.deleted_at IS NULL
              AND c.activo = 1
              AND (c.tienda_id = :tienda_id OR c.tienda_id IS NULL)
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':codigo' => $codigo,
            ':tienda_id' => $tiendaId,
        ]);

        $cupon = $stmt->fetch();

        return $cupon ?: null;
    }

    public function validarCupon(string $codigo, float $subtotal, ?int $tiendaId = null): array
    {
        $cupon = $this->buscarPorCodigo($codigo, $tiendaId);

        if ($cupon === null) {
            return [
                'valido' => false,
                'mensaje' => 'El cupón no existe o no está activo.',
            ];
        }

        // Validar fechas
        $ahora = date('Y-m-d H:i:s');

        if ($cupon['fecha_inicio'] !== null && $ahora < $cupon['fecha_inicio']) {
            return [
                'valido' => false,
                'mensaje' => 'El cupón aún no está vigente.',
            ];
        }

        if ($cupon['fecha_fin'] !== null && $ahora > $cupon['fecha_fin']) {
            return [
                'valido' => false,
                'mensaje' => 'El cupón ha expirado.',
            ];
        }

        // Validar usos
        if ($cupon['usos_maximos'] !== null && (int) $cupon['usos_actuales'] >= (int) $cupon['usos_maximos']) {
            return [
                'valido' => false,
                'mensaje' => 'El cupón ha alcanzado su límite de usos.',
            ];
        }

        // Validar monto mínimo
        if ($cupon['monto_minimo'] !== null && $subtotal < (float) $cupon['monto_minimo']) {
            return [
                'valido' => false,
                'mensaje' => 'El monto mínimo para usar este cupón es $' . number_format((float) $cupon['monto_minimo'], 2),
            ];
        }

        // Calcular descuento
        $descuento = 0.0;

        if ($cupon['tipo_descuento'] === 'porcentaje') {
            $descuento = $subtotal * ((float) $cupon['valor_descuento'] / 100);

            if ($cupon['descuento_maximo'] !== null && $descuento > (float) $cupon['descuento_maximo']) {
                $descuento = (float) $cupon['descuento_maximo'];
            }
        } else {
            $descuento = (float) $cupon['valor_descuento'];
        }

        return [
            'valido' => true,
            'cupon_id' => (int) $cupon['id'],
            'descuento' => $descuento,
            'mensaje' => 'Cupón aplicado correctamente.',
        ];
    }

    // =========================================================================
    // Operaciones de escritura
    // =========================================================================

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO cupones (
                tienda_id, codigo, descripcion, tipo_descuento, valor_descuento,
                descuento_maximo, monto_minimo, fecha_inicio, fecha_fin,
                usos_maximos, activo, created_by, updated_by
            ) VALUES (
                :tienda_id, :codigo, :descripcion, :tipo_descuento, :valor_descuento,
                :descuento_maximo, :monto_minimo, :fecha_inicio, :fecha_fin,
                :usos_maximos, :activo, :created_by, :updated_by
            )
        ");

        $stmt->execute([
            ':tienda_id' => $datos['tienda_id'],
            ':codigo' => $datos['codigo'],
            ':descripcion' => $datos['descripcion'],
            ':tipo_descuento' => $datos['tipo_descuento'],
            ':valor_descuento' => $datos['valor_descuento'],
            ':descuento_maximo' => $datos['descuento_maximo'],
            ':monto_minimo' => $datos['monto_minimo'],
            ':fecha_inicio' => $datos['fecha_inicio'],
            ':fecha_fin' => $datos['fecha_fin'],
            ':usos_maximos' => $datos['usos_maximos'],
            ':activo' => $datos['activo'],
            ':created_by' => $datos['created_by'],
            ':updated_by' => $datos['updated_by'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $stmt = $this->db->prepare("
            UPDATE cupones
            SET tienda_id = :tienda_id,
                codigo = :codigo,
                descripcion = :descripcion,
                tipo_descuento = :tipo_descuento,
                valor_descuento = :valor_descuento,
                descuento_maximo = :descuento_maximo,
                monto_minimo = :monto_minimo,
                fecha_inicio = :fecha_inicio,
                fecha_fin = :fecha_fin,
                usos_maximos = :usos_maximos,
                activo = :activo,
                updated_by = :updated_by
            WHERE id = :id
              AND deleted_at IS NULL
        ");

        return $stmt->execute([
            ':id' => $id,
            ':tienda_id' => $datos['tienda_id'],
            ':codigo' => $datos['codigo'],
            ':descripcion' => $datos['descripcion'],
            ':tipo_descuento' => $datos['tipo_descuento'],
            ':valor_descuento' => $datos['valor_descuento'],
            ':descuento_maximo' => $datos['descuento_maximo'],
            ':monto_minimo' => $datos['monto_minimo'],
            ':fecha_inicio' => $datos['fecha_inicio'],
            ':fecha_fin' => $datos['fecha_fin'],
            ':usos_maximos' => $datos['usos_maximos'],
            ':activo' => $datos['activo'],
            ':updated_by' => $datos['updated_by'],
        ]);
    }

    public function eliminar(int $id, ?int $usuarioId = null): bool
    {
        $stmt = $this->db->prepare("
            UPDATE cupones
            SET deleted_at = NOW(), updated_by = :updated_by
            WHERE id = :id AND deleted_at IS NULL
        ");

        return $stmt->execute([
            ':id' => $id,
            ':updated_by' => $usuarioId,
        ]);
    }

    public function incrementarUsos(int $cuponId): void
    {
        $stmt = $this->db->prepare("
            UPDATE cupones
            SET usos_actuales = usos_actuales + 1
            WHERE id = :id
        ");

        $stmt->execute([':id' => $cuponId]);
    }

    public function decrementarUsos(int $cuponId): void
    {
        $stmt = $this->db->prepare("
            UPDATE cupones
            SET usos_actuales = GREATEST(0, usos_actuales - 1)
            WHERE id = :id
        ");

        $stmt->execute([':id' => $cuponId]);
    }
}
