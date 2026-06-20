<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Auditoria.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';

final class AuditoriaController
{
    use ControllerHelper;

    private Auditoria $auditoriaModel;
    private int $porPagina = 50;

    public function __construct()
    {
        $this->auditoriaModel = new Auditoria();
    }

    // =========================================================================
    // GET auditoria.index — listado con filtros + paginación
    // =========================================================================

    public function index(): void
    {
        // Filtros desde GET
        $filtroTabla   = trim((string) ($_GET['tabla']    ?? ''));
        $filtroAccion  = trim((string) ($_GET['accion']   ?? ''));
        $filtroDesde   = trim((string) ($_GET['desde']    ?? ''));
        $filtroHasta   = trim((string) ($_GET['hasta']    ?? date('Y-m-d')));
        $pagina        = max(1, (int) ($_GET['pagina']    ?? 1));
        $offset        = ($pagina - 1) * $this->porPagina;

        // Superadmin ve todo; otros roles solo ven su tienda
        $tiendaId = $this->tiendaIdPermitida();

        $registros = $this->auditoriaModel->listar(
            tiendaId:  $tiendaId,
            tabla:     $filtroTabla   !== '' ? $filtroTabla   : null,
            accion:    $filtroAccion  !== '' ? $filtroAccion  : null,
            desde:     $filtroDesde   !== '' ? $filtroDesde   : null,
            hasta:     $filtroHasta   !== '' ? $filtroHasta   : null,
            limit:     $this->porPagina,
            offset:    $offset
        );

        $total  = $this->auditoriaModel->contar(
            tiendaId: $tiendaId,
            tabla:    $filtroTabla   !== '' ? $filtroTabla   : null,
            accion:   $filtroAccion  !== '' ? $filtroAccion  : null,
            desde:    $filtroDesde   !== '' ? $filtroDesde   : null,
            hasta:    $filtroHasta   !== '' ? $filtroHasta   : null,
        );

        $tablas        = $this->auditoriaModel->tablasDistintas();
        $totalPaginas  = (int) ceil($total / $this->porPagina);
        $acciones      = ['INSERT', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT', 'EXPORT'];

        require __DIR__ . '/../../resources/views/auditoria/index.php';
    }
}
