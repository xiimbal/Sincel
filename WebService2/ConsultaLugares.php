<?php
require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Localidad.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
 
/**
 * 
 * @param type $latitud
 * @param type $longitud
 * @param type $radio radio es KM.
 * @param type $categoria
 * @param type $pagina
 */
function getLugaresCercanos($latitud, $longitud, $radio, $categoria, $pagina, $usuario, $password) {
    $empresa = 3;
    $registros_por_pagina = 10;
    $separador = "%%";
    $cadena_enviar = "";
    $session = new Session();
    $session->setEmpresa($empresa);
    if($session->getLogin($usuario, $password)){
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        $localidad = new Localidad();
        $localidad->setEmpresa($empresa);
        $cliente = new Cliente();
        $cliente->setEmpresa($empresa);
        /*Obtenemos todos los domicilios ordenados por distancia al punto de la longitud y longitud especificados*/    
        $domicilios = $localidad->obtenerDomiciliosCercanos($latitud, $longitud, $categoria);
        $domicilios_cercanos = array();
        foreach ($domicilios as $idDomicilio => $distancia) {
            if($distancia <= $radio){
                array_push($domicilios_cercanos, $idDomicilio);//Guardamos los id de los domicilios dentro del radio
            }
        }

        $indice = $registros_por_pagina * ($pagina-1);
        for($i=0; $i<$registros_por_pagina; $i++){
            if(isset($domicilios_cercanos[$indice+$i])){
                if($localidad->getLocalidadById($domicilios_cercanos[$indice+$i])){
                    if($cliente->getRegistroById($localidad->getClaveEspecialDomicilio())){
                        $cadena_enviar .= $cliente->getNombreRazonSocial().",".$localidad->getCalle().
                            ",".$localidad->getNoExterior().",".$localidad->getNoInterior().",".$localidad->getColonia().
                            ",".$localidad->getDelegacion().",".$localidad->getEstado().",".$localidad->getPais().",".$localidad->getCodigoPostal().
                            ",".$localidad->getLatitud().",".$localidad->getLongitud().",$separador,";
                    }
                }
            }
        }
        if($cadena_enviar != ""){
            $cadena_enviar = substr($cadena_enviar, 0, strlen($cadena_enviar)-4);
        }
        return $cadena_enviar;
    }else{
        return "Error: Usuario y/o password incorrecto";
    }    
}
 
$server = new soap_server();
$server->configureWSDL("consultalugares", "urn:consultalugares"); 
$server->register("getLugaresCercanos",
    array("latitud" => "xsd:float", "longitud" => "xsd:float", "radio" => "xsd:float", "categoria" => "xsd:int", "pagina" => "xsd:int", "usuario" => "xsd:string", "password" => "xsd:string"),
    array("return" => "xsd:string"),
    "urn:consultalugares",
    "urn:consultalugares#getLugaresCercanos",
    "rpc",
    "encoded",
    "Obtiene la ultima version de categorias");
 
$server->service($HTTP_RAW_POST_DATA);
/*$server->register("getProd");
$server->service($HTTP_RAW_POST_DATA);*/
?>