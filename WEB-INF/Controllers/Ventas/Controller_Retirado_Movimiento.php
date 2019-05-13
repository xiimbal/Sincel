<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../../Classes/ReporteHistorico.class.php");
if(isset($_POST['NumReporte']) && isset($_POST['val']) && isset($_POST['Facturar'])){
    $reporte = new ReporteHistorico();
    $reporte->setFechaCreacion($_SESSION['user']);
    $reporte->setFechaUltimaModificacion($_SESSION['user']);
    $reporte->setNumReporte($_POST['NumReporte']);    
    $reporte->setPantalla("Controller_Facturar_Movimiento PHP");
    if ($reporte->actualizarFacturar($_POST['val'])) {
        echo "Se actualizo correctamente el movimiento " . $_POST['NumReporte'];
    } else {
        echo "Error: No se actualizo el movimiento " . $_POST['NumReporte'];
    }
}else if (isset($_POST['NumReporte']) && isset($_POST['val']) && $_POST['val'] != "" && $_POST['NumReporte'] != "") {
    $reporte = new ReporteHistorico();
    $reporte->setFechaCreacion($_SESSION['user']);
    $reporte->setFechaUltimaModificacion($_SESSION['user']);
    $reporte->setNumReporte($_POST['NumReporte']);
    $reporte->setRetirado($_POST['val']);
    $reporte->setPantalla("Controller_Retirado_Movimiento PHP");
    if ($reporte->actualizarRetirado()) {
        echo "Se actualizo correctamente el movimiento " . $_POST['NumReporte'];
    } else {
        echo "Error: No se actualizo el movimiento " . $_POST['NumReporte'];
    }
}
?>
