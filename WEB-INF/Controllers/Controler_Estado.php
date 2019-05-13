<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/Estado.class.php");
$obj = new Estado();
if (isset($_GET['id']) && isset($_GET['id2'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdEstado($_GET['id']);
    $obj->setIdKFlujo($_GET['id2']);
//    echo $obj->getIdEstado()." ".$obj->getIdKFlujo();
    if ($obj->deleteFlujoEstado()) {
        if ($obj->deleteRegistro()) {
            echo "El estado se eliminó correctamente";
        } else {
            echo "El estado no se pudo eliminar, ya que contiene datos asociados.";
        }
    }

//    
//    if ($obj->deleteRegistro()) {
//
//        if ($obj->deleteFlujoEstado())
//            echo "El estado se eliminó correctamente";
//    } else {
//        echo "El estado no se pudo eliminar, ya que contiene datos asociados.";
//    }
} else if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdEstado($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "El estado se eliminó correctamente";
    } else {
        echo "El estado no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setNombre($parametros['nombre']);
    if ($parametros['area'] == '0')
        $obj->setArea('NULL');
    else
        $obj->setArea($parametros['area']);
    if (isset($parametros['activo']) && $parametros['activo'] == "on") {
        $obj->setActivo(1);
    } else {
        $obj->setActivo(0);
    }
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Alta Estado');
    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            $idUltimoEstado = $obj->getIdEstado();
            $obj->setIdFlujo(6);
            $obj->setOrdenFlujo(0);
            if ($obj->newFlujoEstado($idUltimoEstado)) {
                echo "El estado <b>" . $obj->getNombre() . "</b> se registró correctamente";
            }
        } else {
            echo "Error: El estado <b>" . $obj->getNombre() . "</b> ya se encuentra registrado";
        }
    } else {/* Modificar */
        $obj->setIdEstado($parametros['id']);
        if ($obj->editRegistro()) {
            echo "El estado <b>" . $obj->getNombre() . "</b> se modificó correctamente";
        } else {
            echo "Error: El estado <b>" . $obj->getNombre() . "</b> ya se encuentra registrado";
        }
    }
}
?>
