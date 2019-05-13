<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Localidad.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/CentroCosto.class.php");
include_once("../WEB-INF/Classes/Contacto.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/HistoricoPosiciones.class.php");

/**
 * 
 * @param type $latitud
 * @param type $longitud
 * @param type $radio radio es KM.
 * @param type tipo_contacto
 * @param type $pagina
 */
function getContactosCercanos($latitud, $longitud, $radio, $tipo_contacto, $pagina) {
    set_time_limit (0);    
    $empresa = 3;
    $registros_por_pagina = 10;
    $session = new Session();
    $session->setEmpresa($empresa);
    $catalogo = new Catalogo();
    $catalogo->setEmpresa($empresa);
    $localidad = new Localidad();
    $localidad->setEmpresa($empresa);
    $cliente = new Cliente();
    $cliente->setEmpresa($empresa);
    $contacto = new Contacto();
    $contacto->setEmpresa($empresa);
    $cc = new CentroCosto();
    $cc->setEmpresa($empresa);
    $historico_posiciones = new HistoricoPosiciones();
    $historico_posiciones->setEmpresa($empresa);
    $indice = $registros_por_pagina * ($pagina - 1);

    /* Obtenemos todos los domicilios ordenados por distancia al punto de la longitud y longitud especificados */
    $domicilios_cercanos = $localidad->obtenerContactosCercanos($latitud, $longitud, $tipo_contacto, $indice, $registros_por_pagina, $radio);

    $busqueda = array();
    //for ($i = 0; $i < $registros_por_pagina; $i++) {
    foreach ($domicilios_cercanos as $key => $value) {
        $aux = array();
        if ($contacto->getContactoByClave($key)) {
            if ($cliente->getRegistroById($contacto->getClaveEspecialContacto())) {
                $aux['ClaveCliente'] = $cliente->getClaveCliente();
                $aux['Nombrecliente'] = $cliente->getNombreRazonSocial();
                if ($localidad->getLocalidadByClave($cliente->getClaveCliente())) {
                    $aux['Latitud'] = $localidad->getLatitud();
                    $aux['Longitud'] = $localidad->getLongitud();
                }
            } else if ($cc->getRegistroById($contacto->getClaveEspecialContacto())) {
                if ($cliente->getRegistroById($cc->getClaveCliente())) {
                    $aux['ClaveCliente'] = $cliente->getClaveCliente();
                    $aux['Nombrecliente'] = $cliente->getNombreRazonSocial();
                }
                if ($localidad->getLocalidadByClave($cliente->getClaveCliente())) {
                    $aux['Latitud'] = $localidad->getLatitud();
                    $aux['Longitud'] = $localidad->getLongitud();
                }
            }
            $aux['DistanciaKm'] = number_format($value,4);
            $aux['Contacto'] = $contacto->getNombre();
            $aux['Telefono'] = $contacto->getTelefono();
            $aux['Celular'] = $contacto->getCelular();
            $aux['Correo'] = $contacto->getCorreoElectronico();
            array_push($busqueda, $aux);
        }
    }

    //}

    $historico_posiciones->setIdUsuario("NULL");
    $historico_posiciones->setLatitud($latitud);
    $historico_posiciones->setLongitud($longitud);
    $historico_posiciones->setRadio($radio);
    $historico_posiciones->setIdGiro("NULL");
    $historico_posiciones->setIdTipoContacto($tipo_contacto);
    $historico_posiciones->setIdWebService(6);
    $historico_posiciones->setPantalla("Consulta Contacto WS19");
    $historico_posiciones->setUsuarioCreacion("");
    $historico_posiciones->setUsuarioUltimaModificacion("");
    $historico_posiciones->setRespuesta(json_encode(array_values($busqueda)));
    $historico_posiciones->newRegistro();

    return json_encode(array_values($busqueda));
}

$server = new soap_server();
$server->configureWSDL("consultacontacto", "urn:consultacontacto");
$server->register("getContactosCercanos", array("latitud" => "xsd:float", "longitud" => "xsd:float", "radio" => "xsd:float",
    "tipo_contacto" => "xsd:int", "pagina" => "xsd:int"), array("return" => "xsd:string"), "urn:consultacontacto", "urn:consultacontacto#getContactosCercanos", "rpc", "encoded", "Obtiene los contactos dentro del radio especificado");
$server->service($HTTP_RAW_POST_DATA);
?>