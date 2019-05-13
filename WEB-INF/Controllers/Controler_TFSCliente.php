<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../Classes/TFSCliente.class.php");
$obj = new TFSCliente();
if (isset($_GET['id']) && $_GET['id2']) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdUsuario($_GET['id']);
    $obj->setClaveCliente($_GET['id2']);

    if ($obj->deleteRegistro()) {
        echo "El cliente se eliminó del usuario correctamente";
    } else {
        echo "El cliente no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    if (isset($parametros['usuario']))
        $obj->setIdUsuario($parametros['usuario']);
    $obj->setTipo($parametros['tipo']);
    $obj->setClaveCliente($parametros['cliente']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Alta TFSCliente');

    if (isset($parametros['idUsuario']) && $parametros['idUsuario'] == "" && $parametros['idLocalidad'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if (isset($parametros['localidad'])) {
            $obj->setLocalidad($parametros['localidad']);
            if ($obj->newRegistroTecnicoCliente())
                echo "El cliente fue asignado correctamente";
            else
                echo "Error: El cliente ya se encuentra registrado";
        }
        else {
            if ($obj->newRegistro())
                echo "El cliente fue asignado correctamente";
            else
                echo "Error: El cliente ya se encuentra registrado ";
        }
//        if ($obj->newRegistro()) {
//            echo "El cliente fue asignado correctamente";
//        } else {
//            echo "Error: El cliente ya se encuentra registrado con el usuario";
//        }
    } else {/* Modificar */
        $obj->setId($parametros['idCliente']);
        $obj->setIdUsuario($parametros['idUsuario']);
        if (isset($parametros['localidad'])) {
            $obj->setLocalidad($parametros['localidad']);
//            $obj->setLocalidad($parametros['localidad']);
            $obj->setIdLocalidad($parametros['idLocalidad']);            
            if ($obj->editRegistroTecnicoCliente()) {
                echo "El cliente fue reasignado correctamente";
            } else {
                echo "Error: El cliente ya se encuentra registrado con el usuario ";
            }
        } else {
             if ($obj->editRegistro()) {
                echo "El cliente fue reasignado correctamente";
            } else {
                echo "Error: El cliente ya se encuentra registrado con el usuario";
            }
        }
    }
//        $obj->setId($parametros['idCliente']);        
//        if (isset($parametros['localidad'])) {
//            $obj->setIdUsuario($parametros['idUsuario']);
//            $obj->setLocalidad($parametros['localidad']);
//            $obj->setIdLocalidad($parametros['idLocalidad']);
//            if ($obj->editRegistroTecnicoCliente())
//                echo "El cliente fue reasignado correctamente";
//            else
//                echo "Error: El cliente ya se encuentra registrado con el usuario";
//        } else {
//            if ($obj->editRegistro()) {
//                echo "El cliente fue reasignado correctamente";
//            } else {
//                echo "Error: El cliente ya se encuentra registrado con el usuario";
//            }
//        }
//    }
}
?>

