<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Reporte.php';
require_once __DIR__ . '/../models/Tienda.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';

final class ReporteController
{
    use ControllerHelper;
    private Reporte $reporteModel;
    private Tienda $tiendaModel;

    public function __construct()
    {
        $this->reporteModel = new Reporte();
        $this->tiendaModel = new Tienda();
    }

    public function index(): void
    {
        $tiendaIdPermitida = $this->tiendaIdPermitida();

        $tiendas = $tiendaIdPermitida === null
            ? $this->tiendaModel->listar()
            : [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];

        require __DIR__ . '/../../resources/views/reportes/index.php';
    }

    public function ventas(): void
    {
        $fechaInicio = trim((string) ($_GET['fecha_inicio'] ?? date('Y-m-01')));
        $fechaFin = trim((string) ($_GET['fecha_fin'] ?? date('Y-m-d')));
        $tiendaId = (int) ($_GET['tienda_id'] ?? 0);

        $tiendaIdPermitida = $this->tiendaIdPermitida();

        if ($tiendaIdPermitida !== null) {
            $tiendaId = $tiendaIdPermitida;
        }

        $ventas = $this->reporteModel->ventasPorPeriodo(
            $fechaInicio,
            $fechaFin,
            $tiendaId > 0 ? $tiendaId : null
        );

        $tiendas = $tiendaIdPermitida === null
            ? $this->tiendaModel->listar()
            : [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];

        require __DIR__ . '/../../resources/views/reportes/ventas.php';
    }

    public function ventasPorTienda(): void
    {
        $fechaInicio = trim((string) ($_GET['fecha_inicio'] ?? date('Y-m-01')));
        $fechaFin = trim((string) ($_GET['fecha_fin'] ?? date('Y-m-d')));

        $ventas = $this->reporteModel->ventasPorTienda($fechaInicio, $fechaFin);

        require __DIR__ . '/../../resources/views/reportes/ventas_por_tienda.php';
    }

    public function productosMasVendidos(): void
    {
        $fechaInicio = trim((string) ($_GET['fecha_inicio'] ?? date('Y-m-01')));
        $fechaFin = trim((string) ($_GET['fecha_fin'] ?? date('Y-m-d')));
        $tiendaId = (int) ($_GET['tienda_id'] ?? 0);
        $limite = (int) ($_GET['limite'] ?? 10);

        $tiendaIdPermitida = $this->tiendaIdPermitida();

        if ($tiendaIdPermitida !== null) {
            $tiendaId = $tiendaIdPermitida;
        }

        $productos = $this->reporteModel->productosMasVendidos(
            $fechaInicio,
            $fechaFin,
            $tiendaId > 0 ? $tiendaId : null,
            $limite
        );

        $tiendas = $tiendaIdPermitida === null
            ? $this->tiendaModel->listar()
            : [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];

        require __DIR__ . '/../../resources/views/reportes/productos_mas_vendidos.php';
    }

    public function ventasPorMetodoPago(): void
    {
        $fechaInicio = trim((string) ($_GET['fecha_inicio'] ?? date('Y-m-01')));
        $fechaFin = trim((string) ($_GET['fecha_fin'] ?? date('Y-m-d')));
        $tiendaId = (int) ($_GET['tienda_id'] ?? 0);

        $tiendaIdPermitida = $this->tiendaIdPermitida();

        if ($tiendaIdPermitida !== null) {
            $tiendaId = $tiendaIdPermitida;
        }

        $ventas = $this->reporteModel->ventasPorMetodoPago(
            $fechaInicio,
            $fechaFin,
            $tiendaId > 0 ? $tiendaId : null
        );

        $tiendas = $tiendaIdPermitida === null
            ? $this->tiendaModel->listar()
            : [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];

        require __DIR__ . '/../../resources/views/reportes/ventas_por_metodo_pago.php';
    }

    public function inventario(): void
    {
        $tiendaId = (int) ($_GET['tienda_id'] ?? 0);

        $tiendaIdPermitida = $this->tiendaIdPermitida();

        if ($tiendaIdPermitida !== null) {
            $tiendaId = $tiendaIdPermitida;
        }

        $inventario = $this->reporteModel->inventarioPorTienda($tiendaId > 0 ? $tiendaId : null);

        $tiendas = $tiendaIdPermitida === null
            ? $this->tiendaModel->listar()
            : [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];

        require __DIR__ . '/../../resources/views/reportes/inventario.php';
    }

    public function stockBajo(): void
    {
        $tiendaId = (int) ($_GET['tienda_id'] ?? 0);

        $tiendaIdPermitida = $this->tiendaIdPermitida();

        if ($tiendaIdPermitida !== null) {
            $tiendaId = $tiendaIdPermitida;
        }

        $productos = $this->reporteModel->productosStockBajo($tiendaId > 0 ? $tiendaId : null);

        $tiendas = $tiendaIdPermitida === null
            ? $this->tiendaModel->listar()
            : [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];

        require __DIR__ . '/../../resources/views/reportes/stock_bajo.php';
    }

    public function movimientosInventario(): void
    {
        $fechaInicio = trim((string) ($_GET['fecha_inicio'] ?? date('Y-m-01')));
        $fechaFin = trim((string) ($_GET['fecha_fin'] ?? date('Y-m-d')));
        $tiendaId = (int) ($_GET['tienda_id'] ?? 0);

        $tiendaIdPermitida = $this->tiendaIdPermitida();

        if ($tiendaIdPermitida !== null) {
            $tiendaId = $tiendaIdPermitida;
        }

        $movimientos = $this->reporteModel->movimientosInventario(
            $fechaInicio,
            $fechaFin,
            $tiendaId > 0 ? $tiendaId : null
        );

        $tiendas = $tiendaIdPermitida === null
            ? $this->tiendaModel->listar()
            : [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];

        require __DIR__ . '/../../resources/views/reportes/movimientos_inventario.php';
    }

    public function movimientosCaja(): void
    {
        $fechaInicio = trim((string) ($_GET['fecha_inicio'] ?? date('Y-m-01')));
        $fechaFin = trim((string) ($_GET['fecha_fin'] ?? date('Y-m-d')));
        $tiendaId = (int) ($_GET['tienda_id'] ?? 0);

        $tiendaIdPermitida = $this->tiendaIdPermitida();

        if ($tiendaIdPermitida !== null) {
            $tiendaId = $tiendaIdPermitida;
        }

        $movimientos = $this->reporteModel->movimientosCaja(
            $fechaInicio,
            $fechaFin,
            $tiendaId > 0 ? $tiendaId : null
        );

        $tiendas = $tiendaIdPermitida === null
            ? $this->tiendaModel->listar()
            : [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];

        require __DIR__ . '/../../resources/views/reportes/movimientos_caja.php';
    }

    private function tiendaIdPermitida(): ?int
    {
        $tiendaId = $_SESSION['auth']['rol_principal']['tienda_id'] ?? null;

        return $tiendaId !== null ? (int) $tiendaId : null;
    }
}
