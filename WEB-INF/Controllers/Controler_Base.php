<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/Base.class.php");
$obj = new Base();

if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdBase($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "La base se eliminó correctamente";
    } else {
        echo "La base no se pudo eliminar, ya que contiene datos asociados.";
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
    $obj->setPantalla('Controler_Base');
    
    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            echo "La base ".$obj->getNombre()." se registró correctamente";
        } else {
            echo "Error: La base no se pudo registrar";
        }
    } else {/* Modificar */
        $obj->setIdBase($parametros['id']);
        if ($obj->editRegistro()) {
            echo "La base ".$obj->getNombre()." se modificó correctamente";
        } else {
            echo "Error: la base no se pudo editar";
        }
    }
}
?>
