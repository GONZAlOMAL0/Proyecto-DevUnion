<?php

require_once "Tarjeta.php";
require_once "Rol.php";
require_once "UnidadHabitacional.php";

class Usuario
{
    private int $id;
    private string $nombre;
    private string $apellido;
    private string $cedula;
    private string $contrasenia;
    private string $correo;
    private ?bool $estado;
    private ?DateTime $fechaRegistro;
    private Rol $rol;
    private ?Tarjeta $tarjeta;
    private array $telefonos = [];
    private string $direccion;
    private string $estadoPago;
    private ?UnidadHabitacional $casa;


    public function __construct(
        int $id,
        string $nombre,
        string $apellido,
        ?DateTime $fechaRegistro,
        string $cedula,
        string $contrasenia,
        string $correo,
        ?bool $estado,
        Rol $rol,
        ?Tarjeta $tarjeta = null,
        array $telefonos = [],
        string $direccion = '',
        string $estadoPago = '',
        ?UnidadHabitacional $casa = null
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->fechaRegistro = $fechaRegistro;
        $this->cedula = $cedula;
        $this->contrasenia = $contrasenia;
        $this->correo = $correo;
        $this->estado = $estado;
        $this->rol = $rol;
        $this->tarjeta = $tarjeta;
        $this->telefonos = $telefonos;
        $this->direccion = $direccion;
        $this->estadoPago = $estadoPago;
        $this->casa = $casa;
    }

    public function getId(): int
    {
        return $this->id;
    }
    public function getNombre(): string
    {
        return $this->nombre;
    }
    public function getApellido(): string
    {
        return $this->apellido;
    }
    public function getCedula(): string
    {
        return $this->cedula;
    }
    public function getContrasenia(): string
    {
        return $this->contrasenia;
    }
    public function getCorreo(): string
    {
        return $this->correo;
    }
    public function getEstado(): ?bool
    {
        return $this->estado;
    }
    public function getFechaRegistro(): ?DateTime
    {
        return $this->fechaRegistro;
    }

    public function getRol(): Rol
    {
        return $this->rol;
    }
    public function getTarjeta(): ?Tarjeta
    {
        return $this->tarjeta;
    }
    /** @return string[] */
    public function getTelefonos(): array
    {
        return $this->telefonos;
    }
    public function getDireccion(): string
    {
        return $this->direccion;
    }
    public function getEstadoPago(): string
    {
        return $this->estadoPago;
    }
    public function getCasa(): ?UnidadHabitacional
    {
        return $this->casa;
    }
    public function setCasa(?UnidadHabitacional $casa): void
    {
        $this->casa = $casa;
    }
    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }
    public function setApellido(string $apellido): void
    {
        $this->apellido = $apellido;
    }
    public function setTarjeta(?Tarjeta $tarjeta): void
    {
        $this->tarjeta = $tarjeta;
    }
    public function agregarTelefono(string $telefono): void
    {
        $this->telefonos[] = $telefono;
    }
    public function setEstadoPago(string $estadoPago): void
    {
        $this->estadoPago = $estadoPago;
    }
}
