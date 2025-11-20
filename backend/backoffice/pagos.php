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
        if ($action === 'view_user') {
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0)
                throw new Exception('ID inválido');

            $stmt = $conn->prepare("SELECT id, nombre, apellido, correo, estado, estado_pago, casa_id FROM usuarios WHERE id = ? AND rol = 'USUARIO' LIMIT 1");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $user = $res->fetch_assoc();
            if (!$user)
                throw new Exception('Usuario no encontrado o no es USUARIO');

            $stmtP = $conn->prepare("SELECT id, usuario_id, monto, semana, fecha FROM pagos WHERE usuario_id = ? ORDER BY fecha ASC LIMIT 1");
            $stmtP->bind_param("i", $id);
            $stmtP->execute();
            $resP = $stmtP->get_result();
            $firstPago = $resP->fetch_assoc();

            $stmtC = $conn->prepare("
                SELECT id, nombre FROM casas
                WHERE id NOT IN (
                    SELECT casa_id FROM usuarios WHERE casa_id IS NOT NULL AND id <> ?
                )
                ORDER BY nombre
            ");
            $stmtC->bind_param("i", $id);
            $stmtC->execute();
            $resC = $stmtC->get_result();
            $casas = [];
            while ($r = $resC->fetch_assoc())
                $casas[] = $r;

            echo json_encode([
                'success' => true,
                'data' => [
                    'usuario' => $user,
                    'primerPago' => $firstPago,
                    'casasDisponibles' => $casas
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($action === 'assign_house' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input))
                throw new Exception('Payload inválido');

            $usuario_id = intval($input['usuario_id'] ?? 0);
            $casa_id = intval($input['casa_id'] ?? 0);
            if ($usuario_id <= 0 || $casa_id <= 0)
                throw new Exception('IDs inválidos');

            $stmtU = $conn->prepare("SELECT id, casa_id FROM usuarios WHERE id = ? AND rol = 'USUARIO' LIMIT 1");
            $stmtU->bind_param("i", $usuario_id);
            $stmtU->execute();
            $resU = $stmtU->get_result();
            $user = $resU->fetch_assoc();
            if (!$user)
                throw new Exception('Usuario no encontrado o no es USUARIO');

            $stmtCheck = $conn->prepare("SELECT id FROM usuarios WHERE casa_id = ? AND id <> ? LIMIT 1");
            $stmtCheck->bind_param("ii", $casa_id, $usuario_id);
            $stmtCheck->execute();
            $resCheck = $stmtCheck->get_result();
            if ($resCheck && $resCheck->num_rows > 0) {
                throw new Exception('La casa ya está asignada a otro usuario');
            }

            $stmtUpdate = $conn->prepare("UPDATE usuarios SET casa_id = ?, estado_pago = 'PAGO' WHERE id = ?");
            $stmtUpdate->bind_param("ii", $casa_id, $usuario_id);
            if (!$stmtUpdate->execute()) {
                throw new Exception('Error al asignar casa: ' . $stmtUpdate->error);
            }

            echo json_encode(['success' => true, 'message' => 'Casa asignada y estado_pago actualizado'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($action === 'toggle_active' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input))
                throw new Exception('Payload inválido');

            $usuario_id = intval($input['usuario_id'] ?? 0);
            if ($usuario_id <= 0)
                throw new Exception('ID inválido');

            $stmt = $conn->prepare("SELECT estado FROM usuarios WHERE id = ? AND rol = 'USUARIO' LIMIT 1");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            if (!$row)
                throw new Exception('Usuario no encontrado o no es USUARIO');

            $nuevoEstado = $row['estado'] == 1 ? 0 : 1;
            $stmtUpd = $conn->prepare("UPDATE usuarios SET estado = ? WHERE id = ?");
            $stmtUpd->bind_param("ii", $nuevoEstado, $usuario_id);
            if (!$stmtUpd->execute())
                throw new Exception('Error al actualizar estado: ' . $stmtUpd->error);

            echo json_encode(['success' => true, 'estado' => (int) $nuevoEstado], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($action === 'list_pagos') {
            $usuario_id = intval($_GET['usuario_id'] ?? 0);
            if ($usuario_id <= 0)
                throw new Exception('ID inválido');

            $stmt = $conn->prepare("SELECT id, monto, semana, fecha 
                            FROM pagos 
                            WHERE usuario_id = ? 
                            ORDER BY fecha ASC");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $pagos = [];
            while ($p = $res->fetch_assoc())
                $pagos[] = $p;

            echo json_encode(['success' => true, 'data' => $pagos], JSON_UNESCAPED_UNICODE);
            exit;
        }

        throw new Exception('Acción no soportada');
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$usuariosQ = $conn->query("SELECT u.id, u.nombre, u.apellido, u.correo, u.estado, u.estado_pago, c.nombre as casa
                           FROM usuarios u
                           LEFT JOIN casas c ON u.casa_id = c.id
                           WHERE u.rol = 'USUARIO'
                           ORDER BY u.id DESC");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Backoffice - Usuarios (Aprobación por pago)</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            padding: 1rem;
            background: #f7f8fb;
            color: #222;
        }

        .layout {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 1rem;
            align-items: start;
        }

        table {
            background: #fff;
            border-collapse: collapse;
            width: 100%;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        th,
        td {
            padding: 0.6rem 0.75rem;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        thead th {
            background: #fafafa;
            position: sticky;
            top: 0;
        }

        tr:hover {
            background: #fcfcff;
            cursor: pointer;
        }

        .panel {
            background: #fff;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .muted {
            color: #666;
            font-size: 0.95rem;
        }

        label {
            display: block;
            margin: 0.5rem 0 0.25rem;
            font-weight: 600;
        }

        select,
        button {
            padding: 0.5rem;
            border-radius: 6px;
            border: 1px solid #dcdcdc;
        }

        .btn {
            background: #2469ff;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 6px;
        }

        .btn.secondary {
            background: #f3f4f6;
            color: #111;
            border: 1px solid #dcdcdc;
        }

        .info-row {
            margin-bottom: 0.5rem;
        }

        .small {
            font-size: 0.9rem;
            color: #444;
        }

        .empty {
            color: #999;
            font-style: italic;
        }

        .action-cell {
            width: 120px;
            text-align: center;
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
    <h1>Backoffice - Aprobación por Pago</h1>
    <p class="muted">Listado de usuarios (rol = USUARIO). Seleccioná un usuario para ver su primer pago y
        asignarle/cambiarle casa. También podés activar/desactivar desde la fila o desde el panel.</p>

    <div class="layout">
        <div>
            <div class="panel" style="margin-bottom:1rem;">
                <strong>Usuarios</strong>
                <div class="small">Clic en una fila para ver detalles</div>
            </div>

            <div class="panel">
                <table id="usersTable" aria-label="Usuarios">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Estado</th>
                            <th>Estado Pago</th>
                            <th>Casa</th>
                            <th class="action-cell">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = $usuariosQ->fetch_assoc()): ?>
                            <tr data-userid="<?= $u['id'] ?>">
                                <td><?= $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?></td>
                                <td><?= htmlspecialchars($u['correo']) ?></td>
                                <td><?= $u['estado'] ? 'Activo' : 'Inactivo' ?></td>
                                <td><?= htmlspecialchars($u['estado_pago']) ?></td>
                                <td><?= $u['casa'] ?? 'Sin asignar' ?></td>
                                <td class="action-cell">
                                    <button class="btn secondary toggle-btn" data-userid="<?= $u['id'] ?>"
                                        data-estado="<?= $u['estado'] ?>">
                                        <?= $u['estado'] ? 'Desactivar' : 'Activar' ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <aside>
            <div class="panel" id="detailPanel">
                <h3 id="detailTitle">Seleccioná un usuario</h3>
                <div id="detailContent" class="muted">
                    <p>Al seleccionar un usuario verás su primer pago y las casas disponibles para asignar o cambiar.
                        También podrás activar/desactivar el usuario desde aquí.</p>
                </div>
            </div>
        </aside>
    </div>

    <script>
        const API_BASE = '';
        const detailTitle = document.getElementById('detailTitle');
        const detailContent = document.getElementById('detailContent');

        function bindRowClickAndButtons() {
            document.querySelectorAll('#usersTable tbody tr').forEach(row => {
                row.addEventListener('click', () => {
                    const userId = row.getAttribute('data-userid');
                    cargarDetalleUsuario(userId);
                    document.querySelectorAll('#usersTable tbody tr').forEach(r => r.style.backgroundColor = '');
                    row.style.backgroundColor = '#eef6ff';
                });

                const btn = row.querySelector('.toggle-btn');
                if (btn) {
                    btn.addEventListener('click', async function (e) {
                        e.stopPropagation();
                        const uid = Number(this.dataset.userid);
                        const confirmMsg = (this.dataset.estado == '1') ? '¿Desactivar este usuario?' : '¿Activar este usuario?';
                        if (!confirm(confirmMsg)) return;

                        try {
                            const resp = await fetch(`${API_BASE}?action=toggle_active`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ usuario_id: uid })
                            });
                            const j = await resp.json();
                            if (!j.success) throw new Error(j.message || 'Error');

                            const newEstado = Number(j.estado);
                            const row = document.querySelector(`#usersTable tbody tr[data-userid="${uid}"]`);
                            if (row) {
                                row.children[3].textContent = newEstado ? 'Activo' : 'Inactivo';
                                const rowBtn = row.querySelector('.toggle-btn');
                                if (rowBtn) {
                                    rowBtn.textContent = newEstado ? 'Desactivar' : 'Activar';
                                    rowBtn.dataset.estado = newEstado;
                                }
                            }

                            const hiddenInput = document.querySelector('#assignForm input[name="usuario_id"]');
                            if (hiddenInput && Number(hiddenInput.value) === uid) {
                                cargarDetalleUsuario(uid);
                            }

                            alert('Estado actualizado');
                        } catch (err) {
                            console.error(err);
                            alert('Error: ' + (err.message || 'no se pudo cambiar estado'));
                        }
                    });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            bindRowClickAndButtons();
        });

        async function cargarDetalleUsuario(userId) {
            detailTitle.textContent = 'Cargando...';
            detailContent.innerHTML = '';

            try {
                const res = await fetch(`${API_BASE}?action=view_user&id=${encodeURIComponent(userId)}`);
                const json = await res.json();
                if (!json.success) {
                    detailTitle.textContent = 'Error';
                    detailContent.innerHTML = `<div class="empty">${json.message || 'Error al obtener datos'}</div>`;
                    return;
                }

                const { usuario, primerPago, casasDisponibles } = json.data;
                detailTitle.textContent = `Usuario: ${usuario.nombre} ${usuario.apellido} (ID ${usuario.id})`;

                let html = '';
                html += `<div class="info-row"><strong>Correo:</strong> <span class="small">${escapeHtml(usuario.correo)}</span></div>`;
                html += `<div class="info-row"><strong>Estado:</strong> <span class="small">${usuario.estado ? 'Activo' : 'Inactivo'}</span></div>`;
                html += `<div class="info-row"><strong>Estado de pago:</strong> <span class="small">${escapeHtml(usuario.estado_pago)}</span></div>`;

                if (primerPago) {
                    const fecha = new Date(primerPago.fecha).toLocaleString('es-ES');
                    html += `<hr>`;
                    html += `<div class="info-row" style="display:flex; align-items:center; justify-content: space-between;"><strong>Primer pago</strong> 
                <button type="button" id="btnVerPagos" class="btn secondary small">Ver historial</button>
             </div>`;
                    html += `<div class="info-row small">Semana: ${primerPago.semana} — Monto: $${parseFloat(primerPago.monto).toFixed(2)}</div>`;
                    html += `<div class="info-row small">Fecha: ${fecha}</div>`;
                } else {
                    html += `<hr><div class="info-row"><strong>Primer pago:</strong> <span class="empty">No tiene pagos registrados</span></div>`;
                }
                html += `<div id="pagosExtra" style="margin-top:0.5rem;"></div>`;

                html += `<hr><form id="assignForm">`;
                html += `<input type="hidden" name="usuario_id" value="${usuario.id}">`;
                html += `<label for="casaSelect">Asignar / Cambiar casa (solo casas libres)</label>`;
                html += `<select id="casaSelect" name="casa_id" required>`;
                html += `<option value="">-- Seleccionar casa --</option>`;
                casasDisponibles.forEach(c => {
                    const selected = (Number(usuario.casa_id) === Number(c.id)) ? 'selected' : '';
                    html += `<option value="${c.id}" ${selected}>${escapeHtml(c.nombre)}${selected ? ' (actual)' : ''}</option>`;
                });
                html += `</select>`;
                html += `<div style="margin-top:.6rem;">`;
                html += `<button type="submit" class="btn">Asignar / Cambiar casa</button> `;
                html += `<button type="button" id="btnRefresh" class="btn secondary">Refrescar</button> `;
                html += `<button type="button" id="toggleActiveBtn" class="btn secondary">${usuario.estado ? 'Desactivar usuario' : 'Activar usuario'}</button>`;
                html += `</div>`;
                html += `</form>`;

                detailContent.innerHTML = html;

                document.getElementById('assignForm').addEventListener('submit', async function (e) {
                    e.preventDefault();
                    const casaId = Number(document.getElementById('casaSelect').value);
                    if (!casaId) {
                        alert('Seleccioná una casa');
                        return;
                    }
                    if (!confirm('¿Asignar/cambiar casa y marcar estado_pago como PAGO?')) return;

                    try {
                        const resp = await fetch(`${API_BASE}?action=assign_house`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ usuario_id: usuario.id, casa_id: casaId })
                        });
                        const j = await resp.json();
                        if (!j.success) throw new Error(j.message || 'Error');
                        alert('Hecho: ' + j.message);
                        location.reload();
                    } catch (err) {
                        console.error(err);
                        alert('Error: ' + (err.message || 'no se pudo asignar'));
                    }
                });

                const btnVerPagos = document.getElementById('btnVerPagos');
                if (btnVerPagos) {
                    btnVerPagos.addEventListener('click', async () => {
                        try {
                            const res = await fetch(`${API_BASE}?action=list_pagos&usuario_id=${usuario.id}`);
                            const j = await res.json();
                            if (!j.success) throw new Error(j.message || 'Error');

                            const pagos = j.data;
                            if (pagos.length === 0) {
                                document.getElementById('pagosExtra').innerHTML = `<div class="empty">Sin pagos</div>`;
                                return;
                            }

                            let pagosHtml = `<table style="width:100%; border-collapse:collapse; margin-top:0.5rem;">`;
                            pagosHtml += `<thead><tr><th>ID</th><th>Semana</th><th>Monto</th><th>Fecha</th></tr></thead><tbody>`;
                            pagos.forEach(p => {
                                const fecha = new Date(p.fecha).toLocaleString('es-ES');
                                pagosHtml += `<tr>
                    <td>${p.id}</td>
                    <td>${p.semana}</td>
                    <td>$${parseFloat(p.monto).toFixed(2)}</td>
                    <td>${fecha}</td>
                </tr>`;
                            });
                            pagosHtml += `</tbody></table>`;

                            document.getElementById('pagosExtra').innerHTML = pagosHtml;
                        } catch (err) {
                            console.error(err);
                            alert('Error al cargar pagos');
                        }
                    });
                }

                const toggleBtn = document.getElementById('toggleActiveBtn');
                toggleBtn.addEventListener('click', async () => {
                    const uid = usuario.id;
                    const confirmMsg = usuario.estado ? '¿Desactivar este usuario?' : '¿Activar este usuario?';
                    if (!confirm(confirmMsg)) return;

                    try {
                        const resp = await fetch(`${API_BASE}?action=toggle_active`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ usuario_id: uid })
                        });
                        const j = await resp.json();
                        if (!j.success) throw new Error(j.message || 'Error');

                        const newEstado = Number(j.estado);
                        const row = document.querySelector(`#usersTable tbody tr[data-userid="${uid}"]`);
                        if (row) {
                            row.children[3].textContent = newEstado ? 'Activo' : 'Inactivo';
                            const rowBtn = row.querySelector('.toggle-btn');
                            if (rowBtn) {
                                rowBtn.textContent = newEstado ? 'Desactivar' : 'Activar';
                                rowBtn.dataset.estado = newEstado;
                            }
                        }

                        cargarDetalleUsuario(uid);
                        alert('Estado actualizado');
                    } catch (err) {
                        console.error(err);
                        alert('Error: ' + (err.message || 'no se pudo cambiar estado'));
                    }
                });

                document.getElementById('btnRefresh').addEventListener('click', () => cargarDetalleUsuario(usuario.id));
            } catch (err) {
                console.error(err);
                detailTitle.textContent = 'Error';
                detailContent.innerHTML = `<div class="empty">Error al comunicarse con el servidor</div>`;
            }
        }

        function escapeHtml(s) {
            if (s === null || s === undefined) return '';
            return String(s).replace(/[&<>"']/g, function (m) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
            });
        }
    </script>
</body>

</html>