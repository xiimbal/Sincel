<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/CompCompatiblesEq.class.php");
$obj = new CompCompatiblesEq();
if (isset($_GET['id']) && $_GET['id2']) {/* Para eliminar el registro con el id recibido por get */
    $obj->setNoParteComponente($_GET['id']);
    $obj->setNoParteEquipo($_GET['id2']);

    if ($obj->deleteRegistro()) {
        echo "El componente compatible se eliminó correctamente";
    } else {
        echo "El componente compatible no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setNoParteComponente($parametros['idComponente']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Alta Equipo');

    if (isset($parametros['idComponente']) && $parametros['idComponente'] != "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        $contador = 1;
        while (isset($parametros['equipo' . $contador])) {
            $obj->setNoParteEquipo($parametros['equipo' . $contador]);
            $obj->setSoportado($parametros['soportado' . $contador]);
            if ($obj->newRegistro()) {
                
            } else {
                //echo "Error: El componente compatible ya se encuentra registrado";
            }
            $contador++;
        }
        echo "El componente compatible se registró correctamente";
    } else {/* Modificar */
        $obj->setNoParteEquipo($parametros['idE']);
        $obj->setId($parametros['idC']);
        if ($obj->editRegistro()) {
            echo "El componente compatible se modificó correctamente";
        } else {
            echo "Error: El componente compatible ya se encuentra registrado";
        }
    }
}
?>

