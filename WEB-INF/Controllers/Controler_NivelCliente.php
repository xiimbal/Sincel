<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/NivelCliente.class.php");
$obj = new NivelCliente();
if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdNivelCliente($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "El nivel cliente se eliminó correctamente";
    } else {
        echo "El nivel cliente no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setNivelCliente($parametros['nombre']);
    
    if (isset($parametros['activo']) && $parametros['activo'] == "on") {
        $obj->setActivo(1);
    } else {
        $obj->setActivo(0);
    }
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
    $obj->setPantalla('Controler_NivelCliente');
    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            echo "El nivel cliente ".$obj->getNivelCliente()." se registró correctamente";
        } else {
            echo "Error: El nivel cliente no se pudo registrar";
        }
    } else {/* Modificar */
        $obj->setIdNivelCliente($parametros['id']);
        if ($obj->editRegistro()) {
            echo "El nivel cliente ".$obj->getNivelCliente()." se modificó correctamente";
        } else {
            echo "Error: el nivel cliente no se pudo editar";
        }
    }
}
?>
