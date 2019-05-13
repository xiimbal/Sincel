<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../../Classes/Folio.class.php");
$obj = new Folio();
if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setId_folio($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "El Folio se eliminó correctamente";
    } else {
        echo "El Folio no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }        
    $obj->setFolioInicial($parametros['folioInicial']);
    $obj->setFolioFinal($parametros['folioFinal']);
    $obj->setSerie($parametros['serie']);
    $obj->setNoAprobacion($parametros['noAprobacion']);
    $obj->setAnioAprobacion($parametros['anioAprobacion']);
    $obj->setUltimoFolio($parametros['ultimoFolio']);
    $obj->setRFCemisor($parametros['RFCemisor']);
    if (isset($parametros['activo']) && $parametros['activo'] == 1) {
        $obj->setActivo(1);
    } else {
        $obj->setActivo(0);
    }
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
    $obj->setPantalla("PHP Alta Folio");
    if (isset($parametros['id']) && $parametros['id'] != "") {
        $obj->setId_folio($parametros['id']);
        $obj->setPantalla("PHP Edita Folio");
        if ($obj->editRegistro()) {
            echo "Se actualizo correctamente el Folio";
        } else {
            echo "Error: No se pudo actualizar el Folio intente mas tarde o contacte al administrador";
        }
    } else {
        if ($obj->nuevoRegistro()) {
            echo "Se registro correctamente el Folio";
        } else {
            echo "Error: No se pudo registrar el Folio intente mas tarde o contacte al administrador";
        }
    }
}
?>
