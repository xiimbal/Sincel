<?php
header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
 
function consultaCalificacionesPorUsuario($ClaveCliente, $idUsuario , $IdSession) {
    $empresa = 3;
    $session = new Session();
    $session->setEmpresa($empresa);
    
    $resultadoLoggin = (int)$session->logginWithSession($IdSession);
    
    if ($resultadoLoggin > 0) {
           $cliente = new Cliente();              
           $cliente->setEmpresa($empresa);
           if(!$cliente->getRegistroById($ClaveCliente)){
               return json_encode("La clave de cliente $ClaveCliente no existe");
           }
           $result = $cliente->getCalificacionClientePorUsuario($idUsuario);
           if(mysql_num_rows($result) > 0){
               return json_encode("1");
           }else{
               return json_encode("0");
           }
    }else{
        return json_encode($resultadoLoggin);
    }
}
 
$server = new soap_server();
$server->configureWSDL("tienecalificaciondeusuario", "urn:tienecalificaciondeusuario"); 
$server->register("consultaCalificacionesPorUsuario",
    array("ClaveCliente" => "xsd:string", "idUsuario" => "xsd:int" , "IdSession" => "xsd:string"),
    array("return" => "xsd:string"),
    "urn:tienecalificaciondeusuario",
    "urn:tienecalificaciondeusuario#consultaCalificacionesPorUsuario",
    "rpc",
    "encoded",
    "Obtiene las calificaciones del cliente hechas por un usuario");
 
$server->service($HTTP_RAW_POST_DATA);
/*$server->register("getProd");
$server->service($HTTP_RAW_POST_DATA);*/
?>