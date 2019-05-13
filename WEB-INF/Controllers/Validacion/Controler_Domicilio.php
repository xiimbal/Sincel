<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['form'])) {
    header("Location: ../../index.php");
}

include_once("../../Classes/Localidad.class.php");
$obj = new Localidad();

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}

$obj->setClaveEspecialDomicilio($parametros['clave_2']);
$obj->setCalle($parametros['calle2']);
$obj->setNoExterior($parametros['exterior2']);
$obj->setNoInterior($parametros['interior2']);
$obj->setColonia($parametros['colonia2']);
$obj->setCiudad($parametros['ciudad2']);
$obj->setEstado($parametros['estado2']);
$obj->setDelegacion($parametros['delegacion2']);
$obj->setPais("México");
$obj->setCodigoPostal($parametros['cp2']);

$obj->setActivo(1);
$obj->setUsuarioCreacion($_SESSION['user']);
$obj->setUsuarioUltimaModificacion($_SESSION['user']);
$obj->setPantalla('PHP valida domicilio');

if (isset($parametros['id']) && $parametros['id'] == "") {/* Si el id esta vacio, hay que insertar un NUEVO registro */
    // $obj->setClaveEspecialDomicilio($parametros['clave_cliente2_domicilio']);
    if ($obj->newRegistro(5)) {
        $obj->getIdDomicilio();
        /*if($obj->newRegistro(3)){
            echo $obj->getIdDomicilio();
        }*/
    } else {
        echo "Error: El domicilio no se pudo registrar, intenta más tarde por favor";
    }
} else {/* Modificar */
    $obj->setIdDomicilio($parametros['id']);
    if ($obj->editRegistro()) {
        echo $obj->getIdDomicilio();
    } else {
        echo "Error: El domicilio no se pudo modificar, intenta más tarde por favor";
    }
}
?>