<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/Sucursal.class.php");
$obj = new Sucursal();
if (isset($_GET['id']) && $_GET['id'] != "") {
    $obj->setClaveSucursal($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "La sucursal se eliminó correctamente";
    } else {
        echo "La sucursal no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setClaveProveedor($parametros['proveedor']);
    $obj->setDescripcion($parametros['descripcion']);
    
    if (isset($parametros['activo']) && $parametros['activo'] == "on")
        $obj->setActivo(1);
    else
        $obj->setActivo(0);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Sucursal PHP');
    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro())
            echo "La sucursal <b>" . $obj->getClaveSucursal() . "</b> se registró correctamente";
        else
            echo "Error: La sucursal <b>" . $obj->getClaveSucursal() . "</b> ya se encuentra registrado";
    }else {/* Modificar */
        $obj->setId($parametros['id']);
        if ($obj->editRegistro())
            echo "La sucursal <b>" . $obj->getClaveSucursal() . "</b> se modificó correctamente";
        else
            echo "Error: La sucursal <b>" . $obj->getClaveSucursal() . "</b> ya se encuentra registrado";
    }
}
?>
