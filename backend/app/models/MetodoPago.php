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

    public function listarActivos(): array
    {
        $this->asegurarMetodosBase();

        $sql = "
            SELECT
                id,
                nombre,
                descripcion,
                activo
            FROM metodos_pago
            WHERE activo = 1
            ORDER BY nombre ASC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT
                id,
                nombre,
                descripcion,
                activo
            FROM metodos_pago
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id,
        ]);

        $metodo = $stmt->fetch();

        return $metodo ?: null;
    }

    public function asegurarMetodosBase(): void
    {
        $metodos = [
            [
                'nombre' => 'Efectivo',
                'descripcion' => 'Pago en efectivo en punto de venta.',
            ],
            [
                'nombre' => 'Transferencia',
                'descripcion' => 'Pago por transferencia bancaria.',
            ],
            [
                'nombre' => 'Tarjeta débito',
                'descripcion' => 'Pago con tarjeta débito.',
            ],
            [
                'nombre' => 'Tarjeta crédito',
                'descripcion' => 'Pago con tarjeta crédito.',
            ],
        ];

        foreach ($metodos as $metodo) {
            if (!$this->existeNombre($metodo['nombre'])) {
                $this->crear($metodo);
            }
        }
    }

    private function existeNombre(string $nombre): bool
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM metodos_pago
            WHERE nombre = :nombre
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
        ]);

        $resultado = $stmt->fetch();

        return (int) $resultado['total'] > 0;
    }

    private function crear(array $datos): int
    {
        $sql = "
            INSERT INTO metodos_pago (
                nombre,
                descripcion,
                activo
            ) VALUES (
                :nombre,
                :descripcion,
                1
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $datos['nombre'],
            ':descripcion' => $datos['descripcion'],
        ]);

        return (int) $this->db->lastInsertId();
    }
}