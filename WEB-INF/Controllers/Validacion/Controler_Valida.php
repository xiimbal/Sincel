<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['idTicket']) 
        || !isset($_POST['NoSerie'])) {
    header("Location: ../../index.php");
}

include_once("../../Classes/Ticket.class.php");

$ticket = new Ticket();

$respuesta = $ticket->validarTicket($_POST['NoSerie'], $_POST['idTicket']);
$mensaje = "";

switch ($respuesta){
    case "0":
        $mensaje = "Error: El equipo no se encuentra aún en inventario";
        break;
    case "1":
        $mensaje = "Error: El ticket fue validado correctamente, pero no se pudo actualizar";
        break;
    case "2":
        $mensaje = "Exito: El ticket fue validado correctamente";
        break;
    default:
        $mensaje = "Error: Desconocido";
        break;
}

echo $mensaje;

?>
