<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

function obtenerTitulos($idSession){
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($idSession);

    if ($empresa == "0") {
        return -100;
    }

    $session->setEmpresa($empresa);
    $session->setId_empresa($session->getEmpresa());
    $resultadoLoggin = (int) $session->logginWithSession($idSession);
    
    if ($resultadoLoggin > 0){
//        if(empty($NoSerie)){
//            return -3;
//        }
        $consulta = "SELECT * FROM c_titulos";  //IdTipoComponente = 1 debido a que son solo refacciones
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        $result = $catalogo->obtenerLista($consulta);
        
        $componentes = array();
        while($rs = mysql_fetch_array($result))
        {
            $componente = array();
            $tema = $rs['Tema'];
            $componente[$tema] = $rs['Titulo'];
            array_push($componentes, $componente);
        }
        $json = array_values($componentes);   
        return json_encode($json));     
    }else {
        return json_encode($resultadoLoggin);
    }
    
}

$server = new soap_server();
$server->configureWSDL("obtenertitulos", "urn:obtenertitulos");
$server->register("obtenerTitulos", array("idSession" => "xsd:string"), array("return" => "xsd:string"), 
        "urn:obtenertitulos", "urn:obtenertitulos#obtenerTitulos", "rpc", "encoded", 
        "Obtiene todos los Títulos");
$server->service($HTTP_RAW_POST_DATA);

?>
