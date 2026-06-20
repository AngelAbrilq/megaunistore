<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

/**
 * Modelo AsientoContable — asientos con partida doble.
 * Tablas: asientos_contables, asientos_detalle.
 * Historias: CF-CON-002, CF-CON-003, CF-CON-009, CF-CON-010, CF-CON-011, CF-CON-012.
 */
final class AsientoContable
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function listar(?int $tiendaId = null, ?int $periodoId = null): array
    {
        $sql = "
            SELECT a.id, a.tienda_id, a.numero, a.fecha, a.concepto,
                   a.tipo_origen, a.total_debito, a.total_credito, a.estado,
                   t.nombre AS tienda_nombre
            FROM asientos_contables a
            INNER JOIN tiendas t ON t.id = a.tienda_id
            WHERE a.deleted_at IS NULL
        ";

        $parametros = [];

        if ($tiendaId !== null) {
            $sql .= " AND a.tienda_id = :tienda_id";
            $parametros[':tienda_id'] = $tiendaId;
        }

        if ($periodoId !== null) {
            $sql .= " AND a.periodo_id = :periodo_id";
            $parametros[':periodo_id'] = $periodoId;
        }

        $sql .= " ORDER BY a.fecha DESC, a.id DESC LIMIT 500";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, t.nombre AS tienda_nombre
            FROM asientos_contables a
            INNER JOIN tiendas t ON t.id = a.tienda_id
            WHERE a.id = :id AND a.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function obtenerDetalle(int $asientoId): array
    {
        $stmt = $this->db->prepare("
            SELECT d.id, d.cuenta_id, d.descripcion, d.debito, d.credito,
                   c.codigo AS cuenta_codigo, c.nombre AS cuenta_nombre,
                   cc.nombre AS centro_nombre
            FROM asientos_detalle d
            INNER JOIN cuentas_contables c ON c.id = d.cuenta_id
            LEFT JOIN centros_costo cc ON cc.id = d.centro_costo_id
            WHERE d.asiento_id = :asiento_id
            ORDER BY d.id ASC
        ");
        $stmt->execute([':asiento_id' => $asientoId]);

        return $stmt->fetchAll();
    }

    /**
     * Crea un asiento con sus líneas validando la partida doble
     * (débitos = créditos) dentro de una transacción.
     *
     * @param array $datos  tienda_id, periodo_id, fecha, concepto, tipo_origen, origen_id, empleado_id, estado
     * @param array $lineas cada una: cuenta_id, descripcion, debito, credito, centro_costo_id
     */
    public function crear(array $datos, array $lineas): int
    {
        $totalDebito  = 0.0;
        $totalCredito = 0.0;

        foreach ($lineas as $linea) {
            $totalDebito  += (float) ($linea['debito'] ?? 0);
            $totalCredito += (float) ($linea['credito'] ?? 0);
        }

        if (abs($totalDebito - $totalCredito) > 0.009) {
            throw new RuntimeException('El asiento no está balanceado: débitos y créditos deben ser iguales.');
        }

        if ($totalDebito <= 0) {
            throw new RuntimeException('El asiento debe tener al menos un débito y un crédito mayores que cero.');
        }

        $this->db->beginTransaction();

        try {
            $numero = $this->siguienteNumero((int) $datos['tienda_id']);

            $stmt = $this->db->prepare("
                INSERT INTO asientos_contables (
                    tienda_id, periodo_id, numero, fecha, concepto,
                    tipo_origen, origen_id, empleado_id,
                    total_debito, total_credito, estado
                ) VALUES (
                    :tienda_id, :periodo_id, :numero, :fecha, :concepto,
                    :tipo_origen, :origen_id, :empleado_id,
                    :total_debito, :total_credito, :estado
                )
            ");
            $stmt->execute([
                ':tienda_id'     => $datos['tienda_id'],
                ':periodo_id'    => $datos['periodo_id'],
                ':numero'        => $numero,
                ':fecha'         => $datos['fecha'],
                ':concepto'      => $datos['concepto'],
                ':tipo_origen'   => $datos['tipo_origen'] ?? 'manual',
                ':origen_id'     => $datos['origen_id'] ?? null,
                ':empleado_id'   => $datos['empleado_id'] ?? null,
                ':total_debito'  => number_format($totalDebito, 2, '.', ''),
                ':total_credito' => number_format($totalCredito, 2, '.', ''),
                ':estado'        => $datos['estado'] ?? 'borrador',
            ]);

            $asientoId = (int) $this->db->lastInsertId();

            $stmtDetalle = $this->db->prepare("
                INSERT INTO asientos_detalle (
                    asiento_id, cuenta_id, descripcion, debito, credito, centro_costo_id
                ) VALUES (
                    :asiento_id, :cuenta_id, :descripcion, :debito, :credito, :centro_costo_id
                )
            ");

            foreach ($lineas as $linea) {
                $stmtDetalle->execute([
                    ':asiento_id'      => $asientoId,
                    ':cuenta_id'       => (int) $linea['cuenta_id'],
                    ':descripcion'     => $linea['descripcion'] ?? null,
                    ':debito'          => number_format((float) ($linea['debito'] ?? 0), 2, '.', ''),
                    ':credito'         => number_format((float) ($linea['credito'] ?? 0), 2, '.', ''),
                    ':centro_costo_id' => !empty($linea['centro_costo_id']) ? (int) $linea['centro_costo_id'] : null,
                ]);
            }

            $this->db->commit();

            return $asientoId;
        } catch (Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        $stmt = $this->db->prepare("
            UPDATE asientos_contables
            SET estado = :estado
            WHERE id = :id AND deleted_at IS NULL
        ");

        return $stmt->execute([':id' => $id, ':estado' => $estado]);
    }

    /**
     * Libro mayor: movimientos y saldo por cuenta en un rango de fechas (CF-CON-011).
     */
    public function libroMayor(int $cuentaId, string $desde, string $hasta, ?int $tiendaId = null): array
    {
        $sql = "
            SELECT a.fecha, a.numero, a.concepto, d.descripcion, d.debito, d.credito
            FROM asientos_detalle d
            INNER JOIN asientos_contables a ON a.id = d.asiento_id
            WHERE d.cuenta_id = :cuenta_id
              AND a.estado = 'aprobado'
              AND a.deleted_at IS NULL
              AND a.fecha BETWEEN :desde AND :hasta
        ";

        $parametros = [
            ':cuenta_id' => $cuentaId,
            ':desde'     => $desde,
            ':hasta'     => $hasta,
        ];

        if ($tiendaId !== null) {
            $sql .= " AND a.tienda_id = :tienda_id";
            $parametros[':tienda_id'] = $tiendaId;
        }

        $sql .= " ORDER BY a.fecha ASC, a.id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    /**
     * Saldos agregados por cuenta para Balance General y Estado de Resultados
     * (CF-CON-009, CF-CON-010).
     */
    public function saldosPorCuenta(string $desde, string $hasta, ?int $tiendaId = null): array
    {
        $sql = "
            SELECT c.id, c.codigo, c.nombre, c.tipo, c.naturaleza,
                   COALESCE(SUM(d.debito), 0)  AS total_debito,
                   COALESCE(SUM(d.credito), 0) AS total_credito
            FROM cuentas_contables c
            INNER JOIN asientos_detalle d   ON d.cuenta_id = c.id
            INNER JOIN asientos_contables a ON a.id = d.asiento_id
            WHERE a.estado = 'aprobado'
              AND a.deleted_at IS NULL
              AND a.fecha BETWEEN :desde AND :hasta
        ";

        $parametros = [':desde' => $desde, ':hasta' => $hasta];

        if ($tiendaId !== null) {
            $sql .= " AND a.tienda_id = :tienda_id";
            $parametros[':tienda_id'] = $tiendaId;
        }

        $sql .= " GROUP BY c.id, c.codigo, c.nombre, c.tipo, c.naturaleza ORDER BY c.codigo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    // -------------------------------------------------------------------------

    private function siguienteNumero(int $tiendaId): string
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total
            FROM asientos_contables
            WHERE tienda_id = :tienda_id
        ");
        $stmt->execute([':tienda_id' => $tiendaId]);

        $consecutivo = (int) $stmt->fetch()['total'] + 1;

        return sprintf('AS-%d-%05d', $tiendaId, $consecutivo);
    }
}
