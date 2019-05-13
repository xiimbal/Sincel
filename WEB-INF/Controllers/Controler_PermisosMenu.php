<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/PermisosMenu.class.php");
$obj = new PermisosMemu();
if (isset($_GET['id']) && isset($_GET['id2'])) {
    $obj->setIdPuesto($_GET['id']);
    $obj->setIdSubmenu($_GET['id2']);
    if ($obj->deleteRegistro()) {
        echo "EL permiso se eliminó correctamente";
    } else {
        echo "EL permiso no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setIdPuesto($parametros['puesto']);
    $obj->setIdSubmenu($parametros['submenu']);
    if (isset($parametros['alta']) && $parametros['alta'] == "on")
        $obj->setAlta(1);
    else
        $obj->setAlta(0);
    if (isset($parametros['baja']) && $parametros['baja'] == "on")
        $obj->setBaja(1);
    else
        $obj->setBaja(0);
    if (isset($parametros['modificacion']) && $parametros['modificacion'] == "on")
        $obj->setModificacion(1);
    else
        $obj->setModificacion(0);
    if (isset($parametros['consulta']) && $parametros['consulta'] == "on")
        $obj->setConsulta(1);
    else
        $obj->setConsulta(0);

    if (isset($parametros['idP']) && $parametros['idP'] == "") {
        if ($obj->newRegistro())
            echo "El permiso se registró correctamente";
        else
            echo "Error: El permiso ya se encuentra registrado";
    }
    else {
        $obj->setId($parametros['idS']);
        if ($obj->editRegistro()) {
            echo "El permiso se modificó correctamente";
        } else {
            echo "Error: El permiso ya se encuentra registrado";
        }
    }
}
?>
