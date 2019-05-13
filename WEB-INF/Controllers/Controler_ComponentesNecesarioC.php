<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/ComponentesNecesariosC.class.php");
$obj = new ComponentesNecesariosC();
if (isset($_GET['id']) && $_GET['id2']) {/* Para eliminar el registro con el id recibido por get */
    $obj->setNoPartePadre($_GET['id']);
    $obj->setNoParteHijo($_GET['id2']);

    if ($obj->deleteRegistro()) {
        echo "El componente se eliminó correctamente";
    } else {
        echo "El componente no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setNoPartePadre($parametros['idE']);
    $obj->setNoParteHijo($parametros['componente']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Alta Componente');
    if ($obj->newRegistro()) {
        echo "El componente se registró correctamente";
    } else {
        echo "Error: El componente ya se encuentra registrado";
    }
}
?>
