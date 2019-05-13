<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
if (isset($_GET['folio']) && $_GET['folio'] != "") {
    include_once("../../Classes/ReporteFacturacion.class.php");
    header("Content-type: text/plain");
    header("Content-Disposition: attachment; filename=" . $_GET['folio'] . "Cancelacion.xml");
    $reporte = new ReporteFacturacion();
    $reporte->setFolio($_GET['folio']);
    $query = $reporte->obtenerXMLCancelada();
    if ($rs = mysql_fetch_array($query)) {
        echo $rs['CanceladaSAT'];
    } else {
        echo "No se encontró el XML";
    }
} else {
    echo "No se recibió el folio";
}
?>
