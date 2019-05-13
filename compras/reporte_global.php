<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
set_time_limit(0);
ini_set("memory_limit","512M");

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

$catalogo = new Catalogo();
$objPHPExcel = new PHPExcel();

// Establecer propiedades
$objPHPExcel->getProperties()
        ->setCreator("Techra")
        ->setLastModifiedBy("Techra")
        ->setTitle("Documento Excel")
        ->setSubject("Documento Excel")
        ->setDescription("Reporte de compras")
        ->setKeywords("Excel Office 2007 openxml php")
        ->setCategory("Reportes");
$fila_inicial = 1;
$fila_inicial_backup = $fila_inicial;

$where = " WHERE coc.Activo = 1 AND !ISNULL(koc.IdOrdenCompra) ";

if(isset($_POST['pedido']) && $_POST['pedido']!=""){
    $where .= " AND coc.NoPedido='".$_POST['pedido']."' ";
}

if(isset($_POST['orden']) && $_POST['orden']!=""){
    $where .= " AND coc.Id_orden_compra='".$_POST['orden']."' ";
}

if(isset($_POST['proveedor']) && !empty($_POST['proveedor'])){
    $where .= " AND coc.FacturaEmisor IN(";
    foreach ($_POST['proveedor'] as $value) {
        $where .= "'$value',";
    }
    $where = substr($where, 0, strlen($where)-1);
    $where .= ") ";
}

if(isset($_POST['fecha_inicio']) && $_POST['fecha_inicio']!=""){
    $where .= " AND coc.FechaOrdenCompra>='".$_POST['fecha_inicio']." 00:00:00' ";
}

if(isset($_POST['fecha_fin']) && $_POST['fecha_fin']!=""){
    $where .= " AND coc.FechaOrdenCompra<='".$_POST['fecha_fin']." 23:59:59' ";
}

if(isset($_POST['tipo']) && !empty($_POST['tipo'])){
    if(!in_array(0, $_POST['tipo'])){
        $where .= " AND ISNULL(e.NoParte) ";
        $where .= " AND c.IdTipoComponente IN(".  implode(",", $_POST['tipo']).") ";   
    }else if(in_array(0, $_POST['tipo']) && count($_POST['tipo']) == 1){
        $where .= " AND !ISNULL(e.NoParte) ";        
    }else{
        $where .= " AND (c.IdTipoComponente IN(".  implode(",", $_POST['tipo']).") OR !ISNULL(e.NoParte)) ";   
    }
}

if(isset($_POST['factura']) && $_POST['factura']!=""){
    $where .= " AND kdoc.FolioFactura = '".$_POST['factura']."' ";
}

if(isset($_POST['fecha_factura_inicio']) && $_POST['fecha_factura_inicio']!=""){
    $where .= " AND kdoc.Fecha >= '".$_POST['fecha_factura_inicio']."' ";
}

if(isset($_POST['fecha_factura_fin']) && $_POST['fecha_factura_fin']!=""){
    $where .= " AND kdoc.Fecha <= '".$_POST['fecha_factura_fin']."' ";
}

$consulta = "SELECT 
(CASE WHEN !ISNULL(e.NoParte) THEN CONCAT(e.NoParte,' / ',e.Modelo) WHEN !ISNULL(c.NoParte) THEN CONCAT(c.NoParte,' / ',c.Modelo) ELSE NULL END) AS NoParte,
(CASE WHEN !ISNULL(e.NoParte) THEN NULL WHEN !ISNULL(c.NoParte) THEN c.NoParteAnterior ELSE NULL END) AS NoParteAnterior,
(CASE WHEN !ISNULL(e.NoParte) THEN e.Descripcion WHEN !ISNULL(c.NoParte) THEN c.Descripcion ELSE NULL END) AS Descripcion,
kdoc.NoSerie, 
(CASE WHEN !ISNULL(e.NoParte) THEN 1 WHEN !ISNULL(c.NoParte) THEN koc.Cantidad ELSE NULL END) AS Cantidad,
(CASE WHEN !ISNULL(e.NoParte) THEN 1 WHEN !ISNULL(c.NoParte) AND !ISNULL(kdoc.CantidadEntrada) THEN SUM(kdoc.CantidadEntrada) ELSE 0 END) AS cantidadRecibida,
coc.Id_orden_compra AS OC, kdoc.FolioFactura, kdoc.Fecha AS FechaFactura,coc.FechaOrdenCompra, 
CONCAT(p.NombreComercial,'(',p.RFC,')') AS proveedor,
(CASE WHEN !ISNULL(e.NoParte) THEN 'Equipo' WHEN !ISNULL(c.NoParte) THEN tc.Nombre ELSE NULL END) AS Categoria,
(CASE WHEN !ISNULL(e.NoParte) THEN koc.PrecioUnitario WHEN !ISNULL(c.NoParte) THEN (koc.PrecioUnitario * koc.Cantidad) ELSE NULL END) AS PrecioUnitario, 
koc.Dolar, (CASE WHEN koc.Dolar = 1 THEN 'Dolar' ELSE 'Peso' END) isDolar, coc.TipoCambio
FROM `c_orden_compra` AS coc
LEFT JOIN k_orden_compra AS koc ON koc.IdOrdenCompra = coc.Id_orden_compra
LEFT JOIN k_detalle_entrada_orden_compra AS kdoc ON kdoc.idKOrdenTrabajo = koc.IdDetalleOC
LEFT JOIN c_equipo AS e ON e.NoParte = koc.NoParteEquipo
LEFT JOIN c_componente AS c ON c.NoParte = koc.NoParteComponente
LEFT JOIN c_tipocomponente AS tc ON tc.IdTipoComponente = c.IdTipoComponente
LEFT JOIN c_proveedor AS p ON p.ClaveProveedor = coc.FacturaEmisor
$where
GROUP BY kdoc.idKOrdenTrabajo, koc.IdOrdenCompra, coc.Id_orden_compra
ORDER BY coc.Id_orden_compra DESC, Categoria, kdoc.FolioFactura ,kdoc.NoSerie;";

$result = $catalogo->obtenerLista($consulta);

$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A' . ($fila_inicial), 'No Parte / Modelo')
        ->setCellValue('B' . ($fila_inicial), 'No Parte anterior')
        ->setCellValue('C' . ($fila_inicial), 'Descripción')
        ->setCellValue('D' . ($fila_inicial), 'No Serie')
        ->setCellValue('E' . ($fila_inicial), 'Cantidad solicitada')
        ->setCellValue('F' . ($fila_inicial), 'Cantidad recibida')
        ->setCellValue('G' . ($fila_inicial), 'O.C.')
        ->setCellValue('H' . ($fila_inicial), 'Factura')
        ->setCellValue('I' . ($fila_inicial), 'Costo')
        ->setCellValue('J' . ($fila_inicial), 'Moneda')
        ->setCellValue('K' . ($fila_inicial), 'Tipo Cambio')
        ->setCellValue('L' . ($fila_inicial), 'Fecha OC')
        ->setCellValue('M' . ($fila_inicial), 'Fecha factura')
        ->setCellValue('N' . ($fila_inicial), 'Proveedor')
        ->setCellValue('O' . ($fila_inicial), 'Categoría');

cellColor($objPHPExcel, 'A'.$fila_inicial.':O'.$fila_inicial, '5B9BD5'); //TITULO REPORTE
$styleArray = getStyle(false, "FFFFFF", 10, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('A'.$fila_inicial.':O'.$fila_inicial)->applyFromArray($styleArray);/*TITULO*/

while($rs = mysql_fetch_array($result)){        
    $fila_inicial++;
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A' . ($fila_inicial), $rs['NoParte'])
        ->setCellValue('B' . ($fila_inicial), $rs['NoParteAnterior'])
        ->setCellValue('C' . ($fila_inicial), $rs['Descripcion'])
        ->setCellValue('D' . ($fila_inicial), $rs['NoSerie'])
        ->setCellValue('E' . ($fila_inicial), $rs['Cantidad'])
        ->setCellValue('F' . ($fila_inicial), $rs['cantidadRecibida'])
        ->setCellValue('G' . ($fila_inicial), $rs['OC'])
        ->setCellValue('H' . ($fila_inicial), $rs['FolioFactura'])
        ->setCellValue('I' . ($fila_inicial), $rs['PrecioUnitario'])
        ->setCellValue('J' . ($fila_inicial), $rs['isDolar'])
        ->setCellValue('K' . ($fila_inicial), $rs['TipoCambio'])
        ->setCellValue('L' . ($fila_inicial), $rs['FechaOrdenCompra'])            
        ->setCellValue('M' . ($fila_inicial), $rs['FechaFactura'])
        ->setCellValue('N' . ($fila_inicial), $rs['proveedor'])
        ->setCellValue('O' . ($fila_inicial), $rs['Categoria']);        
}

$objPHPExcel->getActiveSheet()->getStyle('H2:H'.$fila_inicial)->getNumberFormat()->setFormatCode('0');
$objPHPExcel->getActiveSheet()->getStyle('I2:I'.$fila_inicial)->getNumberFormat()->setFormatCode('#,##0.00');

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

// Renombrar Hoja
$objPHPExcel->getActiveSheet()->setTitle('Compras');

// Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
$objPHPExcel->setActiveSheetIndex(0);

// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="ReporteCompras.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;