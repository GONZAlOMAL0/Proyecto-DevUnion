CREATE DATABASE 3MGPROYECTO;
USE 3MGPROYECTO;

CREATE TABLE Administrador (
    Id_Administrador INT PRIMARY KEY,
    Contrasena VARCHAR(50) NOT NULL,
    Correo VARCHAR(100) NOT NULL,
    Nombre VARCHAR(50) NOT NULL,
    Apellido VARCHAR(50) NOT NULL
);

CREATE TABLE TelefonoAdministrador (
    Id_Administrador INT,
    Telefono VARCHAR(20) NOT NULL,
    FOREIGN KEY (Id_Administrador) REFERENCES Administrador(Id_Administrador)
);

CREATE TABLE Usuario (
    Id_Usuario INT PRIMARY KEY,
    Estado VARCHAR(20) NOT NULL,
    Fecha_Registro DATE NOT NULL,
    Cedula VARCHAR(20) NOT NULL,
    Correo VARCHAR(100) NOT NULL,
    Contrasena VARCHAR(50) NOT NULL,
    Nombre VARCHAR(50) NOT NULL,
    Apellido VARCHAR(50) NOT NULL,
    Tipo_Usuario VARCHAR(30) NOT NULL,
    Direccion VARCHAR(100) NOT NULL
);

CREATE TABLE TelefonoUsuario (
    Id_Usuario INT,
    Telefono VARCHAR(20) NOT NULL,
    FOREIGN KEY (Id_Usuario) REFERENCES Usuario(Id_Usuario)
);

CREATE TABLE Tarjeta (
    NumTarjeta VARCHAR(20) PRIMARY KEY,
    Id_Usuario INT,
    NomTarjeta VARCHAR(100) NOT NULL,
    CVV VARCHAR(10) NOT NULL,
    FOREIGN KEY (Id_Usuario) REFERENCES Usuario(Id_Usuario)
);

CREATE TABLE HorasDeTrabajo (
    Id_Hoja INT PRIMARY KEY,
    Id_Usuario INT,
    Cantidad_Hojas INT NOT NULL,
    Estado VARCHAR(20) NOT NULL,
    Semana INT NOT NULL,
    Motivo TEXT,
    FOREIGN KEY (Id_Usuario) REFERENCES Usuario(Id_Usuario)
);

CREATE TABLE Pago (
    Id_Pago INT PRIMARY KEY,
    Id_Usuario INT,
    Suma DECIMAL(10, 2) NOT NULL,
    Estado VARCHAR(20) NOT NULL,
    FOREIGN KEY (Id_Usuario) REFERENCES Usuario(Id_Usuario)
);

CREATE TABLE UnidadHabitacional (
    Id_Unidad INT PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,
    Estado VARCHAR(20) NOT NULL,
    Fecha_Asignacion DATE NOT NULL
);