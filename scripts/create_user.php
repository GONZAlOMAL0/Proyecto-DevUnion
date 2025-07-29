<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $tel = $_POST['tel'];
    $correo = $_POST['mail'];
    $passw = $_POST['passw'];
    $cedula = $_POST['cedula'];

    $errores = [];

    if (empty($nombre) || empty($apellido) || empty($tel) || empty($correo) || empty($passw) || empty($cedula)) {
        $errores[] = "Campos vacíos detectados.";
    }

    if (strlen($passw) < 8) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres.";
    }

    if (!preg_match("/^\d+$/", $tel)) {
        $errores[] = "El teléfono solo debe contener números.";
    }

    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        header("Location: ../login.php");
        exit();
    }

    $postData = [
        "nombre" => $nombre,
        "apellido" => $apellido,
        "correo" => $correo,
        "tel" => $tel,
        "passw" => $passw,
        "cedula" => $cedula
    ];

    $apiUrl = 'https://disenio.mauri.vip/api/index.php?action=newuser';
    $data = json_encode($postData);

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => $data,
        ],
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($apiUrl, false, $context);

    if ($result === FALSE) {
        echo "<script>alert('Error al enviar datos a la API.');</script>";
    } else {
        $response = json_decode($result, true);
        if (isset($response['error'])) {
            echo "<script>alert('Error de la API: " . $response['error'] . "');</script>";
        }
    }

    $servername = "52.0.219.41";
    $usernamedb = "mauro";
    $passworddb = "eccbc87e4b5ce2fe28308fd9f2a7baf3";
    $dbname = "devuniontest";

    $conn = new mysqli($servername, $usernamedb, $passworddb, $dbname);

    if ($conn->connect_error) {
        die("<script>alert('Falló la conexión a la base de datos.');</script>");
    }

    $stmt = $conn->prepare('SELECT id, cedula, estado FROM usuario WHERE cedula = ? AND contrasenia = ?');
    $stmt->bind_param('ss', $cedula, $passw);
    $stmt->execute();
    $result = $stmt->get_result();

    $user = $result->fetch_assoc();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['cedula'] = $user['cedula'];
    $_SESSION['estado'] = $user['estado'];

    echo "<script>
            alert('Registro y autenticación exitosos.');
            window.location.href = '../index.php';
          </script>";
    exit();
} else {
    echo "<script>alert('Método de solicitud no válido.');</script>";
}
?>
