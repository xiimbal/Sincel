<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Localidad.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/ccliente.class.php");
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
                    $cliente_aux = new ccliente();                    
                    $cliente_aux->setEmpresa($empresa);
                    $cliente_aux->getregistrobyID($cliente->getClaveCliente());                    
                    
                    $aux['NombreNegocio'] = $cliente->getNombreRazonSocial();
                    $aux['IdGiro'] = $cliente->getIdGiro();
                    $aux['Correo'] = $cliente->getEmail();
                    $aux['Telefono'] = $cliente->getTelefono();
                    $aux['Facebook'] = $cliente_aux->getFacebook();
                    $aux['Twitter'] = $cliente_aux->getTwitter();
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
        $historico_posiciones->setIdWebService(7);
        $historico_posiciones->setPantalla("Explorar WS23");
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
$server->configureWSDL("explorarLimitado", "urn:explorarLimitado");
$server->register("buscarLugares", array("latitud" => "xsd:float", "longitud" => "xsd:float", "radio" => "xsd:float", "IdSesion" => "xsd:string"), array("return" => "xsd:string"), "urn:explorarLimitado", "urn:explorarLimitado#buscarLugares", "rpc", "encoded", "Busca los lugares más cercanos a las coordenadas especificadas dentro del radio");

$server->service($HTTP_RAW_POST_DATA);
/* $server->register("getProd");
  $server->service($HTTP_RAW_POST_DATA); */
?>
