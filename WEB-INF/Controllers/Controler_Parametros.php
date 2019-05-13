<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/Parametros.class.php");
$obj = new Parametros();
if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdParametro($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "El parámetro se eliminó correctamente";
    } else {
        echo "El parámetro no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setDescripcion($parametros['descripcion']);
    $obj->setValor($parametros['valor']);
    if (isset($parametros['activo']) && $parametros['activo'] == "on") {
        $obj->setActivo(1);
    } else {
        $obj->setActivo(0);
    }
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('c_estado');
    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            echo "El parámetro se registró correctamente";
        } else {
            echo "Error: El parámetro ya se encuentra registrado";
        }
    } else {/* Modificar */
        $obj->setIdParametro($parametros['id']);
        if ($obj->editRegistro()) {
            echo "El parámetro se modificó correctamente";
        } else {
            echo "Error: parámetro ya se encuentra registrado";
        }
    }
}
?>
