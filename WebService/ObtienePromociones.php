<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Promocion.class.php");
include_once("../WEB-INF/Classes/Session.class.php");

function consultaPromociones($ClaveCliente, $IdSession) {
    $empresa = 3;
    $session = new Session();
    $session->setEmpresa($empresa);

    $resultadoLoggin = (int) $session->logginWithSession($IdSession);

    if ($resultadoLoggin > 0) {
        $promocion = new Promocion();
        $promocion->setEmpresa($empresa);
        $result = $promocion->getPromociones($ClaveCliente);
        $promociones_final = array();
        while ($rs = mysql_fetch_array($result)) {
            $promociones = array();            
            $promociones['ClaveNegocio'] = $rs['ClaveCliente'];
            $promociones['IdPromocion'] = $rs['IdPromocion'];            
            $promociones['NombreNegocio'] = $rs['NombreRazonSocial'];            
            $promociones['DescripcionPromocion'] = trim($rs['Descripcion']);
            $promociones['VigenciaInicio'] = $rs['Vigencia'];
            $promociones['VigenciaFin'] = $rs['Vigencia_Fin'];            
            $promociones['TituloPromocion'] = $rs['Titulo'];
            $promociones['ManejaCupon'] = $rs['ManejaCupon'];
            
            /*$promociones['CodigoPromocion'] = $rs['CodigoPromocion'];
            $promociones['Localidad'] = $rs['Localidad'];
            $promociones['Contacto'] = $rs['IdUsuario'];            
            $promociones['NumeroCupon'] = $rs['NumeroCupon'];
            $promociones['CuponesUsados'] = $rs['CuponesUsados'];*/

            $ruta = "../resources/images/promociones/";
            $tmpfile = $ruta . $rs['Imagen'];   // temp filename
            //$filename = $ruta . $rs['Imagen'];      // Original filename    

            $handle = fopen($tmpfile, "r");                  // Open the temp file
            $contents = fread($handle, filesize($tmpfile));  // Read the temp file            
            fclose($handle);                                 // Close the temp file

            $decodeContent = base64_encode($contents);     // Decode the file content, so that we code send a binary string to SOAP            
            $promociones['ImagenPromocion'] = $decodeContent;

            array_push($promociones_final, $promociones);
        }
        return json_encode(array_values($promociones_final));
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("obtienepromociones", "urn:obtienepromociones");
$server->register("consultaPromociones", array("ClaveCliente" => "xsd:string","IdSession" => "xsd:string"), array("return" => "xsd:string"), "urn:obtienepromociones", "urn:obtienepromociones#consultaPromociones", "rpc", "encoded", "Obtiene las promociones");

$server->service($HTTP_RAW_POST_DATA);
/* $server->register("getProd");
  $server->service($HTTP_RAW_POST_DATA); */
?>