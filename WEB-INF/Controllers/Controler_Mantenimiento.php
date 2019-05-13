<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/Mantenimiento.class.php");

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}

$obj = new Mantenimiento();

$obj->setNoSerie($parametros['no_serie']);
$obj->setFecha($parametros['fechaMtto']);
$obj->setEstatus("0");
$obj->setUsuarioCreacion($_SESSION['user']);
$obj->setUsuarioModificacion($_SESSION['user']);
$obj->setPantalla('PHP Mantenimiento preventivo');

if($obj->newRegistro()){
    echo "Mantenimeinto guardado exitosamente";
}else{
    echo "Error: no se pudo registrar el mantenimiento";
}
?>
