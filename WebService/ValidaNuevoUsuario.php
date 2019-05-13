<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/UsuarioPendiente.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

function validaUsuario($IdUsuarioPendiente, $IdSession) {
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);

    if ($empresa == "0") {
        return -100;
    }

    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($IdSession);

    if ($resultadoLoggin > 0) {
        $obj_pendiente = new UsuarioPendiente();
        $obj_pendiente->setEmpresa($empresa);

        if ($obj_pendiente->getUsuarioById($IdUsuarioPendiente)) {
            if ($obj_pendiente->getActivo() == "0") {                
                return -4;//echo "Error: este enlace ya había sido procesado anteriormente";
            }

            if ((int) $obj_pendiente->getTiempoDiferencia() > 30) {                
                return -5;//echo "Error: este enlace han caducado, ya han pasado más de 30 minutos.";
            }
            
            $obj = new Usuario();
            $obj_aux = new Usuario();
            $obj->setEmpresa($empresa);
            $obj_aux->setEmpresa($empresa);

            $obj->setPuesto($obj_pendiente->getPuesto());
            $obj->setNombre($obj_pendiente->getNombre());
            $obj->setPaterno($obj_pendiente->getPaterno());
            $obj->setMaterno($obj_pendiente->getMaterno());
            $obj->setUsuario($obj_pendiente->getUsuario());
            $obj->setPassword($obj_pendiente->getPassword());
            $obj->setActivo(1);
            $obj->setUsuarioCreacion($obj_pendiente->getUsuarioCreacion());
            $obj->setUsuarioModificacion($obj_pendiente->getUsuarioModificacion());
            $obj->setPantalla($obj_pendiente->getPantalla());
            $obj->setEmail($obj_pendiente->getEmail());
            $obj->setTelefono($obj_pendiente->getTelefono());
            $obj->setSexo($obj_pendiente->getSexo());
            $obj->setFechaNacimiento($obj_pendiente->getFechaNacimiento());
            if (!$obj->getUsuarioByUser($obj->getUsuario())) {//si no existe el nombre de usuario
                if ($obj_aux->getRegistroByEmail($obj->getEmail())) { //Si el correo electronico ya está registrado.
                    if ($obj_aux->getPuesto() == "41") {//Si es usuario fb
                        return -6;//echo "Error: El correo electrónico <b>" . $obj->getEmail() . "</b> ya está registrado en el sistema";
                    } else {//Si no es usuario fb
                        return -7;//echo "Error: El correo electrónico <b>" . $obj->getEmail() . "</b> ya está registrado en el sistema";
                    }
                } else {
                    if ($obj->newRegistroSinEcriptar()) {
                        $obj_pendiente->marcarProcesado($IdUsuarioPendiente, $obj->getId());
                        return 1;//echo "El usuario <b>" . $obj->getUsuario() . "</b> ha sido dado de alta exitosamente en el sistema</b>";
                    } else {
                        return -8;//echo "Error: no se pudo registrar el usuario, favor de reportar este problema";
                    }
                }
            } else {
                return -9;//echo "Error: el usuario <b>" . $obj->getUsuario() . " ya está registrado en el sistema</b>";
            }
        } else {
            return -10;//echo "<br/>Error: no se encuentra ningún registro habilitado con los datos de este enlace";
        }
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("validaNuevoUsuario", "urn:validaNuevoUsuario");
$server->register("validaUsuario", array("IdUsuarioPendiente" => "xsd:int", "IdSession" => "xsd:string"), array("return" => "xsd:string"), "urn:validaNuevoUsuario", "urn:validaNuevoUsuario#validaUsuario", "rpc", "encoded", "Obtiene las notas de los tickets segun los filtros especificados");
$server->service($HTTP_RAW_POST_DATA);
?>