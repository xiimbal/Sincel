<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/Cuadrante.class.php");
include_once("../Classes/Catalogo.class.php");

$obj = new Cuadrante;
$catalogo = new Catalogo;

if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdCuadrante($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "El cuadrante se eliminó correctamente";
    } else {
        echo "El cuadrante no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setDescripcion($parametros['descripcion']);
    $obj->setLatitud($parametros['Latitud']);
    $obj->setLongitud($parametros['Longitud']);
    if (isset($parametros['activo']) && $parametros['activo'] == "on") {
        $obj->setActivo(1);
    } else {
        $obj->setActivo(0);
    }
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Catalogo de Cuadrante');

    if (isset($parametros['idCuadrante']) && $parametros['idCuadrante'] == "") {
        if ($obj->newRegistro()) {
            echo "El cuadrante <b>" . $obj->getDescripcion() . "</b> se registró correctamente";
        } else {
            echo "El cuadrante <b>" . $obj->getDescripcion() . "</b> NO se registró correctamente";
        }
    } else {
        $obj->setIdCuadrante($parametros['idCuadrante']);
        if ($obj->editRegistro()) {
            echo "El cuadrante <b>" . $obj->getDescripcion() . "</b> se modificó correctamente";
        } else {
            echo "El cuadrante <b>" . $obj->getDescripcion() . "</b> NO se modificó correctamente";
        }
    }
}
?>