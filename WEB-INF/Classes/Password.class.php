<?php

include_once("Catalogo.class.php");
include_once("Mail.class.php");
/**
 * Description of Password
 *
 * @author MAGG
 */

class Password {
    private $empresa;
    public function recuperarPassword($usuario, $correo){
        $consulta = "SELECT IdUsuario, Loggin, correo FROM `c_usuario` WHERE Loggin = '$usuario' AND correo = '$correo';";
        $catalogo = new Catalogo();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        
        if(mysql_num_rows($result) > 0){
            $mail = new Mail();
            if(isset($this->empresa)){
                $mail->setEmpresa($this->empresa);
            }
            $mail->setSubject("Recuperación de contraseña");
            
            while($rs = mysql_fetch_array($result)){
                
            }
        }else{
            return false;
        }
    }
    
    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
}
