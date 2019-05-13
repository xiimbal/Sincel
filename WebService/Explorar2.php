<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Localidad.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/CentroCosto.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/HistoricoPosiciones.class.php");

function buscarLugares($latitud, $longitud, $radio, $TipoCliente, $pagina, $IdSession, $IdEjecutivos, $TipoEjecutivos, $MostrarLocalidades) {
    $session = new Session();
    //return $IdSession;
    $empresa = $session->obtenerEmpresaBySesion($IdSession);

    if ($empresa == "0") {
        return -100;
    }
    
    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($IdSession);

    if ($resultadoLoggin > 0) {
        $registros_por_pagina = 10;
        $indice = $registros_por_pagina * ($pagina - 1);
        
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
        $domicilios_cercanos = $localidad->obtenerDomiciliosCercanos2($latitud, $longitud, "", $TipoCliente, $indice, $registros_por_pagina, $radio, $IdEjecutivos, $TipoEjecutivos, $resultadoLoggin);

        foreach ($domicilios_cercanos as $idDomicilio => $value) {
            $aux = array();
            if ($localidad->getLocalidadById($idDomicilio)) {
                if ($cliente->getRegistroById($localidad->getClaveEspecialDomicilio())) {                                                                              
                    $aux['NombreNegocio'] = $cliente->getNombreRazonSocial();                    
                    $aux['TipoCliente'] = $cliente->getIdTipoCliente();
                    $aux['Latitud'] = $localidad->getLatitud();                    
                    $aux['Longitud'] = $localidad->getLongitud();
                    $aux['DistanciaKm'] = number_format($value, 4);
                    $direccion = $localidad->getCalle() .
                                ", No ext: " . $localidad->getNoExterior() . " No. Int: " . $localidad->getNoInterior() . ", Col: " . $localidad->getColonia() .
                                ", Del: " . $localidad->getDelegacion() . ", " . $localidad->getEstado() . ", " . $localidad->getPais() . " C.P.: " . $localidad->getCodigoPostal();
                    $aux['Direccion'] = $direccion;
                    $aux['Telefono'] = $cliente->getTelefono();
                    $aux['ClaveNegocio'] = $cliente->getClaveCliente();
                    $aux['RFC'] = $cliente->getRFC();
                    if($MostrarLocalidades == "1"){
                        $centro_costo = new CentroCosto();   
                        $centro_costo->setEmpresa($empresa);
                        $result = $centro_costo->getRegistroValidacion($cliente->getClaveCliente());    
                        $busqueda2 = array();
                        $aux2 = array();                        
                        while($rs = mysql_fetch_array($result)){
                            $localidad2 = new Localidad();
                            $localidad2->setEmpresa($empresa);
                            if($localidad2->getLocalidadByClave($rs['ClaveCentroCosto'])){
                                $aux2['ClaveSucursal'] = $rs['ClaveCentroCosto'];
                                $aux2['NombreSucursal'] = $rs['Nombre'];
                                $direccion = $localidad2->getCalle() .
                                ", No ext: " . $localidad2->getNoExterior() . " No. Int: " . $localidad2->getNoInterior() . ", Col: " . $localidad2->getColonia() .
                                ", Del: " . $localidad2->getDelegacion() . ", " . $localidad2->getEstado() . ", " . $localidad2->getPais() . " C.P.: " . $localidad2->getCodigoPostal();
                                $aux2['Direccion'] = $direccion;
                                $aux2['Latitud'] = $localidad2->getLatitud();
                                $aux2['Longitud'] = $localidad2->getLongitud();
                            }
                            array_push($busqueda2, $aux2);
                        }
                        $aux['Sucursales'] = $busqueda2;
                    }
                    array_push($busqueda, $aux);
                }
            }
        }

        /*$historico_posiciones->setIdUsuario($resultadoLoggin);
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
        $historico_posiciones->newRegistro();*/

        $json = array_values($busqueda);
        return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($json));
    } else {//Loggin invalido
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("explorar", "urn:explorar");
$server->register("buscarLugares", 
        array("latitud" => "xsd:float", "longitud" => "xsd:float", "radio" => "xsd:float", "TipoCliente" => "xsd:int", "pagina" => "xsd:int" ,"IdSesion" => "xsd:string", "IdEjecutivos" => "xsd:string", "TipoEjecutivos" => "xsd:int", "MostrarLocalidades" => "xsd:int"), array("return" => "xsd:string"), "urn:explorar", "urn:explorar#buscarLugares", "rpc", "encoded", "Busca los lugares más cercanos a las coordenadas especificadas dentro del radio");

$server->service($HTTP_RAW_POST_DATA);
/* $server->register("getProd");
  $server->service($HTTP_RAW_POST_DATA); */
?>
