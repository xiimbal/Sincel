<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Localidad.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Session.class.php");

/**
 * 
 * @param type $clavecliente
 * @param type $pagina
 */
function getOpiniones($clavecliente, $pagina) {
    $empresa = 3;
    $registros_por_pagina = 10;
    $session = new Session();
    $session->setEmpresa($empresa);    
    $catalogo = new Catalogo();
    $catalogo->setEmpresa($empresa);    
    $cliente = new Cliente();
    $cliente->setEmpresa($empresa);

    $indice = $registros_por_pagina * ($pagina - 1);
    $busqueda = array();
    $aux = array();
    $consulta = "SELECT Mensaje,Calificacion,DATE(FechaCreacion) AS Fecha,Titulo FROM `k_calificacioncliente` WHERE ClaveCliente = '$clavecliente' LIMIT $indice,$registros_por_pagina;";
    $result = $catalogo->obtenerLista($consulta);    
    while($rs = mysql_fetch_array($result)){     
        $date = new DateTime($rs['Fecha']);        
        $aux['Mensaje'] = $rs['Mensaje'];
        $aux['Calificacion'] = $rs['Calificacion'];
        $aux['Fecha'] = $date->format('d-m-Y');
        $aux['Titulo'] = $rs['Titulo'];
        array_push($busqueda, $aux);
    }    
    return json_encode(array_values($busqueda));   
}

$server = new soap_server();
$server->configureWSDL("consultaopiniones", "urn:consultaopiniones");
$server->register("getOpiniones", array("clavecliente" => "xsd:string", "pagina" => "xsd:int"), 
        array("return" => "xsd:string"), 
        "urn:consultaopiniones", "urn:consultaopiniones#getOpiniones", "rpc", "encoded", 
        "Obtiene las opiniones del cliente especificado");
$server->service($HTTP_RAW_POST_DATA);
?>