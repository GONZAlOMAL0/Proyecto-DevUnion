<?php
class Tarjeta {
    private int $id;
    private string $numeroTarjeta;
    private string $cvv;
    private string $duenoTarjeta;

    public function __construct(
        int $id,
        string $numeroTarjeta,
        string $cvv,
        string $duenoTarjeta
    ) {
        $this->id = $id;
        $this->numeroTarjeta = $numeroTarjeta;
        $this->cvv = $cvv;
        $this->duenoTarjeta = $duenoTarjeta;
    }

    public function getId(): int {
        return $this->id;
    }

    public function setNumeroTarjeta(string $numeroTarjeta): void {
        $this->numeroTarjeta = $numeroTarjeta;
    }

    public function setCvv(string $cvv): void {
        $this->cvv = $cvv;
    }

    public function setDuenoTarjeta(string $duenoTarjeta): void {
        $this->duenoTarjeta = $duenoTarjeta;
    }

    public function getDuenoTarjeta(): string {
        return $this->duenoTarjeta;
    }

    public function getNumeroTarjetaEnmascarado(): string {
        return str_repeat("*", 12) . substr($this->numeroTarjeta, -4);
    }
}
