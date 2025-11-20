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
        if ($action === 'list') {
            $stmt = $conn->query("
                SELECT h.id, h.socio_id, u.nombre, u.apellido, h.semana, h.horas_registradas,
                       h.motivo_inasistencia, h.tipo_compensacion, h.estado, h.fecha_registro
                FROM horas_trabajo h
                INNER JOIN usuarios u ON u.id = h.socio_id
                ORDER BY h.fecha_registro DESC
            ");
            $rows = $stmt->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($action === 'update_estado' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['id'] ?? 0);
            $estado = $input['estado'] ?? '';

            if ($id <= 0 || !in_array($estado, ['pendiente', 'aprobado', 'rechazado']))
                throw new Exception("Datos inválidos");

            $stmt = $conn->prepare("UPDATE horas_trabajo SET estado=? WHERE id=?");
            $stmt->bind_param("si", $estado, $id);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => "Estado actualizado"]);
            exit;
        }

        if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['id'] ?? 0);

            if ($id <= 0)
                throw new Exception("ID inválido");

            $stmt = $conn->prepare("DELETE FROM horas_trabajo WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => "Registro eliminado"]);
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
    <title>Backoffice - Horarios</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f8fb;
            padding: 1rem;
        }

        h1 {
            margin-bottom: 1rem;
        }

        table {
            background: #fff;
            border-collapse: collapse;
            width: 100%;
            margin-top: 1rem;
        }

        th,
        td {
            padding: 0.6rem;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        thead {
            background: #fafafa;
        }

        tr:hover {
            background: #f9f9ff;
        }

        button {
            padding: 0.3rem 0.6rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 0.3rem;
        }

        .btn-approve {
            background: #4caf50;
            color: #fff;
        }

        .btn-reject {
            background: #f44336;
            color: #fff;
        }

        .btn-delete {
            background: #999;
            color: #fff;
        }

        .estado {
            font-weight: bold;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
        }

        .estado-pendiente {
            background: #ffeb3b;
        }

        .estado-aprobado {
            background: #4caf50;
            color: #fff;
        }

        .estado-rechazado {
            background: #f44336;
            color: #fff;
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
    <h1>📋 Backoffice - Horarios de Trabajo</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Socio</th>
                <th>Semana</th>
                <th>Horas</th>
                <th>Motivo</th>
                <th>Compensación</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="tablaHorarios">
            <tr>
                <td colspan="8">Cargando...</td>
            </tr>
        </tbody>
    </table>

    <script>
        async function cargarHorarios() {
            let res = await fetch("?action=list");
            let data = await res.json();
            let tbody = document.getElementById("tablaHorarios");
            tbody.innerHTML = "";

            if (!data.success || data.data.length === 0) {
                tbody.innerHTML = "<tr><td colspan='8'>No hay registros</td></tr>";
                return;
            }

            data.data.forEach(h => {
                let tr = document.createElement("tr");

                tr.innerHTML = `
                    <td>${h.id}</td>
                    <td>${h.nombre} ${h.apellido} (ID:${h.socio_id})</td>
                    <td>${h.semana}</td>
                    <td>${h.horas_registradas}</td>
                    <td>${h.motivo_inasistencia || '-'}</td>
                    <td>${h.tipo_compensacion}</td>
                    <td><span class="estado estado-${h.estado}">${h.estado}</span></td>
                    <td>
                        <button class="btn-approve" onclick="updateEstado(${h.id}, 'aprobado')">✔</button>
                        <button class="btn-reject" onclick="updateEstado(${h.id}, 'rechazado')">✖</button>
                        <button class="btn-delete" onclick="eliminar(${h.id})">🗑</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        async function updateEstado(id, estado) {
            if (!confirm("¿Seguro que quieres marcar como " + estado + "?")) return;
            let res = await fetch("?action=update_estado", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id, estado })
            });
            let data = await res.json();
            alert(data.message);
            if (data.success) cargarHorarios();
        }

        async function eliminar(id) {
            if (!confirm("¿Eliminar este registro?")) return;
            let res = await fetch("?action=delete", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id })
            });
            let data = await res.json();
            alert(data.message);
            if (data.success) cargarHorarios();
        }

        cargarHorarios();
    </script>
</body>

</html>