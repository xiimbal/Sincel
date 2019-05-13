<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Conductor.class.php");
if (isset($_POST['clave']) && $_POST['clave'] != "") {
    $cc = new Conductor();
    if ($cc->deleteRegistro($_POST['clave'])) {
        echo "Se ha eliminado el conductor exitosamente";
    } else {
        echo "Error: el conductor tiene valores dependientes";
    }
} else {
    $parametros = "";
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $cc = new Conductor();
    $cc->setNombre($parametros['nombre']);
    $cc->setApellidoPaterno($parametros['appat']);
    $cc->setApellidoMaterno($parametros['apmat']);
    $cc->setIdUsuario($parametros['usuario']);
    if (isset($parametros['activo']) && $parametros['activo'] == "1") {
        $cc->setActivo($parametros['activo']);
    } else {
        $cc->setActivo(0);
    }
    $cc->setPantalla("PHP Controller_Conductor");
    $cc->setUsuarioCreacion($_SESSION['user']);
    $cc->setUsuarioUltimaModificacion($_SESSION['user']);
    if (isset($parametros['id']) && $parametros['id'] != "") {
        $cc->setIdConductor($parametros['id']);
        if ($cc->editRegistro()) {
            echo "Se ha editado el conductor exitosamente";
        } else {
            echo "Error: El conductor no se logro editar";
        }
    } else {
        if ($cc->newRegistro()) {
            echo "Se ha registrado el nuevo conductor";
        } else {
            echo "Error: El conductor no se registro";
        }
    }
}
?>