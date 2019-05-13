<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Promocion.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Session.class.php");


/**
 * Agrega un registro de promocion cuponera
 * @param type $IdSession id de la sesion activa
 * @param type $IdPromocion id de la promocion a poner en cuponera
 * @return type
 */
function insertaPromocionCuponera($IdSession, $IdPromocion) {    
    $empresa = 3;
    $session = new Session();
    $session->setEmpresa($empresa);    

    $resultadoLoggin = (int)$session->logginWithSession($IdSession);
    
    if ($resultadoLoggin > 0) {
        $promocion = new Promocion();
        $promocion->setEmpresa($empresa);
        $usuario = new Usuario();
        $usuario->setEmpresa($empresa);
        $usuario->getRegistroById($resultadoLoggin);
        
        $promocion->setIdPromocion($IdPromocion);
        $promocion->setUsuarioCreacion($usuario->getUsuario());
        $promocion->setUsuarioUltimaModificacion($usuario->getUsuario());
        $promocion->setPantalla("WS AgregarPromocionCuponera");
        $resultado = $promocion->insertaPromocionCuponera($resultadoLoggin);
        return json_encode($resultado);
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("agregarpromocioncuponera", "urn:agregarpromocioncuponera");
$server->register("insertaPromocionCuponera", array("IdSession" => "xsd:string", "IdPromocion" => "xsd:string"), 
    array("return" => "xsd:string"), 
    "urn:agregarpromocioncuponera", "urn:agregarpromocioncuponera#insertaPromocionCuponera", 
    "rpc", 
    "encoded", 
    "Inserta una promocion en la cuponera");

$server->service($HTTP_RAW_POST_DATA);
/* $server->register("getProd");
  $server->service($HTTP_RAW_POST_DATA); */
?>