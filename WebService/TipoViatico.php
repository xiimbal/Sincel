<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Session.class.php");

function obtenerTipoViaticos($idSession){
    
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($idSession);

    if ($empresa == "0") {
        return -100;
    }

    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($idSession);
    
    if ($resultadoLoggin > 0) { //Id del usuario
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        $consulta = "SELECT idTipoViatico, nombre from c_tipoviatico WHERE activo = 1";
        $result = $catalogo->obtenerLista($consulta);
        $lista = array();
        
        while($rs = mysql_fetch_array($result)){
            $tipoViatico = array();
            $tipoViatico['idTipo'] = $rs['idTipoViatico'];
            $tipoViatico['nombre'] = $rs['nombre'];
            array_push($lista, $tipoViatico);
         }
         
        $json = array_values($lista);
        return json_encode($json);
    }else{
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("tipoViatico", "urn:tipoViatico");
$server->register("obtenerTipoViaticos", 
        array("IdSession" => "xsd:string"), 
        array("return" => "xsd:string"), "urn:tipoViatico", "urn:tipoViatico#obtenerTipoViaticos", "rpc", "encoded", "Regresa todos los tipo viaticos disponibles");
$server->service($HTTP_RAW_POST_DATA);

?>