<?php
header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";

include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
 
function revalidarSesion($IdSession) {    
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);
    
    if($empresa == "0"){
        return -100;
    }
    
    $session->setEmpresa($empresa);
    $usuario_obj = new Usuario();
    $usuario_obj->setEmpresa($empresa);
    
    $respuesta = $session->revalidarSesion($IdSession);
    if($respuesta == -1){
        return json_encode(-1);//El id de sesion no fue encontrado activo
    }else{
        $valores = array();        
        if($usuario_obj->getRegistroById($session->getId_usu())){
            $respuesta['IdPuesto'] = $usuario_obj->getPuesto(); //Id de puesto
        }else{
            $respuesta['IdPuesto'] = 0;
        }
        array_push($valores, $respuesta);       
        return json_encode($valores);
    }
}
 
$server = new soap_server();
$server->configureWSDL("revalidasesion", "urn:revalidasesion"); 
$server->register("revalidarSesion",
    array("IdSesion" => "xsd:string"),
    array("return" => "xsd:string"),
    "urn:revalidasesion",
    "urn:revalidasesion#revalidarSesion",
    "rpc",
    "encoded",
    "Revalida la sesion en caso que exista y sea la ultima activa");
 
$server->service($HTTP_RAW_POST_DATA);
/*$server->register("getProd");
$server->service($HTTP_RAW_POST_DATA);*/
?>
