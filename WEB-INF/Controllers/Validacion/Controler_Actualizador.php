<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['tipo']) 
        || !isset($_POST['idTicket']) || !isset($_POST['idComponente'])) {
    header("Location: ../../index.php");
}

include_once("../../Classes/Ticket.class.php");

$ticket = new Ticket();

if($_POST['tipo'] == "equipo"){
    if($ticket->actualizarEquipo($_POST['idTicket'], $_POST['idComponente'])){
        echo "El equipo se actualizo correctamente";
    }else{
        echo "Error: el equipo no se pudo actualizar";
    }
}else if($_POST['tipo'] == "cliente"){
    if($ticket->actualizarCliente($_POST['idTicket'], $_POST['idComponente'])){
        echo "El cliente fue actualizado correctamente";
    }else{
        echo "Error: el cliente no se pudo actualizar";
    }
}else if($_POST['tipo'] == "centro_costo"){
   if($ticket->actualizarCentroCosto($_POST['idTicket'], $_POST['idComponente'])){
       echo "El centro de costo se actualizo correctamente";
   }else{
       echo "Error: el centro de costo no se pudo actualizar";
   }
}

?>
