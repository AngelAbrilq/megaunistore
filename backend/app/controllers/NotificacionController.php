<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Notificacion.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';

final class NotificacionController
{
    use ControllerHelper;

    private Notificacion $model;
    private int $porPagina = 50;

    public function __construct()
    {
        $this->model = new Notificacion();
    }

    // =========================================================================
    // GET notificaciones.index — listado con filtros
    // =========================================================================

    public function index(): void
    {
        $filtroTipo  = trim((string) ($_GET['tipo']  ?? ''));
        $filtroLeida = isset($_GET['leida']) && $_GET['leida'] !== '' ? (int)$_GET['leida'] : null;
        $pagina      = max(1, (int) ($_GET['pagina'] ?? 1));
        $offset      = ($pagina - 1) * $this->porPagina;

        $tiendaId  = $this->tiendaIdPermitida();
        $usuarioId = null; // Superadmin ve todas; Admin de tienda ve las de su tienda

        $registros    = $this->model->listar(
            usuarioId: $usuarioId,
            tiendaId:  $tiendaId,
            tipo:      $filtroTipo  !== '' ? $filtroTipo  : null,
            leida:     $filtroLeida,
            limit:     $this->porPagina,
            offset:    $offset
        );
        $total        = $this->model->contar($usuarioId, $tiendaId, $filtroTipo !== '' ? $filtroTipo : null, $filtroLeida);
        $noLeidas     = $this->model->contarNoLeidas(null, $tiendaId);
        $tipos        = $this->model->tiposDistintos();
        $totalPaginas = (int) ceil($total / $this->porPagina);
        $csrfToken    = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/notificaciones/index.php';
    }

    // =========================================================================
    // POST notificaciones.marcar_leida
    // =========================================================================

    public function marcarLeida(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderJson(['ok' => false, 'error' => 'Método inválido.']);
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->marcarLeida($id);
        }

        $this->responderJson(['ok' => true]);
    }

    // =========================================================================
    // POST notificaciones.marcar_todas
    // =========================================================================

    public function marcarTodas(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=notificaciones.index');
        }

        $this->validarCsrfToken();

        $tiendaId = $this->tiendaIdPermitida();
        $marcadas = $this->model->marcarTodasLeidas(null, $tiendaId);

        $this->guardarMensaje('success', "{$marcadas} notificaciones marcadas como leídas.");
        $this->redireccionar('index.php?route=notificaciones.index');
    }

    // =========================================================================
    // POST notificaciones.eliminar
    // =========================================================================

    public function eliminar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=notificaciones.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->eliminar($id);
            $this->guardarMensaje('success', 'Notificación eliminada.');
        }

        $this->redireccionar('index.php?route=notificaciones.index');
    }

    // =========================================================================
    // POST notificaciones.limpiar — elimina todas las leídas
    // =========================================================================

    public function limpiar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=notificaciones.index');
        }

        $this->validarCsrfToken();

        $tiendaId = $this->tiendaIdPermitida();
        $eliminadas = $this->model->eliminarLeidas($tiendaId);

        $this->guardarMensaje('success', "{$eliminadas} notificaciones leídas eliminadas.");
        $this->redireccionar('index.php?route=notificaciones.index');
    }

    // =========================================================================
    // GET notificaciones.exportar  — formato: csv | pdf | svg
    // =========================================================================

    public function exportar(): void
    {
        $formato  = strtolower(trim((string) ($_GET['formato'] ?? 'csv')));
        $tiendaId = $this->tiendaIdPermitida();
        $datos    = $this->model->datosExportacion($tiendaId);

        match ($formato) {
            'csv' => $this->exportarCSV($datos),
            'pdf' => $this->exportarPDF($datos),
            'svg' => $this->exportarSVG($datos),
            default => $this->exportarCSV($datos),
        };
    }

    // -------------------------------------------------------------------------
    // CSV
    // -------------------------------------------------------------------------

    private function exportarCSV(array $datos): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="notificaciones_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['ID', 'Título', 'Mensaje', 'Tipo', 'Estado', 'Usuario', 'Tienda', 'Fecha'], ';');

        foreach ($datos as $r) {
            fputcsv($out, [
                $r['id'],
                $r['titulo'],
                $r['mensaje'],
                $r['tipo'],
                $r['estado'],
                $r['usuario']  ?? '—',
                $r['tienda']   ?? 'Global',
                $r['created_at'],
            ], ';');
        }

        fclose($out);
        exit;
    }

    // -------------------------------------------------------------------------
    // PDF
    // -------------------------------------------------------------------------

    private function exportarPDF(array $datos): never
    {
        header('Content-Type: text/html; charset=utf-8');
        require __DIR__ . '/../../resources/views/notificaciones/export_pdf.php';
        exit;
    }

    // -------------------------------------------------------------------------
    // SVG — gráfico de barras por tipo
    // -------------------------------------------------------------------------

    private function exportarSVG(array $datos): never
    {
        header('Content-Type: image/svg+xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="notificaciones_' . date('Ymd_His') . '.svg"');

        // Agrupar por tipo
        $porTipo = [];
        foreach ($datos as $r) {
            $t = $r['tipo'] ?? 'info';
            $porTipo[$t] = ($porTipo[$t] ?? 0) + 1;
        }
        arsort($porTipo);

        $colores  = ['warning' => '#f59e0b', 'error' => '#ef4444', 'info' => '#3b82f6', 'success' => '#22c55e'];
        $default  = '#8b5cf6';

        $barW   = 90;
        $gap    = 30;
        $chartH = 200;
        $padL   = 50;
        $padTop = 48;
        $padBot = 60;
        $n      = count($porTipo);
        $svgW   = max(400, $padL + $n * ($barW + $gap) + 20);
        $svgH   = $padTop + $chartH + $padBot;
        $maxVal = max($porTipo ?: [1]);

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$svgW}\" height=\"{$svgH}\" font-family=\"Arial,sans-serif\">";
        echo "<rect width=\"{$svgW}\" height=\"{$svgH}\" fill=\"#f8fafc\" rx=\"12\"/>";
        echo "<text x=\"" . ($svgW / 2) . "\" y=\"30\" text-anchor=\"middle\" font-size=\"14\" font-weight=\"bold\" fill=\"#172554\">Notificaciones por Tipo</text>";

        // Líneas guía
        for ($i = 0; $i <= 4; $i++) {
            $y   = $padTop + ($chartH / 4) * $i;
            $val = (int)($maxVal * (1 - $i / 4));
            echo "<line x1=\"{$padL}\" y1=\"{$y}\" x2=\"" . ($svgW - 10) . "\" y2=\"{$y}\" stroke=\"#e5e7eb\" stroke-width=\"1\"/>";
            echo "<text x=\"" . ($padL - 5) . "\" y=\"" . ($y + 4) . "\" text-anchor=\"end\" font-size=\"10\" fill=\"#6b7280\">{$val}</text>";
        }

        $i = 0;
        foreach ($porTipo as $tipo => $cnt) {
            $h     = $maxVal > 0 ? (int)(($cnt / $maxVal) * $chartH) : 4;
            $x     = $padL + $i * ($barW + $gap);
            $y     = $padTop + $chartH - $h;
            $color = $colores[$tipo] ?? $default;

            echo "<rect x=\"{$x}\" y=\"{$y}\" width=\"{$barW}\" height=\"{$h}\" fill=\"{$color}\" rx=\"6\"/>";
            echo "<text x=\"" . ($x + $barW / 2) . "\" y=\"" . ($y - 6) . "\" text-anchor=\"middle\" font-size=\"12\" fill=\"{$color}\" font-weight=\"bold\">{$cnt}</text>";
            echo "<text x=\"" . ($x + $barW / 2) . "\" y=\"" . ($padTop + $chartH + 18) . "\" text-anchor=\"middle\" font-size=\"12\" fill=\"#374151\">{$tipo}</text>";
            $i++;
        }

        echo "</svg>";
        exit;
    }

    // =========================================================================
    // Helper JSON
    // =========================================================================

    private function responderJson(array $data): never
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
