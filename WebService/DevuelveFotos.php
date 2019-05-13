<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Session.class.php");

function getFotosCliente($ClaveCliente, $numFotos, $IdSession) {
    $empresa = 3;
    $session = new Session();
    $session->setEmpresa($empresa);

    if (!is_numeric($numFotos) || $numFotos < 0) {
        return json_encode("El número de fotos tiene ser un valor númerico mayor a 0");
    }

    $resultadoLoggin = (int)$session->logginWithSession($IdSession);
    
    if ($resultadoLoggin > 0) {
        $cliente = new Cliente();
        $cliente->setEmpresa($empresa);
        $cliente->setClaveCliente($ClaveCliente);
        $result = $cliente->getCalificacionesCliente($numFotos);
        $fotos = array();
        while ($rs = mysql_fetch_array($result)) {
            $tmpfile = "../" . $rs['Foto'];   // temp filename
            $filename = "../" . $rs['Foto'];      // Original filename    
            
            $handle = fopen($tmpfile, "r");                  // Open the temp file
            $contents = fread($handle, filesize($tmpfile));  // Read the temp file            
            fclose($handle);                                 // Close the temp file
            
            $decodeContent = base64_encode($contents);     // Decode the file content, so that we code send a binary string to SOAP            
            $fotos[$rs['Foto']] = $decodeContent;
        }
        $fotos_array = array();
        array_push($fotos_array, $fotos);
        return json_encode(array_values($fotos_array));
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("devuelvefotos", "urn:devuelvefotos");
$server->register("getFotosCliente", array("ClaveCliente" => "xsd:string", "numFotos" => "xsd:int", "IdSession" => "xsd:string"), 
        array("return" => "xsd:string"), "urn:devuelvefotos", 
        "urn:devuelvefotos#getFotosCliente", "rpc", "encoded", "Devuelve las ultimas N fotos");

$server->service($HTTP_RAW_POST_DATA);
/* $server->register("getProd");
  $server->service($HTTP_RAW_POST_DATA); */
?>