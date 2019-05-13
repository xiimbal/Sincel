<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Session.class.php");

function getUsuario($IdSession, $IdPuesto) {
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);

    if ($empresa == "0") {
        return -100;
    }

    $where = "";
    if(!empty($IdPuesto)){
        $where = " WHERE IdPuesto = $IdPuesto ";
    }
    
    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($IdSession);

    if ($resultadoLoggin > 0) {
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        
        $consulta = "SELECT u.IdUsuario, CONCAT(u.Nombre,' ',u. ApellidoPaterno,' ', u.ApellidoMaterno)  AS Nombre
            FROM c_usuario AS u
            $where
            ORDER BY Nombre;";
        $result = $catalogo->obtenerLista($consulta);
        $resultados = array();                            
        while ($rs = mysql_fetch_array($result)) {
            $aux = array();
            $aux['IdUsuario'] = $rs['IdUsuario'];
            $aux['Nombre'] = $rs['Nombre'];
            array_push($resultados, $aux);
        }
        $json = array_values($resultados);
        return json_encode($json);
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("listaUsuario", "urn:listaUsuario");
$server->register("getUsuario", array("IdSession" => "xsd:string", "IdPuesto" => "xsd:int"), array("return" => "xsd:string"), "urn:listaUsuario", "urn:listaUsuario#getUsuario", "rpc", "encoded", "Regresa los datos de los usuario del puesto especificado");
$server->service($HTTP_RAW_POST_DATA);
?>