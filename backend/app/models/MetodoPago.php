<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class MetodoPago
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =========================================================================
    // Listar todos (activos + inactivos)
    // =========================================================================

    public function listar(): array
    {
        $stmt = $this->db->query(
            "SELECT id, nombre, descripcion, activo
             FROM metodos_pago
             ORDER BY nombre ASC"
        );
        return $stmt->fetchAll();
    }

    // =========================================================================
    // Listar solo activos (usado por ventas/caja)
    // =========================================================================

    public function listarActivos(): array
    {
        $this->asegurarMetodosBase();

        $stmt = $this->db->query(
            "SELECT id, nombre, descripcion, activo
             FROM metodos_pago
             WHERE activo = 1
             ORDER BY nombre ASC"
        );
        return $stmt->fetchAll();
    }

    // =========================================================================
    // Buscar por ID
    // =========================================================================

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, nombre, descripcion, activo
             FROM metodos_pago
             WHERE id = :id
             LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // =========================================================================
    // Crear
    // =========================================================================

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO metodos_pago (nombre, descripcion, activo)
             VALUES (:nombre, :descripcion, :activo)"
        );
        $stmt->execute([
            ':nombre'      => $datos['nombre'],
            ':descripcion' => $datos['descripcion'] ?? null,
            ':activo'      => $datos['activo'] ?? 1,
        ]);
        return (int) $this->db->lastInsertId();
    }

    // =========================================================================
    // Actualizar
    // =========================================================================

    public function actualizar(int $id, array $datos): void
    {
        $stmt = $this->db->prepare(
            "UPDATE metodos_pago
             SET nombre = :nombre, descripcion = :descripcion
             WHERE id = :id"
        );
        $stmt->execute([
            ':nombre'      => $datos['nombre'],
            ':descripcion' => $datos['descripcion'] ?? null,
            ':id'          => $id,
        ]);
    }

    // =========================================================================
    // Toggle activo / inactivo
    // =========================================================================

    public function toggleEstado(int $id, int $nuevoEstado): void
    {
        $stmt = $this->db->prepare(
            "UPDATE metodos_pago SET activo = :activo WHERE id = :id"
        );
        $stmt->execute([':activo' => $nuevoEstado, ':id' => $id]);
    }

    // =========================================================================
    // Eliminar (solo si no tiene pagos asociados)
    // =========================================================================

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM pagos WHERE metodo_pago_id = :id"
        );
        $stmt->execute([':id' => $id]);
        if ((int) $stmt->fetchColumn() > 0) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM metodos_pago WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return true;
    }

    // =========================================================================
    // Verificar nombre duplicado
    // =========================================================================

    public function existeNombre(string $nombre, ?int $excluirId = null): bool
    {
        $sql    = "SELECT COUNT(*) FROM metodos_pago WHERE nombre = :nombre";
        $params = [':nombre' => $nombre];

        if ($excluirId !== null) {
            $sql .= " AND id != :excluir";
            $params[':excluir'] = $excluirId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    // =========================================================================
    // Datos para exportación (con totales de pagos)
    // =========================================================================

    public function datosExportacion(): array
    {
        $stmt = $this->db->query(
            "SELECT
                mp.id,
                mp.nombre,
                mp.descripcion,
                IF(mp.activo = 1, 'Activo', 'Inactivo') AS estado,
                COUNT(p.id)              AS total_pagos,
                COALESCE(SUM(p.monto), 0) AS total_monto
             FROM metodos_pago mp
             LEFT JOIN pagos p ON p.metodo_pago_id = mp.id
             GROUP BY mp.id, mp.nombre, mp.descripcion, mp.activo
             ORDER BY total_monto DESC"
        );
        return $stmt->fetchAll();
    }

    // =========================================================================
    // Semilla de métodos base (idempotente)
    // =========================================================================

    public function asegurarMetodosBase(): void
    {
        $metodos = [
            ['nombre' => 'Efectivo',        'descripcion' => 'Pago en efectivo en punto de venta.'],
            ['nombre' => 'Transferencia',   'descripcion' => 'Pago por transferencia bancaria.'],
            ['nombre' => 'Tarjeta débito',  'descripcion' => 'Pago con tarjeta débito.'],
            ['nombre' => 'Tarjeta crédito', 'descripcion' => 'Pago con tarjeta crédito.'],
        ];

        foreach ($metodos as $m) {
            if (!$this->existeNombre($m['nombre'])) {
                $this->crear($m);
            }
        }
    }
}
