<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/MetodoPago.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';

final class MetodoPagoController
{
    use ControllerHelper;

    private MetodoPago $model;

    public function __construct()
    {
        $this->model = new MetodoPago();
    }

    // =========================================================================
    // GET metodos_pago.index
    // =========================================================================

    public function index(): void
    {
        $metodos   = $this->model->listar();
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/metodos_pago/index.php';
    }

    // =========================================================================
    // GET metodos_pago.create
    // =========================================================================

    public function create(): void
    {
        $csrfToken = $this->generarCsrfToken();
        require __DIR__ . '/../../resources/views/metodos_pago/create.php';
    }

    // =========================================================================
    // POST metodos_pago.store
    // =========================================================================

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=metodos_pago.index');
        }

        $this->validarCsrfToken();

        $datos = $this->validarDatos($_POST);
        if ($datos === null) {
            $this->redireccionar('index.php?route=metodos_pago.create');
        }

        if ($this->model->existeNombre($datos['nombre'])) {
            $this->guardarMensaje('error', 'Ya existe un método de pago con ese nombre.');
            $this->redireccionar('index.php?route=metodos_pago.create');
        }

        $this->model->crear($datos);
        $this->jsonExito('metodos_pago.index', 'Método de pago creado correctamente.');
    }

    // =========================================================================
    // GET metodos_pago.edit
    // =========================================================================

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0 || ($metodo = $this->model->buscarPorId($id)) === null) {
            $this->guardarMensaje('error', 'Método de pago no encontrado.');
            $this->redireccionar('index.php?route=metodos_pago.index');
        }

        $csrfToken = $this->generarCsrfToken();
        require __DIR__ . '/../../resources/views/metodos_pago/edit.php';
    }

    // =========================================================================
    // POST metodos_pago.update
    // =========================================================================

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=metodos_pago.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0 || $this->model->buscarPorId($id) === null) {
            $this->guardarMensaje('error', 'Método de pago no encontrado.');
            $this->redireccionar('index.php?route=metodos_pago.index');
        }

        $datos = $this->validarDatos($_POST);
        if ($datos === null) {
            $this->redireccionar('index.php?route=metodos_pago.edit&id=' . $id);
        }

        if ($this->model->existeNombre($datos['nombre'], $id)) {
            $this->guardarMensaje('error', 'Ya existe otro método de pago con ese nombre.');
            $this->redireccionar('index.php?route=metodos_pago.edit&id=' . $id);
        }

        $this->model->actualizar($id, $datos);
        $this->jsonExito('metodos_pago.index', 'Método de pago actualizado correctamente.');
    }

    // =========================================================================
    // POST metodos_pago.toggle
    // =========================================================================

    public function toggle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=metodos_pago.index');
        }

        $this->validarCsrfToken();

        $id           = (int) ($_POST['id'] ?? 0);
        $estadoActual = (int) ($_POST['estado_actual'] ?? 1);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'ID inválido.');
            $this->redireccionar('index.php?route=metodos_pago.index');
        }

        $nuevoEstado = $estadoActual === 1 ? 0 : 1;
        $this->model->toggleEstado($id, $nuevoEstado);

        $msg = $nuevoEstado === 1 ? 'Método activado.' : 'Método desactivado.';
        $this->guardarMensaje('success', $msg);
        $this->redireccionar('index.php?route=metodos_pago.index');
    }

    // =========================================================================
    // POST metodos_pago.destroy
    // =========================================================================

    public function destroy(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=metodos_pago.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->guardarMensaje('error', 'ID inválido.');
            $this->redireccionar('index.php?route=metodos_pago.index');
        }

        if (!$this->model->eliminar($id)) {
            $this->guardarMensaje('error', 'No se puede eliminar: tiene pagos asociados. Desactívalo en su lugar.');
        } else {
            $this->guardarMensaje('success', 'Método de pago eliminado correctamente.');
        }

        $this->redireccionar('index.php?route=metodos_pago.index');
    }

    // =========================================================================
    // GET metodos_pago.exportar  — formato: csv | pdf | svg
    // =========================================================================

    public function exportar(): void
    {
        $formato = strtolower(trim((string) ($_GET['formato'] ?? 'csv')));
        $datos   = $this->model->datosExportacion();

        match ($formato) {
            'csv'  => $this->exportarCSV($datos),
            'pdf'  => $this->exportarPDF($datos),
            'svg'  => $this->exportarSVG($datos),
            default => $this->exportarCSV($datos),
        };
    }

    // -------------------------------------------------------------------------
    // Exportación CSV
    // -------------------------------------------------------------------------

    private function exportarCSV(array $datos): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="metodos_pago_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');
        // BOM para Excel
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['ID', 'Nombre', 'Descripción', 'Estado', 'Total Pagos', 'Total Monto ($)'], ';');

        foreach ($datos as $r) {
            fputcsv($out, [
                $r['id'],
                $r['nombre'],
                $r['descripcion'] ?? '',
                $r['estado'],
                $r['total_pagos'],
                number_format((float) $r['total_monto'], 2, '.', ''),
            ], ';');
        }

        fclose($out);
        exit;
    }

    // -------------------------------------------------------------------------
    // Exportación PDF (HTML print-ready)
    // -------------------------------------------------------------------------

    private function exportarPDF(array $datos): never
    {
        header('Content-Type: text/html; charset=utf-8');

        $totalPagos = array_sum(array_column($datos, 'total_pagos'));
        $totalMonto = array_sum(array_column($datos, 'total_monto'));

        require __DIR__ . '/../../resources/views/metodos_pago/export_pdf.php';
        exit;
    }

    // -------------------------------------------------------------------------
    // Exportación SVG (gráfico de barras de montos)
    // -------------------------------------------------------------------------

    private function exportarSVG(array $datos): never
    {
        header('Content-Type: image/svg+xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="metodos_pago_' . date('Ymd_His') . '.svg"');

        $activos = array_filter($datos, fn($r) => (float)$r['total_monto'] > 0);
        if (empty($activos)) {
            $activos = $datos; // si no hay montos, mostrar todos
        }
        $activos = array_values($activos);

        $maxMonto = max(array_map(fn($r) => (float)$r['total_monto'], $activos) ?: [1]);
        $barW     = 80;
        $gap      = 30;
        $chartH   = 200;
        $padL     = 60;
        $padTop   = 40;
        $padBot   = 60;
        $n        = count($activos);
        $svgW     = $padL + $n * ($barW + $gap) + 20;
        $svgH     = $padTop + $chartH + $padBot;

        $colors = ['#1e3a8a','#2563eb','#3b82f6','#60a5fa','#93c5fd','#bfdbfe'];

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$svgW}\" height=\"{$svgH}\" font-family=\"Arial,sans-serif\">";
        echo "<rect width=\"{$svgW}\" height=\"{$svgH}\" fill=\"#f8fafc\" rx=\"12\"/>";
        echo "<text x=\"" . ($svgW / 2) . "\" y=\"28\" text-anchor=\"middle\" font-size=\"14\" font-weight=\"bold\" fill=\"#172554\">Métodos de Pago — Monto Total</text>";

        // Líneas guía
        for ($i = 0; $i <= 4; $i++) {
            $y = $padTop + ($chartH / 4) * $i;
            $val = $maxMonto * (1 - $i / 4);
            echo "<line x1=\"{$padL}\" y1=\"{$y}\" x2=\"" . ($svgW - 10) . "\" y2=\"{$y}\" stroke=\"#e5e7eb\" stroke-width=\"1\"/>";
            echo "<text x=\"" . ($padL - 5) . "\" y=\"" . ($y + 4) . "\" text-anchor=\"end\" font-size=\"10\" fill=\"#6b7280\">" . number_format($val, 0) . "</text>";
        }

        foreach ($activos as $i => $r) {
            $monto  = (float) $r['total_monto'];
            $h      = $maxMonto > 0 ? (int)(($monto / $maxMonto) * $chartH) : 4;
            $x      = $padL + $i * ($barW + $gap);
            $y      = $padTop + $chartH - $h;
            $color  = $colors[$i % count($colors)];
            $nombre = mb_strimwidth((string)$r['nombre'], 0, 12, '…');

            echo "<rect x=\"{$x}\" y=\"{$y}\" width=\"{$barW}\" height=\"{$h}\" fill=\"{$color}\" rx=\"6\"/>";
            echo "<text x=\"" . ($x + $barW / 2) . "\" y=\"" . ($y - 6) . "\" text-anchor=\"middle\" font-size=\"11\" fill=\"{$color}\" font-weight=\"bold\">" . number_format($monto, 0) . "</text>";
            echo "<text x=\"" . ($x + $barW / 2) . "\" y=\"" . ($padTop + $chartH + 16) . "\" text-anchor=\"middle\" font-size=\"11\" fill=\"#374151\">{$nombre}</text>";
            echo "<text x=\"" . ($x + $barW / 2) . "\" y=\"" . ($padTop + $chartH + 30) . "\" text-anchor=\"middle\" font-size=\"10\" fill=\"#9ca3af\">{$r['total_pagos']} pagos</text>";
        }

        echo "</svg>";
        exit;
    }

    // =========================================================================
    // Validación interna
    // =========================================================================

    private function validarDatos(array $input): ?array
    {
        $nombre      = trim((string) ($input['nombre']      ?? ''));
        $descripcion = trim((string) ($input['descripcion'] ?? ''));

        if ($nombre === '') {
            $this->guardarMensaje('error', 'El nombre es obligatorio.');
            return null;
        }

        if (strlen($nombre) > 100) {
            $this->guardarMensaje('error', 'El nombre no puede superar 100 caracteres.');
            return null;
        }

        return [
            'nombre'      => $nombre,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
            'activo'      => 1,
        ];
    }
}
