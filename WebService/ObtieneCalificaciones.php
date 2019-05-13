<?php
header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
 
function consultaCalificaciones($ClaveCliente, $IdSession) {
    $empresa = 3;
    $session = new Session();
    $session->setEmpresa($empresa);
    
    $resultadoLoggin = (int)$session->logginWithSession($IdSession);
    
    if ($resultadoLoggin > 0) {
           $cliente = new Cliente();   
           $calificaciones = array();
           $cliente->setEmpresa($empresa);
           if(!$cliente->getRegistroById($ClaveCliente)){
               return json_encode("La clave de cliente $ClaveCliente no existe");
           }
           $result = $cliente->getCalificacionesCliente(NULL);
           while($rs = mysql_fetch_array($result)){
               $calificaciones_aux = array();
               $calificaciones_aux['calificacion'] = $rs['Calificacion'];
               array_push($calificaciones, $calificaciones_aux);               
           }
           return json_encode(array_values($calificaciones));
    }else{
        return json_encode($resultadoLoggin);
    }
}
 
$server = new soap_server();
$server->configureWSDL("obtienecalificaciones", "urn:obtienecalificaciones"); 
$server->register("consultaCalificaciones",
    array("ClaveCliente" => "xsd:string", "IdSession" => "xsd:string"),
    array("return" => "xsd:string"),
    "urn:obtienecalificaciones",
    "urn:obtienecalificaciones#consultaCalificaciones",
    "rpc",
    "encoded",
    "Obtiene las calificaciones del cliente especificado");
 
$server->service($HTTP_RAW_POST_DATA);
/*$server->register("getProd");
$server->service($HTTP_RAW_POST_DATA);*/
?>