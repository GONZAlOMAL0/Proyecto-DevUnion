<?php
session_start();

try {
    require_once __DIR__ . '/../config/db.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = $_POST['cedula'] ?? '';
    $password = $_POST['contrasenia'] ?? '';

    if (!$cedula || !$password) {
        $error = "Usuario y contraseña requeridos";
    } else {
        $stmt = $conn->prepare("SELECT id, cedula, contrasenia, rol FROM usuarios WHERE cedula = ?");

        $stmt->bind_param("s", $cedula);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = "Usuario o contraseña incorrecta";
        } else {
            $row = $result->fetch_assoc();

            if ($row["rol"] != "ADMIN") {
                $error = "Usted no es administrador";
            } else {
                if (password_verify($password, $row['contrasenia'])) {
                    $_SESSION['usuario'] = ['id' => $row['id'], 'cedula' => $row['cedula']];
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "Usuario o contraseña incorrecta";
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Login Backofficea </title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 2em;
        }

        form {
            max-width: 400px;
            margin: auto;
            padding: 2em;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input {
            width: 100%;
            padding: 0.5em;
            margin: 0.5em 0;
        }

        button {
            width: 100%;
            padding: 0.7em;
        }

        .error {
            color: red;
            margin-bottom: 1em;
        }
    </style>
</head>

<body>
    <h1>Login Backoffice</h1>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="POST">
        <label for="usuario">Cedula:</label>
        <input type="text" name="cedula" id="usuario" required>
        <label for="password">Contraseña:</label>
        <input type="password" name="contrasenia" id="password" required>
        <button type="submit">Ingresar</button>
    </form>
</body>

</html>