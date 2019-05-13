<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/ProveedorSucursal.class.php");
$obj = new ProveedorSucursal();
if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setId($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "La sucursal del proveedor se eliminó correctamente";
    } else {
        echo "Error:La sucursal del proveedor no se eliminó";
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
    $obj->setNombre($parametros['nombre']);
    if (isset($parametros['activo']) && $parametros['activo'] == "on") {
        $obj->setActivo(1);
    } else {
        $obj->setActivo(0);
    }
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Proveedor sucursal php');
    if (isset($parametros['id_prov_suc']) && $parametros['id_prov_suc'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            echo "La sucursal del proveedor se registró correctamente";
        } else {
            echo "Error: La sucursal del proveedor ya se encuentra registrado";
        }
    } else {/* Modificar */
        $obj->setId($parametros['id_prov_suc']);
        if ($obj->editRegistro()) {
            echo "La sucursal del proveedor se modificó correctamente";
        } else {
            echo "Error:La sucursal del proveedor ya se encuentra registrado";
        }
    }
}
