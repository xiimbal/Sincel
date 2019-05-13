<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['NoSerie'])) {
    header("Location: ../../../index.php");
}
include_once("../Classes/Incidencia.class.php");

$incidencia = new Incidencia();
$incidencia->setNoSerie($_POST['NoSerie']);
$incidencia->setFecha($_POST['Fecha']);
$incidencia->setFechaFin($_POST['FechaFin']);
$incidencia->setDescripcion($_POST['Descripcion']);
$incidencia->setStatus($_POST['status']);
$incidencia->setClaveCentroCosto($_POST['cc']);

$incidencia->setActivo(1);
$incidencia->setUsuarioCreacion($_SESSION['user']);
$incidencia->setUsuarioUltimaModificacion($_SESSION['user']);
$incidencia->setPantalla("PHP Nueva incidencia");
if (isset($_POST['tipo']) && $_POST['tipo'] != "")
    $incidencia->setIdTipoIncidencia($_POST['tipo']);
else
    $incidencia->setIdTipoIncidencia(1);
if (isset($_POST['id_ticket']) && $_POST['id_ticket'] == "") {
    $incidencia->setId_Ticket("NULL");
} else {
    $incidencia->setId_Ticket($_POST['id_ticket']);
}
if (!$incidencia->newRegistro()) {
    echo "Error: No se pudo registrar la incidencia del equipo " . $incidencia->getNoSerie();
}
?>
