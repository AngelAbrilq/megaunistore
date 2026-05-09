<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class Producto
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function listar(): array
    {
        $sql = "
            SELECT
                p.id,
                p.nombre,
                p.descripcion,
                p.codigo_barras,
                p.imagen_url,
                p.categoria_id,
                p.unidad_medida_id,
                p.estado,
                p.created_at,
                p.updated_at,
                c.nombre AS categoria_nombre,
                u.nombre AS unidad_nombre,
                u.simbolo AS unidad_simbolo,
                GROUP_CONCAT(DISTINCT i.nombre SEPARATOR ', ') AS impuestos,
                GROUP_CONCAT(DISTINCT CONCAT(t.nombre, ' - \$', tp.precio_venta) SEPARATOR ' | ') AS tiendas_precios
            FROM productos p
            LEFT JOIN categorias c ON c.id = p.categoria_id
            LEFT JOIN unidades_medida u ON u.id = p.unidad_medida_id
            LEFT JOIN productos_impuestos pi ON pi.producto_id = p.id AND pi.activo = 1
            LEFT JOIN impuestos i ON i.id = pi.impuesto_id AND i.activo = 1
            LEFT JOIN tiendas_productos tp ON tp.producto_id = p.id AND tp.estado = 1
            LEFT JOIN tiendas t ON t.id = tp.tienda_id
            WHERE p.deleted_at IS NULL
            GROUP BY
                p.id,
                p.nombre,
                p.descripcion,
                p.codigo_barras,
                p.imagen_url,
                p.categoria_id,
                p.unidad_medida_id,
                p.estado,
                p.created_at,
                p.updated_at,
                c.nombre,
                u.nombre,
                u.simbolo
            ORDER BY p.id DESC
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
                codigo_barras,
                imagen_url,
                categoria_id,
                unidad_medida_id,
                estado,
                deleted_at,
                created_at,
                updated_at,
                created_by,
                updated_by
            FROM productos
            WHERE id = :id
              AND deleted_at IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        $producto = $stmt->fetch();

        return $producto ?: null;
    }

    public function existeCodigoBarras(string $codigoBarras, ?int $excluirProductoId = null): bool
    {
        if ($codigoBarras === '') {
            return false;
        }

        if ($excluirProductoId === null) {
            $sql = "
                SELECT COUNT(*) AS total
                FROM productos
                WHERE codigo_barras = :codigo_barras
                  AND deleted_at IS NULL
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':codigo_barras' => $codigoBarras]);
        } else {
            $sql = "
                SELECT COUNT(*) AS total
                FROM productos
                WHERE codigo_barras = :codigo_barras
                  AND id <> :id
                  AND deleted_at IS NULL
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':codigo_barras' => $codigoBarras,
                ':id'            => $excluirProductoId,
            ]);
        }

        $resultado = $stmt->fetch();

        return (int) $resultado['total'] > 0;
    }

    public function crearCompleto(array $producto, array $impuestosIds, array $tiendasProductos): int
    {
        $this->db->beginTransaction();

        try {
            $sql = "
                INSERT INTO productos (
                    nombre,
                    descripcion,
                    codigo_barras,
                    imagen_url,
                    categoria_id,
                    unidad_medida_id,
                    estado,
                    created_by,
                    updated_by
                ) VALUES (
                    :nombre,
                    :descripcion,
                    :codigo_barras,
                    :imagen_url,
                    :categoria_id,
                    :unidad_medida_id,
                    :estado,
                    :created_by,
                    :updated_by
                )
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nombre'          => $producto['nombre'],
                ':descripcion'     => $producto['descripcion'],
                ':codigo_barras'   => $producto['codigo_barras'],
                ':imagen_url'      => $producto['imagen_url'],
                ':categoria_id'    => $producto['categoria_id'],
                ':unidad_medida_id'=> $producto['unidad_medida_id'],
                ':estado'          => $producto['estado'],
                ':created_by'      => $producto['created_by'],
                ':updated_by'      => $producto['updated_by'],
            ]);

            $productoId = (int) $this->db->lastInsertId();

            $this->sincronizarImpuestos($productoId, $impuestosIds);
            $this->sincronizarTiendasProducto($productoId, $tiendasProductos);

            $this->db->commit();

            return $productoId;
        } catch (Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    public function actualizarCompleto(int $id, array $producto, array $impuestosIds, array $tiendasProductos): bool
    {
        $this->db->beginTransaction();

        try {
            $sql = "
                UPDATE productos
                SET
                    nombre          = :nombre,
                    descripcion     = :descripcion,
                    codigo_barras   = :codigo_barras,
                    imagen_url      = :imagen_url,
                    categoria_id    = :categoria_id,
                    unidad_medida_id= :unidad_medida_id,
                    estado          = :estado,
                    updated_by      = :updated_by
                WHERE id = :id
                  AND deleted_at IS NULL
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id'              => $id,
                ':nombre'          => $producto['nombre'],
                ':descripcion'     => $producto['descripcion'],
                ':codigo_barras'   => $producto['codigo_barras'],
                ':imagen_url'      => $producto['imagen_url'],
                ':categoria_id'    => $producto['categoria_id'],
                ':unidad_medida_id'=> $producto['unidad_medida_id'],
                ':estado'          => $producto['estado'],
                ':updated_by'      => $producto['updated_by'],
            ]);

            $this->sincronizarImpuestos($id, $impuestosIds);
            $this->sincronizarTiendasProducto($id, $tiendasProductos);

            $this->db->commit();

            return true;
        } catch (Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    public function cambiarEstado(int $id, int $estado, ?int $updatedBy = null): bool
    {
        $sql = "
            UPDATE productos
            SET
                estado     = :estado,
                updated_by = :updated_by
            WHERE id = :id
              AND deleted_at IS NULL
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id'        => $id,
            ':estado'    => $estado,
            ':updated_by'=> $updatedBy,
        ]);
    }

    public function eliminarLogico(int $id, ?int $updatedBy = null): bool
    {
        $this->db->beginTransaction();

        try {
            $stmtProducto = $this->db->prepare("
                UPDATE productos
                SET deleted_at = NOW(), estado = 0, updated_by = :updated_by
                WHERE id = :id AND deleted_at IS NULL
            ");
            $stmtProducto->execute([':id' => $id, ':updated_by' => $updatedBy]);

            $stmtTiendas = $this->db->prepare("
                UPDATE tiendas_productos SET estado = 0 WHERE producto_id = :producto_id
            ");
            $stmtTiendas->execute([':producto_id' => $id]);

            $stmtImpuestos = $this->db->prepare("
                UPDATE productos_impuestos SET activo = 0 WHERE producto_id = :producto_id
            ");
            $stmtImpuestos->execute([':producto_id' => $id]);

            $this->db->commit();

            return true;
        } catch (Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    public function obtenerImpuestosProducto(int $productoId): array
    {
        $sql = "
            SELECT impuesto_id
            FROM productos_impuestos
            WHERE producto_id = :producto_id
              AND activo = 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':producto_id' => $productoId]);

        return array_map('intval', array_column($stmt->fetchAll(), 'impuesto_id'));
    }

    public function obtenerTiendasProducto(int $productoId): array
    {
        $sql = "
            SELECT tienda_id, precio_venta, precio_compra, estado
            FROM tiendas_productos
            WHERE producto_id = :producto_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':producto_id' => $productoId]);

        $resultado = [];
        foreach ($stmt->fetchAll() as $fila) {
            $resultado[(int) $fila['tienda_id']] = $fila;
        }

        return $resultado;
    }

    // -------------------------------------------------------------------------
    // Métodos privados
    // -------------------------------------------------------------------------

    private function sincronizarImpuestos(int $productoId, array $impuestosIds): void
    {
        $stmtDesactivar = $this->db->prepare("
            UPDATE productos_impuestos SET activo = 0 WHERE producto_id = :producto_id
        ");
        $stmtDesactivar->execute([':producto_id' => $productoId]);

        $impuestosIds = array_values(array_unique(array_filter(array_map('intval', $impuestosIds))));

        if (empty($impuestosIds)) {
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO productos_impuestos (producto_id, impuesto_id, activo)
            VALUES (:producto_id, :impuesto_id, 1)
            ON DUPLICATE KEY UPDATE activo = 1
        ");

        foreach ($impuestosIds as $impuestoId) {
            $stmt->execute([
                ':producto_id' => $productoId,
                ':impuesto_id' => $impuestoId,
            ]);
        }
    }

    private function sincronizarTiendasProducto(int $productoId, array $tiendasProductos): void
    {
        $stmtDesactivar = $this->db->prepare("
            UPDATE tiendas_productos SET estado = 0 WHERE producto_id = :producto_id
        ");
        $stmtDesactivar->execute([':producto_id' => $productoId]);

        if (empty($tiendasProductos)) {
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO tiendas_productos (tienda_id, producto_id, precio_venta, precio_compra, estado)
            VALUES (:tienda_id, :producto_id, :precio_venta, :precio_compra, 1)
            ON DUPLICATE KEY UPDATE
                precio_venta  = VALUES(precio_venta),
                precio_compra = VALUES(precio_compra),
                estado        = 1
        ");

        foreach ($tiendasProductos as $item) {
            $stmt->execute([
                ':tienda_id'    => $item['tienda_id'],
                ':producto_id'  => $productoId,
                ':precio_venta' => $item['precio_venta'],
                ':precio_compra'=> $item['precio_compra'],
            ]);
        }
    }
}
