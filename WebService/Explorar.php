<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Localidad.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/HistoricoPosiciones.class.php");

function buscarLugares($latitud, $longitud, $radio, $IdSession) {
    $empresa = 3;
    $session = new Session();
    $session->setEmpresa($empresa);

    if (!empty($IdSession)) {
        $resultadoLoggin = (int) $session->logginWithSession($IdSession);
    } else {
        $resultadoLoggin = 0;
    }

    if ($resultadoLoggin > 0 || empty($IdSession)) {
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        $localidad = new Localidad();
        $localidad->setEmpresa($empresa);
        $cliente = new Cliente();
        $cliente->setEmpresa($empresa);
        $historico_posiciones = new HistoricoPosiciones();
        $historico_posiciones->setEmpresa($empresa);
        $busqueda = array();

        /* Obtenemos todos los domicilios ordenados por distancia al punto de la longitud y longitud especificados */
        $domicilios_cercanos = $localidad->obtenerDomiciliosCercanos($latitud, $longitud, "", "", 0, 1000, $radio,"","","");

        foreach ($domicilios_cercanos as $idDomicilio => $value) {
            $aux = array();
            if ($localidad->getLocalidadById($idDomicilio)) {
                if ($cliente->getRegistroById($localidad->getClaveEspecialDomicilio())) {
                    //$modalidad = $cliente->getModalidad();
                    $modalidad = 1;
                    //$aux['IdModalidad'] = $cliente->getModalidad();
                    $aux['IdModalidad'] = $modalidad;
                    if ($cliente->getIdEstatusCobranza() == "2") {
                        $aux['IdModalidad'] = 3;
                    }
                    $aux['NombreNegocio'] = $cliente->getNombreRazonSocial();
                    $aux['IdCategoria'] = $cliente->getIdGiro();
                    $aux['Latitud'] = $localidad->getLatitud();
                    $aux['Longitud'] = $localidad->getLongitud();
                    $aux['DistanciaKm'] = number_format($value, 4);

                    //Si es tipo renta y no está marcado como moroso se agregan más datos
                    if ($cliente->getIdEstatusCobranza() == "1" && $modalidad == "1") {
                        /* Obtenemos la foto */
                        $filename = "";
                        /* Obtenemos el logo, sino una foto del cliente */
                        if ($cliente != NULL && $cliente != "") {
                            $filename = $cliente->getFoto();
                        } else {
                            $result = $cliente->getCalificacionesCliente(1);
                            while ($rs = mysql_fetch_array($result)) {
                                $filename = $rs['Foto'];
                            }
                        }

                        if ($filename != "") {
                            $tmpfile = "../" . $filename;   // temp filename   
                            //Buscamos la imagen re-escalada, si no existe, no se envia nada
                            $index_last_dot = strrpos($tmpfile, ".");
                            $location_aux = substr($tmpfile, 0, $index_last_dot);
                            $location_aux .= "resized_300_techra";
                            $location_aux .= substr($tmpfile, $index_last_dot);

                            if (!empty($location_aux)) {
                                $tmpfile = $location_aux;
                            }

                            if (file_exists($tmpfile)) {
                                $handle = fopen($tmpfile, "r");                  // Open the temp file
                                $contents = fread($handle, filesize($tmpfile));  // Read the temp file            
                                fclose($handle);                                 // Close the temp file

                                $decodeContent = base64_encode($contents);     // Decode the file content, so that we code send a binary string to SOAP                                            
                                $aux['Foto'] = $decodeContent;
                            } else {
                                $aux['Foto'] = NULL;
                            }
                        } else {
                            $aux['Foto'] = NULL;
                        }

                        $direccion = $localidad->getCalle() .
                                ", No ext: " . $localidad->getNoExterior() . " No. Int: " . $localidad->getNoInterior() . ", Col: " . $localidad->getColonia() .
                                ", Del: " . $localidad->getDelegacion() . ", " . $localidad->getEstado() . ", " . $localidad->getPais() . " C.P.: " . $localidad->getCodigoPostal();
                        $aux['Direccion'] = $direccion;
                        $aux['Telefono'] = $cliente->getTelefono();
                        $aux['ClaveNegocio'] = $cliente->getClaveCliente();
                        /* Obtenemos el promedio de las calificaciones del cliente */
                        $result = $cliente->getCalificacionesCliente(null);
                        $suma_calis = 0;
                        $count = 0;
                        while ($rs = mysql_fetch_array($result)) {
                            $suma_calis += (int) $rs['Calificacion'];
                            $count++;
                        }
                        if ($count > 0) {
                            $aux['CalificacionPromedio'] = number_format($suma_calis / $count, 2);
                        } else {
                            $aux['CalificacionPromedio'] = 0;
                        }
                    }
                    array_push($busqueda, $aux);
                }
            }
        }

        $historico_posiciones->setIdUsuario($resultadoLoggin);
        $historico_posiciones->setLatitud($latitud);
        $historico_posiciones->setLongitud($longitud);
        $historico_posiciones->setRadio($radio);
        $historico_posiciones->setIdGiro("NULL");
        $historico_posiciones->setIdTipoContacto("NULL");
        $historico_posiciones->setIdWebService(5);
        $historico_posiciones->setPantalla("Explorar WS15");
        $historico_posiciones->setUsuarioCreacion($resultadoLoggin);
        $historico_posiciones->setUsuarioUltimaModificacion($resultadoLoggin);
        $historico_posiciones->setRespuesta(json_encode(array_values($busqueda)));
        $historico_posiciones->newRegistro();

        return json_encode(array_values($busqueda));
    } else {//Loggin invalido
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("explorar", "urn:explorar");
$server->register("buscarLugares", array("latitud" => "xsd:float", "longitud" => "xsd:float", "radio" => "xsd:float", "IdSesion" => "xsd:string"), array("return" => "xsd:string"), "urn:explorar", "urn:explorar#buscarLugares", "rpc", "encoded", "Busca los lugares más cercanos a las coordenadas especificadas dentro del radio");

$server->service($HTTP_RAW_POST_DATA);
/* $server->register("getProd");
  $server->service($HTTP_RAW_POST_DATA); */
?>
