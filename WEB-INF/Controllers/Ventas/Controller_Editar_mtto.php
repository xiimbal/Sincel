<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../../Classes/mtto.class.php");
if (isset($_POST['form'])) {
    $mtto = new mtto();
    $parametros = "";
    parse_str($_POST['form'], $parametros);
    $mtto->setId_mtto($parametros['idmtto']);
    $mtto->setFecha($parametros['fecha']);
    if ($mtto->ActualizarFecha()) {
        echo "Actualización exitosa";
    } else {
        echo "Ocurrió un error";
    }
} else {
    echo "Ocurrió un error";
}
?>
