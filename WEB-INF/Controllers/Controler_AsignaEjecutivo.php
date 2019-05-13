<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['tecnico']) || !isset($_POST['idTicket'])) {
    header("Location: ../../index.php");
}

include_once("../Classes/Ticket.class.php");
include_once("../Classes/Usuario.class.php");
include_once("../Classes/Catalogo.class.php");

$ticket = new Ticket();
$usuario = new Usuario();
$catalogo = new Catalogo();
$IdTicket = $_POST['idTicket'];

$usuario->getRegistroById($_POST['tecnico']);

$ticket->setUsuarioUltimaModificacion($_SESSION['user']);
$ticket->setUsuarioCreacion($_SESSION['user']);
$ticket->setPantalla('PHP Asigna Ejecutivo');

$ticket->setIdTicket($IdTicket);
$ticket->eliminarAsignacionesEjecutivo(); //Eliminamos asignaciones anteriores    
if ($ticket->asociarTicketEjecutivo($usuario->getId())) {
    if ($ticket->crearNotaGeneral($usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno(), $tipo, "94")) {        
        echo "<br/>El ticket <b>$IdTicket</b> fue asignado al ejecutivo ".$usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno()." correctamente";
    }
}else{
    echo "<br/>No se pudo asignar el ticket <b>$IdTicket</b>";
}
?>
