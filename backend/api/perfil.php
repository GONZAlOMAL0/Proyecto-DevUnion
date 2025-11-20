<?php

try {
    require_once __DIR__ . '/../config/db.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

require_once __DIR__ . '/../helper/middleware.php';
require_once __DIR__ . '/../controller/UsuarioController.php';

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
        responder(false, null, 'No autorizado', status: 401);
    }

    $usuarioController = new UsuarioController($conn);
    $datos = $usuarioController->obtenerDatosUsuario(usuario: $usuario);

    if ($datos["estado"] == false) {
        http_response_code(500);
        responder(false, null, 'El usuario no esta activado', 401);
    }

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            $datos = $usuarioController->obtenerDatosUsuario(usuario: $usuario);
            responder(true, $datos);
            break;

        case 'PUT':
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input || !is_array($input)) {
                responder(false, null, 'Datos inválidos', 400);
            }

            $usuarioActualizado = $usuarioController->updateUsuario($usuario->getId(), $input);
            $datosActualizados = $usuarioController->obtenerDatosUsuario($usuarioActualizado);
            responder(true, $datosActualizados, 'Datos actualizados correctamente');
            break;

        default:
            responder(false, null, 'Método no permitido', 405);
            break;
    }

} catch (Exception $e) {
    responder(false, null, $e->getMessage(), 500);
}
