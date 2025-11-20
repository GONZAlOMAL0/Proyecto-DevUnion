<?php

require_once __DIR__ . '/../model/Usuario.php';
require_once __DIR__ . '/../model/UnidadHabitacional.php';
require_once __DIR__ . '/../model/Tarjeta.php';
require_once __DIR__ . '/../model/Rol.php';
require_once __DIR__ . '/../utils/jwt.php';

class UsuarioController
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }
    public function getUsuarioById(int $id): ?Usuario
    {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (!$row) {
            throw new Exception("Usuario no encontrado");
        }

        $tarjeta = $this->obtenerTarjetaPorUsuario($row['id']);
        $telefonos = $this->obtenerTelefonosPorUsuario($row['id']);

        $casa = $this->obtenerCasaPorUsuario($row['id']);

        return new Usuario(
            $row['id'],
            $row['nombre'],
            $row['apellido'],
            new DateTime($row['fechaRegistro']),
            $row['cedula'],
            $row['contrasenia'],
            $row['correo'],
            $row['estado'],
            Rol::from($row['rol']),
            $tarjeta,
            $telefonos,
            $row['direccion'],
            $row['estado_pago'],
            $casa // 👈 ahora se incluye
        );
    }

    public function login(string $cedula, string $contrasenia): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE cedula = ? LIMIT 1");
        $stmt->bind_param("s", $cedula);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($contrasenia, $row['contrasenia'])) {
                $tarjeta = $this->obtenerTarjetaPorUsuario($row['id']);
                $usuario = new Usuario(
                    $row['id'],
                    $row['nombre'],
                    $row['apellido'],
                    new DateTime($row['fechaRegistro']),
                    $row['cedula'],
                    $row['contrasenia'],
                    $row['correo'],
                    $row['estado'],
                    Rol::from($row['rol']),
                    $tarjeta,
                    $this->obtenerTelefonosPorUsuario($row['id']),
                    $row['direccion']
                );

                $token = generarJWT([
                    'sub' => $usuario->getId(),
                    'rol' => $usuario->getRol()->value,
                    'iat' => time(),
                ]);

                return [
                    'usuario' => $usuario,
                    'token' => $token
                ];
            }
        }

        return null;
    }

    public function register(array $data): ?array
    {
        if (!isset($data['nombre'], $data['apellido'], $data['cedula'], $data['contrasenia'], $data['correo'])) {
            throw new Exception("Faltan campos obligatorios");
        }

        $hashContrasenia = password_hash($data['contrasenia'], PASSWORD_BCRYPT);

        $rolDb = strtoupper($data['rol'] ?? 'USUARIO');
        if (!in_array($rolDb, ['ADMIN', 'USUARIO', 'INVITADO'])) {
            throw new Exception("Rol inválido");
        }

        $stmt = $this->conn->prepare("
        INSERT INTO usuarios (nombre, apellido, cedula, contrasenia, correo, direccion) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
        }

        $direccion = $data['direccion'] ?? '';
        $estadoPago = $data['estado_pago'] ?? 'PENDIENTE';

        $stmt->bind_param(
            "ssssss",
            $data['nombre'],
            $data['apellido'],
            $data['cedula'],
            $hashContrasenia,
            $data['correo'],
            $direccion
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        $idUsuario = $stmt->insert_id;

        if (!empty($data['telefonos']) && is_array($data['telefonos'])) {
            foreach ($data['telefonos'] as $tel) {
                $stmtTel = $this->conn->prepare("INSERT INTO telefonos (usuario_id, telefono) VALUES (?, ?)");
                $stmtTel->bind_param("is", $idUsuario, $tel);
                $stmtTel->execute();
            }
        }

        $tarjeta = null;
        if (isset($data['tarjeta']) && is_array($data['tarjeta'])) {
            $stmtTar = $this->conn->prepare("
            INSERT INTO tarjetas (usuario_id, numero, cvv, duenoTarjeta) 
            VALUES (?, ?, ?, ?)
        ");
            $stmtTar->bind_param(
                "isss",
                $idUsuario,
                $data['tarjeta']['numero'],
                $data['tarjeta']['cvv'],
                $data['tarjeta']['dueno']
            );
            $stmtTar->execute();

            $tarjeta = new Tarjeta(
                $stmtTar->insert_id,
                $data['tarjeta']['numero'],
                $data['tarjeta']['cvv'],
                $data['tarjeta']['dueno']
            );
        }

        $usuario = new Usuario(
            $idUsuario,
            $data['nombre'],
            $data['apellido'],
            new DateTime(),
            $data['cedula'],
            $hashContrasenia,
            $data['correo'],
            true,
            Rol::from($rolDb),
            $tarjeta,
            $data['telefonos'] ?? [],
            $direccion,
            $estadoPago
        );

        $token = generarJWT([
            'sub' => $usuario->getId(),
            'rol' => Rol::from($rolDb)->value,
            'iat' => time(),
        ]);

        return [
            'usuario' => $usuario,
            'token' => $token
        ];
    }

    private function obtenerCasaPorUsuario(int $usuarioId): ?UnidadHabitacional
    {
        $stmt = $this->conn->prepare("
            SELECT c.id, c.nombre 
            FROM casas c
            INNER JOIN usuarios u ON u.casa_id = c.id
            WHERE u.id = ? LIMIT 1
        ");
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return new UnidadHabitacional($row['id'], $row['nombre']);
        }

        return null;
    }
    private function obtenerTarjetaPorUsuario(int $usuarioId): ?Tarjeta
    {
        $stmt = $this->conn->prepare("SELECT * FROM tarjetas WHERE usuario_id = ? LIMIT 1");
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return new Tarjeta($row['id'], $row['numero'], $row['cvv'], $row['duenoTarjeta']);
        }

        return null;
    }

    private function obtenerTelefonosPorUsuario(int $usuarioId): array
    {
        $telefonos = [];
        $stmt = $this->conn->prepare("SELECT telefono FROM telefonos WHERE usuario_id = ?");
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $telefonos[] = $row['telefono'];
        }

        return $telefonos;
    }

    public function obtenerDatosUsuario(Usuario $usuario): array
    {
        return [
            'id' => $usuario->getId(),
            'nombre' => $usuario->getNombre(),
            'apellido' => $usuario->getApellido(),
            'fechaRegistro' => $usuario->getFechaRegistro()->format("Y-m-d"),
            'cedula' => $usuario->getCedula(),
            'correo' => $usuario->getCorreo(),
            'estado' => $usuario->getEstado(),
            'rol' => $usuario->getRol()->value,
            'tarjeta' => $usuario->getTarjeta() ? [
                'numero' => $usuario->getTarjeta()->getNumeroTarjetaEnmascarado(),
                'dueno' => $usuario->getTarjeta()->getDuenoTarjeta()
            ] : null,
            'telefonos' => $usuario->getTelefonos(),
            'direccion' => $usuario->getDireccion(),
            'estado_pago' => $usuario->getEstadoPago(),
            'casa' => $usuario->getCasa() ? [
                'id' => $usuario->getCasa()->getId(),
                'nombre' => $usuario->getCasa()->getNombre()
            ] : null,
        ];
    }

    public function updateUsuario(int $id, array $data): ?Usuario
    {
        $usuario = $this->getUsuarioById($id);

        $nombre = $data['nombre'] ?? $usuario->getNombre();
        $apellido = $data['apellido'] ?? $usuario->getApellido();
        $correo = $data['correo'] ?? $usuario->getCorreo();
        $direccion = $data['direccion'] ?? $usuario->getDireccion();

        $contraseniaHash = $usuario->getContrasenia();
        if (!empty($data['contrasenia'])) {
            $contraseniaHash = password_hash($data['contrasenia'], PASSWORD_BCRYPT);
        }

        $stmt = $this->conn->prepare("
            UPDATE usuarios 
            SET nombre = ?, apellido = ?, correo = ?, contrasenia = ?, direccion = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            "sssssi",
            $nombre,
            $apellido,
            $correo,
            $contraseniaHash,
            $direccion,
            $id
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar usuario: " . $stmt->error);
        }

        if (isset($data['telefonos']) && is_array($data['telefonos'])) {
            $stmtDel = $this->conn->prepare("DELETE FROM telefonos WHERE usuario_id = ?");
            $stmtDel->bind_param("i", $id);
            $stmtDel->execute();

            foreach ($data['telefonos'] as $tel) {
                $stmtTel = $this->conn->prepare("INSERT INTO telefonos (usuario_id, telefono) VALUES (?, ?)");
                $stmtTel->bind_param("is", $id, $tel);
                $stmtTel->execute();
            }
        }

        if (isset($data['tarjeta']) && is_array($data['tarjeta'])) {
            $stmtTar = $this->conn->prepare("SELECT id FROM tarjetas WHERE usuario_id = ? LIMIT 1");
            $stmtTar->bind_param("i", $id);
            $stmtTar->execute();
            $result = $stmtTar->get_result();

            if ($row = $result->fetch_assoc()) {
                $stmtUpdateTar = $this->conn->prepare("
                UPDATE tarjetas SET numero = ?, cvv = ?, duenoTarjeta = ? WHERE id = ?
            ");
                $stmtUpdateTar->bind_param(
                    "sssi",
                    $data['tarjeta']['numero'],
                    $data['tarjeta']['cvv'],
                    $data['tarjeta']['dueno'],
                    $row['id']
                );
                $stmtUpdateTar->execute();
            } else {
                $stmtInsertTar = $this->conn->prepare("
                INSERT INTO tarjetas (usuario_id, numero, cvv, duenoTarjeta) VALUES (?, ?, ?, ?)
            ");
                $stmtInsertTar->bind_param(
                    "isss",
                    $id,
                    $data['tarjeta']['numero'],
                    $data['tarjeta']['cvv'],
                    $data['tarjeta']['dueno']
                );
                $stmtInsertTar->execute();
            }
        }

        return $this->getUsuarioById($id);
    }
}
