<?php

require_once __DIR__ . '/../model/Pagos.php';

class PagosController
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function crearPago(array $data): Pagos
    {
        if (!isset($data['usuario_id'], $data['monto'], $data['semana'])) {
            throw new Exception("Faltan campos obligatorios");
        }

        $stmt = $this->conn->prepare("INSERT INTO pagos (usuario_id, monto, semana) VALUES (?, ?, ?)");
        $stmt->bind_param(
            "iii",
            $data['usuario_id'],
            $data['monto'],
            $data['semana']
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        $idPago = $stmt->insert_id;

        return new Pagos(
            $idPago,
            $data['usuario_id'],
            $data['monto'],
            $data['semana']
        );
    }

    public function obtenerPagosPorUsuario(int $usuario_id): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM pagos WHERE usuario_id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pagos = [];
        while ($row = $result->fetch_assoc()) {
            $pagos[] = new Pagos(
                $row['id'],
                $row['usuario_id'],
                $row['monto'],
                $row['semana'],
                $row['fecha'],
            );
        }
        return $pagos;
    }

    public function obtenerDatosPago(Pagos $pago): array
    {
        return [
            'id' => $pago->getId(),
            'usuario_id' => $pago->getUsuarioId(),
            'monto' => $pago->getMonto(),
            'fecha' => $pago->getFecha(),
            'semana' => $pago->getSemana()
        ];
    }
}
