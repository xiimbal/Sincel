<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Promocion.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Session.class.php");


/**
 * Obtiene las promociones en la cuponera del usuario de la sesion actual
 * @param type $IdSession id de la sesion activa
 * @return type
 */
function obtieneCuponera($ClaveCliente, $IdSession) {    
    $empresa = 3;
    $session = new Session();
    $session->setEmpresa($empresa);    

    $resultadoLoggin = (int)$session->logginWithSession($IdSession);
    
    if ($resultadoLoggin > 0) {
        $promocion = new Promocion();
        $promocion->setEmpresa($empresa);
        $usuario = new Usuario();
        $usuario->setEmpresa($empresa);
        $usuario->getRegistroById($resultadoLoggin);
        
        $resultados = array();
        $result = $promocion->obtenerCuponeraUsuario($ClaveCliente, $resultadoLoggin);
        while($rs = mysql_fetch_array($result)){
            $aux = array();
            $aux['ClaveNegocio'] = $rs['ClaveCliente'];
            $aux['NombreNegocio'] = $rs['NombreRazonSocial'];
            $aux['DescripcionPromocion'] = $rs['Descripcion'];
            $aux['VigenciaInicio'] = $rs['Vigencia'];
            $aux['VigenciaFin'] = $rs['Vigencia_Fin'];
            $aux['TituloPromocion'] = $rs['Titulo'];
            $aux['ClavePromocion'] = $rs['CodigoPromocion'];
            $aux['ManejaCupon'] = $rs['ManejaCupon'];
            /*Obtenemos la foto*/
            $filename = "";
            /*Obtenemos el logo, sino una foto del cliente*/
            if($rs['Imagen']!=NULL && $rs['Imagen']!=""){
                $filename = $rs['Imagen'];
            }

            if($filename != ""){
                $tmpfile = "../resources/images/promociones/" . $filename;   // temp filename                        

                $handle = fopen($tmpfile, "r");                  // Open the temp file
                $contents = fread($handle, filesize($tmpfile));  // Read the temp file            
                fclose($handle);                                 // Close the temp file

                $decodeContent = base64_encode($contents);     // Decode the file content, so that we code send a binary string to SOAP                                            
                $aux['ImagenPromocion'] = $decodeContent;
            }else{                                
                $aux['ImagenPromocion'] = NULL;
            }
            array_push($resultados, $aux);
        }
        return json_encode(array_values($resultados));
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("cuponera", "urn:cuponera");
$server->register("obtieneCuponera", array("ClaveCliente" => "xsd:string", "IdSession" => "xsd:string"), 
    array("return" => "xsd:string"), 
    "urn:cuponera", "urn:cuponera#obtieneCuponera", 
    "rpc", 
    "encoded", 
    "Obtiene las promociones activas y vigentes en la cuponera del usuario");

$server->service($HTTP_RAW_POST_DATA);
/* $server->register("getProd");
  $server->service($HTTP_RAW_POST_DATA); */
?>