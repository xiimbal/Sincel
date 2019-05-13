<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['form'])) {
    header("Location: ../../index.php");
}

include_once("../../Classes/Inventario.class.php");
$obj = new Inventario();

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}

$obj->setNoSerie($parametros['no_serie2']);
$obj->setNoParteEquipo($parametros['no_parte2']);
$obj->setUbicacion($parametros['ubicacion2']);
$obj->setActivo(1);

$obj->setUsuarioCreacion($_SESSION['user']);
$obj->setUsuarioUltimaModificacion($_SESSION['user']);
$obj->setPantalla('PHP valida inventario');

if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
    if ($obj->newRegistro()) {
        echo $obj->getNoSerie();
    } else {
        echo "Error: El equipo no se pudo registrar, intenta más tarde por favor";
    }
} else {/* Modificar */
    if ($obj->editRegistro()) {
        echo $obj->getNoSerie();
    } else {
        echo "Error: El equipo no se pudo modificar, intenta más tarde por favor";
    }
}
?>