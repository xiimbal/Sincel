<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/ProveedorProducto.class.php");
$obj = new ProveedorProducto();
if (isset($_GET['id'])) {
    $obj->setId($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "EL producto del proveedor se eliminó correctamente";
    } else {
        echo "EL producto del proveedor  no se eliminó, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    if (isset($parametros['proveedor'])) {
        $obj->setIdProveedor($parametros['proveedor']);
    } else {
        $obj->setIdProveedor($parametros['id_prov']);
    }
    $obj->setIdSucursal($parametros['sucursal']);
    $obj->setIdProducto($parametros['producto']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('k_proveedor_suc_producto');

    if (isset($parametros['txt_prov_suc_prod']) && $parametros['txt_prov_suc_prod'] == "") {
        if ($obj->newRegistro()) {
            echo "El producto del proveedor se registró correctamente";
        } else {
            echo "Error: El producto del proveedor no se registró";
        }
    } else {
        $obj->setId($parametros['txt_prov_suc_prod']);
        if ($obj->editRegistro()) {
            echo "El producto del proveedor se modificó correctamente";
        } else {
            echo "Error: El producto del proveedor no se modificó";
        }
    }
}
?>
