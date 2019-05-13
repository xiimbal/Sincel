<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/ViaticoTicket.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");

function insertaViatico($idTicket, $idTipoViatico, $cantidad, $comentario, $idSession)
{
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($idSession);

    if ($empresa == "0") {
        return -100;
    }

    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($idSession);
    
    if ($resultadoLoggin > 0) { //Id del usuario
        $user_obj = new Usuario();
        $user_obj->setEmpresa($empresa);
        $usuario = "";
        
        if ($user_obj->getRegistroById($resultadoLoggin)) {
            $usuario = $user_obj->getUsuario();
        }
        
        $viatico = new ViaticoTicket();
        $viatico->setEmpresa($empresa);
        $viatico->setIdTicket($idTicket);
        $viatico->setIdTipoViatico($idTipoViatico);
        $viatico->setIdUsuario($resultadoLoggin);
        $viatico->setCosto($cantidad);
        $viatico->setComentario($comentario);
        $viatico->setFecha(date('Y')."-".date('m')."-".date('d'));
        $viatico->setCobrarSiNo(1); //Actualizar
        $viatico->setPagarSiNo(1); //Actualizar
        $viatico->setUsuarioCreacion($usuario);
        $viatico->setUsuarioUltimaModificacion($usuario);
        $viatico->setPantalla("WS NuevoGastoTicket");       
        
        if($viatico->insertarNuevoViatico(0)){
            return 1;
        }else{
            return -3;
        }
    }else{
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("nuevoGastoTicket", "urn:nuevoGastoTicket");
$server->register("insertaViatico", 
        array("idTicket" => "xsd:int", "idTipoViatico" => "xsd:int", "costo" => "xsd:int", "comentario" => "xsd:string", "Fecha" => "xsd:string", "IdSession" => "xsd:string"), 
        array("return" => "xsd:string"), "urn:nuevoGastoTicket", "urn:nuevoGastoTicket#insertaViatico", "rpc", "encoded", "Inserta un viatico de un ticket");
$server->service($HTTP_RAW_POST_DATA);

?>

