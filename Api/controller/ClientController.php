<?php
require 'model/user.php';

class ClientController {

    private $userObj;


    public function __construct() {

        $this->userObj = new UserModel();

    }

    public function tester() {
        return $this->userObj->test();
    }
 
    public function newUser($nombre, $apellido, $tel, $correo, $cedula, $passw) {
        $this->userObj->newUser($nombre, $apellido, $tel, $correo, $cedula, $passw); 
        return ['success' => 'Creada'];
    }

    public function eliminarUser($id) {
        $this->userObj->eliminarUser($id); 
        return ['success' => 'borrado'];
    }
    public function cambiarUsername($newusername,$oldusername) {
        $this->userObj->cambiarUsername($newusername,$oldusername); 
        return ['success' => 'cambiado'];
    }



    
}
?>
