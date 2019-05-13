<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
if (isset($_POST['id'])) {
    include_once("../../Classes/Catalogo.class.php");
    $catalogo = new Catalogo();
    $catalogo->insertarRegistro("DELETE FROM k_solicitud WHERE k_solicitud.id_solicitud=".$_POST['id']);
    $catalogo->insertarRegistro("DELETE FROM c_solicitud WHERE c_solicitud.id_solicitud=".$_POST['id']);
}else{
    echo "No se pudo borrar";
}
?>
