<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/RangoTarifa.class.php");
$obj = new RangoTarifa();

if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdTarifa($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "El rango de tarifa se eliminó correctamente";
    } else {
        echo "El rango de tarifa no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }

    $obj->setTarifa($parametros['nombre']); 
    $obj->setActivo(1);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
    $obj->setPantalla('Controler Rango Tarifa PHP');

    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            $obj->insertarDetalles($parametros);
            echo "El rango de tarifa " . $obj->getTarifa() . " se registró correctamente";
        } else {
            echo "Error: El rango de tarifa " . $obj->getTarifa() . " ya se encuentra registrado";
        }
    } else {/* Modificar */
        $obj->setIdTarifa($parametros['id']);
        if ($obj->editRegistro()) {
            $obj->insertarDetalles($parametros);
            echo "El rango de tarifa " . $obj->getTarifa() . " se modificó correctamente";
        } else {
            echo "Error: El rango de tarifa " . $obj->getTarifa() . " ya se encuentra registrado";
        }
    }
}

?>