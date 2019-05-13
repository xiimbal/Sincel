<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/Producto.class.php");
$obj = new Producto();

if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setId($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "El producto se eliminó correctamente";
    } else {
        echo "El producto no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }

    $obj->setNombre($parametros['nombre']);
    $obj->setDescripcion($parametros['descripcion']);
    $obj->setOrden($parametros['orden']);
    if (isset($parametros['activo']) && $parametros['activo'] == "on") {
        $obj->setActivo(1);
    } else {
        $obj->setActivo(0);
    }
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('CargaDefault');

    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            echo "El producto " . $obj->getNombre() . " se registró correctamente";
        } else {
            echo "Error: El producto " . $obj->getNombre() . " ya se encuentra registrado";
        }
    } else {/* Modificar */
        $obj->setId($parametros['id']);
        if ($obj->editRegistro()) {
            echo "El producto " . $obj->getNombre() . " se modificó correctamente";
        } else {
            echo "Error: El producto " . $obj->getNombre() . " ya se encuentra registrado";
        }
    }
}
?>