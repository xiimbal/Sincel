<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Evento.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

function obtenerLocalidades($ClaveCliente, $idSession){
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
        
        if(!isset($ClaveCliente) || $ClaveCliente == ""){
            return -4;
        }
        
        $query = "SELECT cc.ClaveCentroCosto, cc.Nombre, d.* FROM c_centrocosto AS cc
            LEFT JOIN c_domicilio AS d ON d.IdDomicilio = (SELECT MIN(IdDomicilio) FROM c_domicilio WHERE ClaveEspecialDomicilio = cc.ClaveCentroCosto)
            WHERE cc.ClaveCliente = '$ClaveCliente' AND cc.Activo = 1;";
        
        $result = $catalogo->obtenerLista($query);
        $localidades = array();
        while($rs = mysql_fetch_array($result))
        {
            $localidad = array();
            $localidad['ClaveCentroCosto'] = $rs['ClaveCentroCosto'];
            $localidad['Nombre'] = $rs['Nombre'];
            $domicilio = $direccion = $rs['Calle'] .", No ext: " . $rs['NoExterior'] . " No. Int: " . $rs['NoInterior'] . ", Col: " . $rs['Colonia'] .
                               ", Del: " . $rs['Delegacion'] . ", " . $rs['Estado'] . ", " . $rs['Pais'] . " C.P.: " . $rs['CodigoPostal'];
            $localidad['domicilio'] = $domicilio;
            array_push($localidades, $localidad);
        }   
        $json = array_values($localidades);
        return json_encode($json));
    } else {
        return json_encode($resultadoLoggin);
    }
    
}

$server = new soap_server();
$server->configureWSDL("localidadesdecliente", "urn:localidadesdecliente");
$server->register("obtenerLocalidades", array("ClaveCliente" => "xsd:string", "idSession" => "xsd:string"), array("return" => "xsd:string"), 
        "urn:localidadesdecliente", "urn:localidadesdecliente#obtenerLocalidades", "rpc", "encoded", 
        "Obtiene las localidades y su domicilio de un cliente");
$server->service($HTTP_RAW_POST_DATA);

?>