<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Backoffice - Inicio</title>
    <style>
        body {
            margin: 0;
            font-family: Inter, Arial, sans-serif;
            background: #eef1f7;
            color: #333;
        }

        header {
            background: #2c3e50;
            padding: 1rem 2rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav a {
            color: white;
            margin-right: 1rem;
            text-decoration: none;
            font-weight: 500;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 1rem;
        }

        h1 {
            font-size: 1.8rem;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            transition: 0.2s ease;
            cursor: pointer;
            text-decoration: none;
            color: #333;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
        }

        .card h3 {
            margin: 0;
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .card p {
            margin: 0;
            opacity: 0.7;
        }
    </style>
</head>

<body>

    <header>
        <div><strong>Backoffice</strong></div>
        <nav>
            <a href="casas.php">Casas</a>
            <a href="horarios.php">Horarios</a>
            <a href="pagos.php">Pagos</a>
            <a href="#">Salir</a>
        </nav>
    </header>

    <div class="container">
        <h1>Panel de Administración</h1>
        <p>Selecciona una sección para comenzar.</p>

        <div class="cards">
            <a class="card" href="casas.php">
                <h3>🏠 Casas</h3>
                <p>Administrar casas registradas.</p>
            </a>

            <a class="card" href="horarios.php">
                <h3>🕒 Horarios</h3>
                <p>Gestionar horarios y disponibilidad.</p>
            </a>

            <a class="card" href="pagos.php">
                <h3>💳 Pagos</h3>
                <p>Controlar y revisar pagos del sistema.</p>
            </a>
        </div>
    </div>

</body>

</html>