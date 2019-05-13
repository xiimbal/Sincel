<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
/*include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$permisos_grid = new PermisosSubMenu();
$nombre_modelo = $permisos_grid->getModeloSistema();
$NoParte = $permisos_grid->getNoParteSistema();

set_time_limit(0);
ini_set("memory_limit","512M");

$dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
 
$fecha = $dias[date('w')].", ".date('j')." de ".$meses[date('n')-1]. " del ".date('Y') ;*/

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
        ->setDescription("Reporte Equipos Demo")
        ->setKeywords("Excel Office 2007 openxml php")
        ->setCategory("Reportes");
$fila_inicial = 1;
$fila_inicial_backup = $fila_inicial;

/*$where = " WHERE coc.Activo = 1 AND !ISNULL(koc.IdOrdenCompra) ";

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
}*/

$consulta = "select `s`.`id_solicitud` AS `id_solicitud`,`s`.`fecha_solicitud` AS `fecha_solicitud`,`s`.`fecha_regreso` AS `fecha_regreso`,`b`.`NoParte` AS `NoParte`,`e`.`Modelo` AS `Modelo`,`b`.`NoSerie` AS `NoSerie`,(case when (`ks`.`ClaveCentroCosto` is not null) then (select `c_cliente`.`NombreRazonSocial` from (`c_centrocosto` join `c_cliente` on((`c_cliente`.`ClaveCliente` = `c_centrocosto`.`ClaveCliente`))) where (`c_centrocosto`.`ClaveCentroCosto` = `ks`.`ClaveCentroCosto`)) else `c`.`NombreRazonSocial` end) AS `NombreCliente`,(case when (`ks`.`ClaveCentroCosto` is not null) then (select `c_centrocosto`.`Nombre` from `c_centrocosto` where (`c_centrocosto`.`ClaveCentroCosto` = `ks`.`ClaveCentroCosto`)) else `cc`.`Nombre` end) AS `CentroCostoNombre`,`ksd`.`ClaveCentroCosto` AS `ClaveCentroCosto`,(case when (`ks`.`ClaveCentroCosto` is not null) then `ks`.`ClaveCentroCosto` else `cc`.`ClaveCentroCosto` end) AS `ClaveCentroCosto2`,(case when (`ks`.`ClaveCentroCosto` is not null) then (select `c_cliente`.`ClaveCliente` from (`c_centrocosto` join `c_cliente` on((`c_cliente`.`ClaveCliente` = `c_centrocosto`.`ClaveCliente`))) where (`c_centrocosto`.`ClaveCentroCosto` = `ks`.`ClaveCentroCosto`)) else `c`.`ClaveCliente` end) AS `ClaveCliente` from (((`c_centrocosto` `cc` left join (`k_anexoclientecc` `kacc` left join ((`c_inventarioequipo` `cinv` left join ((`c_solicitud` `s` left join `k_solicitud` `ksd` on((`ksd`.`id_solicitud` = `s`.`id_solicitud`))) left join `c_bitacora` `b` on(((`b`.`id_solicitud` = `s`.`id_solicitud`) and (`b`.`NoParte` = `ksd`.`Modelo`) and (`b`.`ClaveCentroCosto` = `ksd`.`ClaveCentroCosto`)))) on((`cinv`.`NoSerie` = `b`.`NoSerie`))) left join `k_serviciogimgfa` `ks` on((`ks`.`IdKserviciogimgfa` = `cinv`.`IdKserviciogimgfa`))) on((`kacc`.`IdAnexoClienteCC` = `cinv`.`IdAnexoClienteCC`))) on((`cc`.`ClaveCentroCosto` = `kacc`.`CveEspClienteCC`))) left join `c_cliente` `c` on((`c`.`ClaveCliente` = `cc`.`ClaveCliente`))) left join `c_equipo` `e` on((`e`.`NoParte` = `b`.`NoParte`))) where ((`cinv`.`Demo` = 1) and (`s`.`id_tiposolicitud` = 4) and (`ksd`.`tipo` = 0)) group by `b`.`NoSerie` having (`ksd`.`ClaveCentroCosto` = `ClaveCentroCosto2`) order by `s`.`id_solicitud";

$result = $catalogo->obtenerLista($consulta);

$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A' . ($fila_inicial), 'Id Solicitud')
        ->setCellValue('B' . ($fila_inicial), 'Fecha Solicitud')
        ->setCellValue('C' . ($fila_inicial), 'Fecha Regreso')
        ->setCellValue('D' . ($fila_inicial), 'No Parte')
        //->setCellValue('B' . ($fila_inicial), $NoParte.' anterior')
        ->setCellValue('E' . ($fila_inicial), 'Modelo')
        //->setCellValue('D' . ($fila_inicial), 'No Serie')
        ->setCellValue('F' . ($fila_inicial), 'No Serie')
        ->setCellValue('G' . ($fila_inicial), 'Nombre Cliente')
        ->setCellValue('H' . ($fila_inicial), 'Centro Costo Nombre')
        ->setCellValue('I' . ($fila_inicial), 'Clave Centro Costo')
        ->setCellValue('J' . ($fila_inicial), 'Clave Centro Costo2')
        //->setCellValue('K' . ($fila_inicial), 'Moneda')
        //->setCellValue('K' . ($fila_inicial), 'Tipo Cambio')
        ->setCellValue('K' . ($fila_inicial), 'Clave Cliente');
        //->setCellValue('M' . ($fila_inicial), 'Fecha factura');
        
        //->setCellValue('O' . ($fila_inicial), 'Categoría');
        

cellColor($objPHPExcel, 'A'.$fila_inicial.':L'.$fila_inicial, '5B9BD5'); //TITULO REPORTE
$styleArray = getStyle(false, "FFFFFF", 10, "Arial", false);
$objPHPExcel->getActiveSheet()->getStyle('A'.$fila_inicial.':O'.$fila_inicial)->applyFromArray($styleArray);/*TITULO*/

while($rs = mysql_fetch_array($result)){        
    $fila_inicial++;
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A' . ($fila_inicial), $rs['id_solicitud'])
        ->setCellValue('B' . ($fila_inicial), $rs['fecha_solicitud'])
        ->setCellValue('C' . ($fila_inicial), $rs['fecha_regreso']) 
        ->setCellValue('D' . ($fila_inicial), $rs['NoParte'])
        ->setCellValue('E' . ($fila_inicial), $rs['Modelo'])
        //->setCellValue('D' . ($fila_inicial), $rs['NoSerie'])
        ->setCellValue('F' . ($fila_inicial), $rs['NoSerie'])
        ->setCellValue('G' . ($fila_inicial), $rs['NombreCliente'])
        ->setCellValue('H' . ($fila_inicial), $rs['CentroCostoNombre'])
        ->setCellValue('I' . ($fila_inicial), $rs['ClaveCentroCosto'])
        ->setCellValue('J' . ($fila_inicial), $rs['ClaveCentroCosto2'])
        //->setCellValue('K' . ($fila_inicial), $rs['isDolar'])
        //->setCellValue('K' . ($fila_inicial), $rs['TipoCambio'])
        ->setCellValue('K' . ($fila_inicial), $rs['ClaveCliente']);
        //->setCellValue('M' . ($fila_inicial), $rs['FechaFactura']);
        //->setCellValue('N' . ($fila_inicial), $rs['proveedor'])
        
               
}

$objPHPExcel->getActiveSheet()->getStyle('H2:H'.$fila_inicial)->getNumberFormat()->setFormatCode('0');
//$objPHPExcel->getActiveSheet()->getStyle('I2:I'.$fila_inicial)->getNumberFormat()->setFormatCode('#,##0.00');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
//$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(50);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);

// Renombrar Hoja
$objPHPExcel->getActiveSheet()->setTitle('Compras');

// Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
$objPHPExcel->setActiveSheetIndex(0);

// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="ReporteDemo.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;