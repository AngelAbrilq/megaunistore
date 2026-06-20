<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/ControllerHelper.php';
require_once __DIR__ . '/../../config/database.php';

/**
 * BackupController — Gestión de exportaciones y respaldos del sistema.
 *
 * Permite:
 *  - Ver el historial de exportaciones (tabla exportaciones)
 *  - Exportar datos clave del sistema en CSV, PDF o SVG
 *  - Registrar cada exportación en la tabla `exportaciones`
 *
 * Permiso requerido: backups.manage
 */
final class BackupController
{
    use ControllerHelper;

    private PDO $db;

    // Conjuntos de datos exportables
    private const CONJUNTOS = [
        'ventas'     => ['label' => 'Ventas',      'emoji' => '🛒'],
        'productos'  => ['label' => 'Productos',   'emoji' => '📦'],
        'inventario' => ['label' => 'Inventario',  'emoji' => '🏭'],
        'usuarios'   => ['label' => 'Usuarios',    'emoji' => '👥'],
        'clientes'   => ['label' => 'Clientes',    'emoji' => '🙋'],
        'auditoria'  => ['label' => 'Auditoría',   'emoji' => '🔍'],
    ];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =========================================================================
    // GET backups.index — panel principal
    // =========================================================================

    public function index(): void
    {
        $historial    = $this->obtenerHistorial();
        $estadisticas = $this->obtenerEstadisticas();
        $csrfToken    = $this->generarCsrfToken();
        $conjuntos    = self::CONJUNTOS;

        require __DIR__ . '/../../resources/views/backups/index.php';
    }

    // =========================================================================
    // GET backups.exportar — genera y descarga la exportación
    // Params: conjunto (ventas|productos|…), formato (csv|pdf|svg)
    // =========================================================================

    public function exportar(): void
    {
        $conjunto = trim((string) ($_GET['conjunto'] ?? 'ventas'));
        $formato  = strtolower(trim((string) ($_GET['formato'] ?? 'csv')));

        if (!array_key_exists($conjunto, self::CONJUNTOS)) {
            $this->guardarMensaje('error', 'Conjunto de datos no válido.');
            $this->redireccionar('index.php?route=backups.index');
        }

        $datos = $this->obtenerDatos($conjunto);

        // Registrar en historial (solo CSV y PDF como formatos "reales")
        if (in_array($formato, ['csv', 'pdf', 'xlsx'], true)) {
            $this->registrarExportacion($conjunto, $formato);
        }

        match ($formato) {
            'csv'  => $this->exportarCSV($datos, $conjunto),
            'pdf'  => $this->exportarPDF($datos, $conjunto),
            'svg'  => $this->exportarSVG($datos, $conjunto),
            default => $this->exportarCSV($datos, $conjunto),
        };
    }

    // =========================================================================
    // POST backups.limpiar_historial — borra registros de exportaciones
    // =========================================================================

    public function limpiarHistorial(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=backups.index');
        }

        $this->validarCsrfToken();

        // Solo eliminamos exportaciones huérfanas (sin reporte_id válido)
        // Para proteger integridad, limpiamos las más antiguas de 90 días
        $stmt = $this->db->prepare(
            "DELETE FROM exportaciones WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)"
        );
        $stmt->execute();
        $eliminadas = $stmt->rowCount();

        $this->guardarMensaje('success', "Historial limpiado: {$eliminadas} registros eliminados (>90 días).");
        $this->redireccionar('index.php?route=backups.index');
    }

    // =========================================================================
    // Datos para exportar según el conjunto elegido
    // =========================================================================

    private function obtenerDatos(string $conjunto): array
    {
        return match ($conjunto) {
            'ventas'     => $this->datosVentas(),
            'productos'  => $this->datosProductos(),
            'inventario' => $this->datosInventario(),
            'usuarios'   => $this->datosUsuarios(),
            'clientes'   => $this->datosClientes(),
            'auditoria'  => $this->datosAuditoria(),
            default      => [],
        };
    }

    private function datosVentas(): array
    {
        $stmt = $this->db->query(
            "SELECT v.id, v.codigo_venta, DATE(v.fecha) AS fecha,
                    t.nombre AS tienda,
                    CONCAT(u.nombre,' ',u.apellido) AS vendedor,
                    v.subtotal, v.descuento, v.impuesto, v.total,
                    v.estado, mp.nombre AS metodo_pago
             FROM ventas v
             LEFT JOIN tiendas    t  ON t.id  = v.tienda_id
             LEFT JOIN usuarios   u  ON u.id  = v.usuario_id
             LEFT JOIN pagos      pg ON pg.venta_id  = v.id
             LEFT JOIN metodos_pago mp ON mp.id = pg.metodo_pago_id
             WHERE v.deleted_at IS NULL
             ORDER BY v.fecha DESC
             LIMIT 2000"
        );
        return $stmt->fetchAll();
    }

    private function datosProductos(): array
    {
        $stmt = $this->db->query(
            "SELECT p.id, p.codigo, p.nombre,
                    c.nombre AS categoria,
                    p.precio_compra, p.precio_venta, p.stock_minimo,
                    IF(p.activo=1,'Activo','Inactivo') AS estado,
                    t.nombre AS tienda
             FROM productos p
             LEFT JOIN categorias c ON c.id = p.categoria_id
             LEFT JOIN tiendas    t ON t.id = p.tienda_id
             WHERE p.deleted_at IS NULL
             ORDER BY p.nombre ASC
             LIMIT 2000"
        );
        return $stmt->fetchAll();
    }

    private function datosInventario(): array
    {
        $stmt = $this->db->query(
            "SELECT i.id,
                    p.nombre AS producto,
                    p.codigo,
                    t.nombre AS tienda,
                    i.cantidad, i.stock_minimo,
                    IF(i.cantidad <= i.stock_minimo,'⚠️ Bajo mínimo','OK') AS alerta,
                    i.updated_at
             FROM inventario i
             LEFT JOIN productos p ON p.id = i.producto_id
             LEFT JOIN tiendas   t ON t.id = i.tienda_id
             WHERE p.deleted_at IS NULL
             ORDER BY alerta DESC, p.nombre ASC
             LIMIT 2000"
        );
        return $stmt->fetchAll();
    }

    private function datosUsuarios(): array
    {
        $stmt = $this->db->query(
            "SELECT u.id,
                    u.nombre, u.apellido, u.email,
                    u.telefono,
                    IF(u.activo=1,'Activo','Inactivo') AS estado,
                    u.created_at
             FROM usuarios u
             WHERE u.deleted_at IS NULL
             ORDER BY u.nombre ASC
             LIMIT 2000"
        );
        return $stmt->fetchAll();
    }

    private function datosClientes(): array
    {
        $stmt = $this->db->query(
            "SELECT c.id,
                    CONCAT(u.nombre,' ',u.apellido) AS nombre,
                    u.email,
                    c.nit, c.direccion, c.ciudad,
                    t.nombre AS tienda,
                    c.created_at
             FROM clientes c
             LEFT JOIN usuarios u ON u.id = c.usuario_id
             LEFT JOIN tiendas  t ON t.id = c.tienda_id
             WHERE c.deleted_at IS NULL
             ORDER BY u.nombre ASC
             LIMIT 2000"
        );
        return $stmt->fetchAll();
    }

    private function datosAuditoria(): array
    {
        $stmt = $this->db->query(
            "SELECT al.id, al.tabla, al.accion, al.registro_id,
                    CONCAT(u.nombre,' ',u.apellido) AS usuario,
                    t.nombre AS tienda,
                    al.ip_address, al.created_at
             FROM audit_log al
             LEFT JOIN usuarios u ON u.id = al.usuario_id
             LEFT JOIN tiendas  t ON t.id = al.tienda_id
             ORDER BY al.created_at DESC
             LIMIT 2000"
        );
        return $stmt->fetchAll();
    }

    // =========================================================================
    // Historial de exportaciones
    // =========================================================================

    private function obtenerHistorial(): array
    {
        $stmt = $this->db->query(
            "SELECT e.id, e.formato, e.archivo_url, e.tamano_bytes, e.created_at,
                    COALESCE(r.nombre, '—') AS reporte_nombre,
                    COALESCE(t.nombre, 'Sistema') AS tienda
             FROM exportaciones e
             LEFT JOIN reportes r ON r.id = e.reporte_id
             LEFT JOIN tiendas  t ON t.id = r.tienda_id
             ORDER BY e.created_at DESC
             LIMIT 100"
        );
        return $stmt->fetchAll();
    }

    // =========================================================================
    // Estadísticas para el panel
    // =========================================================================

    private function obtenerEstadisticas(): array
    {
        $stats = [];

        // Total exportaciones
        $stats['total_exportaciones'] = (int) $this->db->query(
            "SELECT COUNT(*) FROM exportaciones"
        )->fetchColumn();

        // Por formato
        $stmt = $this->db->query(
            "SELECT formato, COUNT(*) AS cnt FROM exportaciones GROUP BY formato"
        );
        $stats['por_formato'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Tamaño total
        $stats['tamano_total'] = (int) $this->db->query(
            "SELECT COALESCE(SUM(tamano_bytes), 0) FROM exportaciones"
        )->fetchColumn();

        // Última exportación
        $stats['ultima'] = $this->db->query(
            "SELECT MAX(created_at) FROM exportaciones"
        )->fetchColumn();

        return $stats;
    }

    // =========================================================================
    // Registrar exportación en la tabla exportaciones
    // (usa reporte_id = 1 como marcador "manual" si no existe un reporte real)
    // =========================================================================

    private function registrarExportacion(string $conjunto, string $formato): void
    {
        // Obtener o crear un reporte "sistema" para registrar la exportación
        $tiendaId  = $this->tiendaIdPermitida() ?? 1;
        $usuarioId = $this->usuarioIdActual();

        try {
            // Insertar un reporte temporal para poder FK en exportaciones
            $stmt = $this->db->prepare(
                "INSERT INTO reportes (tienda_id, nombre, tipo, creado_por)
                 VALUES (:tienda_id, :nombre, :tipo, :usuario_id)"
            );
            $stmt->execute([
                ':tienda_id' => $tiendaId,
                ':nombre'    => 'Exportación manual: ' . $conjunto,
                ':tipo'      => 'backup_' . $conjunto,
                ':usuario_id' => $usuarioId,
            ]);
            $reporteId = (int) $this->db->lastInsertId();

            $stmt = $this->db->prepare(
                "INSERT INTO exportaciones (reporte_id, formato, archivo_url, tamano_bytes)
                 VALUES (:reporte_id, :formato, :archivo_url, 0)"
            );
            $stmt->execute([
                ':reporte_id'  => $reporteId,
                ':formato'     => $formato === 'csv' ? 'csv' : ($formato === 'pdf' ? 'pdf' : 'xlsx'),
                ':archivo_url' => 'backup_' . $conjunto . '_' . date('Ymd_His') . '.' . $formato,
            ]);
        } catch (\Throwable) {
            // No interrumpir la descarga si falla el registro
        }
    }

    // =========================================================================
    // Exportación CSV
    // =========================================================================

    private function exportarCSV(array $datos, string $conjunto): never
    {
        if (empty($datos)) {
            $this->guardarMensaje('error', 'No hay datos para exportar.');
            $this->redireccionar('index.php?route=backups.index');
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="backup_' . $conjunto . '_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 para Excel

        // Cabeceras desde las claves del primer registro
        fputcsv($out, array_keys($datos[0]), ';');

        foreach ($datos as $row) {
            fputcsv($out, array_map(fn($v) => $v ?? '', $row), ';');
        }

        fclose($out);
        exit;
    }

    // =========================================================================
    // Exportación PDF (HTML print-ready)
    // =========================================================================

    private function exportarPDF(array $datos, string $conjunto): never
    {
        header('Content-Type: text/html; charset=utf-8');

        $meta     = self::CONJUNTOS[$conjunto] ?? ['label' => $conjunto, 'emoji' => '📄'];
        $columnas = !empty($datos) ? array_keys($datos[0]) : [];

        require __DIR__ . '/../../resources/views/backups/export_pdf.php';
        exit;
    }

    // =========================================================================
    // Exportación SVG — gráfico de resumen según el conjunto
    // =========================================================================

    private function exportarSVG(array $datos, string $conjunto): never
    {
        header('Content-Type: image/svg+xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="backup_' . $conjunto . '_' . date('Ymd_His') . '.svg"');

        // Generar datos del gráfico según el conjunto
        $grafico = $this->prepararGraficoSVG($datos, $conjunto);

        $n      = count($grafico['items']);
        $barW   = 80;
        $gap    = 24;
        $chartH = 220;
        $padL   = 60;
        $padTop = 50;
        $padBot = 70;
        $svgW   = max(500, $padL + $n * ($barW + $gap) + 20);
        $svgH   = $padTop + $chartH + $padBot;
        $maxVal = max(array_column($grafico['items'], 'valor') ?: [1]);

        $colors = ['#1e3a8a','#2563eb','#3b82f6','#60a5fa','#93c5fd','#1d4ed8','#7c3aed','#db2777'];

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$svgW}\" height=\"{$svgH}\" font-family=\"Arial,sans-serif\">";
        echo "<rect width=\"{$svgW}\" height=\"{$svgH}\" fill=\"#f8fafc\" rx=\"14\"/>";
        echo "<text x=\"" . ($svgW / 2) . "\" y=\"32\" text-anchor=\"middle\" font-size=\"15\" font-weight=\"bold\" fill=\"#172554\">{$grafico['titulo']}</text>";
        echo "<text x=\"" . ($svgW / 2) . "\" y=\"46\" text-anchor=\"middle\" font-size=\"11\" fill=\"#6b7280\">" . date('d/m/Y H:i') . " — {$n} registros</text>";

        // Líneas guía
        for ($i = 0; $i <= 4; $i++) {
            $y   = $padTop + ($chartH / 4) * $i;
            $val = (int)($maxVal * (1 - $i / 4));
            echo "<line x1=\"{$padL}\" y1=\"{$y}\" x2=\"" . ($svgW - 10) . "\" y2=\"{$y}\" stroke=\"#e5e7eb\" stroke-width=\"1\"/>";
            echo "<text x=\"" . ($padL - 5) . "\" y=\"" . ($y + 4) . "\" text-anchor=\"end\" font-size=\"10\" fill=\"#9ca3af\">{$val}</text>";
        }

        foreach ($grafico['items'] as $i => $item) {
            $h     = $maxVal > 0 ? (int)(($item['valor'] / $maxVal) * $chartH) : 4;
            $x     = $padL + $i * ($barW + $gap);
            $y     = $padTop + $chartH - $h;
            $color = $colors[$i % count($colors)];
            $label = mb_strimwidth((string)$item['label'], 0, 14, '…');

            echo "<rect x=\"{$x}\" y=\"{$y}\" width=\"{$barW}\" height=\"{$h}\" fill=\"{$color}\" rx=\"6\"/>";
            echo "<text x=\"" . ($x + $barW / 2) . "\" y=\"" . ($y - 6) . "\" text-anchor=\"middle\" font-size=\"11\" fill=\"{$color}\" font-weight=\"bold\">{$item['valor']}</text>";
            echo "<text x=\"" . ($x + $barW / 2) . "\" y=\"" . ($padTop + $chartH + 18) . "\" text-anchor=\"middle\" font-size=\"11\" fill=\"#374151\" transform=\"rotate(-20 " . ($x + $barW / 2) . " " . ($padTop + $chartH + 18) . ")\">{$label}</text>";
        }

        echo "</svg>";
        exit;
    }

    private function prepararGraficoSVG(array $datos, string $conjunto): array
    {
        $titulo = (self::CONJUNTOS[$conjunto]['emoji'] ?? '') . ' ' . (self::CONJUNTOS[$conjunto]['label'] ?? $conjunto) . ' — Resumen';

        return match ($conjunto) {
            'ventas' => [
                'titulo' => $titulo,
                'items'  => $this->agrupar($datos, 'tienda', 'total', 8),
            ],
            'productos' => [
                'titulo' => $titulo,
                'items'  => $this->agrupar($datos, 'categoria', null, 8),
            ],
            'inventario' => [
                'titulo' => $titulo,
                'items'  => $this->agrupar($datos, 'tienda', 'cantidad', 8),
            ],
            'auditoria' => [
                'titulo' => $titulo,
                'items'  => $this->agrupar($datos, 'accion', null, 8),
            ],
            default => [
                'titulo' => $titulo,
                'items'  => [['label' => 'Total', 'valor' => count($datos)]],
            ],
        };
    }

    /** Agrupa datos por columna clave, sumando una columna valor o contando */
    private function agrupar(array $datos, string $colClave, ?string $colValor, int $max): array
    {
        $grupos = [];
        foreach ($datos as $r) {
            $k = (string)($r[$colClave] ?? 'N/A');
            if (!isset($grupos[$k])) $grupos[$k] = 0;
            $grupos[$k] += $colValor !== null ? (float)($r[$colValor] ?? 0) : 1;
        }

        arsort($grupos);
        $items = [];
        $i = 0;
        foreach ($grupos as $label => $val) {
            if ($i++ >= $max) break;
            $items[] = ['label' => $label, 'valor' => (int)$val];
        }

        return $items ?: [['label' => 'Sin datos', 'valor' => 0]];
    }
}
