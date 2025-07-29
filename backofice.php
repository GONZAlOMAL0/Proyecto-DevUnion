<?php
// Conexión a la base de datos

$conexion = new mysqli("52.0.219.41", "mauro", "eccbc87e4b5ce2fe28308fd9f2a7baf3", "devuniontest");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Cambiar estado si se hace clic en el botón
if (isset($_POST['cambiar_estado'])) {
    $id = $_POST['id_usuario'];
    $conexion->query("UPDATE usuario SET estado = 1 WHERE id = $id");
}

// Obtener todos los usuarios
$resultado = $conexion->query("SELECT * FROM usuario");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Usuarios</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: left; }
    </style>
</head>
<body>

<h2>Usuarios Registrados</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Apellido</th>
        <th>Teléfono</th>
        <th>Correo</th>
        <th>Contraseña</th>
        <th>Cédula</th>
        <th>Dirección</th>
        <th>Fecha Registro</th>
        <th>Estado</th>
        <th>Acción</th>
    </tr>

    <?php while ($fila = $resultado->fetch_assoc()): ?>
        <tr>
            <td><?= $fila['id'] ?></td>
            <td><?= htmlspecialchars($fila['nombre']) ?></td>
            <td><?= htmlspecialchars($fila['apellido']) ?></td>
            <td><?= htmlspecialchars($fila['tel']) ?></td>
            <td><?= htmlspecialchars($fila['correo']) ?></td>
            <td><?= htmlspecialchars($fila['contrasenia']) ?></td>
            <td><?= htmlspecialchars($fila['cedula']) ?></td>
            <td><?= htmlspecialchars($fila['direccion']) ?></td>
            <td><?= $fila['fecha_registro'] ?></td>
            <td><?= $fila['estado'] ?></td>
            <td>
                <?php if ($fila['estado'] == 0): ?>
                    <form method="POST" style="margin:0;">
                        <input type="hidden" name="id_usuario" value="<?= $fila['id'] ?>">
                        <button type="submit" name="cambiar_estado">Activar</button>
                    </form>
                <?php else: ?>
                    Activo
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>

</table>

</body>
</html>

<?php $conexion->close(); ?>
