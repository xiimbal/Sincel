<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/ccliente.class.php");
include_once("../WEB-INF/Classes/Session.class.php");

function consultaFavoritos($idUsuario, $IdSession) {
    $empresa = 3;
    $session = new Session();
    $session->setEmpresa($empresa);

    $resultadoLoggin = (int) $session->logginWithSession($IdSession);

    if ($resultadoLoggin > 0) {
        $cliente = new Cliente();
        $cliente->setEmpresa($empresa);
        $cliente->setIdUsuario($idUsuario);

        $cliente_detalle = new ccliente();

        $result = $cliente->obtieneFavoritos();
        $cliente_final = array();
        while ($rs = mysql_fetch_array($result)) {
            $ClaveCliente = $rs['ClaveCliente'];

            $cliente->getRegistroById($ClaveCliente);
            $cliente_detalle->setEmpresa($empresa);
            $cliente_detalle->getregistrobyID($ClaveCliente);
            
            if($cliente_detalle->getActivo() == "0"){
                continue;
            }
            
            $cliente_aux = array();
            $cliente_aux['ClaveCliente'] = ($cliente->getClaveCliente());
            $cliente_aux['NombreCliente'] = ($cliente->getNombreRazonSocial());
            $cliente_aux['IdGiro'] = ($cliente->getIdGiro());
            
            if($cliente->getFoto() != ""){
                $tmpfile = "../" . $cliente->getFoto();   // temp filename                

                $handle = fopen($tmpfile, "r");                  // Open the temp file
                $contents = fread($handle, filesize($tmpfile));  // Read the temp file            
                fclose($handle);                                 // Close the temp file

                $decodeContent = base64_encode($contents);     // Decode the file content, so that we code send a binary string to SOAP            
                $cliente_aux['Foto'] = $decodeContent;            
            }else{
                $cliente_aux['Foto'] = "";
            }

            array_push($cliente_final, $cliente_aux);
        }

        return json_encode(array_values($cliente_final));
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("obtienefavoritos", "urn:obtienefavoritos");
$server->register("consultaFavoritos", array("idUsuario" => "xsd:int", "IdSession" => "xsd:string"), array("return" => "xsd:string"), "urn:obtienefavoritos", "urn:obtienefavoritos#consultaFavoritos", "rpc", "encoded", "Obtiene datos de los clientes favoritos");

$server->service($HTTP_RAW_POST_DATA);
/* $server->register("getProd");
  $server->service($HTTP_RAW_POST_DATA); */
?>