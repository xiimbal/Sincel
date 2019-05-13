<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/mtto.class.php");
if (isset($_POST['form'])) {
    $mtto = new mtto();
    $parametros = "";
    parse_str($_POST['form'], $parametros);
    $mtto->setCliente($parametros['cliente']);
    $mtto->setFecha($parametros['fecha']);
    $mtto->setLocalidad($parametros['localidad']);
    $mtto->setNoSerie($parametros['NoSerie']);
    $mtto->setUserCreacion($_SESSION['user']);
    if ($mtto->nuevomtto()) {
        echo "Se agregó exitosamente";
    } else {
        echo "Ocurrió un error";
    }
} else {
    echo "Ocurrió un error";
}
?>
