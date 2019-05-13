<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

ini_set("memory_limit", "512M");
set_time_limit(0);

include_once("../WEB-INF/Classes/PHP_XLSXWriter-master/xlsxwriter.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Almacen.class.php");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$catalogo = new Catalogo();
$almacen = new Almacen();
if (!$almacen->getRegistroById($_GET['almacen'])) {
    echo "Error: el almacén solicitado no existe";
    exit;
}

$filename = "ReporteResurtidoAlmacen.xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$writer = new XLSXWriter();
$writer->setAuthor('Techra');
$cabeceras = array('Almacen' => "string", 'TipoComponente' => "string", 'Modelo' => "string", 'NoParte' => "string", 'Descripcion' => "string", 'Proveedor' => "string",
    'Existencia' => "int", 'CantidadMinima' => "int", 'CantidadMaxima' => "int", 'Salidas' => "int", 'Entradas' => "int", 'Cantidad_Propuesta_Compra' => "string");

$hoja = "Reporte";
$writer->writeSheetHeader($hoja, $cabeceras);
$writer->writeSheetRow($hoja, array("REPORTE DE INVENTARIO DE COMPONENTES: " . $almacen->getNombre()));
$writer->writeSheetRow($hoja, array($catalogo->formatoFechaReportes(date('Y-m-d')) . " a las " . date('H:m:s')));


$FechaInicio = "";
$FechaFin = "";
if (isset($_GET['fecha_inicial']) && !empty($_GET['fecha_inicial'])) {
    $FechaInicio = $_GET['fecha_inicial'];
}

if (isset($_GET['fecha_final']) && !empty($_GET['fecha_final'])) {
    $FechaFin = $_GET['fecha_final'];
}

if (isset($_GET['slProveedor']) && !empty($_GET['slProveedor'])) {
    $Proveedor = $_GET['slProveedor'];
}

if (isset($_GET['tipo']) && !empty($_GET['tipo'])) {
    $Tipos = explode(",", $_GET['tipo']);
}

if (isset($_GET['modelo']) && !empty($_GET['modelo'])) {
    $NoParte = $_GET['modelo'];
}

if (isset($_GET['agrupar']) && !empty($_GET['agrupar'])) {
    $Agrupar = true;
}

$result = $almacen->reporteResurtidoAlmacen($FechaInicio, $FechaFin, $Proveedor, $Tipos, $NoParte, $Agrupar);
while ($rs = mysql_fetch_array($result)) {
    $array_valores = array();
    foreach ($cabeceras as $key => $value) {
        array_push($array_valores, $rs[$key]);
    }
    $writer->writeSheetRow($hoja, $array_valores);
}

$writer->writeToStdOut();
exit(0);
