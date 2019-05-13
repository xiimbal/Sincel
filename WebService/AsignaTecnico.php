<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Evento.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Ticket.class.php");

function asignaTec($IdTecnico, $IdTicket, $IdPrioridad, $Duracion, $IdUnidadDuracion, $FechaHora, $IdSession) {
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);

    if ($empresa == "0") {
        return -100;
    }

    if (empty($IdPrioridad) || empty($IdTicket) || empty($IdUnidadDuracion) || empty($Duracion) || empty($FechaHora)) {
        return -4;
    }

    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($IdSession);

    if ($resultadoLoggin > 0) {
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        
        if(empty($IdTecnico)){//si no se manda el id de tecnico, se toma el id del usuario de la sesión
            $IdTecnico = $resultadoLoggin;
        }
        
        $consulta = "SELECT IdTicket FROM `k_tecnicoticket` WHERE IdUsuario = $IdTecnico AND FechaHoraInicio = '$FechaHora'";
        $result = $catalogo->obtenerLista($consulta);
        if(mysql_num_rows($result) > 0){
            while($rs = mysql_fetch_array($result)){
                return -8;
            }            
        }

        
        $usuario_obj = new Usuario();
        $usuario_obj->setEmpresa($empresa);
        if (!$usuario_obj->getRegistroById($IdTecnico)) {
            return -5; //No se encuentra el id del tecnico
        }

        $user_obj = new Usuario();
        $user_obj->setEmpresa($empresa);
        if ($user_obj->getRegistroById($resultadoLoggin)) {
            $usuario = $user_obj->getUsuario();
        } else {
            $usuario = "User WS";
        }
        
        $obj = new Ticket();
        $obj->setEmpresa($empresa);
        $obj->setUsuarioCreacion($usuario);
        $obj->setUsuarioUltimaModificacion($usuario);
        $obj->setPantalla("WS AsignaTecnico");

        $obj->setIdTicket($IdTicket);
        if ($obj->asociarTicketTecnicoGeneral($IdTecnico, $IdPrioridad, $Duracion, $IdUnidadDuracion, $FechaHora)) {
            if (!$obj->crearNota($usuario_obj->getNombre() . " " . $usuario_obj->getPaterno() . " " . $usuario_obj->getMaterno(), "")) {
                return -6; //No se pudo crear la nota de asignacion
            }
        } else {
            return -7; //No se pudo asociar el tecnico con el ticket
        }
        return 1;
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("asignatecnico", "urn:asignatecnico");
$server->register("asignaTec", array(
    "IdTecnico" => "xsd:int", "IdTicket" => "xsd:int", "IdPrioridad" => "xsd:int", "Duracion" => "xsd:int", "IdUnidadDuracion" => "xsd:int",
    "FechaHora" => "xsd:string", "IdSession" => "xsd:string"), array("return" => "xsd:string"), "urn:nuevoTicket", "urn:nuevoTicket#insertaTicket", "rpc", "encoded", "Inserta un nuevo ticket");
$server->service($HTTP_RAW_POST_DATA);
?>

