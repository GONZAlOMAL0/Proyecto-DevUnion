<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/db.php';

if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_GET['action'];

    try {
        /* ========================
           LISTAR CASAS
        ======================== */
        if ($action === 'list') {
            $stmt = $conn->query("SELECT * FROM casas ORDER BY id ASC");
            $rows = $stmt->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'data' => $rows]);
            exit;
        }

        /* ========================
           CREAR CASA
        ======================== */
        if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $nombre = trim($input['nombre'] ?? '');

            if ($nombre === '')
                throw new Exception("El nombre no puede estar vacío");

            $stmt = $conn->prepare("INSERT INTO casas (nombre) VALUES (?)");
            $stmt->bind_param("s", $nombre);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => "Casa creada"]);
            exit;
        }

        /* ========================
           UPDATE CASA
        ======================== */
        if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['id'] ?? 0);
            $nombre = trim($input['nombre'] ?? '');

            if ($id <= 0 || $nombre === '')
                throw new Exception("Datos inválidos");

            $stmt = $conn->prepare("UPDATE casas SET nombre=? WHERE id=?");
            $stmt->bind_param("si", $nombre, $id);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => "Casa actualizada"]);
            exit;
        }

        /* ========================
           ELIMINAR CASA
        ======================== */
        if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);

            $id = intval($input['id'] ?? 0);
            if ($id <= 0)
                throw new Exception("ID inválido");

            $stmt = $conn->prepare("DELETE FROM casas WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => "Casa eliminada"]);
            exit;
        }

        throw new Exception("Acción no soportada");

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Backoffice - Casas</title>
    <style>
        body {
            font-family: "Inter", Arial, sans-serif;
            background: #eef1f7;
            padding: 2rem;
            color: #333;
        }

        h1 {
            margin-bottom: 1.5rem;
            font-size: 1.9rem;
            color: #2c3e50;
        }

        /* Tarjeta del formulario */
        #formCrear {
            background: #ffffff;
            padding: 1.5rem;
            width: 350px;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        #formCrear h3 {
            margin-top: 0;
            margin-bottom: 0.8rem;
            color: #34495e;
        }

        input {
            width: 100%;
            padding: 0.6rem;
            margin-top: 0.4rem;
            margin-bottom: 0.8rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        button {
            padding: 0.45rem 0.8rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        #btnCrear {
            background: #3498db;
            color: white;
            width: 100%;
            font-weight: bold;
        }

        #btnCrear:hover {
            background: #2980b9;
        }

        /* Tabla */
        table {
            width: 100%;
            background: #fff;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        th {
            background: #f4f6fa;
            font-weight: bold;
            color: #555;
        }

        th,
        td {
            padding: 0.8rem;
            border-bottom: 1px solid #eee;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: #fafbfe;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .btn-delete:hover {
            background: #c0392b;
        }
    </style>
</head>

<body>
    <a href="index.php" style="
       display:inline-block;
       padding:0.55rem 1.2rem;
       background:#2980b9;
       color:white;
       border-radius:8px;
       text-decoration:none;
       font-weight:600;
       box-shadow:0 2px 6px rgba(0,0,0,0.15);
       transition:0.2s;
       margin-bottom:1rem;
   " onmouseover="this.style.background='#1f6fa3'" onmouseout="this.style.background='#2980b9'">
        ⬅ Volver al Panel
    </a>
    <h1>🏠 Backoffice - Casas</h1>

    <div id="formCrear">
        <h3>Nueva Casa</h3>
        <input type="text" id="nombreNueva" placeholder="Nombre de la casa">
        <button id="btnCrear" onclick="crearCasa()">Crear</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="tablaCasas">
            <tr>
                <td colspan="3">Cargando...</td>
            </tr>
        </tbody>
    </table>

    <script>
        async function cargarCasas() {
            let res = await fetch("?action=list");
            let data = await res.json();

            let tbody = document.getElementById("tablaCasas");
            tbody.innerHTML = "";

            if (!data.success || data.data.length === 0) {
                tbody.innerHTML = "<tr><td colspan='3'>No hay casas</td></tr>";
                return;
            }

            data.data.forEach(c => {
                let tr = document.createElement("tr");

                tr.innerHTML = `
                    <td>${c.id}</td>
                    <td>
                        <input style="width:90%; padding:0.4rem; border:1px solid #ccc; border-radius:6px;" 
                            value="${c.nombre}" onchange="updateCasa(${c.id}, this.value)">
                    </td>
                    <td>
                        <button class="btn-delete" onclick="eliminarCasa(${c.id})">🗑 Eliminar</button>
                    </td>
                `;

                tbody.appendChild(tr);
            });
        }

        async function crearCasa() {
            let nombre = document.getElementById("nombreNueva").value;
            if (nombre.trim() === "") return alert("Ingrese un nombre");

            let res = await fetch("?action=create", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ nombre })
            });

            let data = await res.json();
            alert(data.message);
            if (data.success) {
                document.getElementById("nombreNueva").value = "";
                cargarCasas();
            }
        }

        async function updateCasa(id, nombre) {
            let res = await fetch("?action=update", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id, nombre })
            });

            let data = await res.json();
            if (!data.success) alert(data.message);
        }

        async function eliminarCasa(id) {
            if (!confirm("¿Eliminar esta casa?")) return;

            let res = await fetch("?action=delete", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id })
            });

            let data = await res.json();
            alert(data.message);
            if (data.success) cargarCasas();
        }

        cargarCasas();
    </script>

</body>

</html>