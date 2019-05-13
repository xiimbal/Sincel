<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/UbicacionUsuario.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");

/**
 * 
 * @param type $Latitud
 * @param type $Longitud
 * @param type $PorcentajeBateria
 * @param type $IdSession
 * @return int
 */
function insertaUbicacion($Latitud, $Longitud, $PorcentajeBateria, $IdSession) {
    if($Latitud == "0" && $Longitud == "0"){
        return -4;
    }
    
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);

    if ($empresa == "0") {
        return -100;
    }

    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($IdSession);

    if ($resultadoLoggin > 0) {
        $ubicacion = new UbicacionUsuario();
        $ubicacion->setEmpresa($empresa);
        $user_obj = new Usuario();
        $user_obj->setEmpresa($empresa);

        $usuario = "NuevaUbicacion WS";
        $pantalla = "NuevaUbicacion WS";
        
        $result = $ubicacion->getLastUbication($resultadoLoggin);
        if(mysql_num_rows($result) > 0){//En caso de que el usuario actual ya tenga ubicaciones
            while($rs = mysql_fetch_array($result)){
                //return round($rs['Latitud'],7)."==". round($Latitud,7)." && ".round($rs['Longitud'],7)." == ".round($Longitud,7);
                $epsilon = 0.00001;
                if(abs($rs['Latitud']-$Latitud) < $epsilon && abs($rs['Longitud']-$Longitud) < $epsilon){//Si la ultima ubicacion del usuario es la misma de la que se recibio en la peticion actual
                    if($ubicacion->actualizarFechaUbicacion($rs['IdUbicacion'])){//Si se actualiza solo la fecha de la ultima ubicacion
                        return 1;
                    }                    
                }
            }
        }

        $ubicacion->setIdUsuario($resultadoLoggin);
        $ubicacion->setLatitud($Latitud);
        $ubicacion->setLongitud($Longitud);
        $ubicacion->setPorcentajeBateria($PorcentajeBateria);

        if ($user_obj->getRegistroById($resultadoLoggin)) {
            $ubicacion->setUsuarioCreacion($user_obj->getUsuario());
            $ubicacion->setUsuarioUltimaModificacion($user_obj->getUsuario());
        } else {
            $ubicacion->setUsuarioCreacion($usuario);
            $ubicacion->setUsuarioUltimaModificacion($usuario);
        }
        $ubicacion->setPantalla($pantalla);

        if ($ubicacion->newRegistro()) {
            return 1;
        } else {
            return 0;
        }
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("nuevaUbicacion", "urn:nuevaUbicacion");
$server->register("insertaUbicacion", array("Latitud" => "xsd:float", "Longitud" => "xsd:float", "PorcentajeBateria" => "xsd:int", "IdSession" => "xsd:string"), array("return" => "xsd:string"), "urn:nuevaUbicacion", "urn:nuevaUbicacion#insertaUbicacion", "rpc", "encoded", "Inserta ubicacion del usuario");
$server->service($HTTP_RAW_POST_DATA);
?>