<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Caja
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
                    c.id,
                    c.tienda_id,
                    c.nombre,
                    c.descripcion,
                    c.estado,
                    t.nombre AS tienda_nombre
                FROM cajas c
                INNER JOIN tiendas t ON t.id = c.tienda_id
                ORDER BY t.nombre ASC, c.nombre ASC
            ";

            $stmt = $this->db->query($sql);
            $cajas = $stmt->fetchAll();
        } else {
            $sql = "
                SELECT
                    c.id,
                    c.tienda_id,
                    c.nombre,
                    c.descripcion,
                    c.estado,
                    t.nombre AS tienda_nombre
                FROM cajas c
                INNER JOIN tiendas t ON t.id = c.tienda_id
                WHERE c.tienda_id = :tienda_id
                ORDER BY c.nombre ASC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tienda_id' => $tiendaId,
            ]);

            $cajas = $stmt->fetchAll();
        }

        foreach ($cajas as &$caja) {
            $caja['abierta'] = $this->estaAbierta((int) $caja['id']);
            $caja['saldo_actual'] = $this->calcularSaldoActual((int) $caja['id']);
            $caja['ultimo_movimiento'] = $this->obtenerUltimoMovimiento((int) $caja['id']);
        }

        unset($caja);

        return $cajas;
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT
                c.id,
                c.tienda_id,
                c.nombre,
                c.descripcion,
                c.estado,
                t.nombre AS tienda_nombre
            FROM cajas c
            INNER JOIN tiendas t ON t.id = c.tienda_id
            WHERE c.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id,
        ]);

        $caja = $stmt->fetch();

        if (!$caja) {
            return null;
        }

        $caja['abierta'] = $this->estaAbierta($id);
        $caja['saldo_actual'] = $this->calcularSaldoActual($id);
        $caja['ultimo_movimiento'] = $this->obtenerUltimoMovimiento($id);

        return $caja;
    }

    public function crear(array $datos): int
    {
        $sql = "
            INSERT INTO cajas (
                tienda_id,
                nombre,
                descripcion,
                estado
            ) VALUES (
                :tienda_id,
                :nombre,
                :descripcion,
                :estado
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':tienda_id' => $datos['tienda_id'],
            ':nombre' => trim($datos['nombre']),
            ':descripcion' => $datos['descripcion'] ?? null,
            ':estado' => $datos['estado'] ?? 1,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function cambiarEstado(int $id, int $estado): bool
    {
        $sql = "
            UPDATE cajas
            SET estado = :estado
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':estado' => $estado,
        ]);
    }

    public function listarMovimientos(?int $cajaId = null, ?int $tiendaId = null): array
    {
        $where = [];
        $params = [];

        if ($cajaId !== null) {
            $where[] = 'cm.caja_id = :caja_id';
            $params[':caja_id'] = $cajaId;
        }

        if ($tiendaId !== null) {
            $where[] = 'c.tienda_id = :tienda_id';
            $params[':tienda_id'] = $tiendaId;
        }

        $whereSql = '';

        if (!empty($where)) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        $sql = "
            SELECT
                cm.id,
                cm.caja_id,
                cm.empleado_id,
                cm.tipo,
                cm.monto,
                cm.monto_real,
                cm.diferencia,
                cm.descripcion,
                cm.venta_id,
                cm.created_at,
                c.nombre AS caja_nombre,
                c.tienda_id,
                t.nombre AS tienda_nombre
            FROM cajas_movimientos cm
            INNER JOIN cajas c ON c.id = cm.caja_id
            INNER JOIN tiendas t ON t.id = c.tienda_id
            $whereSql
            ORDER BY cm.created_at DESC, cm.id DESC
            LIMIT 300
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function obtenerUltimoMovimiento(int $cajaId): ?array
    {
        $sql = "
            SELECT
                id,
                caja_id,
                tipo,
                monto,
                monto_real,
                diferencia,
                descripcion,
                venta_id,
                created_at
            FROM cajas_movimientos
            WHERE caja_id = :caja_id
            ORDER BY id DESC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':caja_id' => $cajaId,
        ]);

        $movimiento = $stmt->fetch();

        return $movimiento ?: null;
    }

    public function estaAbierta(int $cajaId): bool
    {
        $ultimoMovimiento = $this->obtenerUltimoMovimiento($cajaId);

        if ($ultimoMovimiento === null) {
            return false;
        }

        return $ultimoMovimiento['tipo'] !== 'cierre';
    }

    public function buscarCajaAbiertaPorTienda(int $tiendaId): ?array
    {
        $cajas = $this->listar($tiendaId);

        foreach ($cajas as $caja) {
            if ((int) $caja['estado'] === 1 && (bool) $caja['abierta'] === true) {
                return $caja;
            }
        }

        return null;
    }

    public function registrarApertura(int $cajaId, float $montoInicial, ?string $descripcion = null): bool
    {
        if ($this->estaAbierta($cajaId)) {
            throw new RuntimeException('La caja ya está abierta.');
        }

        return $this->registrarMovimiento(
            $cajaId,
            'apertura',
            $montoInicial,
            null,
            null,
            null,
            $descripcion ?? 'Apertura de caja'
        );
    }

    public function registrarCierre(int $cajaId, float $montoReal, ?string $descripcion = null): bool
    {
        if (!$this->estaAbierta($cajaId)) {
            throw new RuntimeException('La caja no está abierta.');
        }

        $saldoSistema = $this->calcularSaldoActual($cajaId);
        $diferencia = $montoReal - $saldoSistema;

        return $this->registrarMovimiento(
            $cajaId,
            'cierre',
            $saldoSistema,
            $montoReal,
            $diferencia,
            null,
            $descripcion ?? 'Cierre de caja'
        );
    }

    public function registrarIngresoManual(int $cajaId, float $monto, ?string $descripcion = null): bool
    {
        if (!$this->estaAbierta($cajaId)) {
            throw new RuntimeException('La caja debe estar abierta para registrar ingresos.');
        }

        return $this->registrarMovimiento(
            $cajaId,
            'ingreso',
            $monto,
            null,
            null,
            null,
            $descripcion ?? 'Ingreso manual'
        );
    }

    public function registrarEgresoManual(int $cajaId, float $monto, ?string $descripcion = null): bool
    {
        if (!$this->estaAbierta($cajaId)) {
            throw new RuntimeException('La caja debe estar abierta para registrar egresos.');
        }

        $saldoActual = $this->calcularSaldoActual($cajaId);

        if ($monto > $saldoActual) {
            throw new RuntimeException('El egreso no puede superar el saldo actual de la caja.');
        }

        return $this->registrarMovimiento(
            $cajaId,
            'egreso',
            $monto,
            null,
            null,
            null,
            $descripcion ?? 'Egreso manual'
        );
    }

    public function registrarIngresoVenta(int $cajaId, int $ventaId, float $monto): bool
    {
        if (!$this->estaAbierta($cajaId)) {
            throw new RuntimeException('La caja debe estar abierta para registrar ingresos por venta.');
        }

        return $this->registrarMovimiento(
            $cajaId,
            'ingreso',
            $monto,
            null,
            null,
            $ventaId,
            'Ingreso por venta #' . $ventaId
        );
    }

    public function calcularSaldoActual(int $cajaId): float
    {
        $ultimaApertura = $this->obtenerUltimaApertura($cajaId);

        if ($ultimaApertura === null) {
            return 0.0;
        }

        $sql = "
            SELECT
                COALESCE(SUM(
                    CASE
                        WHEN tipo = 'apertura' THEN monto
                        WHEN tipo = 'ingreso' THEN monto
                        WHEN tipo = 'egreso' THEN -monto
                        ELSE 0
                    END
                ), 0) AS saldo
            FROM cajas_movimientos
            WHERE caja_id = :caja_id
              AND id >= :apertura_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':caja_id' => $cajaId,
            ':apertura_id' => (int) $ultimaApertura['id'],
        ]);

        $resultado = $stmt->fetch();

        return (float) $resultado['saldo'];
    }

    private function obtenerUltimaApertura(int $cajaId): ?array
    {
        $sql = "
            SELECT
                id,
                caja_id,
                tipo,
                monto,
                created_at
            FROM cajas_movimientos
            WHERE caja_id = :caja_id
              AND tipo = 'apertura'
            ORDER BY id DESC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':caja_id' => $cajaId,
        ]);

        $apertura = $stmt->fetch();

        return $apertura ?: null;
    }

    private function registrarMovimiento(
        int $cajaId,
        string $tipo,
        float $monto,
        ?float $montoReal = null,
        ?float $diferencia = null,
        ?int $ventaId = null,
        ?string $descripcion = null
    ): bool {
        $sql = "
            INSERT INTO cajas_movimientos (
                caja_id,
                empleado_id,
                tipo,
                monto,
                monto_real,
                diferencia,
                descripcion,
                venta_id
            ) VALUES (
                :caja_id,
                NULL,
                :tipo,
                :monto,
                :monto_real,
                :diferencia,
                :descripcion,
                :venta_id
            )
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':caja_id' => $cajaId,
            ':tipo' => $tipo,
            ':monto' => number_format($monto, 2, '.', ''),
            ':monto_real' => $montoReal !== null ? number_format($montoReal, 2, '.', '') : null,
            ':diferencia' => $diferencia !== null ? number_format($diferencia, 2, '.', '') : null,
            ':descripcion' => $descripcion,
            ':venta_id' => $ventaId,
        ]);
    }
}