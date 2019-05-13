<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/TecnicoZona.class.php");
$obj = new TecnicoZona();
if (isset($_GET['id']) && $_GET['id2']) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdUsuario($_GET['id']);
    $obj->setClaveZona($_GET['id2']);
    if ($obj->deleteRegistro()) {
        echo "La zona se eliminó del cliente correctamente";
    } else {
        echo "La zona no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    if (isset($parametros['usuario']))
        $obj->setIdUsuario($parametros['usuario']);
    $obj->setGZona($parametros['gzona']);
    $obj->setClaveZona($parametros['zona']);
    if (isset($parametros['activo']) && $parametros['activo'] == "on")
        $obj->setActivo(1);
    else
        $obj->setActivo(0);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificaciona($_SESSION['user']);
    $obj->setPantalla('Alta TFSCliente');
    if (isset($parametros['accion']) && $parametros['accion'] == "") {
        if ($obj->newRegistro())
            echo "La zona fue asignado correctamente";
        else
            echo "La zona no fue asignado correctamente";
    } else {       
        $obj->setId($parametros['id']);
        $obj->setId2($parametros['id2']);
        
        if ($obj->editRegistro())
            echo "La zona fue reasignado correctamente";
        else
            echo "Error: La zona ya se encuentra registrado con el usuario";
    }
}
?>
