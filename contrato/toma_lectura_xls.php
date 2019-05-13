<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
ini_set("memory_limit","200M");

$dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");

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

function setAllBorders(){
    $styleArray = array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    );
    return $styleArray;
}

require_once('../WEB-INF/Classes/PHPExcel/IOFactory.php');
require_once('../WEB-INF/Classes/PHPExcel.php');
include_once("../WEB-INF/Classes/Lectura.class.php");
include_once("../WEB-INF/Classes/CentroCosto.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/DatosFacturacionEmpresa.class.php");

$lectura = new Lectura();
$cc_objeto = new CentroCosto();
$cliente_objeto = new Cliente();
$catalogo = new Catalogo();
$facturacionEmpresa = new DatosFacturacionEmpresa();

if(isset($_POST['cc']) && $_POST['cc']!=""){
    $cc = $_POST['cc'];
    $cliente = "";
}else if(isset($_POST['cliente']) && $_POST['cliente']!=""){
    $cc = "";
    $cliente = $_POST['cliente'];
}

$year = $_POST['year'];
$month = $_POST['month'];
$mes_lectura = $meses[$month-1]."-".$year;

if($cc!=""){
    $cc_objeto->getRegistroById($cc);
    $cliente_objeto->getRegistroById($cc_objeto->getClaveCliente());
}else{
    $cliente_objeto->getRegistroById($cliente);
}
$facturacionEmpresa->getRegistroById($cliente_objeto->getIdDatosFacturacionEmpresa());

if(isset($_POST['fecha_lectura']) && $_POST['fecha_lectura']!=""){
    $fecha = $_POST['fecha_lectura'];
}else{
    $fecha = "$year-$month-01";
}

/*Obtenemos el mes anterior*/
$result = $catalogo->obtenerLista("SELECT DATE_SUB('$year-$month-01',INTERVAL 1 MONTH) AS anterior;");
while($rs = mysql_fetch_array($result)){
    $mes_anterior = $rs['anterior'];
}
$aux = explode("-", $mes_anterior);
$mes_anterior = $meses[intval($aux[1])-1]." ".$aux[0];
/*Obtenemos el mes actual*/
$mes_actual = $meses[intval($month)-1]." ".date("Y");

$result = $lectura->getLecturasByCC($cliente,$cc,null, $year, $month, $fecha);

$objPHPExcel = new PHPExcel();

// Establecer propiedades
$objPHPExcel->getProperties()
        ->setCreator("")
        ->setLastModifiedBy("")
        ->setTitle("Documento Excel")
        ->setSubject("Documento Excel")
        ->setDescription("Reporte de lecturas")
        ->setKeywords("Excel Office 2007 openxml php")
        ->setCategory("Lecturas");

$fila_inicial = $fila_inicial_aux = 7;

$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('G' . (1), 'REPORTE DE LECTURAS '.$mes_lectura)->mergeCells('G1:N1')
        ->setCellValue('G' . (2), 'Cliente: '.$cliente_objeto->getNombreRazonSocial())->mergeCells('G2:N2');
        if(!isset($cc) || $cc==""){
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('G' . (3), "Todas las localidades")->mergeCells('G3:N3');
        }else{
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('G' . (3), 'Localidad: '.$cc_objeto->getNombre())->mergeCells('G3:N3');
        }
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A' . ($fila_inicial), 'No Serie')->mergeCells('A' . ($fila_inicial).':A'. ($fila_inicial+2))
        ->setCellValue('B' . ($fila_inicial), 'Modelo')->mergeCells('B' . ($fila_inicial).':B'. ($fila_inicial+2))
        ->setCellValue('C' . ($fila_inicial), 'Contador')->mergeCells('C' . ($fila_inicial).':J'. ($fila_inicial))
        ->setCellValue('I' . ($fila_inicial), 'Nivel toner')->mergeCells('K' . ($fila_inicial).':R'. ($fila_inicial))
        ->setCellValue('C' . ($fila_inicial+1), 'B/N')->mergeCells('C' . ($fila_inicial+1).':F'. ($fila_inicial+1))
        ->setCellValue('C' . ($fila_inicial+2), $mes_anterior)
        ->setCellValue('D' . ($fila_inicial+2), "Usuario")
        ->setCellValue('E' . ($fila_inicial+2), "Fecha")
        ->setCellValue('F' . ($fila_inicial+2), $mes_actual)        
        
        ->setCellValue('G' . ($fila_inicial+1), 'Color')->mergeCells('G' . ($fila_inicial+1).':J'. ($fila_inicial+1))
        ->setCellValue('G' . ($fila_inicial+2), $mes_anterior)
        ->setCellValue('H' . ($fila_inicial+2), "Usuario")
        ->setCellValue('I' . ($fila_inicial+2), "Fecha")
        ->setCellValue('J' . ($fila_inicial+2), $mes_actual)
        
        ->setCellValue('K' . ($fila_inicial+1), 'Negro')->mergeCells('K' . ($fila_inicial+1).':L'. ($fila_inicial+1))
        ->setCellValue('K' . ($fila_inicial+2), $mes_anterior)
        ->setCellValue('L' . ($fila_inicial+2), $mes_actual)
        ->setCellValue('M' . ($fila_inicial+1), 'Cian')->mergeCells('M' . ($fila_inicial+1).':N'. ($fila_inicial+1))
        ->setCellValue('M' . ($fila_inicial+2), $mes_anterior)
        ->setCellValue('N' . ($fila_inicial+2), $mes_actual)
        ->setCellValue('O' . ($fila_inicial+1), 'Magenta')->mergeCells('O' . ($fila_inicial+1).':P'. ($fila_inicial+1))
        ->setCellValue('O' . ($fila_inicial+2), $mes_anterior)
        ->setCellValue('P' . ($fila_inicial+2), $mes_actual)
        ->setCellValue('Q' . ($fila_inicial+1), 'Amarillo')->mergeCells('Q' . ($fila_inicial+1).':R'. ($fila_inicial+1))
        ->setCellValue('Q' . ($fila_inicial+2), $mes_anterior)
        ->setCellValue('R' . ($fila_inicial+2), $mes_actual);
if(!isset($cc) || $cc==""){
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('S' . (($fila_inicial)), "Localidad")->mergeCells('S' . (($fila_inicial)).':T'. ($fila_inicial+2));
}

$contador = 0;
$fila_inicial = $fila_inicial_aux + 3;
while($rs = mysql_fetch_array($result)){
    /*Vemos las caracteristicas del equipo actual*/
    $color = false;
    $fa = false;
    /*Es FA?*/
    $aux = explode(",", $rs['caracteristicas']);
    if(in_array("2", $aux)){
        $fa = true;
    }
    /*Es color?*/
    $aux = explode(",", $rs['servicio']);
    if(in_array("1", $aux)){
        $color = true;                    
    }
   
    if(!$fa){
        $contadorbn = $rs['ContadorBNPaginas'];
        $contadorcolor = $rs['ContadorColorPaginas'];
        $contadorbnA = $rs['ContadorBNPaginasA'];
        $contadorcolorA = $rs['ContadorColorPaginasA'];                        
    }else{
        $contadorbn = $rs['ContadorBNML'];
        $contadorcolor = $rs['ContadorColorML'];
        $contadorbnA = $rs['ContadorBNMLA'];
        $contadorcolorA = $rs['ContadorColorMLA'];
    }
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A' . ($fila_inicial+$contador), $rs['NoSerie'])
        ->setCellValue('B' . ($fila_inicial+$contador), $rs['Modelo'])
        ->setCellValue('C' . ($fila_inicial+$contador), $contadorbnA)
        ->setCellValue('D' . ($fila_inicial+$contador), $rs['UsuarioUltimaModificacion'])
        ->setCellValue('E' . ($fila_inicial+$contador), $rs['FechaCreacion'])            
        ->setCellValue('F' . ($fila_inicial+$contador), $contadorbn)
        ->setCellValue('G' . ($fila_inicial+$contador), $contadorcolorA);
    if($contadorcolorA!=""){
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H' . ($fila_inicial+$contador), $rs['UsuarioUltimaModificacion']);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I' . ($fila_inicial+$contador), $rs['FechaCreacion']);
    }
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('J' . ($fila_inicial+$contador), $contadorcolor)
        ->setCellValue('K' . ($fila_inicial+$contador), $rs['NivelTonNegroA'])
        ->setCellValue('L' . ($fila_inicial+$contador), $rs['NivelTonNegro'])
        ->setCellValue('M' . ($fila_inicial+$contador), $rs['NivelTonCianA'])
        ->setCellValue('N' . ($fila_inicial+$contador), $rs['NivelTonCian'])
        ->setCellValue('O' . ($fila_inicial+$contador), $rs['NivelTonMagentaA'])
        ->setCellValue('P' . ($fila_inicial+$contador), $rs['NivelTonMagenta'])
        ->setCellValue('Q' . ($fila_inicial+$contador), $rs['NivelTonAmarilloA'])
        ->setCellValue('R' . ($fila_inicial+$contador), $rs['NivelTonAmarillo']);
    if(!isset($cc) || $cc==""){
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('S' . ($fila_inicial+$contador), $rs['NombreCentroCosto'])
                ->mergeCells('S' . (($fila_inicial+$contador)).':T'. ($fila_inicial+$contador));
    }
    $contador++;
}

/* Poner imagen */
$objDrawing = new PHPExcel_Worksheet_Drawing();
$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
$objDrawing->setName("name");
$objDrawing->setDescription("Description");
$objDrawing->setPath('../'.$facturacionEmpresa->getImagenPHP());
$objDrawing->setCoordinates('A1');
$objDrawing->setOffsetX(1);
$objDrawing->setOffsetY(3);

/*Formato de reporte*/
cellColor($objPHPExcel, 'G1:N1', 'C0C0C0'); //TITULO REPORTE
$styleArray = getStyle(true, "000000", 12, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('G1:N1')->applyFromArray($styleArray);/*TITULO*/

cellColor($objPHPExcel, 'A'.$fila_inicial_aux.':S'.($fila_inicial_aux+2), 'C0C0C0'); //TITULO REPORTE
$styleArray = getStyle(true, "000000", 10, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('A'.$fila_inicial_aux.':T'.($fila_inicial_aux+2))->applyFromArray($styleArray);/*Cabeceras de la tabla*/
$styleArray = setAllBorders();
$objPHPExcel->getActiveSheet()->getStyle('A'.$fila_inicial_aux.':T'.($fila_inicial+$contador-1))->applyFromArray($styleArray);/*Cabeceras de la tabla*/

/*Resize*/
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
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
if(!isset($cc) || $cc==""){
    $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
}
// Renombrar Hoja
$objPHPExcel->getActiveSheet()->setTitle('Lecturas');

// Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
$objPHPExcel->setActiveSheetIndex(0);

// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Lecturas.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
?>