<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/ServicioGimGfa.class.php");
$obj = new ServicioGim();
if (isset($_GET['id']) && $_GET['id2']) {/* Para eliminar el registro con el id recibido por get */
    $obj->setNoPartesEquipo($_GET['id']);
    $obj->setIdTipoComponente($_GET['id2']);

    if ($obj->deleteRegistro()) {
        echo "El servicio se eliminó correctamente";
    } else {
        echo "El servicio no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setIdServicio($parametros['idServicio']);
    $obj->setClaveEsp($parametros['claveEspecial']);
    $obj->setClaveCentroCosto($parametros['clave']);
    $obj->setIdAnexoCliente($parametros['cliente']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Alta ticket');
    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            echo "El servicio se registró correctamente";
        } else {
            echo "Error: El servicio ya se encuentra registrado";
        }
    } else {/* Modificar */        
        $obj->setIdC($parametros['id']);
        if ($obj->editRegistro()) {
            echo "El servicio se modificó correctamente";
        } else {
            echo "Error: El servicio ya se encuentra registrado";
        }
    }
}
?>
