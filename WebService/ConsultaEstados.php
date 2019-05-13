<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

function obtenerEstados($idSession){
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($idSession);

    if ($empresa == "0") {
        return -100;
    }

    $session->setEmpresa($empresa);
    $session->setId_empresa($session->getEmpresa());
    $resultadoLoggin = (int) $session->logginWithSession($idSession);
    
    if ($resultadoLoggin > 0){
        $consulta = "SELECT * FROM c_ciudades Where Activo = 1 ORDER BY Ciudad;";  
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        $result = $catalogo->obtenerLista($consulta);
        
        $componentes = array();
        while($rs = mysql_fetch_array($result))
        {
            $componente = array();
            $componente['IdEstado']=$rs['IdCiudad'];
            $componente['NombreEstado']=$rs['Ciudad'];
            array_push($componentes, $componente);
        }
        
        $json = array_values($componentes);
        return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($json));
        
    }else {
        return json_encode($resultadoLoggin);
    }
    
}

$server = new soap_server();
$server->configureWSDL("consultaEstados", "urn:consultaEstados");
$server->register("obtenerEstados", array("idSession" => "xsd:string"), array("return" => "xsd:string"), 
        "urn:consultaEstados", "urn:consultaEstados#obtenerEstados", "rpc", "encoded", 
        "Obtiene todos los Estados");
$server->service($HTTP_RAW_POST_DATA);

?>
