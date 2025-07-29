


<link rel="stylesheet" type="text/css" href="login.css" />

<div class="container" id="container">
	<div class="form-container sign-up-container">
		<form action="scripts/create_user.php" method="POST">
			<h1>Crea tu cuenta</h1>
			<span>O usa tu email para registrarte</span>
			<input name="nombre" type="text" placeholder="Nombre" />
			<input name="apellido" type="text" placeholder="Apellido" />
			<input name="tel" type="text" placeholder="Telefono" />
			<input name="mail" type="email" placeholder="Correo" />
            <input name="passw" type="password" placeholder="Contrasenia" />
			<input name="cedula" type="text" placeholder="Cedula" />
			<button id="btn-log" type="submit">Sign Up</button>
		</form>
	</div>
	<div class="form-container sign-in-container">
		<form method="post" action="">
			<h1>Iniciar Sesion</h1>
			<input name="email_login" type="text" placeholder="Cedula" />
			<input name="passw_login" type="password" placeholder="Password" />
			<a href="#">Olvidaste tu contraseña?</a>
			<button>Sign In</button>
		</form>
	</div>
	<div class="overlay-container">
		<div class="overlay">
			<div class="overlay-panel overlay-left">
				<h1>Hola de vuelta!</h1>
				<p>Ingresa la informacion de tu cuenta aqui e inicia sesion!</p>
				<button class="ghost" id="signIn">Sign In</button>
			</div>
			<div class="overlay-panel overlay-right">
				<h1>Hola!</h1>
				<p>Ingresa tus datos y crea tu cuenta ya!</p>
				<button class="ghost" id="signUp">Sign Up</button>
			</div>
		</div>
	</div>
</div>



<script> 

    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const container = document.getElementById('container');
    
    signUpButton.addEventListener('click', () => {
        container.classList.add("right-panel-active");
    });
    
    signInButton.addEventListener('click', () => {
        container.classList.remove("right-panel-active");
    }); 
</script>

<?php
session_start();

$servername = "52.0.219.41";
$usernamedb = "mauro";
$passworddb = "eccbc87e4b5ce2fe28308fd9f2a7baf3";
$dbname = "devuniontest";

$conn = new mysqli($servername, $usernamedb, $passworddb, $dbname);

if ($conn->connect_error) {
	die("<script>alert('Falló la conexión a la base de datos.');</script>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$cedula = $_POST['email_login'];
	$passw = $_POST['passw_login'];

    $stmt = $conn->prepare('SELECT id, cedula, estado FROM usuario WHERE cedula = ? AND contrasenia = ?');
    $stmt->bind_param('ss', $cedula, $passw);
    $stmt->execute();
    $result = $stmt->get_result();

    $user = $result->fetch_assoc();

	if ($user) {
		$_SESSION['user_id'] = $user['id'];
		$_SESSION['cedula'] = $user['cedula'];
		// $_SESSION['estado'] = $user['estado'];
		echo "<script>
		alert('Registro y autenticación exitosos.');
		window.location.href = 'index.php';
	  </script>";
		exit;
	} else {
		echo "<script>alert('Usuario o contraseña inválidos');</script>";
	}
}

$conn->close();
?>