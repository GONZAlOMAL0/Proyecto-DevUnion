<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coperativa</title>
    <link rel="stylesheet" href="style.css">

</head>

<body>

    
<?php
    session_start();
    include "./scripts/mysql.php";
    

    $stmt = $conn->prepare('SELECT estado FROM usuario WHERE cedula = ? ');
    $stmt->bind_param('s', $_SESSION['cedula']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user["estado"] == 0){

        echo("usuario no activado");
        die();
    }

    include "./scripts/nav.php";
echo($_SESSION['user_id'] . "<br>" . $_SESSION['cedula'] );
?>
</body>
</html>

