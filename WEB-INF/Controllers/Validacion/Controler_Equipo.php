<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['form'])) {
    header("Location: ../../index.php");
}

include_once("../../Classes/Equipo.class.php");
$obj = new Equipo();

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}

$obj->setNoParte($parametros['no_serie2']);
$obj->setModelo($parametros['modelo2']);
$obj->setActivo(1);
$obj->setDescripcion('');
$obj->setPrecio(0);
$obj->setMeses(0);
$obj->setImpresiones(0);

$obj->setUsuarioCreacion($_SESSION['user']);
$obj->setUsuarioModificacion($_SESSION['user']);
$obj->setPantalla('PHP valida equipo');

if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
    if ($obj->newRegistro() && $obj->actualizarUbicacion($parametros['ubicacion2'])) {
        echo $obj->getNoParte();
    } else {
        echo "Error: El equipo no se pudo registrar, el No. de serie ya existe";
    }
} else {/* Modificar */
    if ($obj->editRegistro() && $obj->actualizarUbicacion($parametros['ubicacion2'])) {
        echo $obj->getNoParte();
    } else {
        echo "Error: El equipo no se pudo modificar, intenta más tarde por favor";
    }
}
?>