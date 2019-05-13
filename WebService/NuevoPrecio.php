<?php
//WS38
header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/lib/PHPImagen.lib.php");

function insertaPresio( $IdSession) {    
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);

    if ($empresa == "0") {
        return -100;
    }

    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($IdSession);
    $parametroGlobal = new ParametroGlobal();
    $catalogo = new Catalogo();
    $parametroGlobal->setEmpresa($empresa);
    $catalogo->setEmpresa($empresa);    
    $devolver=array();
    
    if ($resultadoLoggin > 0) {
        $aux = array();
        $aux['ParametroGlobal'] = $parametroGlobal;
        array_push($devolver, $aux);
        $aux['catalogo'] = $catalogo;
        array_push($devolver, $aux);
        $aux['session'] = $session;
        array_push($devolver, $aux);

        $json=array_values($devolver);

        return $json;
        return json_decode($resultadoLoggin);
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("NuevoPrecio", "urn:NuevoPrecio");
$server->register("insertaPresio", 
        array("IdSession" => "xsd:string"), 
    array("return" => "xsd:string"), 
    "urn:NuevoPrecio", "urn:NuevoPrecio#insertaPresio", 
    "rpc", 
    "encoded", 
    "Inserta un nuevo presio");
$server->service($HTTP_RAW_POST_DATA);
?>