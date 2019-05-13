<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/CentroCostoReal.class.php");
if (isset($_POST['clave']) && $_POST['clave'] != "") {
    $cc = new CentroCostoReal();
    $cc->setId_cc($_POST['clave']);
    if ($cc->deleteRegistro()) {
        echo "Se ha eliminado el centro de costo exitosamente";
    } else {
        echo "Error: el centro de costo tiene valores dependientes";
    }
} else {
    $parametros = "";
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $cc = new CentroCostoReal();
    $cc->setNombre($parametros['nombre']);
    $cc->setPantalla("PHP Controller_CC");
    $cc->setUsuarioCreacion($_SESSION['user']);
    $cc->setUsuarioUltimaModificacion($_SESSION['user']);
    $cc->setClaveCliente($parametros['cliente']);
    if (isset($parametros['localidades'])) {
        $cc->setLocalidades($parametros['localidades']);
    } else {
        $cc->setLocalidades(null);
    }
    if (isset($parametros['Moroso']) && $parametros['Moroso'] == "1") {
        $cc->setMoroso("1");
    } else {
        $cc->setMoroso("0"); //Por default es 0
    }
    if (isset($parametros['clave']) && $parametros['clave'] != "") {
        $cc->setId_cc($parametros['clave']);
        if ($cc->updateRegistro()) {
            echo "Se ha editado el centro de costo exitosamente";
        } else {
            echo "Error: El centro de costo no se logro editar";
        }
    } else {
        if ($cc->nuevoRegistro()) {
            echo "Se ha registrado el nuevo centro de costo";
        } else {
            echo "Error: El centro de costo no se registro";
        }
    }
}
?>