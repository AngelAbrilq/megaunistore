<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/HoraExtra.php';
require_once __DIR__ . '/../models/Vacacion.php';
require_once __DIR__ . '/../models/Empleado.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';

/**
 * Controlador RRHH avanzado: horas extra y vacaciones/ausencias.
 * Cubre: NR-NOM-005, NR-NOM-006, REQ-7.7.2.
 */
final class RrhhController
{
    use ControllerHelper;

    private HoraExtra $horaModel;
    private Vacacion $vacacionModel;
    private Empleado $empleadoModel;

    public function __construct()
    {
        $this->horaModel     = new HoraExtra();
        $this->vacacionModel = new Vacacion();
        $this->empleadoModel = new Empleado();
    }

    // =========================================================================
    // HORAS EXTRA (NR-NOM-005)
    // =========================================================================

    public function horasExtra(): void
    {
        $tiendaId  = $this->tiendaIdPermitida();
        $registros = $this->horaModel->listar($tiendaId);
        $empleados = $this->empleadoModel->listarParaSelect($tiendaId);
        $recargos  = HoraExtra::RECARGOS;
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/rrhh/horas_extra.php';
    }

    public function horaExtraStore(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=rrhh.horas_extra');
        }

        $this->validarCsrfToken();

        $empleadoId = (int) ($_POST['empleado_id'] ?? 0);
        $fecha      = trim((string) ($_POST['fecha'] ?? ''));
        $tipo       = (string) ($_POST['tipo'] ?? '');
        $horas      = (float) ($_POST['horas'] ?? 0);
        $valorHora  = (float) ($_POST['valor_hora'] ?? 0);

        if ($empleadoId <= 0 || $fecha === '' || !isset(HoraExtra::RECARGOS[$tipo]) || $horas <= 0 || $valorHora <= 0) {
            $this->guardarMensaje('error', 'Todos los campos son obligatorios y deben ser válidos.');
            $this->redireccionar('index.php?route=rrhh.horas_extra');
        }

        $empleado = $this->empleadoModel->buscarPorId($empleadoId);
        if ($empleado === null) {
            $this->guardarMensaje('error', 'El empleado no existe.');
            $this->redireccionar('index.php?route=rrhh.horas_extra');
        }

        $this->validarAccesoATienda((int) $empleado['tienda_id']);

        // Cálculo automático con recargo legal según el tipo (NR-NOM-005)
        $valorTotal = $horas * $valorHora * HoraExtra::RECARGOS[$tipo];

        $this->horaModel->crear([
            'empleado_id' => $empleadoId,
            'fecha'       => $fecha,
            'tipo'        => $tipo,
            'horas'       => number_format($horas, 2, '.', ''),
            'valor_hora'  => number_format($valorHora, 2, '.', ''),
            'valor_total' => number_format($valorTotal, 2, '.', ''),
        ]);

        $this->guardarMensaje('success', 'Horas extra registradas (valor calculado con recargo del ' . ((HoraExtra::RECARGOS[$tipo] - 1) * 100) . '%).');
        $this->redireccionar('index.php?route=rrhh.horas_extra');
    }

    public function horaExtraEstado(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=rrhh.horas_extra');
        }

        $this->validarCsrfToken();

        $id     = (int) ($_POST['id'] ?? 0);
        $estado = (string) ($_POST['estado'] ?? '');

        if (!in_array($estado, ['aprobada', 'rechazada'], true)) {
            $this->guardarMensaje('error', 'Estado no válido.');
            $this->redireccionar('index.php?route=rrhh.horas_extra');
        }

        $registro = $id > 0 ? $this->horaModel->buscarPorId($id) : null;
        if ($registro === null) {
            $this->guardarMensaje('error', 'El registro no existe.');
            $this->redireccionar('index.php?route=rrhh.horas_extra');
        }

        $this->validarAccesoATienda((int) $registro['tienda_id']);

        $this->horaModel->cambiarEstado($id, $estado, $this->usuarioIdActual());

        $this->guardarMensaje('success', 'Horas extra ' . ($estado === 'aprobada' ? 'aprobadas' : 'rechazadas') . '.');
        $this->redireccionar('index.php?route=rrhh.horas_extra');
    }

    // =========================================================================
    // VACACIONES Y AUSENCIAS (NR-NOM-006)
    // =========================================================================

    public function vacaciones(): void
    {
        $tiendaId   = $this->tiendaIdPermitida();
        $solicitudes = $this->vacacionModel->listar($tiendaId);
        $empleados  = $this->empleadoModel->listarParaSelect($tiendaId);
        $csrfToken  = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/rrhh/vacaciones.php';
    }

    public function vacacionStore(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=rrhh.vacaciones');
        }

        $this->validarCsrfToken();

        $empleadoId = (int) ($_POST['empleado_id'] ?? 0);
        $tipo       = (string) ($_POST['tipo'] ?? '');
        $inicio     = trim((string) ($_POST['fecha_inicio'] ?? ''));
        $fin        = trim((string) ($_POST['fecha_fin'] ?? ''));
        $motivo     = trim((string) ($_POST['motivo'] ?? ''));

        $tiposValidos = ['vacacion', 'incapacidad', 'licencia', 'calamidad', 'permiso'];

        if ($empleadoId <= 0 || !in_array($tipo, $tiposValidos, true) || $inicio === '' || $fin === '' || $inicio > $fin) {
            $this->guardarMensaje('error', 'Empleado, tipo y rango de fechas válido son obligatorios.');
            $this->redireccionar('index.php?route=rrhh.vacaciones');
        }

        $empleado = $this->empleadoModel->buscarPorId($empleadoId);
        if ($empleado === null) {
            $this->guardarMensaje('error', 'El empleado no existe.');
            $this->redireccionar('index.php?route=rrhh.vacaciones');
        }

        $this->validarAccesoATienda((int) $empleado['tienda_id']);

        if ($this->vacacionModel->existeSolape($empleadoId, $inicio, $fin)) {
            $this->guardarMensaje('error', 'El empleado ya tiene una novedad que se solapa con esas fechas.');
            $this->redireccionar('index.php?route=rrhh.vacaciones');
        }

        $dias = (int) ((strtotime($fin) - strtotime($inicio)) / 86400) + 1;

        $this->vacacionModel->crear([
            'empleado_id'  => $empleadoId,
            'tipo'         => $tipo,
            'fecha_inicio' => $inicio,
            'fecha_fin'    => $fin,
            'dias'         => $dias,
            'motivo'       => $motivo ?: null,
        ]);

        $this->guardarMensaje('success', 'Solicitud registrada (' . $dias . ' día(s)). Pendiente de aprobación.');
        $this->redireccionar('index.php?route=rrhh.vacaciones');
    }

    public function vacacionEstado(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=rrhh.vacaciones');
        }

        $this->validarCsrfToken();

        $id     = (int) ($_POST['id'] ?? 0);
        $estado = (string) ($_POST['estado'] ?? '');

        if (!in_array($estado, ['aprobada', 'rechazada'], true)) {
            $this->guardarMensaje('error', 'Estado no válido.');
            $this->redireccionar('index.php?route=rrhh.vacaciones');
        }

        $solicitud = $id > 0 ? $this->vacacionModel->buscarPorId($id) : null;
        if ($solicitud === null) {
            $this->guardarMensaje('error', 'La solicitud no existe.');
            $this->redireccionar('index.php?route=rrhh.vacaciones');
        }

        $this->validarAccesoATienda((int) $solicitud['tienda_id']);

        $this->vacacionModel->cambiarEstado($id, $estado, $this->usuarioIdActual());

        $this->guardarMensaje('success', 'Solicitud ' . ($estado === 'aprobada' ? 'aprobada' : 'rechazada') . '.');
        $this->redireccionar('index.php?route=rrhh.vacaciones');
    }
}
