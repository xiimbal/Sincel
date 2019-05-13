<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../../Classes/Catalogo.class.php");
$catalogo = new Catalogo();
if ($_POST['tipo'] != 6) {
    $query = $catalogo->obtenerLista("DELETE FROM k_solicitud WHERE id_solicitud=" . $_POST['solicitud'] . " AND id_partida='" . $_POST['partida'] . "'");
} else {
    $query = $catalogo->obtenerLista("SELECT IdDetalleVD FROM k_solicitud WHERE id_solicitud=" . $_POST['solicitud'] . " AND id_partida='" . $_POST['partida'] . "';");
    
    while ($rs = mysql_fetch_array($query)) {
        $catalogo->obtenerLista("DELETE FROM k_solicitud WHERE id_solicitud=" . $_POST['solicitud'] . " AND id_partida='" . $_POST['partida'] . "';");
        $catalogo->obtenerLista("DELETE FROM k_ventadirectadet WHERE IdVentaDirectaDet= " . $rs['IdDetalleVD']. ";");        
    }
}
?>