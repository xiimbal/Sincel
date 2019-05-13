<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/UbicacionUsuario.class.php");
include_once("../WEB-INF/Classes/Session.class.php");

/**
 * 
 * @param type $arrayUsuarios
 * @param type $IdSession
 * @return int
 */
function getUbicaciones($arrayUsuarios, $IdSession) {
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);

    if ($empresa == "0") {
        return -100;
    }

    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($IdSession);

    if ($resultadoLoggin > 0) {
        $ubicacion = new UbicacionUsuario();
        $ubicacion->setEmpresa($empresa);

        $result = $ubicacion->obtenerUbicacion($arrayUsuarios);
        $resultados = array();
        while ($rs = mysql_fetch_array($result)) {
            $aux = array();
            $aux['IdUsuario'] = $rs['IdUsuario'];
            $aux['Latitud'] = $rs['Latitud'];
            $aux['Longitud'] = $rs['Longitud'];
            $aux['Nombre'] = $rs['Nombre'];
            $aux['Usuario'] = $rs['Loggin'];
            $aux['IdPuesto'] = $rs['IdPuesto'];
            $aux['IdTicket'] = $rs['IdTicket'];
            $aux['IdEstatusTicket'] = $rs['IdEstatusAtencion'];
            $aux['FechaHora'] = $rs['FechaHora'];
            $aux['PorcentajeBateria'] = $rs['PorcentajeBateria'];
            array_push($resultados, $aux);
        }
        $json = array_values($resultados);
        return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($json));
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("localizaUsuario", "urn:localizaUsuario");
$server->register("getUbicaciones", array("arrayUsuarios" => "xsd:string", "IdSession" => "xsd:string"), array("return" => "xsd:string"), "urn:localizaUsuario", "urn:localizaUsuario#getUbicaciones", "rpc", "encoded", "Regresa las últimas coordenadas registradas de los usuarios especificados");
$server->service($HTTP_RAW_POST_DATA);
?>