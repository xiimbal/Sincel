<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../Classes/Campania.class.php");
include_once("../Classes/Catalogo.class.php");

$obj = new Campania;
$catalogo = new Catalogo;

if (isset($_GET['id'])) {/* Para eliminar el registro con el id recibido por get */
    $obj->setIdCampania($_GET['id']);
    if ($obj->deleteRegistro()) {
        echo "La campaña se eliminó correctamente";
    } else {
        echo "La campaña no se pudo eliminar, ya que contiene datos asociados.";
    }
} else {
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }

    $obj->setDescripcion($parametros['descripcion']);
    $obj->setArea($parametros['area']);
    if (isset($parametros['activo']) && $parametros['activo'] == "on") {
        $obj->setActivo(1);
    } else {
        $obj->setActivo(0);
    }
    $obj->setUsuarioCreacion($_SESSION['user']);
    $obj->setUsuarioModificacion($_SESSION['user']);
    $obj->setPantalla('Catalogo de Campaña');
    $obj->setLocalidad($parametros['localidad']);
    $obj->setCliente($parametros['cliente']);

    $query = $catalogo->obtenerLista("SELECT Nombre FROM c_centrocosto WHERE ClaveCentroCosto='" . $obj->getLocalidad() . "'");
    $rs = mysql_fetch_array($query);
    $localidad = $rs['Nombre'];

    if (isset($parametros['idCampania']) && $parametros['idCampania'] == "") {
        if ($obj->newRegistro()) {
            echo "La campaña del cliente en <b>" . $localidad . "</b> se registró correctamente";
        } else {
            echo "La campaña del cliente en <b>" . $localidad . "</b> NO se registró correctamente";
        }
    } else {
        $obj->setIdCampania($parametros['idCampania']);
        if ($obj->editRegistro()) {
            echo "La campaña en <b>" . $localidad . "</b> se modificó correctamente";
        } else {
            echo "La campaña en <b>" . $localidad . "</b> NO se registró correctamente";
        }
    }
}
?>