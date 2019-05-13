<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
ini_set("memory_limit","256M");
set_time_limit (0);

include_once("../WEB-INF/Classes/PHP_XLSXWriter-master/xlsxwriter.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/ReporteFacturacion_net.class.php");

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "ReporteCargasMasivas.xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$writer = new XLSXWriter();
$writer->setAuthor('Techra');
$cabeceras = array('Factura' => 'string', 'Fecha' => "date", 'Numero_cliente' => "string", 'Nombre_cliente' => "string", 'Subtotal' => "money",
    'IVA' => "money", 'Total' => "money", 'Importe_pagado' => "money", 'Importe_por_pagar' => "money",
    'Fecha_pago' => "date", 'Estado' => "string", 'RFC' => "string", 'Razon_social_emisor' => "string", 'Tipo_factura' => "string", 
    'Ejecutivo_cuenta' => "string",'Ejecutivo_atencion' => "string",'Periodo' => "string", 'Pagos_con_NDC' => "string", 'Categoria' => "string");


$hoja = "Reporte";
$writer->writeSheetHeader($hoja, $cabeceras);

$catalogo = new Catalogo();
$reporte = new ReporteFacturacion();
if (isset($_GET['Ejecutivo']) && $_GET['Ejecutivo'] != "") {
    $reporte->setEjecutivo($_GET['Ejecutivo']);
}

if (isset($_GET['RFC']) && $_GET['RFC'] != "") {
    $reporte->setRFC($_GET['RFC']);
}
if (isset($_GET['fecha1']) && $_GET['fecha1'] != "") {
    $reporte->setFechaInicial($_GET['fecha1']);
}
if (isset($_GET['fecha2']) && $_GET['fecha2'] != "") {
    $reporte->setFechaFinal($_GET['fecha2']);
}
if (isset($_GET['rfccliente']) && $_GET['rfccliente'] != "") {
    $reporte->setRfccliente($_GET['rfccliente']);
}
if (isset($_GET['cliente']) && $_GET['cliente'] != "") {
    $reporte->setCliente($_GET['cliente']);
}
if (isset($_GET['status']) && $_GET['status'] != "") {
    $estatus = explode(",", $_GET['status']);
    $reporte->setStatus($estatus);
}
if (isset($_GET['docto']) && $_GET['docto'] != "") {    
    $reporte->setDocto($_GET['docto']);
}
if (isset($_GET['folio']) && $_GET['folio'] != "") {
    $reporte->setFolio($_GET['folio']);
}
if (isset($_GET['TF']) && $_GET['TF'] != "") {
    $tipos = explode(",", $_GET['TF']);
    $reporte->setTipoFactura($tipos);
}
if (isset($_GET['periodo']) && $_GET['periodo'] != "") {
    $reporte->setPeriodoFacturacion($_GET['periodo']);
}
$result = $reporte->getTabla(false);

while ($rs = mysql_fetch_array($result)) {
    $array_valores = array();
    
    $subtotal = (float) str_replace(',','',$rs['subtotal']);
    $importe = (float) str_replace(',','',$rs['importe']);;
    $total = (float) str_replace(',','',$rs['Total']);
    $estadoFactura = "";
    switch ($rs['EstadoFactura']){
        case 'C':
            $estadoFactura = "Cancelado";
            break;
        case 'INC':
            $estadoFactura = "Incobrable";
            break;
        case 'NP':
            $estadoFactura = "No pagado";
            break;
        case 'P':
            $estadoFactura = "Pagado";
            break;
        case 'NDC':            
            $estadoFactura = "Nota de crédito";
            break;
        default:
            $estadoFactura = $rs['EstadoFactura'];            
            break;
    }
    $tipoComprobante = "";
    switch ($rs['TipoComprobante']){
        case 'F':
            $tipoComprobante = "Factura";
            break;
        case 'NDC':
            $subtotal = $subtotal * (-1);
            $importe = $importe * (-1);
            $total = $total * (-1);
            $tipoComprobante = "Nota de crédito";
            break;
        default:
            $tipoComprobante = "";
            break;
    }
    
    array_push($array_valores, $rs['Folio']);
    array_push($array_valores, $rs['FechaFacturacion']);
    array_push($array_valores, $rs['ClaveCliente']);
    array_push($array_valores, $rs['NombreReceptor']);
    array_push($array_valores, $subtotal);
    array_push($array_valores, $importe);
    array_push($array_valores, $total);
    
    if($rs['EstadoFactura']=="P"){
        array_push($array_valores, $total);        
    }else if($rs['EstadoFactura']=="C" || $rs['EstadoFactura']=="INC"){
        array_push($array_valores, 0);        
    }else{
        if(isset($rs['pagado'])){
            array_push($array_valores, $rs['pagado']);            
        }else{
            array_push($array_valores, 0);        
        }
    }
    
    if($rs['EstadoFactura']=="P"){
        array_push($array_valores, 0);   
    }else if($rs['EstadoFactura']=="C" || $rs['EstadoFactura']=="INC"){
        array_push($array_valores, 0);   
    }else{
        if(isset($rs['pagado'])){
            array_push($array_valores, $total - ((float)$rs['pagado']));               
        }else{
            array_push($array_valores, $total);   
        }
    }
    
    if(isset($rs['FechaPago']) && $rs['FechaPago']!="0000-00-00 00:00:00"){
        $fechaPago = substr($rs['FechaPago'],0,10);
    }else{
        $fechaPago = "";
    }
    if(isset($rs['PeriodoFacturacion'])){
        $periodo = substr($catalogo->formatoFechaReportes($rs['PeriodoFacturacion']), 6);
    }else{
        $periodo = "";
    }
    $pagos = $rs['PagadoNDC'];
    array_push($array_valores, $fechaPago);
    array_push($array_valores, $estadoFactura);
    array_push($array_valores, $rs['RFCReceptor']);
    array_push($array_valores, $rs['NombreEmisor']);
    array_push($array_valores, $tipoComprobante);
    array_push($array_valores, $rs['ejecutivo']);
    array_push($array_valores, $rs['Ejecutivo_atencion']);
    array_push($array_valores, $periodo);
    array_push($array_valores, $pagos);
    array_push($array_valores, $rs['TipoFactura']);
    
    $writer->writeSheetRow($hoja, $array_valores);
}

$writer->writeToStdOut();
exit(0);


