<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Session.class.php");

/**
 * 
 * @param type $claveCliente
 * @param type $idUsuario
 * @param type $tipoOperacion 1 para insertar, 2 para eliminar
 * @param type $usuario
 * @param type $password
 * @return type
 */
function procesaFavorito($claveCliente, $idUsuario, $tipoOperacion, $IdSession) {    
    $empresa = 3;
    $session = new Session();
    $session->setEmpresa($empresa);    

    $resultadoLoggin = (int)$session->logginWithSession($IdSession);
    
    if ($resultadoLoggin > 0) {
        $cliente = new Cliente();        
        $cliente->setEmpresa($empresa);        
        
        $cliente->setClaveCliente($claveCliente);
        $cliente->setIdUsuario($idUsuario);
        if($tipoOperacion == 1){
            if($cliente->marcarFavorito()){
                return json_encode(1);
            }else{
                return json_encode(0);
            }
        }else if($tipoOperacion == 2){
            if($cliente->desmarcarFavorito()){
                return json_encode(1);
            }else{
                return json_encode(0);
            }
        }else{
            return json_encode(0);
        }
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("favoritoinserta", "urn:favoritoinserta");
$server->register("procesaFavorito", array("claveCliente" => "xsd:string", "idUsuario" => "xsd:int", "tipoOperacion" => "xsd:int", 
    "IdSession" => "xsd:string"), 
    array("return" => "xsd:string"), 
    "urn:favoritoinserta", "urn:favoritoinserta#procesaFavorito", 
    "rpc", 
    "encoded", 
    "Inserta favorito");

$server->service($HTTP_RAW_POST_DATA);
/* $server->register("getProd");
  $server->service($HTTP_RAW_POST_DATA); */
?>