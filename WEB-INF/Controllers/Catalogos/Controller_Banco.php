<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Banco.class.php");
if (isset($_POST['clave']) && $_POST['clave'] != "") {
    $ba = new Banco();
    if ($ba->deleteRegistro($_POST['clave'])) {
        echo "Se ha eliminado el banco exitosamente";
    } else {
        echo "Error: el banco tiene valores dependientes";
    }
} else {
    $parametros = "";
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
    }
    $ba = new Banco();
    $ba->setNombre($parametros['nombre']);
    $ba->setDescripcion($parametros['descripcion']);
    if (isset($parametros['activo']) && $parametros['activo'] == "1") {
        $ba->setActivo($parametros['activo']);
    } else {
        $ba->setActivo(0);
    }
    $ba->setPantalla("PHP Controller_Banco");
    $ba->setUsuarioCreacion($_SESSION['user']);
    $ba->setUsuarioUltimaModificacion($_SESSION['user']);
    if (isset($parametros['id']) && $parametros['id'] != "") {
        $ba->setIdBanco($parametros['id']);
        if ($ba->editRegistro()) {
            echo "Se ha editado el conductor exitosamente";
        } else {
            echo "Error: El conductor no se logro editar";
        }
    } else {
        if ($ba->newRegistro()) {
            echo "Se ha registrado el nuevo conductor";
        } else {
            echo "Error: El conductor no se registro";
        }
    }
}
?>

