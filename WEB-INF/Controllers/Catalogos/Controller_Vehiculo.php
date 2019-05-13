<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Vehiculo.class.php");
if (isset($_POST['clave']) && $_POST['clave'] != "") {
    $cc = new Vehiculo();
    if ($cc->deleteRegistro($_POST['clave'])) {
        echo "Se ha eliminado el vehículo exitosamente";
    } else {
        echo "Error: El vehículo tiene valores dependientes";
    }
} else {
    $parametros = "";
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $cc = new Vehiculo();
    $cc->setPlacas($parametros['placas']);
    $cc->setModelo($parametros['modelo']);
    $cc->setCapacidad($parametros['capacidad']);
    if (isset($parametros['activo']) && $parametros['activo'] == "1") {
        $cc->setActivo($parametros['activo']);
    } else {
        $cc->setActivo(0);
    }
    $cc->setPantalla("PHP Controller_Vehículo");
    $cc->setUsuarioCreacion($_SESSION['user']);
    $cc->setUsuarioUltimaModificacion($_SESSION['user']);
    if (isset($parametros['id']) && $parametros['id'] != "") {
        $cc->setIdVehiculo($parametros['id']);
        if ($cc->editRegistro()) {
            echo "Se ha editado el vehículo exitosamente";
        } else {
            echo "Error: El vehículo no se logro editar";
        }
    } else {
        if ($cc->newRegistro()) {
            echo "Se ha registrado el nuevo vehículo";
        } else {
            echo "Error: El vehículo no se registro";
        }
    }
}
?>