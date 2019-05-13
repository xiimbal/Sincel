<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
if (isset($_GET['id'])) {
    include_once("../../Classes/Catalogo.class.php");
    $catalogo = new Catalogo();
    $catalogo->insertarRegistro("DELETE k_ventadirectadet.* FROM k_ventadirectadet INNER JOIN c_ventadirecta ON c_ventadirecta.IdVentaDirecta=k_ventadirectadet.IdVentaDirecta WHERE c_ventadirecta.IdVentaDirecta=".$_GET['id']);
    $catalogo->insertarRegistro("DELETE FROM c_ventadirecta WHERE c_ventadirecta.IdVentaDirecta=".$_GET['id']);
}else{
    echo "No se pudo borrar";
}
?>
