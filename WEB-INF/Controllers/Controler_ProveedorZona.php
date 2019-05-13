<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/ProveedorZona.class.php");
$obj = new ProveedorZona();
if (isset($_GET['id'])) {
    $obj->setId($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "La zona de la sucursal se eliminó correctamente";
    } else {
        echo "La zona de la sucursal  no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    if (isset($parametros['sl_proveedor'])) {
        $obj->setIdProveedor($parametros['sl_proveedor']);
    } else {
        $obj->setIdProveedor($parametros['id_prov']);
    }
    $obj->setIdSucursal($parametros['sl_sucursal']);
    $obj->setGZona($parametros['gzona']);
    $obj->setIdZona($parametros['zona']);
    $obj->setTiempoMaxSolucion($parametros['tiempo']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('k_proveedoreszona');
    if (isset($parametros['idProvSucZona']) && $parametros['idProvSucZona'] == "") {
        if ($obj->newRegistro()) {
            echo "La zona del proveedor se registró correctamente";
        } else {
            echo "Error:La zona del proveedor no se registró";
        }
    } else {
        $obj->setId($parametros['idProvSucZona']);
        if ($obj->editRegistro()) {
            echo "La zona del proveedor se modificó correctamente";
        } else {
            echo "Error: La zona del proveedor  no se modificó";
        }
    }
}
