<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Evento.class.php");

/**
 *  
 */
function getEventosVigentes($ClaveCliente) {
    $empresa = 3;
    $evento = new Evento();
    $evento->setEmpresa($empresa);

    $eventos = array();    
    $result = $evento->getEventosVigentes($ClaveCliente);
    
    while ($rs = mysql_fetch_array($result)) {
        $aux = array();
        $aux['TituloEvento'] = $rs['Nombre'];
        $aux['NombreNegocio'] = $rs['NombreRazonSocial'];
        $aux['FechaInicio'] = $rs['FechaInicio'];
        $aux['FechaFin'] = $rs['FechaFin'];
        $direccion = $rs['Calle'] .
                ", No ext: " . $rs['NoExterior'] . " No. Int: " . $rs['NoInterior'] . ", Col: " . $rs['Colonia'] .
                ", Del: " . $rs['Delegacion'] . ", " . $rs['Estado'] . ", " . $rs['Pais'] . " C.P.: " . $rs['CodigoPostal'];
        $aux['Direccion'] = $direccion;
        $aux['Latitud'] = $rs['Latitud'];
        $aux['Longitud'] = $rs['Longitud'];
        $aux['DescripcionEvento'] = $rs['Descripcion'];        
        $filename = $rs['Imagen'];
        
        if ($filename != "") {
            $tmpfile = "../" . $filename;   // temp filename     
            
            //Buscamos la imagen re-escalada, si no existe, no se envia nada
            $index_last_dot = strrpos($tmpfile, ".");
            $location_aux = substr($tmpfile, 0, $index_last_dot);
            $location_aux .= "resized_300_techra";
            $location_aux .= substr($tmpfile, $index_last_dot);

            if(!empty($location_aux)){
                $tmpfile = $location_aux;
            }

            if(file_exists($tmpfile)){
                $handle = fopen($tmpfile, "r");                  // Open the temp file
                $contents = fread($handle, filesize($tmpfile));  // Read the temp file            
                fclose($handle);                                 // Close the temp file

                $decodeContent = base64_encode($contents);     // Decode the file content, so that we code send a binary string to SOAP            
                $aux['NombreFoto'] = $filename;
                $aux['Foto'] = $decodeContent;
            }else{
                $cliente_aux['NombreFoto'] = "";
                $cliente_aux['Foto'] = NULL;
            }
        } else {
            $aux['NombreFoto'] = $filename;
            $aux['Foto'] = NULL;
        }        
        array_push($eventos, $aux);
        
    }

    return json_encode(array_values($eventos));
}

$server = new soap_server();
$server->configureWSDL("consultaevento", "urn:consultaevento");
$server->register("getEventosVigentes", array("ClaveCliente" => "xsd:string"), 
        array("return" => "xsd:string"), 
        "urn:consultaevento", "urn:consultaevento#getEventosVigentes", 
        "rpc", "encoded", 
        "Obtiene los negocios cercanos a la localización especificadaeventos vigentes registrados en el sistema");
$server->service($HTTP_RAW_POST_DATA);
?>