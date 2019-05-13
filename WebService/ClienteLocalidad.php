<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Evento.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/ReporteLectura.class.php");

function obtenerClientes($idSession, $ClaveCliente, $ClaveLocalidad){
    
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($idSession);

    if ($empresa == "0") {
        return -100;
    }

    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($idSession);
    
    if ($resultadoLoggin > 0){
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        $reporte = new ReporteLectura();
        $reporte->setEmpresa($empresa);
        
        $query = $reporte->generarConsulta("", $ClaveLocalidad, "", $ClaveCliente, "", "", true, false, false, false, false, false, "");        
        $busqueda = array();
        $result = $catalogo->obtenerLista($query);
        while($rs = mysql_fetch_array($result)){
            $aux = array();
            $aux['NoSerie'] = $rs['NoSerie'];
            $aux['Modelo'] = $rs['Modelo'];
            $aux['NoParte'] = $rs['NoParteEquipo'];
            array_push($busqueda, $aux);
        } 
        $json = array_values($busqueda);
        return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($json));
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("clientelocalidad", "urn:clientelocalidad");
$server->register("obtenerClientes", array("idSession" => "xsd:string", "ClaveCliente" => "xsd:string", "ClaveLocalidad" => "xsd:string"), array("return" => "xsd:string"), 
        "urn:clientelocalidad", "urn:clientelocalidad#obtenerClientes", "rpc", "encoded", 
        "Obtiene el No. de Serie, modelo y No.Parte dependiendo el cliente y su localidad");
$server->service($HTTP_RAW_POST_DATA);
?>


