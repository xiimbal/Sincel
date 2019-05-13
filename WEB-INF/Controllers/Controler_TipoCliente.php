<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/TipoCliente.class.php");
$obj = new TipoCliente();
if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdTipoCliente($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "El tipo cliente se eliminó correctamente";
    } else {
        echo "El tipo cliente no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setNombre($parametros['nombre']);
    $obj->setDescripcion($parametros['descripcion']);
    $obj->setRadio($parametros['radio']);
    if (isset($parametros['activo']) && $parametros['activo'] == "on") {
        $obj->setActivo(1);
    } else {
        $obj->setActivo(0);
    }
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
    $obj->setPantalla('Controler_TipoCliente');
    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            echo "El tipo cliente ".$obj->getNombre()." se registró correctamente";
        } else {
            echo "Error: El tipo cliente no se pudo registrar";
        }
    } else {/* Modificar */
        $obj->setIdTipoCliente($parametros['id']);
        if ($obj->editRegistro()) {
            echo "El tipo cliente ".$obj->getNombre()." se modificó correctamente";
        } else {
            echo "Error: el tipo cliente no se pudo editar";
        }
    }
}
?>
