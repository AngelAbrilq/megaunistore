<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Nomina
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // -------------------------------------------------------------------------
    // Listado de períodos
    // -------------------------------------------------------------------------

    public function listar(?int $tiendaId = null): array
    {
        if ($tiendaId === null) {
            $stmt = $this->db->query("
                SELECT n.*, t.nombre AS tienda_nombre,
                       u.nombre AS aprobado_nombre, u.apellido AS aprobado_apellido
                FROM nominas n
                INNER JOIN tiendas t ON t.id = n.tienda_id
                LEFT  JOIN usuarios u ON u.id = n.aprobado_por
                ORDER BY n.id DESC
            ");
        } else {
            $stmt = $this->db->prepare("
                SELECT n.*, t.nombre AS tienda_nombre,
                       u.nombre AS aprobado_nombre, u.apellido AS aprobado_apellido
                FROM nominas n
                INNER JOIN tiendas t ON t.id = n.tienda_id
                LEFT  JOIN usuarios u ON u.id = n.aprobado_por
                WHERE n.tienda_id = :tienda_id
                ORDER BY n.id DESC
            ");
            $stmt->execute([':tienda_id' => $tiendaId]);
        }
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT n.*, t.nombre AS tienda_nombre,
                   u.nombre AS aprobado_nombre, u.apellido AS aprobado_apellido
            FROM nominas n
            INNER JOIN tiendas t ON t.id = n.tienda_id
            LEFT  JOIN usuarios u ON u.id = n.aprobado_por
            WHERE n.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // -------------------------------------------------------------------------
    // Crear período
    // -------------------------------------------------------------------------

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO nominas (tienda_id, periodo_inicio, periodo_fin, tipo, estado)
            VALUES (:tienda_id, :periodo_inicio, :periodo_fin, :tipo, 'borrador')
        ");
        $stmt->execute([
            ':tienda_id'     => (int) $datos['tienda_id'],
            ':periodo_inicio' => $datos['periodo_inicio'],
            ':periodo_fin'    => $datos['periodo_fin'],
            ':tipo'           => $datos['tipo'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    // -------------------------------------------------------------------------
    // Calcular: genera nomina_empleado por cada empleado activo con contrato
    // -------------------------------------------------------------------------

    public function calcular(int $nominaId): int
    {
        $nomina = $this->buscarPorId($nominaId);
        if ($nomina === null) {
            return 0;
        }

        // Empleados activos con contrato vigente en la tienda
        $stmt = $this->db->prepare("
            SELECT e.id AS empleado_id,
                   c.id AS contrato_id,
                   c.salario_base,
                   c.tipo_contrato
            FROM empleados e
            INNER JOIN contratos c ON c.empleado_id = e.id
                AND c.estado = 'activo'
                AND (c.fecha_fin IS NULL OR c.fecha_fin >= :hoy)
            WHERE e.tienda_id = :tienda_id
              AND e.estado = 'activo'
              AND e.deleted_at IS NULL
        ");
        $stmt->execute([
            ':tienda_id' => $nomina['tienda_id'],
            ':hoy'       => date('Y-m-d'),
        ]);
        $empleados = $stmt->fetchAll();

        $procesados = 0;

        foreach ($empleados as $emp) {
            // Evitar duplicado
            $existe = $this->db->prepare("
                SELECT COUNT(*) AS total FROM nomina_empleado
                WHERE nomina_id = :nomina_id AND empleado_id = :empleado_id
            ");
            $existe->execute([':nomina_id' => $nominaId, ':empleado_id' => $emp['empleado_id']]);
            if ((int) $existe->fetch()['total'] > 0) {
                continue;
            }

            $salario     = (float) $emp['salario_base'];
            $devengado   = $salario;
            $deduccion   = round($salario * 0.04, 2); // salud empleado 4 %
            $deduccion  += round($salario * 0.04, 2); // pensión empleado 4 %
            $neto        = $devengado - ($deduccion);

            $ins = $this->db->prepare("
                INSERT INTO nomina_empleado
                    (nomina_id, empleado_id, contrato_id, dias_trabajados,
                     total_devengado, total_deducciones, neto_pagar, estado)
                VALUES
                    (:nomina_id, :empleado_id, :contrato_id, 30,
                     :total_devengado, :total_deducciones, :neto_pagar, 'pendiente')
            ");
            $ins->execute([
                ':nomina_id'        => $nominaId,
                ':empleado_id'      => $emp['empleado_id'],
                ':contrato_id'      => $emp['contrato_id'],
                ':total_devengado'  => $devengado,
                ':total_deducciones' => $deduccion,
                ':neto_pagar'       => $neto,
            ]);
            $procesados++;
        }

        // Actualizar totales en cabecera
        $this->recalcularTotales($nominaId);

        // Cambiar estado a calculada
        $this->db->prepare("
            UPDATE nominas SET estado = 'calculada' WHERE id = :id AND estado = 'borrador'
        ")->execute([':id' => $nominaId]);

        return $procesados;
    }

    // -------------------------------------------------------------------------
    // Aprobar
    // -------------------------------------------------------------------------

    public function aprobar(int $nominaId, int $adminId): bool
    {
        return $this->db->prepare("
            UPDATE nominas
            SET estado = 'aprobada', aprobado_por = :admin_id, aprobado_at = NOW()
            WHERE id = :id AND estado = 'calculada'
        ")->execute([':id' => $nominaId, ':admin_id' => $adminId]);
    }

    // -------------------------------------------------------------------------
    // Pagar
    // -------------------------------------------------------------------------

    public function pagar(int $nominaId): bool
    {
        $this->db->prepare("
            UPDATE nomina_empleado SET estado = 'pagado'
            WHERE nomina_id = :nomina_id
        ")->execute([':nomina_id' => $nominaId]);

        return $this->db->prepare("
            UPDATE nominas SET estado = 'pagada', pagado_at = NOW()
            WHERE id = :id AND estado = 'aprobada'
        ")->execute([':id' => $nominaId]);
    }

    // -------------------------------------------------------------------------
    // Detalle (empleados de una nómina)
    // -------------------------------------------------------------------------

    public function obtenerEmpleados(int $nominaId): array
    {
        $stmt = $this->db->prepare("
            SELECT ne.*,
                   u.nombre AS empleado_nombre, u.apellido AS empleado_apellido,
                   u.email  AS empleado_email,
                   e.codigo_empleado,
                   c.tipo_contrato, c.cargo_id
            FROM nomina_empleado ne
            INNER JOIN empleados e  ON e.id  = ne.empleado_id
            INNER JOIN usuarios  u  ON u.id  = e.usuario_id
            INNER JOIN contratos c  ON c.id  = ne.contrato_id
            WHERE ne.nomina_id = :nomina_id
            ORDER BY u.apellido ASC, u.nombre ASC
        ");
        $stmt->execute([':nomina_id' => $nominaId]);
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------------------------
    // KPIs para el dashboard
    // -------------------------------------------------------------------------

    public function kpis(?int $tiendaId = null): array
    {
        if ($tiendaId !== null) {
            $s = $this->db->prepare("
                SELECT COUNT(*) FROM nominas
                WHERE tienda_id = :tid AND estado IN ('borrador','calculada','aprobada')
            ");
            $s->execute([':tid' => $tiendaId]);
            $pendientes = (int) $s->fetchColumn();

            $s2 = $this->db->prepare("
                SELECT COALESCE(SUM(total_neto),0) FROM nominas
                WHERE tienda_id = :tid AND estado = 'pagada'
                  AND pagado_at >= DATE_FORMAT(CURDATE(),'%Y-%m-01')
                  AND pagado_at <  DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH),'%Y-%m-01')
            ");
            $s2->execute([':tid' => $tiendaId]);
            $pagoMes = (float) $s2->fetchColumn();
        } else {
            $s = $this->db->query("
                SELECT COUNT(*) FROM nominas WHERE estado IN ('borrador','calculada','aprobada')
            ");
            $pendientes = (int) $s->fetchColumn();

            $s2 = $this->db->query("
                SELECT COALESCE(SUM(total_neto),0) FROM nominas
                WHERE estado = 'pagada'
                  AND pagado_at >= DATE_FORMAT(CURDATE(),'%Y-%m-01')
                  AND pagado_at <  DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH),'%Y-%m-01')
            ");
            $pagoMes = (float) $s2->fetchColumn();
        }

        return [
            'pendientes' => $pendientes,
            'pago_mes'   => $pagoMes,
        ];
    }

    // -------------------------------------------------------------------------
    // Helper privado
    // -------------------------------------------------------------------------

    private function recalcularTotales(int $nominaId): void
    {
        $row = $this->db->prepare("
            SELECT COALESCE(SUM(total_devengado),0)  AS dev,
                   COALESCE(SUM(total_deducciones),0) AS ded,
                   COALESCE(SUM(neto_pagar),0)        AS neto
            FROM nomina_empleado
            WHERE nomina_id = :nomina_id
        ");
        $row->execute([':nomina_id' => $nominaId]);
        $totales = $row->fetch();

        $this->db->prepare("
            UPDATE nominas
            SET total_devengado   = :dev,
                total_deducciones = :ded,
                total_neto        = :neto
            WHERE id = :id
        ")->execute([
            ':dev'  => $totales['dev'],
            ':ded'  => $totales['ded'],
            ':neto' => $totales['neto'],
            ':id'   => $nominaId,
        ]);
    }
}
