<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

function obtenerCuadrantes($idSession){
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($idSession);

    if ($empresa == "0") {
        return -100;
    }

    $session->setEmpresa($empresa);
    $session->setId_empresa($session->getEmpresa());
    $resultadoLoggin = (int) $session->logginWithSession($idSession);
    
    if ($resultadoLoggin > 0){
        $consulta = "SELECT DISTINCT(e.IdEstado) AS IdEstado, e.Nombre FROM c_estado AS e
                     INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND (kfe.IdFlujo = 2 OR e.IdEstado = 2) ORDER BY Nombre;"; 
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        $result = $catalogo->obtenerLista($consulta);
        
        $componentes = array();
        while($rs = mysql_fetch_array($result))
        {
            $componente = array();
            $componente['IdCuadrante']=$rs['IdEstado'];
            $componente['Cuadrante']=$rs['Nombre'];
            array_push($componentes, $componente);
        }
        
        $json = array_values($componentes);
        return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($json));
        
    }else {
        return json_encode($resultadoLoggin);
    }
    
}

$server = new soap_server();
$server->configureWSDL("consultaCuadrantes", "urn:consultaCuadrantes");
$server->register("obtenerCuadrantes", array("idSession" => "xsd:string"), array("return" => "xsd:string"), 
        "urn:consultaCuadrantes", "urn:consultaCuadrantes#obtenerCuadrantes", "rpc", "encoded", 
        "Obtiene todos los Cuadrantes");
$server->service($HTTP_RAW_POST_DATA);

?>
