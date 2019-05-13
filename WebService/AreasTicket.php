<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

function obtenerAreasTicket($idTicket, $idSession){
    
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($idSession);

    if ($empresa == "0") {
        return -100;
    }

    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($idSession);
    
    if ($resultadoLoggin > 0){       
        $user_obj = new Usuario();
        $user_obj->setEmpresa($empresa);
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        $puesto = "";
        
        if ($user_obj->getRegistroById($resultadoLoggin)) {
            $puesto = $user_obj->getPuesto();
        }
        
        $consulta = "SELECT DISTINCT IdEstado, Nombre FROM (SELECT e.* from c_estado e
                    LEFT JOIN c_ticket AS t ON t.AreaAtencion = e.IdArea
                    WHERE t.IdTicket = $idTicket AND e.Activo = 1
                    UNION
                    SELECT e.* from c_estado e
                    LEFT JOIN k_areapuesto AS ap ON ap.IdEstado = e.IdEstado
                    WHERE ap.IdPuesto = $puesto AND e.Activo = 1) AS aux ORDER BY IdEstado";
        $result = $catalogo->obtenerLista($consulta);
        $estados = array();
        while($rs = mysql_fetch_array($result))
        {
            $estado = array();
            $estado['IdEstado'] = $rs['IdEstado'];
            $estado['Nombre'] = $rs['Nombre'];
            array_push($estados, $estado);
        }
        $json = array_values($estados);   
        return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($json)); 
        
    }else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("areasticket", "urn:areasticket");
$server->register("obtenerAreasTicket", array("idTicket" => "xsd:int", "idSession" => "xsd:string"), array("return" => "xsd:string"), 
        "urn:areasticket", "urn:areasticket#obtenerAreasTicket", "rpc", "encoded", 
        "Obtiene todos los componentes de tipo refacción de un equipo");
$server->service($HTTP_RAW_POST_DATA);

?>