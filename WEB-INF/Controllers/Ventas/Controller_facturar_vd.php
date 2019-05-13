<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
if (isset($_GET['id'])) {
    include_once("../../Classes/Catalogo.class.php");
    $catalogo = new Catalogo();
    $catalogo->insertarRegistro("UPDATE c_ventadirecta SET Estatus='2',UsuarioUltimaModificacion='" . $_SESSION['user'] . "',FechaUltimaModificacion=NOW() WHERE c_ventadirecta.IdVentaDirecta=".$_GET['id']);
}else{
    echo "No se pudo borrar";
}
?>
