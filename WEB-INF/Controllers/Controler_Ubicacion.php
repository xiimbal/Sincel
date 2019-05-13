<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../Classes/Ubicacion.class.php");
include_once("../Classes/Catalogo.class.php");
$ubicacion = new Ubicacion();
//print_r($_GET);
if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $ubicacion->setIdUbicacion($_GET['id']);
    if ($ubicacion->deleteRegistro()) {
        echo "La Ubicación se eliminó correctamente";
    } else {
        echo "La Ubicación no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }

    $ubicacion->setDescripcion($parametros['txtDescripcion']);
    $ubicacion->setActivo(1);
    $ubicacion->setUsuarioCreacion($_SESSION['user']);
    $ubicacion->setUsuarioUltimaModificacion($_SESSION['user']);
    $ubicacion->setPantalla('PHP catalogos Ubicación');
    $ubicacion->setCalle($parametros['txtCalle']);
    $ubicacion->setExterior($parametros['txtExterior']);
    $ubicacion->setColonia($parametros['txtColonia']);
    $ubicacion->setDelegacion($parametros['txtDelegacion']);
    $ubicacion->setCp($parametros['txtcp']);
    $ubicacion->setEstado($parametros['slcEstado']);
    $ubicacion->setLatitud($parametros['Latitud']);
    $ubicacion->setLongitud($parametros['Longitud']);

    if (isset($parametros['id']) && $parametros['id'] == "") {
        if ($ubicacion->newRegistro()) {
            echo "La Ubicación de <b>" . $ubicacion->getDescripcion() . "</b> se registró correctamente";
        } else {
            echo "Error: La Ubicación no se registro";
        }
    } else {/* Modificar */
        $ubicacion->setIdUbicacion($parametros['id']);
        if ($ubicacion->editRegistro()) {
            echo "La Ubicación <b>" . $ubicacion->getDescripcion() . "</b> se modificó correctamente";
        } else {
            echo "Error: La Ubicación no se modificó";
        }
    }
}    