<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/ContactoEmpleado.class.php");
$obj = new ContactoEmpleado();

if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdFormaContacto($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "La forma de contacto se eliminó correctamente";
    } else {
        echo "La forma de contacto no se pudo eliminar, ya que contiene datos asociados.";
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
    $obj->setPantalla('Controler_ContactoEmpleado');
    
    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            echo "La forma de contacto ".$obj->getNombre()." se registró correctamente";
        } else {
            echo "Error: La forma de contacto no se pudo registrar";
        }
    } else {/* Modificar */
        $obj->setIdFormaContacto($parametros['id']);
        if ($obj->editRegistro()) {
            echo "La forma de contacto ".$obj->getNombre()." se modificó correctamente";
        } else {
            echo "Error: la forma de contacto no se pudo editar";
        }
    }
}
?>
