<?php
class HorasTrabajo
{
    private int $id;
    private int $socioId;
    private string $semana;
    private int $horasRegistradas;
    private ?string $motivoInasistencia;
    private string $tipoCompensacion; // 'ninguna' | 'exoneracion' | 'pago_compensatorio'
    private ?int $pagoCompensatorioId;
    private string $estado; // 'pendiente' | 'aprobado' | 'rechazado'
    private string $fechaRegistro;

    public function __construct(
        int $id,
        int $socioId,
        string $semana,
        int $horasRegistradas,
        ?string $motivoInasistencia,
        string $tipoCompensacion,
        ?int $pagoCompensatorioId,
        string $estado,
        string $fechaRegistro
    ) {
        $this->id = $id;
        $this->socioId = $socioId;
        $this->semana = $semana;
        $this->horasRegistradas = $horasRegistradas;
        $this->motivoInasistencia = $motivoInasistencia;
        $this->tipoCompensacion = $tipoCompensacion;
        $this->pagoCompensatorioId = $pagoCompensatorioId;
        $this->estado = $estado;
        $this->fechaRegistro = $fechaRegistro;
    }

    public function getId(): int
    {
        return $this->id;
    }
    public function getSocioId(): int
    {
        return $this->socioId;
    }
    public function getSemana(): string
    {
        return $this->semana;
    }
    public function getHorasRegistradas(): int
    {
        return $this->horasRegistradas;
    }
    public function getMotivoInasistencia(): ?string
    {
        return $this->motivoInasistencia;
    }
    public function getTipoCompensacion(): string
    {
        return $this->tipoCompensacion;
    }
    public function getPagoCompensatorioId(): ?int
    {
        return $this->pagoCompensatorioId;
    }
    public function getEstado(): string
    {
        return $this->estado;
    }
    public function getFechaRegistro(): string
    {
        return $this->fechaRegistro;
    }

    public function setHorasRegistradas(int $horas): void
    {
        $this->horasRegistradas = $horas;
    }
    public function setMotivoInasistencia(?string $motivo): void
    {
        $this->motivoInasistencia = $motivo;
    }
    public function setTipoCompensacion(string $tipo): void
    {
        $this->tipoCompensacion = $tipo;
    }
    public function setPagoCompensatorioId(?int $id): void
    {
        $this->pagoCompensatorioId = $id;
    }
    public function setEstado(string $estado): void
    {
        $this->estado = $estado;
    }
}
