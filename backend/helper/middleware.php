<?php
require_once __DIR__ . '/../utils/jwt.php';
require_once __DIR__ . '/../controller/UsuarioController.php';
require_once __DIR__ . '/../config/db.php';

function middleware(): Usuario
{
    global $conn;
    $controller = new UsuarioController($conn);

    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';

    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['error' => 'Token no proporcionado']);
        exit;
    }

    $token = $matches[1];
    $payload = validarJWT($token);

    if (!$payload) {
        http_response_code(401);
        echo json_encode(['error' => 'Token inválido o expirado']);
        exit;
    }

    http_response_code(response_code: 201);
    $user_id = $payload['sub'];
    return $controller->getUsuarioById($user_id);
}
