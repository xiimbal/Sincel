<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../../Classes/mtto.class.php");
if ($_POST['id']) {
    $mtto = new mtto();
    $mtto->setId_mtto($_POST['id']);
    if ($mtto->deletebyid()) {
        echo "Se ha borrado";
    } else {
        echo "ocurrió un error";
    }
} else {
    echo "ocurrió un error";
}
?>
