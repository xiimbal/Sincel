<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
ini_set("memory_limit","256M");

$dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
 
$fecha = $dias[date('w')].", ".date('j')." de ".$meses[date('n')-1]. " del ".date('Y') ;

function cellColor($objPHPExcel, $cells, $color) {
    $objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()
            ->applyFromArray(array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => $color)
    ));
}

function getStyle($bold, $color, $size, $name, $cursive) {
    $styleArray = array(
        'font' => array(
            'bold' => $bold,
            'italic' => $cursive,
            'color' => array('rgb' => $color),
            'size' => $size,
            'name' => $name
        ),
        'alignment' => array(
            'wrap' => true,
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
    ));
    return $styleArray;
}

require_once('../WEB-INF/Classes/PHPExcel/IOFactory.php');
require_once('../WEB-INF/Classes/PHPExcel.php');
require_once('../WEB-INF/Classes/Catalogo.class.php');
include_once("../WEB-INF/Classes/ReporteFacturacion_net.class.php");
$catalogo = new Catalogo();
$objPHPExcel = new PHPExcel();

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
$query = $reporte->getTabla(false);

// Establecer propiedades
$objPHPExcel->getProperties()
        ->setCreator("")
        ->setLastModifiedBy("")
        ->setTitle("Documento Excel")
        ->setSubject("Documento Excel")
        ->setDescription("Reporte de facturación")
        ->setKeywords("Excel Office 2007 openxml php")
        ->setCategory("Reportes");
$fila_inicial = 3;
$fila_inicial_backup = $fila_inicial;
$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A' . (1), 'REPORTE DE FACTURACIÓN')->mergeCells('A1:F1')
        ->setCellValue('J' . (1), $fecha)->mergeCells('J1:L1')        
        ->setCellValue('A' . ($fila_inicial), 'Factura')
        ->setCellValue('B' . ($fila_inicial), 'Fecha')
        ->setCellValue('C' . ($fila_inicial), 'Número de cliente')
        ->setCellValue('D' . ($fila_inicial), 'Nombre cliente')
        ->setCellValue('E' . ($fila_inicial), 'Subtotal')
        ->setCellValue('F' . ($fila_inicial), 'IVA')
        ->setCellValue('G' . ($fila_inicial), 'Importe Total')
        ->setCellValue('H' . ($fila_inicial), 'Importe pagado')
        ->setCellValue('I' . ($fila_inicial), 'Importe por pagar')
        ->setCellValue('J' . ($fila_inicial), 'Fecha Pago')
        ->setCellValue('K' . ($fila_inicial), 'Estado')
        ->setCellValue('L' . ($fila_inicial), 'RFC')
        ->setCellValue('M' . ($fila_inicial), 'Razón Social del Emisor')
        ->setCellValue('N' . ($fila_inicial), 'Tipo de factura')
        ->setCellValue('O' . ($fila_inicial), 'Ejecutivo de cuenta')
        ->setCellValue('P' . ($fila_inicial), 'Periodo Facturacion')
        ->setCellValue('Q' . ($fila_inicial), 'Pagos con NDC')
        ->setCellValue('R' . ($fila_inicial), 'Categoría');
$fila_inicial++;
set_time_limit(0);
while ($rs = mysql_fetch_array($query)) {
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
            $tipoComprobante = "Nota de crédito";
            break;
        default:
            $tipoComprobante = "";
            break;
    }
    $objPHPExcel->setActiveSheetIndex(0)            
            ->setCellValue('A' . ($fila_inicial), $rs['Folio'])
            ->setCellValue('B' . ($fila_inicial), $rs['FechaFacturacion'])
            ->setCellValue('C' . ($fila_inicial), $rs['ClaveCliente'])
            ->setCellValue('D' . ($fila_inicial), $rs['NombreReceptor'])
            ->setCellValue('E' . ($fila_inicial), $subtotal)
            ->setCellValue('F' . ($fila_inicial), $importe)
            ->setCellValue('G' . ($fila_inicial), $total);
            if($rs['EstadoFactura']=="P"){
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H' . ($fila_inicial), $total);
            }else if($rs['EstadoFactura']=="C" || $rs['EstadoFactura']=="INC"){
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H' . ($fila_inicial), 0);
            }else{
                if(isset($rs['pagado'])){
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H' . ($fila_inicial), $rs['pagado']);
                }else{
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H' . ($fila_inicial), 0);
                }
            }
            if($rs['EstadoFactura']=="P"){
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I' . ($fila_inicial), 0);
            }else if($rs['EstadoFactura']=="C" || $rs['EstadoFactura']=="INC"){
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I' . ($fila_inicial), 0);
            }else{
                if(isset($rs['pagado'])){
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I' . ($fila_inicial), $total - ((float)$rs['pagado']) );
                }else{
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I' . ($fila_inicial), $total);
                }
            }
            if(isset($rs['FechaPago'])){
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
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('J' . ($fila_inicial), $fechaPago)
            ->setCellValue('K' . ($fila_inicial), $estadoFactura)
            ->setCellValue('L' . ($fila_inicial), $rs['RFCReceptor'])
            ->setCellValue('M' . ($fila_inicial), $rs['NombreEmisor'])
            ->setCellValue('N' . ($fila_inicial), $tipoComprobante)
            ->setCellValue('O' . ($fila_inicial), $rs['ejecutivo'])
            ->setCellValue('P' . ($fila_inicial), $periodo)
            ->setCellValue('Q' . ($fila_inicial), $pagos)
            ->setCellValue('R' . ($fila_inicial), $rs['TipoFactura']);
    $fila_inicial++;
}

cellColor($objPHPExcel, 'A1:F1', 'C0C0C0'); //TITULO REPORTE
cellColor($objPHPExcel, 'A'.$fila_inicial_backup.':R'.$fila_inicial_backup, 'C0C0C0'); //TITULO REPORTE

$styleArray = getStyle(true, "000000", 12, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($styleArray);/*TITULO*/
$styleArray = getStyle(true, "000000", 10, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('A'.$fila_inicial_backup.':R'.$fila_inicial_backup)->applyFromArray($styleArray);/*Cabeceras de la tabla*/
$styleArray = getStyle(true, "000000", 9, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('J1:R1')->applyFromArray($styleArray);/*Fecha y hora*/
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
// Renombrar Hoja
$objPHPExcel->getActiveSheet()->setTitle('Facturación');

// Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
$objPHPExcel->setActiveSheetIndex(0);

// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="ReporteFacturacion.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
?>