<?php
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

function responder($success, $data = null, $message = null, $status = 200)
{
    http_response_code($status);
    $response = ['success' => $success];
    if ($data !== null)
        $response['data'] = $data;
    if ($message !== null)
        $response['message'] = $message;
    echo json_encode($response);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            // ✅ GET con socio_id obligatorio
            if (!isset($_GET['socio_id'])) {
                responder(false, null, "Debe indicar socio_id", 400);
            }

            $socio_id = intval($_GET['socio_id']);
            $stmt = $conn->prepare("SELECT * FROM horas_trabajo WHERE socio_id = ? ORDER BY semana DESC");
            $stmt->bind_param("i", $socio_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $horas = [];
            while ($row = $result->fetch_assoc()) {
                $horas[] = $row;
            }

            responder(true, $horas);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                responder(false, null, "Datos inválidos", 400);
            }

            $socio_id = $input['socio_id'] ?? null;
            $semana = $input['semana'] ?? null;
            $horas_registradas = $input['horas_registradas'] ?? null;
            $motivo_inasistencia = $input['motivo_inasistencia'] ?? null;
            $tipo_compensacion = $input['tipo_compensacion'] ?? 'ninguna';
            $pago_compensatorio_id = $input['pago_compensatorio_id'] ?? null;

            if (!$socio_id || !$semana || $horas_registradas === null) {
                responder(false, null, "Campos requeridos: socio_id, semana, horas_registradas", 400);
            }

            if ($horas_registradas < 21) {
                if (!$motivo_inasistencia || !$tipo_compensacion) {
                    responder(false, null, "Si las horas son menores a 21, debe indicar motivo y tipo_compensacion", 400);
                }
                if ($tipo_compensacion === "pago_compensatorio" && !$pago_compensatorio_id) {
                    responder(false, null, "Debe indicar pago_compensatorio_id para pago compensatorio", 400);
                }
            } else {
                $tipo_compensacion = "ninguna";
                $motivo_inasistencia = null;
                $pago_compensatorio_id = null;
            }

            $stmt = $conn->prepare("INSERT INTO horas_trabajo 
                (socio_id, semana, horas_registradas, motivo_inasistencia, tipo_compensacion, pago_compensatorio_id, estado) 
                VALUES (?, ?, ?, ?, ?, ?, 'pendiente')");
            $stmt->bind_param(
                "isissi",
                $socio_id,
                $semana,
                $horas_registradas,
                $motivo_inasistencia,
                $tipo_compensacion,
                $pago_compensatorio_id
            );

            if ($stmt->execute()) {
                $id = $stmt->insert_id;
                $res = $conn->query("SELECT * FROM horas_trabajo WHERE id = $id")->fetch_assoc();
                responder(true, $res, "Registro creado correctamente", 201);
            } else {
                responder(false, null, $stmt->error, 500);
            }
            break;

        default:
            responder(false, null, "Método no permitido", 405);
    }
} catch (Exception $e) {
    responder(false, null, $e->getMessage(), 500);
}
