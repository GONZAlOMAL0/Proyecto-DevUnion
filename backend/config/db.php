<?php

$conn = new mysqli("localhost", "root", "", "proyecto");

if ($conn->connect_error) {
    throw new Exception('Error de conexión a la base de datos: ' . $conn->connect_error);
}
