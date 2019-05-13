<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

ini_set("memory_limit","512M");
set_time_limit (0);

include_once("../WEB-INF/Classes/PHP_XLSXWriter-master/xlsxwriter.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

$filename = "ReportePagos4-4.xlsx";
header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');

$catalogo = new Catalogo();
$permisos_grid = new PermisosSubMenu();
$writer = new XLSXWriter();//Nuevo libro
$writer->setAuthor('Techra');
$hoja = "4.4 Pago a proveedores";
$nombre_objeto = $permisos_grid->getNombreTicketSistema();
$where = "";

if(isset($_GET['FechaInicio']) && $_GET['FechaInicio']!=""){
    $FechaInicio = $_GET['FechaInicio'];
    $where .= "fp.Fecha >= '$FechaInicio 00:00:00'";
}

if(isset($_GET['FechFin']) && $_GET['FechFin']!=""){
    $FechaFin = $_GET['FechFin'];
    if(!empty($where)){
        $where .= " AND ";
    }
    $where .= "fp.Fecha <= '$FechaFin 23:59:59'";
}

$query = "SELECT DATE_FORMAT(fp.Fecha,'%Y-%m-%d') AS fechaS, p.ClaveProveedor, p.NombreComercial, t.IdTicket, nt.NoBoleto,
(SELECT SUM(importe) FROM k_financial WHERE IdFinancial = f.IdPrestamo) AS financial,
SUM(CASE WHEN ktoc.FacturoViaticos = 0 THEN oc.Total_Ticket ELSE 0 END) AS Servicio,
SUM(CASE WHEN ktoc.FacturoViaticos = 1 THEN oc.Total_Ticket ELSE 0 END) AS Viaticos
FROM c_factura_proveedor fp LEFT JOIN c_pagosparciales_proveedor pp ON pp.id_factura = fp.IdFacturaProveedor
INNER JOIN c_proveedor p ON p.ClaveProveedor = fp.IdEmisor INNER JOIN c_orden_compra oc ON oc.Id_orden_compra = fp.IdOrdenCompra
INNER JOIN k_tickets_oc ktoc ON ktoc.IdOrdenCompra = oc.Id_orden_compra INNER JOIN c_ticket t ON t.IdTicket = ktoc.IdTicket
LEFT JOIN c_notaticket nt ON nt.IdNotaTicket = t.IdTicket AND nt.IdEstatusAtencion = 277 INNER JOIN c_usuario u ON u.RFC = p.RFC
LEFT JOIN c_financial f ON f.IdOperador = u.IdUsuario $where ORDER BY fechaS";
$cabeceras = array("Fecha" => "string", "Proveedor" => "string",$nombre_objeto => "string", "Boleto" => "string", "Servicio" => "money", 
"Viáticos" => "money", "Total Ingresos" => "money", "Financial" => "money", "Total Egresos" => "money", "Total" => "money");
$writer->writeSheetHeader($hoja, $cabeceras);
$result = $catalogo->obtenerLista($query);
$fecha = array();
$proveedor = array();
$boletoTicket = array();
$servicios = array();
$viaticos = array();
$ingresos = array();
$financial = array();
$egresos = array();
while($rs = mysql_fetch_array($result)){
    array_push($fecha, $rs['fechaS']);
    if(!in_array($rs['NombreComercial'], $proveedor[$rs['fechaS']][$rs['ClaveProveedor']])){
        $proveedor[$rs['fechaS']][$rs['ClaveProveedor']] = $rs['NombreComercial'];
    }
    $boletoTicket[$rs['fechaS']][$rs['ClaveProveedor']][$rs['IdTicket']] = $rs['NoBoleto'];
    //servicios
    if(isset($servicios[$rs['fechaS']][$rs['ClaveProveedor']][$rs['IdTicket']])){//Ya estaba, entonces sólo hay que sumar
        if(!empty($rs['Servicio'])){
            $servicios[$rs['fechaS']][$rs['ClaveProveedor']][$rs['IdTicket']] += $rs['Servicio'];
        }
    }else{
        if(!empty($rs['Servicio'])){
            $servicios[$rs['fechaS']][$rs['ClaveProveedor']][$rs['IdTicket']] = $rs['Servicio'];
        }else{
            $servicios[$rs['fechaS']][$rs['ClaveProveedor']][$rs['IdTicket']] = 0;
        }
    }
    //viáticos
    if(isset($viaticos[$rs['fechaS']][$rs['ClaveProveedor']][$rs['IdTicket']])){//Ya estaba, entonces sólo hay que sumar
        if(!empty($rs['Viaticos'])){
            $viaticos[$rs['fechaS']][$rs['ClaveProveedor']][$rs['IdTicket']] += $rs['Viaticos'];
        }
    }else{
        if(!empty($rs['Viaticos'])){
            $viaticos[$rs['fechaS']][$rs['ClaveProveedor']][$rs['IdTicket']] = $rs['Viaticos'];
        }else{
            $viaticos[$rs['fechaS']][$rs['ClaveProveedor']][$rs['IdTicket']] = 0;
        }
    }    
    //financial
    if(isset($financial[$rs['fechaS']][$rs['ClaveProveedor']][$rs['IdTicket']])){//Ya estaba, entonces sólo hay que sumar
        if(!empty($rs['financial'])){
            $financial[$rs['fechaS']][$rs['ClaveProveedor']][$rs['IdTicket']] += $rs['financial'];
        }
    }else{
        if(!empty($rs['financial'])){
            $financial[$rs['fechaS']][$rs['ClaveProveedor']][$rs['IdTicket']] = $rs['financial'];
        }else{
            $financial[$rs['fechaS']][$rs['ClaveProveedor']][$rs['IdTicket']] = 0;
        }
    } 
}
foreach($fecha AS $indice => $date){
    foreach($proveedor[$date] AS $claveProveedor => $nombreProveedor){
        $valores = array();
        array_push($valores, $date);
        array_push($valores, $nombreProveedor);
        foreach($boletoTicket[$date][$claveProveedor] AS $ticket => $boleto){
            array_push($valores, $ticket);
            array_push($valores, $boleto);
            array_push($valores, $servicios[$date][$claveProveedor][$ticket]);
            array_push($valores, $viaticos[$date][$claveProveedor][$ticket]);
            $ingresos = $servicios[$date][$claveProveedor][$ticket] + $viaticos[$date][$claveProveedor][$ticket];
            array_push($valores, $ingresos);
            array_push($valores, $financial[$date][$claveProveedor][$ticket]);
            $egresos = $financial[$date][$claveProveedor][$ticket];
            array_push($valores, $egresos);
            $total = $ingresos - $egresos;
            array_push($valores, $total);
        }
        $writer->writeSheetRow($hoja, $valores);
        unset($valores);
    }
}
$writer->writeToStdOut();
exit(0);