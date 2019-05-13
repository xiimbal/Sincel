<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

function obtenerCampanias($idSession){
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($idSession);

    if ($empresa == "0") {
        return -100;
    }

    $session->setEmpresa($empresa);
    $session->setId_empresa($session->getEmpresa());
    $resultadoLoggin = (int) $session->logginWithSession($idSession);
    
    if ($resultadoLoggin > 0){
        $consulta = "SELECT * FROM c_area Where Activo = 1 AND ClaveCentroCosto != 'NULL' ORDER BY Descripcion;";  //IdTipoComponente = 1 debido a que son solo refacciones
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        $result = $catalogo->obtenerLista($consulta);
        
        $componentes = array();
        while($rs = mysql_fetch_array($result))
        {
            $componente = array();
            $componente['IdCampania']=$rs['IdArea'];
            $componente['Descripcion']=$rs['Descripcion'];
            //$componente['Cuadrante']=$rs['IdEstado'];
            array_push($componentes, $componente);
        }
        
        $json = array_values($componentes);
        return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($json));
        
    }else {
        return json_encode($resultadoLoggin);
    }
    
}

$server = new soap_server();
$server->configureWSDL("consultaCampanias", "urn:consultaCampanias");
$server->register("obtenerCampanias", array("idSession" => "xsd:string"), array("return" => "xsd:string"), 
        "urn:consultaCampanias", "urn:consultaCampanias#obtenerCampanias", "rpc", "encoded", 
        "Obtiene todas las Campañas");
$server->service($HTTP_RAW_POST_DATA);

?>
