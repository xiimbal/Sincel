<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
ini_set("memory_limit","512M");
set_time_limit (0);

require_once('../WEB-INF/Classes/PHPExcel/IOFactory.php');
require_once('../WEB-INF/Classes/PHPExcel.php');
include_once('../WEB-INF/Classes/Catalogo.class.php');

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

$dias = array("Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado");
$meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
$fecha = $dias[date('w')] . ", " . date('j') . " de " . $meses[date('n') - 1] . " del " . date('Y');

$consultaTipoComponente = "";
$tipoComponFiltro = "";
if (isset($_POST['tipoComponenteFiltro']) && $_POST['tipoComponenteFiltro'] > 0) {
    $tipoComponFiltro = $_POST['tipoComponenteFiltro'];
    $consultaTipoComponente = "AND c.IdTipoComponente='" . $_POST['tipoComponenteFiltro'] . "'";
}

$filtro_responsable_almacen = "";
$array_almacenes = array();
if(isset($_POST['almacen']) && $_POST['almacen']!=""){
    $filtro_responsable_almacen = " AND al.id_almacen IN(".implode(",",$_POST['almacen']).") ";
    $array_almacenes = $_POST['almacen'];    
}

$modelo = "";
$filtro_modelo = "";
if(isset($_POST['NoParte']) && $_POST['NoParte']!=""){
    $modelo = $_POST['NoParte'];
    $filtro_modelo = " AND c.Modelo LIKE '%$modelo%' ";
}

if ($id_almacenes != "") {    
    $consulta = "SELECT * FROM k_almacencomponente ac,c_almacen al,c_componente c 
    WHERE ac.NoParte=c.NoParte AND ac.id_almacen=al.id_almacen
    AND al.id_almacen IN ($id_almacenes) $consultaTipoComponente $filtro_responsable_almacen $filtro_modelo
    ORDER BY ac.id_almacen,c.Modelo ASC;";
} else {
    $consulta = "SELECT * FROM k_almacencomponente ac,c_almacen al,c_componente c 
        WHERE ac.NoParte=c.NoParte AND ac.id_almacen=al.id_almacen $consultaTipoComponente $filtro_responsable_almacen $filtro_modelo 
        ORDER BY ac.id_almacen,c.Modelo ASC;";
}

$catalogo = new Catalogo();
$objPHPExcel = new PHPExcel();
// Establecer propiedades
$objPHPExcel->getProperties()
        ->setCreator("")
        ->setLastModifiedBy("")
        ->setTitle("Documento Excel")
        ->setSubject("Documento Excel")
        ->setDescription("Reporte de facturación")
        ->setKeywords("Excel Office 2007 openxml php")
        ->setCategory("Reportes");

$fila_inicial = 2;
$fila_inicial_backup = $fila_inicial;
$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A' . (1), 'REPORTE DE INVENTARIO DE COMPONENTES')->mergeCells('A1:G1')
        ->setCellValue('H' . (1), $fecha)->mergeCells('H1:J1')
        ->setCellValue('A' . ($fila_inicial), 'Almacén')
        ->setCellValue('B' . ($fila_inicial), 'Inventarios')
        ->setCellValue('C' . ($fila_inicial), 'No. Parte')
        ->setCellValue('D' . ($fila_inicial), 'Descripción')
        ->setCellValue('E' . ($fila_inicial), 'Existencia')
        ->setCellValue('F' . ($fila_inicial), 'Apartados')
        ->setCellValue('G' . ($fila_inicial), 'Mínimo')
        ->setCellValue('H' . ($fila_inicial), 'Máximo')
        ->setCellValue('I' . ($fila_inicial), 'Ubicación')
        ->setCellValue('J' . ($fila_inicial), 'Precio (Dlls)');
$fila_inicial++;
$bool = TRUE;

$query = $catalogo->obtenerLista($consulta);
while($rs = mysql_fetch_array($query))
{
    $columnas = array("nombre_almacen", "Modelo", "NoParte", "Descripcion", "cantidad_existencia", "cantidad_apartados", "CantidadMinima", "CantidadMaxima", "Ubicacion",
    "PrecioDolares", "NoParte", "id_almacen");
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . ($fila_inicial), $rs['nombre_almacen'])
            ->setCellValue('B' . ($fila_inicial), $rs['Modelo'])
            ->setCellValue('C' . ($fila_inicial), $rs['NoParte'])
            ->setCellValue('D' . ($fila_inicial), $rs['Descripcion'])
            ->setCellValue('E' . ($fila_inicial), $rs['cantidad_existencia'])
            ->setCellValue('F' . ($fila_inicial), $rs['cantidad_apartados'])
            ->setCellValue('G' . ($fila_inicial), $rs['CantidadMinima'])
            ->setCellValue('H' . ($fila_inicial), $rs['CantidadMaxima'])
            ->setCellValue('I' . ($fila_inicial), $rs['Ubicacion'])
            ->setCellValue('J' . ($fila_inicial), "$".$rs['PrecioDolares']);
    if ($bool) {
        cellColor($objPHPExcel, 'A' . $fila_inicial . ':Q' . $fila_inicial, 'ddebf7'); //TITULO REPORTE
        $bool = FALSE;
    } else {
        $bool = TRUE;
    }
    $fila_inicial++;
}
//

cellColor($objPHPExcel, 'A1:J1', '5b9bd5'); //TITULO REPORTE
cellColor($objPHPExcel, 'A' . $fila_inicial_backup . ':J' . $fila_inicial_backup, '5b9bd5'); //TITULO REPORTE
$styleArray = getStyle(true, "000000", 12, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->applyFromArray($styleArray); /* TITULO */
$styleArray = getStyle(true, "000000", 10, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('A' . $fila_inicial_backup . ':J' . $fila_inicial_backup)->applyFromArray($styleArray); /* Cabeceras de la tabla */
$styleArray = getStyle(true, "000000", 9, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('H1:J1')->applyFromArray($styleArray); /* Fecha y hora */

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(75);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);

// Renombrar Hoja
$objPHPExcel->getActiveSheet()->setTitle('Inventario de componentes');

// Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
$objPHPExcel->setActiveSheetIndex(0);

// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Inventario de componentes.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
