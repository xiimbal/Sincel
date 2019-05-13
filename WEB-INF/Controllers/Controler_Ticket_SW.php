<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['tecnico']) || !isset($_POST['idTicket']) 
        || !isset($_POST['tipo'])) {
    header("Location: ../../index.php");
}

include_once("../Classes/Ticket.class.php");
include_once("../Classes/NotaTicket.class.php");
include_once("../Classes/Usuario.class.php");

$ticket = new Ticket();
$usuario = new Usuario();

$usuario->getRegistroById($_POST['tecnico']);

$ticket->setIdTicket($_POST['idTicket']);
$ticket->setUsuarioUltimaModificacion($_SESSION['user']);
$ticket->setUsuarioCreacion($_SESSION['user']);
$ticket->setPantalla('PHP Asigna Ticket Tecnico');

if($_POST['tipo'] == "1"){
    $tipo = "técnico de HW";
    $estadoNota =  "47";
}else if($_POST['tipo'] == "2"){
    $tipo = "técnico de SW";
    $estadoNota = "48";
}else{
    $tipo = "TFS";
    $estadoNota = "";
}

if($estadoNota!=""){//Cerramos las notas de asignar tecnico que estén abiertas
    $nota = new NotaTicket();
    $result = $nota->getNotasByTicket($ticket->getIdTicket());
    while($rs = mysql_fetch_array($result)){
        if($rs['IdEstatusAtencion'] == $estadoNota && $rs['IdEstadoNota']=="1"){
            $nota->cerrarNota($rs['IdNotaTicket']);
        }
    }
}

$ticket->eliminarAsignacionesAnteriores($_POST['tipo']);//Eliminamos asignaciones anteriores
if($ticket->asociarTicketTecnico($_POST['tecnico'], $_POST['tipo'])){
    if($ticket->crearNota($usuario->getNombre()." ".$usuario->getPaterno()." ".$usuario->getMaterno(), $tipo)){
        echo "El ticket fue asignado al técnico correctamente";
    }
}

?>
