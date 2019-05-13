<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Mensajeria.class.php");
if (isset($_POST['clave']) && $_POST['clave'] != "") {
    $cc = new Mensajeria();
    if ($cc->deleteRegistro($_POST['clave'])) {
        echo "Se ha eliminado la mensajería exitosamente";
    } else {
        echo "Error: la mensjaería tiene valores dependientes";
    }
} else {
    $parametros = "";
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $cc = new Mensajeria();
    $cc->setNombre($parametros['nombre']);
    if (isset($parametros['activo']) && $parametros['activo'] == "1") {
        $cc->setActivo($parametros['activo']);
    } else {
        $cc->setActivo(0);
    }
    $cc->setPantalla("PHP Controller_Mensajeria");
    $cc->setUsuarioCreacion($_SESSION['user']);
    $cc->setUsuarioUltimaModificacion($_SESSION['user']);
    if (isset($parametros['id']) && $parametros['id'] != "") {
        $cc->setIdMensajeria($parametros['id']);
        if ($cc->editRegistro()) {
            echo "Se ha editado la mensajería exitosamente";
        } else {
            echo "Error: La mensajería no se logro editar";
        }
    } else {
        if ($cc->newRegistro()) {
            echo "Se ha registrado la nueva mensajería";
        } else {
            echo "Error: La mensajería no se registro";
        }
    }
}
?>