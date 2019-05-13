<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

function obtenerComponentePorEquipo($NoParteEquipo , $idSession){
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($idSession);

    if ($empresa == "0") {
        return -100;
    }

    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($idSession);
    
    if ($resultadoLoggin > 0){
        if(empty($NoParteEquipo)){
            return -3;
        }
        $consulta = "SELECT NoParte, Modelo FROM c_componente c, k_equipocomponentecompatible ecc
                     WHERE ecc.NoParteComponente = c.NoParte AND ecc.NoParteEquipo = '$NoParteEquipo' 
                     AND c.Activo = 1 AND c.IdTipoComponente = 1";  //IdTipoComponente = 1 debido a que son solo refacciones
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        $result = $catalogo->obtenerLista($consulta);
        
        $componentes = array();
        while($rs = mysql_fetch_array($result))
        {
            $componente = array();
            $componente['NoParte'] = $rs['NoParte'];
            $componente['Modelo'] = $rs['Modelo'];
            array_push($componentes, $componente);
        }  
        $json = array_values($componentes);
        return json_encode($json);
        
    }else {
        return json_encode($resultadoLoggin);
    }
    
}

$server = new soap_server();
$server->configureWSDL("obtenerpartecomponente", "urn:obtenerpartecomponente");
$server->register("obtenerComponentePorEquipo", array("NoParteEquipo" => "xsd:string", "idSession" => "xsd:string"), array("return" => "xsd:string"), 
        "urn:obtenerpartecomponente", "urn:obtenerpartecomponente#obtenerComponentePorEquipo", "rpc", "encoded", 
        "Obtiene todos los componentes de tipo refacción de un equipo");
$server->service($HTTP_RAW_POST_DATA);

?>
