<?php
class UserModel {
    private $conexion;

    public function __construct() {
        $this->conexion = new mysqli('52.0.219.41', 'mauro', 'eccbc87e4b5ce2fe28308fd9f2a7baf3', 'devuniontest');
        if ($this->conexion->connect_error) {
            die(json_encode(['error' => 'Conexión fallida: ' . $this->conexion->connect_error]));
        }
    }

    public function test() {
        return "Funciona";
    }


    public function newUser($nombre, $apellido, $tel, $correo, $contrasenia, $cedula) {


        $sql = "SELECT * FROM usuario WHERE cedula = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $cedula);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            die(json_encode(['error' => 'nombre de cedula ya esta en uso']));
        }
        $consulta = $this->conexion->prepare("INSERT INTO usuario (nombre, apellido, tel, correo, contrasenia, cedula) VALUES (?,?,?,?,?,?);");
        $consulta->bind_param('ssssss', $nombre, $apellido, $tel, $correo, $contrasenia, $cedula);
        if (!$consulta->execute()) {
            die(json_encode(['error' => 'Error en la consulta: ' . $consulta->error]));
        }
    }

    public function eliminarUser($id) {

        $consulta = $this->conexion->prepare("DELETE FROM usuario WHERE id_usuario = ?;");
        $consulta->bind_param('s', $id);
        if (!$consulta->execute()) {
            die(json_encode(['error' => 'Error en la consulta: ' . $consulta->error]));
        }
    }

    
    public function cambiarUsername($newusername, $oldusername) {
        if ($newusername === $oldusername) {
            die(json_encode(['error' => 'El nuevo nombre de usuario es igual al actual, no se realizó ningún cambio']));
        }

        $verificarOld = $this->conexion->prepare("SELECT COUNT(*) FROM usuario WHERE username = ?");
        $verificarOld->bind_param('s', $oldusername);
        $verificarOld->execute();
        $verificarOld->bind_result($oldCount);
        $verificarOld->fetch();
        $verificarOld->close();
    
        if ($oldCount == 0) {
            die(json_encode(['error' => 'El nombre de usuario actual no existe']));
        }
    
        $verificarNew = $this->conexion->prepare("SELECT COUNT(*) FROM usuario WHERE username = ?");
        $verificarNew->bind_param('s', $newusername);
        $verificarNew->execute();
        $verificarNew->bind_result($newCount);
        $verificarNew->fetch();
        $verificarNew->close();
    
        if ($newCount > 0) {
            die(json_encode(['error' => 'El nuevo nombre de usuario ya está en uso']));
        }
    
        $consulta = $this->conexion->prepare("UPDATE usuario SET username = ? WHERE username = ?");
        $consulta->bind_param('ss', $newusername, $oldusername);
        if (!$consulta->execute()) {
            die(json_encode(['error' => 'Error en la consulta: ' . $consulta->error]));
        }
    
        $consulta->close();
    }




}
?>