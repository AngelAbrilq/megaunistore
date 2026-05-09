<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Venta
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
                    v.id,
                    v.tienda_id,
                    v.cliente_id,
                    v.caja_id,
                    v.fecha,
                    v.subtotal,
                    v.descuento,
                    v.impuesto,
                    v.total,
                    v.estado,
                    v.created_at,
                    t.nombre  AS tienda_nombre,
                    cj.nombre AS caja_nombre,
                    c.nombre  AS cliente_nombre,
                    c.apellido AS cliente_apellido,
                    u.email   AS creado_por_email
                FROM ventas v
                INNER JOIN tiendas t  ON t.id  = v.tienda_id
                LEFT  JOIN cajas  cj  ON cj.id = v.caja_id
                LEFT  JOIN clientes c ON c.id  = v.cliente_id
                LEFT  JOIN usuarios u ON u.id  = v.created_by
                WHERE v.deleted_at IS NULL
                ORDER BY v.id DESC
            ";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        }

        $sql = "
            SELECT
                v.id,
                v.tienda_id,
                v.cliente_id,
                v.caja_id,
                v.fecha,
                v.subtotal,
                v.descuento,
                v.impuesto,
                v.total,
                v.estado,
                v.created_at,
                t.nombre  AS tienda_nombre,
                cj.nombre AS caja_nombre,
                c.nombre  AS cliente_nombre,
                c.apellido AS cliente_apellido,
                u.email   AS creado_por_email
            FROM ventas v
            INNER JOIN tiendas t  ON t.id  = v.tienda_id
            LEFT  JOIN cajas  cj  ON cj.id = v.caja_id
            LEFT  JOIN clientes c ON c.id  = v.cliente_id
            LEFT  JOIN usuarios u ON u.id  = v.created_by
            WHERE v.deleted_at IS NULL
              AND v.tienda_id = :tienda_id
            ORDER BY v.id DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tienda_id' => $tiendaId]);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT
                v.id,
                v.tienda_id,
                v.cliente_id,
                v.empleado_id,
                v.caja_id,
                v.fecha,
                v.subtotal,
                v.descuento,
                v.impuesto,
                v.total,
                v.estado,
                v.deleted_at,
                v.created_at,
                v.updated_at,
                v.created_by,
                v.updated_by,
                t.nombre  AS tienda_nombre,
                cj.nombre AS caja_nombre,
                c.nombre  AS cliente_nombre,
                c.apellido AS cliente_apellido,
                c.email   AS cliente_email
            FROM ventas v
            INNER JOIN tiendas t  ON t.id  = v.tienda_id
            LEFT  JOIN cajas  cj  ON cj.id = v.caja_id
            LEFT  JOIN clientes c ON c.id  = v.cliente_id
            WHERE v.id = :id
              AND v.deleted_at IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        $venta = $stmt->fetch();

        return $venta ?: null;
    }

    public function obtenerDetalle(int $ventaId): array
    {
        $sql = "
            SELECT
                vd.id,
                vd.venta_id,
                vd.producto_id,
                vd.cantidad,
                vd.precio_unitario,
                vd.descuento,
                vd.subtotal,
                p.nombre       AS producto_nombre,
                p.codigo_barras
            FROM ventas_detalle vd
            INNER JOIN productos p ON p.id = vd.producto_id
            WHERE vd.venta_id = :venta_id
            ORDER BY vd.id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':venta_id' => $ventaId]);

        return $stmt->fetchAll();
    }

    public function obtenerPagos(int $ventaId): array
    {
        $sql = "
            SELECT
                pg.id,
                pg.venta_id,
                pg.metodo_pago_id,
                pg.monto,
                pg.referencia,
                pg.estado,
                pg.created_at,
                mp.nombre AS metodo_pago_nombre
            FROM pagos pg
            INNER JOIN metodos_pago mp ON mp.id = pg.metodo_pago_id
            WHERE pg.venta_id = :venta_id
            ORDER BY pg.id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':venta_id' => $ventaId]);

        return $stmt->fetchAll();
    }

    public function productosVendiblesPorTienda(int $tiendaId): array
    {
        $sql = "
            SELECT
                p.id,
                p.nombre,
                p.codigo_barras,
                tp.precio_venta,
                i.cantidad     AS stock,
                um.simbolo     AS unidad_simbolo
            FROM tiendas_productos tp
            INNER JOIN productos p      ON p.id  = tp.producto_id
            LEFT  JOIN inventario i     ON i.tienda_id  = tp.tienda_id
                                       AND i.producto_id = tp.producto_id
            LEFT  JOIN unidades_medida um ON um.id = p.unidad_medida_id
            WHERE tp.tienda_id = :tienda_id
              AND tp.estado    = 1
              AND p.estado     = 1
              AND p.deleted_at IS NULL
            ORDER BY p.nombre ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tienda_id' => $tiendaId]);

        return $stmt->fetchAll();
    }

    // =========================================================================
    // Operaciones de escritura
    // =========================================================================

    public function crearVenta(array $venta, array $items, array $pago): int
    {
        $this->db->beginTransaction();

        try {
            $tiendaId    = (int) $venta['tienda_id'];
            $usuarioId   = (int) $venta['created_by'];
            $cajaAbierta = $this->buscarCajaAbiertaPorTienda($tiendaId);

            if ($cajaAbierta === null) {
                throw new RuntimeException(
                    'No hay una caja abierta para esta tienda. Abre una caja antes de registrar ventas.'
                );
            }

            $calculo = $this->calcularTotalesYValidarStock($tiendaId, $items);

            // Intentar resolver el empleado_id del usuario autenticado
            $empleadoId = $this->buscarEmpleadoPorUsuario($usuarioId, $tiendaId);

            $stmtVenta = $this->db->prepare("
                INSERT INTO ventas (
                    tienda_id, cliente_id, empleado_id, caja_id,
                    subtotal, descuento, impuesto, total,
                    estado, created_by, updated_by
                ) VALUES (
                    :tienda_id, :cliente_id, :empleado_id, :caja_id,
                    :subtotal, :descuento, :impuesto, :total,
                    'completada', :created_by, :updated_by
                )
            ");

            $stmtVenta->execute([
                ':tienda_id'   => $tiendaId,
                ':cliente_id'  => $venta['cliente_id'],
                ':empleado_id' => $empleadoId,
                ':caja_id'     => (int) $cajaAbierta['id'],
                ':subtotal'    => $calculo['subtotal'],
                ':descuento'   => $calculo['descuento'],
                ':impuesto'    => $calculo['impuesto'],
                ':total'       => $calculo['total'],
                ':created_by'  => $usuarioId,
                ':updated_by'  => $usuarioId,
            ]);

            $ventaId = (int) $this->db->lastInsertId();

            $this->insertarDetalleVenta($ventaId, $calculo['items']);
            $this->registrarPago(
                $ventaId,
                (int) $pago['metodo_pago_id'],
                (string) $calculo['total'],
                $pago['referencia'] ?? null
            );
            $this->registrarMovimientoCaja(
                (int) $cajaAbierta['id'],
                'ingreso',
                (float) $calculo['total'],
                'Ingreso por venta #' . $ventaId,
                $ventaId
            );

            $this->db->commit();

            return $ventaId;
        } catch (Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    public function anularVenta(int $ventaId, ?int $usuarioId = null): bool
    {
        $this->db->beginTransaction();

        try {
            $venta = $this->buscarPorId($ventaId);

            if ($venta === null) {
                throw new RuntimeException('La venta no existe.');
            }

            if ($venta['estado'] === 'anulada') {
                throw new RuntimeException('La venta ya está anulada.');
            }

            if ($venta['caja_id'] !== null && !$this->cajaEstaAbierta((int) $venta['caja_id'])) {
                throw new RuntimeException(
                    'No se puede anular la venta porque la caja asociada ya está cerrada.'
                );
            }

            $detalle = $this->obtenerDetalle($ventaId);

            foreach ($detalle as $item) {
                $inventario = $this->buscarInventario(
                    (int) $venta['tienda_id'],
                    (int) $item['producto_id']
                );

                if ($inventario === null) {
                    throw new RuntimeException(
                        'No existe inventario para devolver el producto: ' . $item['producto_nombre']
                    );
                }

                $nuevaCantidad = (float) $inventario['cantidad'] + (float) $item['cantidad'];

                $this->actualizarCantidadInventario((int) $inventario['id'], $nuevaCantidad);
                $this->registrarMovimientoInventario(
                    (int) $inventario['id'],
                    'entrada',
                    (float) $item['cantidad'],
                    'Anulacion de venta #' . $ventaId,
                    $ventaId,
                    'ventas'
                );
            }

            if ($venta['caja_id'] !== null) {
                $this->registrarMovimientoCaja(
                    (int) $venta['caja_id'],
                    'egreso',
                    (float) $venta['total'],
                    'Egreso por anulacion de venta #' . $ventaId,
                    $ventaId
                );
            }

            $stmtVenta = $this->db->prepare("
                UPDATE ventas
                SET estado = 'anulada', updated_by = :updated_by
                WHERE id = :id AND deleted_at IS NULL
            ");
            $stmtVenta->execute([':id' => $ventaId, ':updated_by' => $usuarioId]);

            $stmtPagos = $this->db->prepare("
                UPDATE pagos SET estado = 'rechazado' WHERE venta_id = :venta_id
            ");
            $stmtPagos->execute([':venta_id' => $ventaId]);

            $this->db->commit();

            return true;
        } catch (Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    // =========================================================================
    // Métodos privados — cálculo y validación
    // =========================================================================

    private function calcularTotalesYValidarStock(int $tiendaId, array $items): array
    {
        $subtotalVenta  = 0.0;
        $impuestoVenta  = 0.0;
        $descuentoVenta = 0.0;
        $itemsCalculados = [];

        foreach ($items as $item) {
            $productoId = (int) $item['producto_id'];
            $cantidad   = (float) $item['cantidad'];

            if ($productoId <= 0 || $cantidad <= 0) {
                throw new RuntimeException('Hay productos o cantidades invalidas en la venta.');
            }

            $producto = $this->obtenerProductoVendible($tiendaId, $productoId);

            if ($producto === null) {
                throw new RuntimeException(
                    'El producto seleccionado no esta disponible para esta tienda.'
                );
            }

            $inventario = $this->buscarInventario($tiendaId, $productoId);

            if ($inventario === null) {
                throw new RuntimeException(
                    'El producto no tiene inventario registrado: ' . $producto['nombre']
                );
            }

            $stockActual = (float) $inventario['cantidad'];

            if ($cantidad > $stockActual) {
                throw new RuntimeException(
                    'Stock insuficiente para el producto: ' . $producto['nombre']
                );
            }

            $precioUnitario    = (float) $producto['precio_venta'];
            $subtotalItem      = $precioUnitario * $cantidad;
            $porcentajeImpuesto = $this->obtenerPorcentajeImpuestosProducto($productoId);
            $impuestoItem      = $subtotalItem * ($porcentajeImpuesto / 100);

            $subtotalVenta += $subtotalItem;
            $impuestoVenta += $impuestoItem;

            $itemsCalculados[] = [
                'producto_id'     => $productoId,
                'producto_nombre' => $producto['nombre'],
                'inventario_id'   => (int) $inventario['id'],
                'stock_actual'    => $stockActual,
                'cantidad'        => number_format($cantidad, 2, '.', ''),
                'precio_unitario' => number_format($precioUnitario, 2, '.', ''),
                'descuento'       => '0.00',
                'subtotal'        => number_format($subtotalItem, 2, '.', ''),
            ];
        }

        $total = $subtotalVenta - $descuentoVenta + $impuestoVenta;

        return [
            'subtotal' => number_format($subtotalVenta, 2, '.', ''),
            'descuento'=> number_format($descuentoVenta, 2, '.', ''),
            'impuesto' => number_format($impuestoVenta, 2, '.', ''),
            'total'    => number_format($total, 2, '.', ''),
            'items'    => $itemsCalculados,
        ];
    }

    private function insertarDetalleVenta(int $ventaId, array $items): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO ventas_detalle (
                venta_id, producto_id, cantidad, precio_unitario, descuento, subtotal
            ) VALUES (
                :venta_id, :producto_id, :cantidad, :precio_unitario, :descuento, :subtotal
            )
        ");

        foreach ($items as $item) {
            // Descontar del inventario
            $this->actualizarCantidadInventario(
                $item['inventario_id'],
                $item['stock_actual'] - (float) $item['cantidad']
            );

            $this->registrarMovimientoInventario(
                $item['inventario_id'],
                'salida',
                (float) $item['cantidad'],
                'Venta #' . $ventaId,
                $ventaId,
                'ventas'
            );

            $stmt->execute([
                ':venta_id'       => $ventaId,
                ':producto_id'    => $item['producto_id'],
                ':cantidad'       => $item['cantidad'],
                ':precio_unitario'=> $item['precio_unitario'],
                ':descuento'      => $item['descuento'],
                ':subtotal'       => $item['subtotal'],
            ]);
        }
    }

    private function registrarPago(
        int $ventaId,
        int $metodoPagoId,
        string $monto,
        ?string $referencia = null
    ): void {
        $stmt = $this->db->prepare("
            INSERT INTO pagos (venta_id, metodo_pago_id, monto, referencia, estado)
            VALUES (:venta_id, :metodo_pago_id, :monto, :referencia, 'aprobado')
        ");
        $stmt->execute([
            ':venta_id'      => $ventaId,
            ':metodo_pago_id'=> $metodoPagoId,
            ':monto'         => $monto,
            ':referencia'    => $referencia,
        ]);
    }

    // =========================================================================
    // Métodos privados — helpers de BD
    // =========================================================================

    private function obtenerProductoVendible(int $tiendaId, int $productoId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.id, p.nombre, p.codigo_barras, tp.precio_venta
            FROM tiendas_productos tp
            INNER JOIN productos p ON p.id = tp.producto_id
            WHERE tp.tienda_id   = :tienda_id
              AND tp.producto_id = :producto_id
              AND tp.estado      = 1
              AND p.estado       = 1
              AND p.deleted_at   IS NULL
            LIMIT 1
        ");
        $stmt->execute([':tienda_id' => $tiendaId, ':producto_id' => $productoId]);

        $producto = $stmt->fetch();

        return $producto ?: null;
    }

    private function buscarInventario(int $tiendaId, int $productoId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, tienda_id, producto_id, cantidad
            FROM inventario
            WHERE tienda_id  = :tienda_id
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
            ':id'      => $inventarioId,
            ':cantidad'=> number_format($cantidad, 2, '.', ''),
        ]);
    }

    private function registrarMovimientoInventario(
        int $inventarioId,
        string $tipo,
        float $cantidad,
        string $motivo,
        ?int $refId   = null,
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
            ':inventario_id'=> $inventarioId,
            ':tipo'         => $tipo,
            ':cantidad'     => number_format($cantidad, 2, '.', ''),
            ':motivo'       => $motivo,
            ':ref_id'       => $refId,
            ':ref_tipo'     => $refTipo,
        ]);
    }

    private function obtenerPorcentajeImpuestosProducto(int $productoId): float
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(i.porcentaje), 0) AS porcentaje_total
            FROM productos_impuestos pi
            INNER JOIN impuestos i ON i.id = pi.impuesto_id
            WHERE pi.producto_id = :producto_id
              AND pi.activo      = 1
              AND i.activo       = 1
        ");
        $stmt->execute([':producto_id' => $productoId]);

        $resultado = $stmt->fetch();

        return (float) $resultado['porcentaje_total'];
    }

    private function buscarCajaAbiertaPorTienda(int $tiendaId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT c.id, c.tienda_id, c.nombre, c.estado
            FROM cajas c
            WHERE c.tienda_id = :tienda_id
              AND c.estado    = 1
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

    private function cajaEstaAbierta(int $cajaId): bool
    {
        $stmt = $this->db->prepare("
            SELECT tipo FROM cajas_movimientos
            WHERE caja_id = :caja_id
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([':caja_id' => $cajaId]);

        $ultimo = $stmt->fetch();

        if (!$ultimo) {
            return false;
        }

        return $ultimo['tipo'] !== 'cierre';
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
            ':caja_id'     => $cajaId,
            ':tipo'        => $tipo,
            ':monto'       => number_format($monto, 2, '.', ''),
            ':descripcion' => $descripcion,
            ':venta_id'    => $ventaId,
        ]);
    }

    /**
     * Busca el empleado_id asociado a un usuario en una tienda específica.
     * Retorna null si el usuario no tiene empleado registrado (ej: superadmin).
     */
    private function buscarEmpleadoPorUsuario(int $usuarioId, int $tiendaId): ?int
    {
        $stmt = $this->db->prepare("
            SELECT id FROM empleados
            WHERE usuario_id = :usuario_id
              AND tienda_id  = :tienda_id
              AND deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':tienda_id'  => $tiendaId,
        ]);

        $empleado = $stmt->fetch();

        return $empleado ? (int) $empleado['id'] : null;
    }
}
