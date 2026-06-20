<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Devolucion
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
                    d.id,
                    d.venta_id,
                    d.tienda_id,
                    d.motivo,
                    d.monto_devuelto,
                    d.estado,
                    d.created_at,
                    v.total AS venta_total,
                    t.nombre AS tienda_nombre,
                    u.email AS creado_por_email
                FROM devoluciones d
                INNER JOIN ventas v ON v.id = d.venta_id
                INNER JOIN tiendas t ON t.id = d.tienda_id
                LEFT JOIN usuarios u ON u.id = d.created_by
                WHERE d.deleted_at IS NULL
                ORDER BY d.id DESC
            ";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        }

        $sql = "
            SELECT
                d.id,
                d.venta_id,
                d.tienda_id,
                d.motivo,
                d.monto_devuelto,
                d.estado,
                d.created_at,
                v.total AS venta_total,
                t.nombre AS tienda_nombre,
                u.email AS creado_por_email
            FROM devoluciones d
            INNER JOIN ventas v ON v.id = d.venta_id
            INNER JOIN tiendas t ON t.id = d.tienda_id
            LEFT JOIN usuarios u ON u.id = d.created_by
            WHERE d.deleted_at IS NULL
              AND d.tienda_id = :tienda_id
            ORDER BY d.id DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tienda_id' => $tiendaId]);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT
                d.id,
                d.venta_id,
                d.tienda_id,
                d.motivo,
                d.monto_devuelto,
                d.estado,
                d.deleted_at,
                d.created_at,
                d.updated_at,
                d.created_by,
                d.updated_by,
                v.total AS venta_total,
                v.fecha AS venta_fecha,
                v.estado AS venta_estado,
                t.nombre AS tienda_nombre
            FROM devoluciones d
            INNER JOIN ventas v ON v.id = d.venta_id
            INNER JOIN tiendas t ON t.id = d.tienda_id
            WHERE d.id = :id
              AND d.deleted_at IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        $devolucion = $stmt->fetch();

        return $devolucion ?: null;
    }

    public function obtenerDetalle(int $devolucionId): array
    {
        $sql = "
            SELECT
                dd.id,
                dd.devolucion_id,
                dd.producto_id,
                dd.cantidad,
                dd.precio_unitario,
                dd.subtotal,
                p.nombre AS producto_nombre,
                p.codigo_barras
            FROM devoluciones_detalle dd
            INNER JOIN productos p ON p.id = dd.producto_id
            WHERE dd.devolucion_id = :devolucion_id
            ORDER BY dd.id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':devolucion_id' => $devolucionId]);

        return $stmt->fetchAll();
    }

    // =========================================================================
    // Operaciones de escritura
    // =========================================================================

    public function crearDevolucion(int $ventaId, array $items, string $motivo, int $usuarioId): int
    {
        $this->db->beginTransaction();

        try {
            // Obtener información de la venta
            $venta = $this->buscarVenta($ventaId);

            if ($venta === null) {
                throw new RuntimeException('La venta no existe.');
            }

            if ($venta['estado'] === 'anulada') {
                throw new RuntimeException('No se puede hacer devolución de una venta anulada.');
            }

            // Validar que la venta no tenga más de 15 días de antigüedad
            $diasDesdeVenta = (int) ceil((time() - strtotime($venta['created_at'])) / 86400);
            if ($diasDesdeVenta > 15) {
                throw new RuntimeException(
                    'No se puede procesar la devolución: la venta tiene más de 15 días de antigüedad (' . $diasDesdeVenta . ' días).'
                );
            }

            $tiendaId = (int) $venta['tienda_id'];
            $cajaAbierta = $this->buscarCajaAbiertaPorTienda($tiendaId);

            if ($cajaAbierta === null) {
                throw new RuntimeException(
                    'No hay una caja abierta para esta tienda. Abre una caja antes de procesar devoluciones.'
                );
            }

            // Validar y calcular totales
            $detalleVenta = $this->obtenerDetalleVenta($ventaId);
            $itemsCalculados = $this->validarItemsDevolucion($items, $detalleVenta, $tiendaId);

            $montoDevuelto = array_sum(array_column($itemsCalculados, 'subtotal'));

            // Crear registro de devolución
            $stmtDevolucion = $this->db->prepare("
                INSERT INTO devoluciones (
                    venta_id, tienda_id, motivo, monto_devuelto,
                    estado, created_by, updated_by
                ) VALUES (
                    :venta_id, :tienda_id, :motivo, :monto_devuelto,
                    'completada', :created_by, :updated_by
                )
            ");

            $stmtDevolucion->execute([
                ':venta_id' => $ventaId,
                ':tienda_id' => $tiendaId,
                ':motivo' => $motivo,
                ':monto_devuelto' => number_format($montoDevuelto, 2, '.', ''),
                ':created_by' => $usuarioId,
                ':updated_by' => $usuarioId,
            ]);

            $devolucionId = (int) $this->db->lastInsertId();

            // Insertar detalle de devolución
            $this->insertarDetalleDevolucion($devolucionId, $itemsCalculados);

            // Registrar movimiento de caja (egreso)
            $this->registrarMovimientoCaja(
                (int) $cajaAbierta['id'],
                'egreso',
                $montoDevuelto,
                'Devolución #' . $devolucionId . ' de venta #' . $ventaId,
                $ventaId
            );

            $this->db->commit();

            return $devolucionId;
        } catch (Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    // =========================================================================
    // Métodos privados
    // =========================================================================

    private function buscarVenta(int $ventaId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, tienda_id, total, estado, created_at
            FROM ventas
            WHERE id = :id AND deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([':id' => $ventaId]);

        $venta = $stmt->fetch();

        return $venta ?: null;
    }

    private function obtenerDetalleVenta(int $ventaId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                vd.id,
                vd.producto_id,
                vd.cantidad,
                vd.precio_unitario,
                vd.subtotal,
                p.nombre AS producto_nombre
            FROM ventas_detalle vd
            INNER JOIN productos p ON p.id = vd.producto_id
            WHERE vd.venta_id = :venta_id
        ");
        $stmt->execute([':venta_id' => $ventaId]);

        return $stmt->fetchAll();
    }

    private function validarItemsDevolucion(array $items, array $detalleVenta, int $tiendaId): array
    {
        $itemsCalculados = [];

        foreach ($items as $item) {
            $productoId = (int) $item['producto_id'];
            $cantidadDevolver = (float) $item['cantidad'];

            if ($productoId <= 0 || $cantidadDevolver <= 0) {
                throw new RuntimeException('Hay productos o cantidades inválidas en la devolución.');
            }

            // Buscar el producto en el detalle de la venta
            $itemVenta = null;

            foreach ($detalleVenta as $detalle) {
                if ((int) $detalle['producto_id'] === $productoId) {
                    $itemVenta = $detalle;
                    break;
                }
            }

            if ($itemVenta === null) {
                throw new RuntimeException('El producto no pertenece a esta venta.');
            }

            $cantidadVendida = (float) $itemVenta['cantidad'];

            if ($cantidadDevolver > $cantidadVendida) {
                throw new RuntimeException(
                    'La cantidad a devolver excede la cantidad vendida para: ' . $itemVenta['producto_nombre']
                );
            }

            // Devolver al inventario
            $inventario = $this->buscarInventario($tiendaId, $productoId);

            if ($inventario === null) {
                throw new RuntimeException(
                    'No existe inventario para devolver el producto: ' . $itemVenta['producto_nombre']
                );
            }

            $nuevaCantidad = (float) $inventario['cantidad'] + $cantidadDevolver;

            $this->actualizarCantidadInventario((int) $inventario['id'], $nuevaCantidad);
            $this->registrarMovimientoInventario(
                (int) $inventario['id'],
                'entrada',
                $cantidadDevolver,
                'Devolución de venta',
                null,
                'devoluciones'
            );

            $precioUnitario = (float) $itemVenta['precio_unitario'];
            $subtotal = $precioUnitario * $cantidadDevolver;

            $itemsCalculados[] = [
                'producto_id' => $productoId,
                'cantidad' => number_format($cantidadDevolver, 2, '.', ''),
                'precio_unitario' => number_format($precioUnitario, 2, '.', ''),
                'subtotal' => number_format($subtotal, 2, '.', ''),
            ];
        }

        return $itemsCalculados;
    }

    private function insertarDetalleDevolucion(int $devolucionId, array $items): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO devoluciones_detalle (
                devolucion_id, producto_id, cantidad, precio_unitario, subtotal
            ) VALUES (
                :devolucion_id, :producto_id, :cantidad, :precio_unitario, :subtotal
            )
        ");

        foreach ($items as $item) {
            $stmt->execute([
                ':devolucion_id' => $devolucionId,
                ':producto_id' => $item['producto_id'],
                ':cantidad' => $item['cantidad'],
                ':precio_unitario' => $item['precio_unitario'],
                ':subtotal' => $item['subtotal'],
            ]);
        }
    }

    private function buscarInventario(int $tiendaId, int $productoId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, tienda_id, producto_id, cantidad
            FROM inventario
            WHERE tienda_id = :tienda_id
              AND producto_id = :producto_id
            LIMIT 1
        ");
        $stmt->execute([':tienda_id' => $tiendaId, ':producto_id' => $productoId]);

        $inventario = $stmt->fetch();

        return $inventario ?: null;
    }

    private function actualizarCantidadInventario(int $inventarioId, float $cantidad): void
    {
        $stmt = $this->db->prepare("
            UPDATE inventario SET cantidad = :cantidad WHERE id = :id
        ");
        $stmt->execute([
            ':id' => $inventarioId,
            ':cantidad' => number_format($cantidad, 2, '.', ''),
        ]);
    }

    private function registrarMovimientoInventario(
        int $inventarioId,
        string $tipo,
        float $cantidad,
        string $motivo,
        ?int $refId = null,
        ?string $refTipo = null
    ): void {
        $stmt = $this->db->prepare("
            INSERT INTO movimientos_inventario (
                inventario_id, tipo, cantidad, motivo, empleado_id, ref_id, ref_tipo
            ) VALUES (
                :inventario_id, :tipo, :cantidad, :motivo, NULL, :ref_id, :ref_tipo
            )
        ");
        $stmt->execute([
            ':inventario_id' => $inventarioId,
            ':tipo' => $tipo,
            ':cantidad' => number_format($cantidad, 2, '.', ''),
            ':motivo' => $motivo,
            ':ref_id' => $refId,
            ':ref_tipo' => $refTipo,
        ]);
    }

    private function buscarCajaAbiertaPorTienda(int $tiendaId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT c.id, c.tienda_id, c.nombre, c.estado
            FROM cajas c
            WHERE c.tienda_id = :tienda_id
              AND c.estado = 1
              AND EXISTS (
                  SELECT 1 FROM cajas_movimientos cm_a
                  WHERE cm_a.caja_id = c.id AND cm_a.tipo = 'apertura'
              )
              AND (
                  SELECT cm_u.tipo
                  FROM cajas_movimientos cm_u
                  WHERE cm_u.caja_id = c.id
                  ORDER BY cm_u.id DESC
                  LIMIT 1
              ) <> 'cierre'
            ORDER BY c.id ASC
            LIMIT 1
        ");
        $stmt->execute([':tienda_id' => $tiendaId]);

        $caja = $stmt->fetch();

        return $caja ?: null;
    }

    private function registrarMovimientoCaja(
        int $cajaId,
        string $tipo,
        float $monto,
        string $descripcion,
        ?int $ventaId = null
    ): void {
        $stmt = $this->db->prepare("
            INSERT INTO cajas_movimientos (
                caja_id, empleado_id, tipo, monto, monto_real, diferencia, descripcion, venta_id
            ) VALUES (
                :caja_id, NULL, :tipo, :monto, NULL, NULL, :descripcion, :venta_id
            )
        ");
        $stmt->execute([
            ':caja_id' => $cajaId,
            ':tipo' => $tipo,
            ':monto' => number_format($monto, 2, '.', ''),
            ':descripcion' => $descripcion,
            ':venta_id' => $ventaId,
        ]);
    }
}
