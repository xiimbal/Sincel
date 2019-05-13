<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/ProveedorServicio.class.php");
$obj = new ProveedorServicio();
if (isset($_GET['id'])) {
    $obj->setId($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "EL servicio de la sucursal se eliminó correctamente";
    } else {
        echo "EL servicio de la sucursal no se pudo eliminar, ya que contiene datos asociados.";
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
    $obj->setIdServicio($parametros['sl_servicio']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('servicio sucursal');
    if (isset($parametros['idProvSucServ']) && $parametros['idProvSucServ'] == "") {
        if ($obj->newRegistro()){
            echo "El servicio de la sucursal se registró correctamente";
        }else{
            echo "Error: El servicio de la sucursal ya se encuentra registrado";
        }
    }
    else {
        $obj->setId($parametros['idProvSucServ']);
        if ($obj->editRegistro()) {
            echo "El servicio de la sucursal se modificó correctamente";
        } else {
            echo "Error: El servicio de la sucursal ya se encuentra registrado";
        }
    }
}
?>
