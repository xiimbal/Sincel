<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/ComponentesMiniAlmacen.class.php");
$obj = new ComponentesMiniAlmacen();
if (isset($_GET['id']) && $_GET['id2']) {/* Para eliminar el registro con el id recibido por get */
    $obj->setMinialmacen($_GET['id']);
    $obj->setNoParte($_GET['id2']);

    if ($obj->deleteRegistro()) {
        echo "El componente se eliminó correctamente del mini almacén";
    } else {
        echo "El componente no se pudo eliminar del mini almacén, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $obj->setMinialmacen($parametros['idminiAlmacen']);
//    $obj->setNoParte($parametros['componente']);
//    $obj->setCantidadExistente($parametros['existente']);
//    $obj->setCantidadMinima($parametros['minima']);
//    $obj->setCantidadMaxima($parametros['maxima']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Alta componente en mini almacén');

    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        $contador = 1;
        while (isset($parametros['componente' . $contador])) {
            $obj->setNoParte($parametros['componente' . $contador]);
            $obj->setCantidadExistente($parametros['existente' . $contador]);
            $obj->setCantidadMinima($parametros['minima' . $contador]);
            $obj->setCantidadMaxima($parametros['maxima' . $contador]);
            if ($obj->newRegistro()) {
                 echo "El componente <b>".$obj->getNoParte()."</b> se registró correctamente <br/>";
            } else {
                echo "El componente <b>".$obj->getNoParte()."</b>  ya se encuentra registrado en el mini almacén <br/>";
            }
            $contador++;
        }
       // echo "El componente <b>".$obj->getNoParte()."</b> se registró correctamente";
    } else {/* Modificar */
       $obj->setNoParte($parametros['componente']);
       if($obj->editRegistro())
           echo "Error: El componente <b>".$obj->getNoParte ()."</b> del mini almanén se modificó correctamente";
       else
           echo "Error: El componente <b>".$obj->getNoParte ()."</b> del mini almanén no se modificó correctamente";
//        $obj->setNoParteEquipo($parametros['idE']);
//        $obj->setId($parametros['idC']);
//        if ($obj->editRegistro()) {
//            echo "El componente compatible se modificó correctamente";
//        } else {
//            echo "Error: El componente compatible ya se encuentra registrado";
//        }
    }
}
?>

