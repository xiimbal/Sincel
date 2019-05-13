<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/ResponsableAlmacen.class.php");
$obj = new ResponsableAlmacen();
if (isset($_GET['id']) && isset($_GET['id2'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setUsuario($_GET['id']);
    $obj->setAlmacen($_GET['id2']);
    if ($obj->deleteRegistro())
        echo "El almacén se eliminó correctamente del encargado.";
    else
        echo "El almacén no se pudo eliminar.";
}
else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
//    if (isset($parametros['responsable']))
    $obj->setUsuario($parametros['responsable']);
    $obj->setAlmacen($parametros['almacen']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Asignar reponsable al almacén');
    if (isset($parametros['id']) && $parametros['id'] == "") {
        if ($obj->newRegistro() == "1")
            echo "El usuario fue asignado al almacén correctamente";
        else if ($obj->newRegistro() == "2")
            echo "Error: El usuario no fue asignado al almacén correctamente";
        else if ($obj->newRegistro() == "3")
            echo "Error: El usuario ya se encuentra registrado con el almacén, intenté con otro por favor";
    } else {
        $obj->setId($parametros['id']);
        $obj->setId2($parametros['id2']);
        if ($obj->editRegistro() == "1")
            echo "El usuario fue asignado al almacén correctamente";
        else if ($obj->editRegistro() == "2")
            echo "Error: El usuario no fue asignado al almacén correctamente";
        else if ($obj->editRegistro() == "3")
            echo "Error: El usuario ya se encuentra registrado con el almacén, intenté con otro por favor";
    }
}
?>
