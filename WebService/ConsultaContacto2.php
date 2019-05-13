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
 * @param type $ClaveCliente
 * @param type $ClaveLocalidad
 * @param type $tipos_contactos.
 * @param type $pagina
 * @param type $IdSession
 */

function getContactosPorClave($ClaveCliente, $ClaveLocalidad, $tipos_contactos, $pagina, $IdSession){
    set_time_limit(0);       
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);
    
    $registros_por_pagina = 10;
    $localidad = new Localidad();
    $localidad->setEmpresa($empresa);
    $cliente = new Cliente();
    $cliente->setEmpresa($empresa);
    $contacto = new Contacto();
    $contacto->setEmpresa($empresa);
    $cc = new CentroCosto();
    $cc->setEmpresa($empresa);
    
    $indice = $registros_por_pagina * ($pagina - 1);
    $registros_por_pagina = 10 * $pagina;

    if(!isset($ClaveCliente) || $ClaveCliente == ""){
        return -4;  //No se encontro la clave del cliente
    }
    
    /* Obtenemos todos los domicilios ordenados por distancia al punto de la longitud y longitud especificados */    
    $domicilios = $localidad->obtenerContactoClaveCliente($ClaveCliente, $ClaveLocalidad, $tipos_contactos, $indice, $registros_por_pagina);    
    
    $busqueda = array();
    foreach ($domicilios as $key => $value) {
        $aux = array();
        if ($contacto->getContactoByClave($key)) {
            if ($cliente->getRegistroById($contacto->getClaveEspecialContacto())) {
                $aux['ClaveCliente'] = $cliente->getClaveCliente();
                $aux['Nombrecliente'] = $cliente->getNombreRazonSocial();
            } else if ($cc->getRegistroById($contacto->getClaveEspecialContacto())) {
                if ($cliente->getRegistroById($cc->getClaveCliente())) {
                    $aux['ClaveCliente'] = $cliente->getClaveCliente();
                    $aux['Nombrecliente'] = $cliente->getNombreRazonSocial();
                }
            }
            $aux['Contacto'] = $contacto->getNombre();
            $aux['Telefono'] = $contacto->getTelefono();
            $aux['Celular'] = $contacto->getCelular();
            $aux['Correo'] = $contacto->getCorreoElectronico();
            array_push($busqueda, $aux);
        }
    }
    $json = array_values($busqueda);
    return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($json));
}
$server = new soap_server();
$server->configureWSDL("consultacontacto2", "urn:consultacontacto2");
$server->register("getContactosPorClave", array("ClaveCliente" => "xsd:string", "ClaveLocalidad" => "xsd:string", "tipos_contactos" => "xsd:string",
    "pagina" => "xsd:int", "IdSession" => "xsd:string"), array("return" => "xsd:string"), "urn:consultacontacto2", "urn:consultacontacto2#getContactosPorClave", "rpc", "encoded", "Obtiene los contactos dependiendo la clave o localidad del cliente");
$server->service($HTTP_RAW_POST_DATA);
?>

