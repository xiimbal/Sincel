<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set ( "America/Mexico_City" );

require_once "../lib/nusoap.php";

include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
 
function autenticaUsuario($usuario, $password, $IdSessionAnterior) {
    //$empresa = 3;    
    $session = new Session();
    if(!$session->getLogginMultiBD($usuario, $password)){
        return json_encode(0);
    }
    $session->setEmpresa($session->getId_empresa());
    $usuario_obj = new Usuario();
    $usuario_obj->setEmpresa($session->getId_empresa());
    if($session->getLogginMultiBD($usuario, $password) && $session->getLogin($usuario, $password)){
        $parametros = new Parametros();
        $parametros->setEmpresa($session->getId_empresa());
        
        $respuesta = $session->generarClaveSession(15, $IdSessionAnterior);
        $respuesta['IdUsuario'] = $session->getId_usu();
        $respuesta['PermisoEP'] = 3; //Permiso para eventos y promociones
        $respuesta['IdEmpresa'] = $session->getId_empresa(); //Permiso para eventos y promociones
        if($usuario_obj->getRegistroById($session->getId_usu())){
            $respuesta['IdPuesto'] = $usuario_obj->getPuesto(); //Id de puesto
        }else{
            $respuesta['IdPuesto'] = 0;
        }
        
        if($parametros->getRegistroById("37")){
            $respuesta['MinutosMonitoreoAnterior'] = $parametros->getValor();
        }else{
            $respuesta['MinutosMonitoreoAnterior'] = 60;
        }
        
        if($parametros->getRegistroById("38")){
            $respuesta['MinutosMonitoreoPosterior'] = $parametros->getValor();
        }else{
            $respuesta['MinutosMonitoreoPosterior'] = 30;
        }
        
        $valores = array();        
        array_push($valores, $respuesta);
        $json = array_values($valores);
        return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($json));
    }else{
        return json_encode(0);
    }        
}
 
$server = new soap_server();
$server->configureWSDL("autenticausuario", "urn:autenticausuario"); 
$server->register("autenticaUsuario",
    array("usuario" => "xsd:string", "password" => "xsd:string", "IdSessionAnterior" => "xsd:string"),
    array("return" => "xsd:string"),
    "urn:autenticausuario",
    "urn:autenticausuario#autenticaUsuario",
    "rpc",
    "encoded",
    "Autentifica usuario, 0 no identificado, id del usuario si identificado");
 
$server->service($HTTP_RAW_POST_DATA);
/*$server->register("getProd");
$server->service($HTTP_RAW_POST_DATA);*/
?>