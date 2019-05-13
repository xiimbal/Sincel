<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set ( "America/Mexico_City" );

require_once "../lib/nusoap.php";

include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
 
function consultaCodigo($codigo, $IdSession) {
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);

    if ($empresa == "0") {
        return -100;
    }

    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($IdSession);
    
    if ($resultadoLoggin > 0) {
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        $query = $catalogo->obtenerLista("SELECT cu.* FROM `c_usuario` cu LEFT JOIN c_domicilio_usturno cd ON cd.IdUsuario=cu.IdUsuario WHERE cd.Codigo = '" . $codigo . "';");
        $valores = array();
        while ($rs = mysql_fetch_array($query)) {
            $dato = array();
            $dato['IdUsuario']=$rs['IdUsuario'];
            $dato['Loggin'] = $rs['Loggin'];
            array_push($valores, $dato);
        }
        
        $json = array_values($valores);
        return json_encode($json);
    }else{
        return json_encode($resultadoLoggin);
    }
}
 
$server = new soap_server();
$server->configureWSDL("verificarCodigo", "urn:verificarCodigo"); 
$server->register("consultaCodigo",
    array("codigo" => "xsd:string", "IdSession" => "xsd:string"),
    array("return" => "xsd:string"),
    "urn:verificarCodigo",
    "urn:verificarCodigo#consultaCodigo",
    "rpc",
    "encoded",
    "Obtiene Id y Loggin de Uusuario por Codigo de Barras");
 
$server->service($HTTP_RAW_POST_DATA);
/*$server->register("getProd");
$server->service($HTTP_RAW_POST_DATA);*/
?>
