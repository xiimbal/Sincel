<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/Componente.class.php");
$obj = new Componente();

if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    if (isset($_GET['idPadreInicial'])) {/* Para eliminar un registro de la tabla k_componentecomponenteinicial */
        $obj->deleteComponenteInicial($_GET['idPadreInicial'], $_GET['id']);
        echo "El componente se eliminó correctamente";
    } else if (isset($_GET['idPadreNecesario'])) {
        $obj->deleteComponenteNecesario($_GET['idPadreNecesario'], $_GET['id']);
        echo "El componente se eliminó correctamente";
    } else {
        $obj->setNumero($_GET['id']);
        if ($obj->deleteRegistro()) {
            echo "El componente se eliminó correctamente";
        } else {
            echo "El componente no se pudo eliminar, ya que contiene datos asociados.";
        }
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }

    $obj->setNumero($parametros['parte']);
    $obj->setTipo($parametros['tipo']);
    if (isset($parametros['activo']) && $parametros['activo'] == "on") {
        $obj->setActivo(1);
    } else {
        $obj->setActivo(0);
    }
    //$obj->setImagen($parametros['estado']);
    $obj->setModelo($parametros['nombre']);
    $parametros['descripcion'] = str_replace("'", "´", $parametros['descripcion']);
    $obj->setDescripcion($parametros['descripcion']);
    $obj->setPrecio($parametros['precio']);
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('PHP admin componentes');
    $obj->setParteAnterior($parametros['parte_anterior']);
    $obj->setRendimiento($parametros['rendimiento']);
    if ($parametros['tipo'] == "2"){
        $obj->setColor($parametros['color']);
    }else{
        $obj->setColor("NULL");
    }

    if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
        if ($obj->newRegistro()) {
            echo "El componente con número de parte " . $obj->getNumero() . " se registró correctamente //*// " . $obj->getNumero();
        } else {
            echo "Error: El componente con número de parte " . $obj->getNumero() . " ya se encuentra registrado, intenta con otro numero de parte por favor";
        }
    } else {/* Modificar */
        if ($obj->editRegistro()) {
            echo "El componente con número de parte " . $obj->getNumero() . " se modificó correctamente //*// " . $obj->getNumero();
        } else {
            echo "Error: El componente no se pudo modificar, intenta más tarde por favor";
        }
    }
}