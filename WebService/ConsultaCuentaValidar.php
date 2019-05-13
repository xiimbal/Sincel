<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/UsuarioPendiente.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

function getCuentas($IdSession) {
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);

    if ($empresa == "0") {
        return -100;
    }
    
    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($IdSession);

    if ($resultadoLoggin > 0) {
        $obj = new UsuarioPendiente();
        $obj->setEmpresa($empresa);        
        $result = $obj->getUsuariosPendientes();
        $busqueda = array();
        while ($rs = mysql_fetch_array($result)) {
            $aux = array();
            $aux['Nombre'] = $rs['Nombre'];
            $aux['correo'] = $rs['correo'];
            $aux['IdUsuario'] = $rs['IdUsuario'];
            $aux['Usuario'] = $rs['Loggin'];
            array_push($busqueda, $aux);
        }
        $json = array_values($busqueda);
        return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($json));
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("consultaCuentaValidar", "urn:consultaCuentaValidar");
$server->register("getCuentas", array("IdSession" => "xsd:string"), array("return" => "xsd:string"), "urn:consultaCuentaValidar", "urn:consultaCuentaValidar#getCuentas", "rpc", "encoded", "Obtiene las notas de los tickets segun los filtros especificados");
$server->service($HTTP_RAW_POST_DATA);
?>