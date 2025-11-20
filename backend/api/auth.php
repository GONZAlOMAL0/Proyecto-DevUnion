<?php
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../config/db.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

require_once __DIR__ . '/../controller/UsuarioController.php';

$controller = new UsuarioController($conn);

$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['action'] ?? '';

$body = json_decode(file_get_contents('php://input'), associative: true);

switch ($path) {
    case 'register':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        try {
            $respuesta = $controller->register(data: $body);

            http_response_code(201);
            echo json_encode([
                'usuario' => $controller->obtenerDatosUsuario($respuesta['usuario']),
                'token' => $respuesta['token']
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'login':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        if (!isset($body['cedula'], $body['contrasenia'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Cédula y contraseña requeridas']);
            exit;
        }

        $respuesta = $controller->login($body['cedula'], $body['contrasenia']);

        if (!$respuesta) {
            http_response_code(401);
            echo json_encode(['error' => 'Cédula o contraseña incorrecta']);
        }

        http_response_code(200);
        echo json_encode(['token' => $respuesta['token']]);
        break;
}