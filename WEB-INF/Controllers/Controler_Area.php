<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/Area.class.php");
$obj = new Area();
if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdArea($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "El Área se eliminó correctamente";
    } else {
        echo "El Área no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setDescripcion($parametros['descripcion']);
    if (isset($parametros['activo']) && $parametros['activo'] == "on")
        $obj->setActivo(1);
    else
        $obj->setActivo(0);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Alta área');
    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            echo "El área <b>" . $obj->getDescripcion() . "</b> se registró correctamente";
        } else {
            echo "Error: El área <b>" . $obj->getDescripcion() . "</b> ya se encuentra registrado";
        }
    } else {/* Modificar */
        $obj->setIdArea($parametros['id']);
        if ($obj->editRegistro()) {
            echo "El área <b>" . $obj->getDescripcion() . "</b> se modificó correctamente";
        } else {
            echo "Error: El área <b>" . $obj->getDescripcion() . "</b> ya se encuentra registrado";
        }
    }
}
?>
