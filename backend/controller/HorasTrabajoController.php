<?php
require_once __DIR__ . '/../model/HorasTrabajo.php';

class HorasTrabajoController
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function getById(int $id): ?HorasTrabajo
    {
        $stmt = $this->conn->prepare("SELECT * FROM horas_trabajo WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (!$row) {
            throw new Exception("Registro de horas no encontrado");
        }

        return $this->mapRowToModel($row);
    }

    public function getAll(): array
    {
        $result = $this->conn->query("SELECT * FROM horas_trabajo ORDER BY fecha_registro DESC");
        $horas = [];

        while ($row = $result->fetch_assoc()) {
            $horas[] = $this->mapRowToModel($row);
        }

        return $horas;
    }

    public function create(array $data): HorasTrabajo
    {
        $tipoComp = $data['tipo_compensacion'] ?? 'ninguna';
        $pagoComp = $data['pago_compensatorio_id'] ?? null;
        $motivo = $data['motivo_inasistencia'] ?? null;

        $stmt = $this->conn->prepare("
            INSERT INTO horas_trabajo 
            (socio_id, semana, horas_registradas, motivo_inasistencia, tipo_compensacion, pago_compensatorio_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "isissi",
            $data['socio_id'],
            $data['semana'],
            $data['horas_registradas'],
            $motivo,
            $tipoComp,
            $pagoComp
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al crear registro: " . $stmt->error);
        }

        $id = $stmt->insert_id;
        return $this->getById($id);
    }

    public function update(int $id, array $data): HorasTrabajo
    {
        $horas = $this->getById($id);

        $socioId = $data['socio_id'] ?? $horas->getSocioId();
        $semana = $data['semana'] ?? $horas->getSemana();
        $horasReg = $data['horas_registradas'] ?? $horas->getHorasRegistradas();
        $motivo = $data['motivo_inasistencia'] ?? $horas->getMotivoInasistencia();
        $tipoComp = $data['tipo_compensacion'] ?? $horas->getTipoCompensacion();
        $pagoComp = $data['pago_compensatorio_id'] ?? $horas->getPagoCompensatorioId();
        $estado = $data['estado'] ?? $horas->getEstado();

        $stmt = $this->conn->prepare("
            UPDATE horas_trabajo
            SET socio_id=?, semana=?, horas_registradas=?, motivo_inasistencia=?, tipo_compensacion=?, pago_compensatorio_id=?, estado=?
            WHERE id=?
        ");

        $stmt->bind_param(
            "isissisi",
            $socioId,
            $semana,
            $horasReg,
            $motivo,
            $tipoComp,
            $pagoComp,
            $estado,
            $id
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar registro: " . $stmt->error);
        }

        return $this->getById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM horas_trabajo WHERE id = ?");
        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            throw new Exception("Error al eliminar registro: " . $stmt->error);
        }

        return true;
    }

    private function mapRowToModel(array $row): HorasTrabajo
    {
        return new HorasTrabajo(
            $row['id'],
            $row['socio_id'],
            $row['semana'],
            $row['horas_registradas'],
            $row['motivo_inasistencia'],
            $row['tipo_compensacion'],
            $row['pago_compensatorio_id'],
            $row['estado'],
            $row['fecha_registro']
        );
    }

    public function formatHorasTrabajo(HorasTrabajo $horas): array
    {
        return [
            'id' => $horas->getId(),
            'socio_id' => $horas->getSocioId(),
            'semana' => $horas->getSemana(),
            'horas_registradas' => $horas->getHorasRegistradas(),
            'motivo_inasistencia' => $horas->getMotivoInasistencia(),
            'tipo_compensacion' => $horas->getTipoCompensacion(),
            'pago_compensatorio_id' => $horas->getPagoCompensatorioId(),
            'estado' => $horas->getEstado(),
            'fecha_registro' => $horas->getFechaRegistro()
        ];
    }
}
