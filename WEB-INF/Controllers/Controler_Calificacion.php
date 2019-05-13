<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/Cliente.class.php");
$obj = new Cliente();

if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setId_calificacion($_GET['id']);
    if ($obj->deleteCalificacion()) {
        echo "La calificación se eliminó correctamente";
    } else {
        echo "La calificación no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    
    $obj->setClaveCliente($parametros['cliente']);
    $obj->setCalificacion($parametros['calificacion']);
    $obj->setTitulo($parametros['titulo']);
    $obj->setMensaje($parametros['mensaje']);
    $obj->setIdUsuario($parametros['usuario']);        
    $obj->setFoto("");                    
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioUltimaModificacion($_SESSION['user']);
    $obj->setPantalla('Controler_Calificacion');
    
    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->agregarCalificacion($obj->getIdUsuario())) {
            echo $obj->getId_calificacion();
        } else {
            echo "Error: La calificación no se pudo registrar";
        }
    } else {/* Modificar */
        $obj->setId_calificacion($parametros['id']);
        if ($obj->editCalificacion()) {
            echo $obj->getId_calificacion();
        } else {
            echo "Error: la calificación no se pudo editar";
        }
    }
}
?>