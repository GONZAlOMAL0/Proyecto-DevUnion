<?php

try {
    require_once __DIR__ . '/../../config/db.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

require_once __DIR__ . '/../../helper/middleware.php';
require_once __DIR__ . '/../../controller/PagosController.php';

header('Content-Type: application/json');

function responder($success, $data = null, $message = null, $status = 200)
{
    http_response_code($status);
    $response = ['success' => $success];
    if ($data !== null)
        $response['data'] = $data;
    if ($message !== null)
        $response['message'] = $message;
    echo json_encode($response);
    exit;
}

try {
    $usuario = middleware();
    if (!$usuario) {
        responder(false, null, 'No autorizado', 401);
    }

    $pagosController = new PagosController($conn);
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            if (isset($_GET['action']) && $_GET['action'] === 'semanas') {
                $fechaInicio = new DateTime("2025-01-01");
                $hoy = new DateTime();
                $diff = $fechaInicio->diff($hoy);
                $semanasTranscurridas = (int) floor($diff->days / 7) + 1;

                $pagos = $pagosController->obtenerPagosPorUsuario($usuario->getId());
                $semanasPagas = array_map(function ($pago) {
                    return (int) $pago->getSemana();
                }, $pagos);

                responder(true, [
                    'semanaActual' => $semanasTranscurridas,
                    'semanasPagas' => $semanasPagas
                ]);
            }

            $pagos = $pagosController->obtenerPagosPorUsuario($usuario->getId());
            $datosPagos = array_map(function ($pago) use ($pagosController) {
                return $pagosController->obtenerDatosPago($pago);
            }, $pagos);
            responder(true, $datosPagos);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !is_array($input)) {
                responder(false, null, 'Datos inválidos', 400);
            }

            $input['usuario_id'] = $usuario->getId();
            $nuevoPago = $pagosController->crearPago($input);
            $datosPago = $pagosController->obtenerDatosPago($nuevoPago);
            responder(true, $datosPago, 'Pago creado correctamente', 201);
            break;

        default:
            responder(false, null, 'Método no permitido', 405);
            break;
    }

} catch (Exception $e) {
    responder(false, null, $e->getMessage(), 500);
}
