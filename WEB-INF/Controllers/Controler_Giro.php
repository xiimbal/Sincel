<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/Giro.class.php");
$obj = new Giro();
if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdGiro($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "El giro se eliminó correctamente";
    } else {
        echo "El giro no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setNombre($parametros['nombre']);
    $obj->setDescripcion($parametros['descripcion']);
    
    if (isset($parametros['activo']) && $parametros['activo'] == "on") {
        $obj->setActivo(1);
    } else {
        $obj->setActivo(0);
    }
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
    $obj->setPantalla('Controler_Giro');
    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            echo "El giro ".$obj->getNombre()." se registró correctamente";
        } else {
            echo "Error: El giro no se pudo registrar";
        }
    } else {/* Modificar */
        $obj->setIdGiro($parametros['id']);
        if ($obj->editRegistro()) {
            echo "El giro ".$obj->getNombre()." se modificó correctamente";
        } else {
            echo "Error: el giro no se pudo editar";
        }
    }
}
?>
