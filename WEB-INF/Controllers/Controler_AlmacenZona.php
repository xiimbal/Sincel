<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/AlmacenZona.class.php");
$obj = new AlmacenZona();
if (isset($_GET['id']) && isset($_GET['id2'])) {
    $obj->setIdAlmacen($_GET['id']);
    $obj->setClaveZona($_GET['id2']);
    if ($obj->deleteRegistro()) {
        echo "La zona del almacén se eliminó correctamente";
    } else {
        echo "La zona del almacén  no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setIdAlmacen($parametros['almacen']);
    $obj->setIdGZona($parametros['gzona']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Almacen-zona');

    if (isset($parametros['accion']) && $parametros['accion'] == "") {
        $contador = 1;
        while (isset($parametros['zona' . $contador])) {
            $obj->setClaveZona($parametros['zona' . $contador]);
            if ($obj->newRegistro())
                echo "";
            else
                echo "";
            $contador++;
        }
         echo "La zona del almacén se registró correctamente";
    }
    else {
        $obj->setClaveZona($parametros['zona1']);
        $obj->setId($parametros['zona']);
        if ($obj->editRegistro()) {
            echo "La zona del almacén se modificó correctamente";
        } else {
            echo "Error:La zona  del almacén ya se encuentra registrado";
        }
    }
}
?>
