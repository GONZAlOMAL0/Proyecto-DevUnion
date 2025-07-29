<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Encabezado de respuesta JSON
header('Content-Type: application/json');

// Autocarga de clases (por si tenés más controladores en futuro)
spl_autoload_register(function ($className) {
    $filename = 'controller/' . $className . '.php';
    if (file_exists($filename)) {
        require_once $filename;
    }
});

// Nombre del controlador y acción
$controllerName = isset($_GET['controller']) ? ucfirst($_GET['controller']) . 'Controller' : 'ClientController';
$actionName = isset($_GET['action']) ? $_GET['action'] : 'tester';

// Verificar si la clase del controlador existe
if (!class_exists($controllerName)) {
    http_response_code(404);
    echo json_encode(['error' => "Controlador '$controllerName' no encontrado"]);
    exit;
}

// Crear instancia del controlador
$controller = new $controllerName();

// Verificar que el método exista en el controlador
if (!method_exists($controller, $actionName)) {
    http_response_code(404);
    echo json_encode(['error' => "Acción '$actionName' no encontrada en el controlador '$controllerName'"]);
    exit;
}

$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($requestMethod) {
    case 'GET':
        $datos = $controller->$actionName();
        echo json_encode($datos);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if ($actionName === 'newuser') {
            $campos = ['nombre', 'apellido', 'tel', 'correo', 'passw', 'cedula'];
            $faltantes = array_diff($campos, array_keys($input ?? []));
            if (empty($faltantes)) {
                $resultado = $controller->newUser(
                    $input['nombre'],
                    $input['apellido'],
                    $input['tel'],
                    $input['correo'],
                    $input['passw'],
                    $input['cedula']
                );
                echo json_encode($resultado);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Parámetros insuficientes: ' . implode(', ', $faltantes)]);
            }
        } else {
            $datos = $controller->$actionName($input);
            echo json_encode($datos);
        }
        break;

    case 'DELETE':
        parse_str(file_get_contents('php://input'), $input);
        if ($actionName === 'eliminaruser' && isset($input['id'])) {
            $resultado = $controller->eliminarUser($input['id']);
            echo json_encode($resultado);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetros insuficientes o acción incorrecta']);
        }
        break;

    case 'PATCH':
        parse_str(file_get_contents('php://input'), $input);
        if ($actionName === 'cambiarusername' && isset($input['oldusername'], $input['newusername'])) {
            $resultado = $controller->cambiarUsername($input['newusername'], $input['oldusername']);
            echo json_encode($resultado);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetros insuficientes o acción incorrecta']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>