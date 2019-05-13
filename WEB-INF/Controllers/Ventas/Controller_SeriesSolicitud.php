<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../../Classes/Solicitud.class.php");

if(isset($_POST['id_solicitud']) && isset($_POST['id_partida']) && isset($_POST['eliminar'])){
    $solicitud = new Solicitud();
    $solicitud->setUsuarioUltimaModificacion($_SESSION['user']); $solicitud->setPantalla("Controller_SeriesSolicitud.php");
    if($solicitud->eliminarEquipoPartida($_POST['id_solicitud'], $_POST['id_partida'])){        
        echo "<br/>El equipo fue marcado como no surtido correctamente";
    }else{
        echo "<br/>Error: El equipo NO fue marcado como no surtido, intenta de nuevo por favor.";
    }
}

?>
