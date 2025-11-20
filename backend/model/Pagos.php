<?php

class Pagos
{
    private int $id;
    private int $usuario_id;
    private int $monto;
    private int $semana;
    private string $fecha;


    public function __construct(int $id, int $usuario_id, float $monto, int $semana, string $fecha = null)
    {
        $this->id = $id;
        $this->usuario_id = $usuario_id;
        $this->monto = $monto;
        $this->semana = $semana;
        $this->fecha = $fecha ?? date('Y-m-d H:i:s');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsuarioId(): int
    {
        return $this->usuario_id;
    }

    public function getMonto(): int
    {
        return $this->monto;
    }

    public function getSemana(): int
    {
        return $this->semana;
    }

    public function getFecha(): string {
        return $this->fecha;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setUsuarioId(int $usuario_id): void
    {
        $this->usuario_id = $usuario_id;
    }

    public function setMonto(int $monto): void
    {
        $this->monto = $monto;
    }

    public function setSemana(int $semana): void
    {
        $this->semana = $semana;
    }
}