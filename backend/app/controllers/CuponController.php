<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Cupon.php';
require_once __DIR__ . '/../models/Tienda.php';
require_once __DIR__ . '/../Helpers/ControllerHelper.php';

final class CuponController
{
    use ControllerHelper;
    private Cupon $cuponModel;
    private Tienda $tiendaModel;

    public function __construct()
    {
        $this->cuponModel = new Cupon();
        $this->tiendaModel = new Tienda();
    }

    public function index(): void
    {
        $tiendaIdPermitida = $this->tiendaIdPermitida();
        $cupones = $this->cuponModel->listar($tiendaIdPermitida);
        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/cupones/index.php';
    }

    public function create(): void
    {
        $tiendaIdPermitida = $this->tiendaIdPermitida();

        $tiendas = $tiendaIdPermitida === null
            ? $this->tiendaModel->listar()
            : [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/cupones/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=cupones.index');
        }

        $this->validarCsrfToken();

        $datos = $this->validarDatos($_POST);

        if ($datos === null) {
            $this->redireccionar('index.php?route=cupones.create');
        }

        try {
            $cuponId = $this->cuponModel->crear($datos);
            $this->jsonExito('cupones.index', 'Cupón creado correctamente.');
        } catch (Throwable $error) {
            $this->guardarMensaje('error', $error->getMessage());
            $this->redireccionar('index.php?route=cupones.create');
        }
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Cupón no válido.');
            $this->redireccionar('index.php?route=cupones.index');
        }

        $cupon = $this->cuponModel->buscarPorId($id);

        if ($cupon === null) {
            $this->guardarMensaje('error', 'El cupón no existe o fue eliminado.');
            $this->redireccionar('index.php?route=cupones.index');
        }

        if ($cupon['tienda_id'] !== null) {
            $this->validarAccesoATienda((int) $cupon['tienda_id']);
        }

        $tiendaIdPermitida = $this->tiendaIdPermitida();

        $tiendas = $tiendaIdPermitida === null
            ? $this->tiendaModel->listar()
            : [$this->tiendaModel->buscarPorId($tiendaIdPermitida)];

        $csrfToken = $this->generarCsrfToken();

        require __DIR__ . '/../../resources/views/cupones/edit.php';
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=cupones.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Cupón no válido.');
            $this->redireccionar('index.php?route=cupones.index');
        }

        $cupon = $this->cuponModel->buscarPorId($id);

        if ($cupon === null) {
            $this->guardarMensaje('error', 'El cupón no existe o fue eliminado.');
            $this->redireccionar('index.php?route=cupones.index');
        }

        if ($cupon['tienda_id'] !== null) {
            $this->validarAccesoATienda((int) $cupon['tienda_id']);
        }

        $datos = $this->validarDatos($_POST);

        if ($datos === null) {
            $this->redireccionar('index.php?route=cupones.edit&id=' . $id);
        }

        try {
            $this->cuponModel->actualizar($id, $datos);
            $this->jsonExito('cupones.index', 'Cupón actualizado correctamente.');
        } catch (Throwable $error) {
            $this->guardarMensaje('error', $error->getMessage());
            $this->redireccionar('index.php?route=cupones.edit&id=' . $id);
        }
    }

    public function destroy(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('index.php?route=cupones.index');
        }

        $this->validarCsrfToken();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->guardarMensaje('error', 'Cupón no válido.');
            $this->redireccionar('index.php?route=cupones.index');
        }

        $cupon = $this->cuponModel->buscarPorId($id);

        if ($cupon === null) {
            $this->guardarMensaje('error', 'El cupón no existe o fue eliminado.');
            $this->redireccionar('index.php?route=cupones.index');
        }

        if ($cupon['tienda_id'] !== null) {
            $this->validarAccesoATienda((int) $cupon['tienda_id']);
        }

        try {
            $this->cuponModel->eliminar($id, $this->usuarioIdActual());
            $this->guardarMensaje('success', 'Cupón eliminado correctamente.');
        } catch (Throwable $error) {
            $this->guardarMensaje('error', $error->getMessage());
        }

        $this->redireccionar('index.php?route=cupones.index');
    }

    public function validar(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['valido' => false, 'mensaje' => 'Método no permitido.']);
            exit;
        }

        $codigo = trim((string) ($_POST['codigo'] ?? ''));
        $subtotal = (float) ($_POST['subtotal'] ?? 0);
        $tiendaId = (int) ($_POST['tienda_id'] ?? 0);

        if ($codigo === '') {
            echo json_encode(['valido' => false, 'mensaje' => 'Debes ingresar un código de cupón.']);
            exit;
        }

        if ($subtotal <= 0) {
            echo json_encode(['valido' => false, 'mensaje' => 'El subtotal debe ser mayor a cero.']);
            exit;
        }

        try {
            $resultado = $this->cuponModel->validarCupon($codigo, $subtotal, $tiendaId > 0 ? $tiendaId : null);
            echo json_encode($resultado);
        } catch (Throwable $error) {
            echo json_encode(['valido' => false, 'mensaje' => $error->getMessage()]);
        }

        exit;
    }

    private function validarDatos(array $input): ?array
    {
        $tiendaId = trim((string) ($input['tienda_id'] ?? ''));
        $codigo = trim((string) ($input['codigo'] ?? ''));
        $descripcion = trim((string) ($input['descripcion'] ?? ''));
        $tipoDescuento = trim((string) ($input['tipo_descuento'] ?? ''));
        $valorDescuento = trim((string) ($input['valor_descuento'] ?? ''));
        $descuentoMaximo = trim((string) ($input['descuento_maximo'] ?? ''));
        $montoMinimo = trim((string) ($input['monto_minimo'] ?? ''));
        $fechaInicio = trim((string) ($input['fecha_inicio'] ?? ''));
        $fechaFin = trim((string) ($input['fecha_fin'] ?? ''));
        $usosMaximos = trim((string) ($input['usos_maximos'] ?? ''));
        $activo = (int) ($input['activo'] ?? 0);

        if ($codigo === '') {
            $this->guardarMensaje('error', 'El código del cupón es obligatorio.');
            return null;
        }

        if ($tipoDescuento === '' || !in_array($tipoDescuento, ['porcentaje', 'fijo'], true)) {
            $this->guardarMensaje('error', 'El tipo de descuento debe ser "porcentaje" o "fijo".');
            return null;
        }

        if ($valorDescuento === '' || !is_numeric($valorDescuento) || (float) $valorDescuento <= 0) {
            $this->guardarMensaje('error', 'El valor del descuento debe ser un número mayor a cero.');
            return null;
        }

        if ($tiendaId !== '' && (int) $tiendaId > 0) {
            $this->validarAccesoATienda((int) $tiendaId);
        }

        return [
            'tienda_id' => $tiendaId !== '' ? (int) $tiendaId : null,
            'codigo' => $codigo,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
            'tipo_descuento' => $tipoDescuento,
            'valor_descuento' => number_format((float) $valorDescuento, 2, '.', ''),
            'descuento_maximo' => $descuentoMaximo !== '' && is_numeric($descuentoMaximo) ? number_format((float) $descuentoMaximo, 2, '.', '') : null,
            'monto_minimo' => $montoMinimo !== '' && is_numeric($montoMinimo) ? number_format((float) $montoMinimo, 2, '.', '') : null,
            'fecha_inicio' => $fechaInicio !== '' ? $fechaInicio : null,
            'fecha_fin' => $fechaFin !== '' ? $fechaFin : null,
            'usos_maximos' => $usosMaximos !== '' && is_numeric($usosMaximos) ? (int) $usosMaximos : null,
            'activo' => $activo,
            'created_by' => $this->usuarioIdActual(),
            'updated_by' => $this->usuarioIdActual(),
        ];
    }

    private function tiendaIdPermitida(): ?int
    {
        $tiendaId = $_SESSION['auth']['rol_principal']['tienda_id'] ?? null;

        return $tiendaId !== null ? (int) $tiendaId : null;
    }

    private function validarAccesoATienda(int $tiendaId): void
    {
        $tiendaIdPermitida = $this->tiendaIdPermitida();

        if ($tiendaIdPermitida !== null && $tiendaIdPermitida !== $tiendaId) {
            $this->denegarAcceso();
        }
    }

    private function denegarAcceso(): void
    {
        http_response_code(403);

        $errorTitulo = 'Acceso denegado';
        $errorMensaje = 'No tienes permisos para gestionar cupones de esta tienda.';

        require __DIR__ . '/../../resources/errors/403.php';
        exit;
    }

    private function usuarioIdActual(): int
    {
        return (int) ($_SESSION['auth']['usuario_id'] ?? 0);
    }

    private function generarCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    private function validarCsrfToken(): void
    {
        $tokenSesion = $_SESSION['csrf_token'] ?? '';
        $tokenFormulario = $_POST['csrf_token'] ?? '';

        if ($tokenSesion === '' || !hash_equals($tokenSesion, $tokenFormulario)) {
            http_response_code(419);
            echo 'Token de seguridad inválido.';
            exit;
        }
    }

    private function guardarMensaje(string $tipo, string $mensaje): void
    {
        $_SESSION['flash'] = [
            'type' => $tipo,
            'message' => $mensaje,
        ];
    }

    private function redireccionar(string $ruta): void
    {
        header('Location: ' . $ruta);
        exit;
    }
}
