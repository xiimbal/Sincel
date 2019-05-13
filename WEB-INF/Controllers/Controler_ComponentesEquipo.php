<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/ComponentesEquipo.class.php");
$obj = new ComponentesEquipo();
if (isset($_GET['id']) && $_GET['id2']) {/* Para eliminar el registro con el id recibido por get */
    $obj->setNoPartesEquipo($_GET['id']);
    $obj->setNoPartesComponentes($_GET['id2']);

    if ($obj->deleteRegistro()) {
        echo "El componente del equipo se eliminó correctamente";
    } else {
        echo "El componente del equipo no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setNoPartesEquipo($parametros['idE']);
    $obj->setNoPartesComponentes($parametros['componentes']);
    $obj->setInstalado($parametros['instalado']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Alta Equipo');

    if (isset($parametros['idC']) && $parametros['idC'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            echo "El componente del equipo se registró correctamente";
        } else {
            echo "Error: El componente del equipo ya se encuentra registrado";
        }
    } else {/* Modificar */
        $obj->setNoPartesEquipo($parametros['idE']);
        $obj->setId($parametros['idC']);
        if ($obj->editRegistro()) {
            echo "El componente del equipo se modificó correctamente";
        } else {
            echo "Error: El componente del equipo ya se encuentra registrado";
        }
    }
}
?>

