<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/ccliente.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Localidad.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/HistoricoPosiciones.class.php");

function consultaDetalleCliente($ClaveCliente, $tipo, $latitud, $longitud, $IdSession) {
    $empresa = 3;
    $session = new Session();
    $session->setEmpresa($empresa);

    if (!empty($IdSession)) {
        $resultadoLoggin = (int) $session->logginWithSession($IdSession);
    } else {
        $resultadoLoggin = 0;
    }

    if ($resultadoLoggin > 0 || empty($IdSession)) {
        $historico_posiciones = new HistoricoPosiciones();
        $historico_posiciones->setEmpresa($empresa);
        $cliente = new Cliente();
        $cliente->setEmpresa($empresa);
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);        
        $cliente->setIdUsuario($resultadoLoggin);

        $cliente_detalle = new ccliente();
        $cliente_detalle->setEmpresa($empresa);
        if (!$cliente->getRegistroById($ClaveCliente)) {
            return json_encode("La clave de cliente $ClaveCliente no existe");
        }
        $cliente_detalle->getregistrobyID($ClaveCliente);

        $resultCategorias = $cliente_detalle->obtieneMultiCategoria();
        $aux = array();
        while ($rs = mysql_fetch_array($resultCategorias)) {
            array_push($aux, $rs['IdGiro']);
        }        

        /*Obtenemos promedio de calificacion*/
        $calificaciones = array();
        $result = $cliente->getCalificacionesCliente(null);
        $suma_calis = 0;
        $count = 0;
        $promedio = 0;        
        if (mysql_num_rows($result) > 0) {            
            while ($rs = mysql_fetch_array($result)) {
                $suma_calis += (int) $rs['Calificacion'];
                $count++;
                
                if(isset($rs['Calificacion'])){
                    $calificaciones[$rs['Calificacion']] ++;
                }else{
                    $calificaciones[$rs['Calificacion']] = 1;
                }
            }
            $promedio = ($suma_calis / $count);                        
        }
        /*Obtenemos numero de veces favorito*/
        $consulta = "SELECT COUNT(ClaveCliente) AS cuenta FROM k_favoritocliente WHERE ClaveCliente = '$ClaveCliente' GROUP BY ClaveCliente ORDER BY cuenta DESC;";
        $result = $catalogo->obtenerLista($consulta);
        $numero_favorito = 0;
        while($rs = mysql_fetch_array($result)){
            $numero_favorito = $rs['cuenta'];
        }                
        
        $cliente_aux = array();
        if ($tipo >= 1 && $tipo <= 3) {
            //$cliente_aux['ClaveCliente'] = ($cliente->getClaveCliente());
            $cliente_aux['Email'] = ($cliente->getEmail());
            $cliente_aux['SitioWeb'] = ($cliente->getSitioweb());            
            $cliente_aux['SubGiro'] = $aux;
            $cliente_aux['Horario'] = ($cliente_detalle->getHorario());
            $cliente_aux['Favorito'] = ($cliente->isFavorito($ClaveCliente)) ? 1 : 0;
            $cliente_aux['Facebook'] = ($cliente_detalle->getFacebook());
            $cliente_aux['Twitter'] = ($cliente_detalle->getTwitter());
            $cliente_aux['Telefono'] = ($cliente_detalle->getTelefono());
            $cliente_aux['Promedio'] = $promedio;
            $cliente_aux['VecesFavorito'] = $numero_favorito;
            $cliente_aux['Calificaciones'] = $calificaciones;

            $result = $cliente->getCalificacionesCliente(null);
            $cliente_aux['NumeroOpiniones'] = mysql_num_rows($result);
            $i = 1;
            while ($rs = mysql_fetch_array($result)) {
                if($i == mysql_num_rows($result)){//Solo se guarda el ultimo registro
                    $cliente_aux['UltimoTitulo'] = $rs['Titulo'];
                    $cliente_aux['UltimaCalificacion'] = $rs['Calificacion'];
                    $cliente_aux['UltimoMensaje'] = $rs['Mensaje'];
                    $cliente_aux['FechaUltimoMensaje'] = $rs['FechaCreacion'];
                }
                $i++;                
            }

            if ($tipo == 1) {
                $cliente_aux['IdGiro'] = ($cliente->getIdGiro());                                                
                $filename = $cliente->getFoto();
                
                if ($filename != "") {
                    $tmpfile = "../" . $filename;   // temp filename  
                    
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
                        $cliente_aux['NombreFoto'] = $filename;
                        $cliente_aux['Foto'] = $decodeContent;
                    }else{
                        $cliente_aux['NombreFoto'] = "";
                        $cliente_aux['Foto'] = NULL;
                    }
                } else {
                    $cliente_aux['NombreFoto'] = "";
                    $cliente_aux['Foto'] = NULL;
                }
            }
        }

        if ($tipo >= 2 && $tipo <= 3) {
            //$cliente_aux['Modalidad'] = ($cliente->getModalidad());
            $cliente_aux['Latitud'] = ($cliente_detalle->getLatitud());
            $cliente_aux['Longitud'] = ($cliente_detalle->getLongitud());
            $cliente_aux['Direccion'] = $cliente_detalle->getCalleF() . " No. ext: " . $cliente_detalle->getNoExtF() . " No. Int: " . $cliente_detalle->getNoIntF() . ", Colonia: " . $cliente_detalle->getColoniaF() . ", " . $cliente_detalle->getDelegacionF() . ", " . $cliente_detalle->getEstadoF() . ", " . $cliente_detalle->getPais();
        }

        //Se calcula la distancia
        if(!empty($latitud) && !empty($longitud)){
            $localidad = new Localidad();
            $cliente_aux['distanciaKm'] = $localidad->calcularDistancia($cliente_detalle->getLatitud(), $cliente_detalle->getLongitud(), $latitud, $longitud);
        }else{
            $cliente_aux['distanciaKm'] = 0;
        }
        
        $cliente_final = array();
        array_push($cliente_final, $cliente_aux);
                        
        $historico_posiciones->setIdUsuario($resultadoLoggin);
        $historico_posiciones->setLatitud($latitud); $historico_posiciones->setLongitud($longitud);
        $historico_posiciones->setRadio("NULL");
        $historico_posiciones->setClaveCliente($ClaveCliente);
        $historico_posiciones->setIdGiro("NULL"); $historico_posiciones->setIdTipoContacto("NULL");
        if($tipo == 1){
            $historico_posiciones->setIdWebService(1);
            $historico_posiciones->setPantalla("Cliente Consulta WS5");
        }else if($tipo == 2){
            $historico_posiciones->setIdWebService(2);
            $historico_posiciones->setPantalla("Cliente Detalle WS6");
        }
        $historico_posiciones->setUsuarioCreacion($resultadoLoggin); $historico_posiciones->setUsuarioUltimaModificacion($resultadoLoggin);
        $historico_posiciones->setRespuesta(json_encode(array_values($cliente_final)));
        $historico_posiciones->newRegistro();
        
        return json_encode(array_values($cliente_final));
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("consultacliente", "urn:consultacliente");
$server->register("consultaDetalleCliente", 
        array("ClaveCliente" => "xsd:string", "tipo" => "xsd:int", "latitud" => "xsd:float", "longitud" => "xsd:float", "IdSession" => "xsd:string"), array("return" => "xsd:string"), "urn:consultacliente", "urn:consultacliente#consultaDetalleCliente", "rpc", "encoded", "Obtiene datos del cliente especificado");

$server->service($HTTP_RAW_POST_DATA);
/* $server->register("getProd");
  $server->service($HTTP_RAW_POST_DATA); */
?>