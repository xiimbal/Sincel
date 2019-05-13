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
include_once("../WEB-INF/Classes/EquipoCaracteristicasFormatoServicio.class.php");

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

$where = "";
$serie = "";
$modelo = "";
$solicitud = "";
$bitacora = "";
$mostrarContador = "";

if(isset($_POST['no_serie']) && $_POST['no_serie']!=""){
    $where = "WHERE b.NoSerie LIKE '%".$_POST['no_serie']."%'";
    $serie = $_POST['no_serie'];
}

if(isset($_POST['modelo']) && $_POST['modelo']!=""){
    if($where!=""){
        $where.=" AND e.Modelo LIKE '%".$_POST['modelo']."%'";
    }else{
        $where = "WHERE e.Modelo LIKE '%".$_POST['modelo']."%'";
    }
    $modelo = $_POST['modelo'];
}

if(isset($_POST['id_solicitud']) && $_POST['id_solicitud']!=""){
    if($where!=""){
        $where.=" AND id_solicitud = ".$_POST['id_solicitud'];
    }else{
        $where = "WHERE id_solicitud = ".$_POST['id_solicitud'];
    }
    $solicitud = $_POST['id_solicitud'];
}

if(isset($_POST['id_bitacora']) && $_POST['id_bitacora']!=""){    
    if($where!=""){
        $where.=" AND id_bitacora = ".$_POST['id_bitacora'];
    }else{
        $where = "WHERE id_bitacora = ".$_POST['id_bitacora'];
    }
    $bitacora = $_POST['id_bitacora'];
}

$claveCliente = "";
$having = "";
if(isset($_POST['cliente']) && $_POST['cliente']!=""){    
    if($having!=""){
        $having.=" AND ClaveCliente = '".$_POST['cliente']."'";
    }else{
        $having = "HAVING ClaveCliente = '".$_POST['cliente']."'";
    }    
    $claveCliente = $_POST['cliente'];
}

if(isset($_POST['mostrar_contadores']) && $_POST['mostrar_contadores']=="on"){
    $mostrarContador = "checked='checked'";
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
if($mostrarContador != ""){ //Si se va a mostrar los contadores
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A' . (1), 'BITÁCORAS')->mergeCells('A1:D1')
        ->setCellValue('E' . (1), $fecha)->mergeCells('E1:G1')
        ->setCellValue('A' . ($fila_inicial), 'Folio')
        ->setCellValue('B' . ($fila_inicial), 'Folio solicitud')
        ->setCellValue('C' . ($fila_inicial), 'Modelo')
        ->setCellValue('D' . ($fila_inicial), 'No. Serie')
        ->setCellValue('E' . ($fila_inicial), 'Ubicación')
        ->setCellValue('F' . ($fila_inicial), 'Contador BN')
        ->setCellValue('G' . ($fila_inicial), 'Contador Color')
        ->setCellValue('H' . ($fila_inicial), 'Calle')
        ->setCellValue('I' . ($fila_inicial), 'No. Interior')
        ->setCellValue('J' . ($fila_inicial), 'No. Exterior')
        ->setCellValue('K' . ($fila_inicial), 'Colonia')
        ->setCellValue('L' . ($fila_inicial), 'Delegación')
        ->setCellValue('M' . ($fila_inicial), 'Ciudad')            
        ->setCellValue('N' . ($fila_inicial), 'Estado')
        ->setCellValue('O' . ($fila_inicial), 'País')
        ->setCellValue('P' . ($fila_inicial), 'Código Postal');
}else{
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A' . (1), 'BITÁCORAS')->mergeCells('A1:C1')
        ->setCellValue('D' . (1), $fecha)->mergeCells('D1:E1')
        ->setCellValue('A' . ($fila_inicial), 'Folio')
        ->setCellValue('B' . ($fila_inicial), 'Folio solicitud')
        ->setCellValue('C' . ($fila_inicial), 'Modelo')
        ->setCellValue('D' . ($fila_inicial), 'No. Serie')
        ->setCellValue('E' . ($fila_inicial), 'Ubicación')
        ->setCellValue('F' . ($fila_inicial), 'Calle')
        ->setCellValue('G' . ($fila_inicial), 'No. Interior')
        ->setCellValue('H' . ($fila_inicial), 'No. Exterior')
        ->setCellValue('I' . ($fila_inicial), 'Colonia')
        ->setCellValue('J' . ($fila_inicial), 'Delegación')
        ->setCellValue('K' . ($fila_inicial), 'Ciudad')            
        ->setCellValue('L' . ($fila_inicial), 'Estado')
        ->setCellValue('M' . ($fila_inicial), 'País')
        ->setCellValue('N' . ($fila_inicial), 'Código Postal');
}
$fila_inicial++;
$bool = TRUE;

$consulta = "SELECT b.id_bitacora, b.id_solicitud, 
    (CASE WHEN !ISNULL(c2.ClaveCliente) THEN c2.NombreRazonSocial ELSE c.NombreRazonSocial END) AS NombreRazonSocial, 
    (CASE WHEN !ISNULL(c2.ClaveCliente) THEN c2.ClaveCliente ELSE c.ClaveCliente END) AS ClaveCliente, 
    (CASE WHEN !ISNULL(cc2.ClaveCentroCosto) THEN cc2.Nombre ELSE cc.Nombre END) AS localidad,
    (CASE WHEN !ISNULL(d2.IdDomicilio) THEN d2.Calle ELSE d.Calle END) AS Calle,
    (CASE WHEN !ISNULL(d2.IdDomicilio) THEN d2.NoInterior ELSE d.NoInterior END) AS NoInterior,
    (CASE WHEN !ISNULL(d2.IdDomicilio) THEN d2.NoExterior ELSE d.NoExterior END) AS NoExterior,
    (CASE WHEN !ISNULL(d2.IdDomicilio) THEN d2.Colonia ELSE d.Colonia END) AS Colonia,
    (CASE WHEN !ISNULL(d2.IdDomicilio) THEN d2.Delegacion ELSE d.Delegacion END) AS Delegacion,
    (CASE WHEN !ISNULL(d2.IdDomicilio) THEN d2.Ciudad ELSE d.Ciudad END) AS Ciudad,
    (CASE WHEN !ISNULL(d2.IdDomicilio) THEN d2.Estado ELSE d.Estado END) AS Estado,
    (CASE WHEN !ISNULL(d2.IdDomicilio) THEN d2.Pais ELSE d.Pais END) AS Pais,
    (CASE WHEN !ISNULL(d2.IdDomicilio) THEN d2.CodigoPostal ELSE d.CodigoPostal END) AS CodigoPostal,
    a.nombre_almacen,
    IF(ISNULL(csrg.IdSolicitudRetiroGeneral),'0',
    IF(csr.PendienteRetiro=0 AND csrg.Contestado=0,'1',/*Solicitud de retiro*/
    IF(csr.PendienteRetiro=1 AND csrg.Contestado=1 AND csrg.Aceptada=1,'2',
    IF(csrg.Aceptada=1 AND csrg.Contestado=1 AND csr.IdAlmacen!=9 AND cal.id_almacen=9 AND (ISNULL(rh.NumReporte) OR rh.Retirado = 0),'3',/*Solicitud retiro aceptada*/
    IF(rh.Retirado = 1 AND meq.pendiente = 1,'4',/*Retirado*/
    IF(meq.pendiente = 0,'0',/*Aceptado en almacen*/
    '0')))))) AS MoverRojo,
    b.NoSerie, CONCAT(e.Modelo,' / ',b.NoParte) AS NoParteCompuesta "; 
if($mostrarContador != ""){ //Si se va a mostrar los contadores
    $consulta .= ",b.NoParte,(SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.Fecha ELSE lt.Fecha END) AS Fecha,
        (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorBNPaginas ELSE lt.ContadorBN END) AS ContadorBN,
        (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorColorPaginas ELSE lt.ContadorCL END)AS ContadorCL,
        (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorBNML ELSE lt.ContadorBNA END) AS ContadorBNML,
        (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorColorML ELSE lt.ContadorCLA END) AS ContadorCLML,
        (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonNegro ELSE lt.NivelTonNegro END) AS NivelTonNegro,
        (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonCian ELSE lt.NivelTonCian END) AS NivelTonCian,
        (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonMagenta ELSE lt.NivelTonMagenta END) AS NivelTonMagenta,
        (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonAmarillo ELSE lt.NivelTonAmarillo END) AS NivelTonAmarillo ";
}
$consulta.= "FROM `c_bitacora` AS b
    LEFT JOIN c_equipo AS e ON b.NoParte = e.NoParte
    LEFT JOIN c_inventarioequipo AS cinv ON cinv.NoSerie = b.NoSerie
    LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora = b.id_bitacora)
    LEFT JOIN c_solictudretirogeneral AS csrg ON csrg.IdSolicitudRetiroGeneral=csr.IdSolicitudRetiroGeneral
    LEFT JOIN movimientos_equipo AS meq ON meq.id_movimientos = (SELECT MAX(id_movimientos) FROM movimientos_equipo WHERE NoSerie = b.NoSerie AND DATE(Fecha) = DATE(csrg.FechaReporte) AND clave_centro_costo_anterior = csr.ClaveLocalidad)
    LEFT JOIN reportes_movimientos AS rm ON rm.id_movimientos = meq.id_movimientos
    LEFT JOIN reportes_historicos AS rh ON rh.NumReporte = rm.id_reportes
    LEFT JOIN k_almacenequipo AS ke ON ke.NoSerie=b.NoSerie
    LEFT JOIN c_almacen AS cal ON cal.id_almacen=ke.id_almacen
    LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
    LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
    LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
    LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
    LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
    LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = cc2.ClaveCliente
    LEFT JOIN `c_domicilio` AS `d` ON `d`.`ClaveEspecialDomicilio` = `cc`.`ClaveCentroCosto`
    LEFT JOIN `c_domicilio` AS `d2` ON `d2`.`ClaveEspecialDomicilio` = `cc2`.`ClaveCentroCosto`
    LEFT JOIN k_almacenequipo AS kae ON kae.NoSerie = b.NoSerie
    LEFT JOIN c_almacen AS a ON a.id_almacen = kae.id_almacen "; 
if($mostrarContador != ""){ //Si se va a mostrar los contadores
    $consulta .= "LEFT JOIN c_lectura AS l ON l.NoSerie = b.NoSerie AND l.Fecha = (SELECT MAX(Fecha) FROM c_lectura WHERE NoSerie = b.NoSerie)
        LEFT JOIN c_lecturasticket AS lt ON lt.ClvEsp_Equipo = b.NoSerie AND lt.Fecha = (SELECT MAX(Fecha) FROM c_lecturasticket WHERE ClvEsp_Equipo = b.NoSerie)";
}
$consulta .= " $where GROUP BY id_bitacora $having;";

$query = $catalogo->obtenerLista($consulta);
while($rs = mysql_fetch_array($query))
{
    if(isset($rs['localidad']) && $rs['localidad']!=""){
        $ubicacion = $rs['NombreRazonSocial'] . " - ".$rs['localidad'];
    }else if(isset ($rs['nombre_almacen']) && $rs['nombre_almacen']!=""){
        $ubicacion = $rs['nombre_almacen'] . " (Almacén)";
    }

    if ($rs['MoverRojo'] == "1") {
        $ubicacion = "<br/>El equipo tiene una solicitud de retiro";
    } else if($rs['MoverRojo'] == "3"){
        $ubicacion = "<br/>Solicitud de retiro aceptada";
    } else if($rs['MoverRojo'] == "4"){
        $ubicacion = "<br/>Falta entrada a almacén";
    }
    
    $contadorBN = "";
    $contadorCL = "";
    if($mostrarContador != ""){
        $equipoCaracteristica = new EquipoCaracteristicasFormatoServicio();
        if($equipoCaracteristica->isFormatoAmplio($rs['NoParte'])){//Si es un equipo FA
            $contadorBN = number_format($rs['ContadorBNML'], 0);
            if($equipoCaracteristica->isColor($rs['NoParte'])){//Si es un equipo a color
                $contadorCL = number_format($rs['ContadorCLML'], 0);
            }else{
                $contadorCL = "";
            }
        }else{//Si no es FA
            $contadorBN = number_format($rs['ContadorBN'], 0);
            if($equipoCaracteristica->isColor($rs['NoParte'])){//Si es un equipo a color
                $contadorCL = number_format($rs['ContadorCL'], 0);
            }else{
                $contadorCL = "";
            }
        }
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . ($fila_inicial), $rs['id_bitacora'])
            ->setCellValue('B' . ($fila_inicial), $rs['id_solicitud'])
            ->setCellValue('C' . ($fila_inicial), $rs['NoParteCompuesta'])
            ->setCellValue('D' . ($fila_inicial), $rs['NoSerie'])
            ->setCellValue('E' . ($fila_inicial), $ubicacion)
            ->setCellValue('F' . ($fila_inicial), $contadorBN)
            ->setCellValue('G' . ($fila_inicial), $contadorCL)
            ->setCellValue('H' . ($fila_inicial), $rs['Calle'])
            ->setCellValue('I' . ($fila_inicial), $rs['NoInterior'])
            ->setCellValue('J' . ($fila_inicial), $rs['NoExterior'])
            ->setCellValue('K' . ($fila_inicial), $rs['Colonia'])
            ->setCellValue('L' . ($fila_inicial), $rs['Delegacion'])
            ->setCellValue('M' . ($fila_inicial), $rs['Ciudad'])            
            ->setCellValue('N' . ($fila_inicial), $rs['Estado'])
            ->setCellValue('O' . ($fila_inicial), $rs['País'])
            ->setCellValue('P' . ($fila_inicial), $rs['CodigoPostal']);
    }else{
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . ($fila_inicial), $rs['id_bitacora'])
            ->setCellValue('B' . ($fila_inicial), $rs['id_solicitud'])
            ->setCellValue('C' . ($fila_inicial), $rs['NoParteCompuesta'])
            ->setCellValue('D' . ($fila_inicial), $rs['NoSerie'])
            ->setCellValue('E' . ($fila_inicial), $ubicacion)
            ->setCellValue('F' . ($fila_inicial), $rs['Calle'])
            ->setCellValue('G' . ($fila_inicial), $rs['NoInterior'])
            ->setCellValue('H' . ($fila_inicial), $rs['NoExterior'])
            ->setCellValue('I' . ($fila_inicial), $rs['Colonia'])
            ->setCellValue('J' . ($fila_inicial), $rs['Delegacion'])
            ->setCellValue('K' . ($fila_inicial), $rs['Ciudad'])            
            ->setCellValue('L' . ($fila_inicial), $rs['Estado'])
            ->setCellValue('M' . ($fila_inicial), $rs['Pais'])
            ->setCellValue('N' . ($fila_inicial), $rs['CodigoPostal']);
    }
    
    if ($bool) {
        if($mostrarContador != ""){
            cellColor($objPHPExcel, 'A' . $fila_inicial . ':P' . $fila_inicial, 'ddebf7'); //TITULO REPORTE
        }else{
            cellColor($objPHPExcel, 'A' . $fila_inicial . ':N' . $fila_inicial, 'ddebf7'); //TITULO REPORTE
        }       
        $bool = FALSE;
    } else {
        $bool = TRUE;
    }
    $fila_inicial++;
}
//
if($mostrarContador != ""){
    cellColor($objPHPExcel, 'A1:G1', '5b9bd5'); //TITULO REPORTE
    cellColor($objPHPExcel, 'A' . $fila_inicial_backup . ':P' . $fila_inicial_backup, '5b9bd5'); //TITULO REPORTE
    $styleArray = getStyle(true, "000000", 12, "Arial", false);
    $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($styleArray); /* TITULO */
    $styleArray = getStyle(true, "000000", 10, "Arial", false);
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_inicial_backup . ':P' . $fila_inicial_backup)->applyFromArray($styleArray); /* Cabeceras de la tabla */
    $styleArray = getStyle(true, "000000", 9, "Arial", false);
    $objPHPExcel->getActiveSheet()->getStyle('E1:P1')->applyFromArray($styleArray); /* Fecha y hora */

}else{
    cellColor($objPHPExcel, 'A1:E1', '5b9bd5'); //TITULO REPORTE
    cellColor($objPHPExcel, 'A' . $fila_inicial_backup . ':N' . $fila_inicial_backup, '5b9bd5'); //TITULO REPORTE
    $styleArray = getStyle(true, "000000", 12, "Arial", false);
    $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->applyFromArray($styleArray); /* TITULO */
    $styleArray = getStyle(true, "000000", 10, "Arial", false);
    $objPHPExcel->getActiveSheet()->getStyle('A' . $fila_inicial_backup . ':C' . $fila_inicial_backup)->applyFromArray($styleArray); /* Cabeceras de la tabla */
    $styleArray = getStyle(true, "000000", 9, "Arial", false);
    $objPHPExcel->getActiveSheet()->getStyle('D1:N1')->applyFromArray($styleArray); /* Fecha y hora */
}

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(50);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(60);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
// Renombrar Hoja
$objPHPExcel->getActiveSheet()->setTitle('Reporte de bitácora');

// Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
$objPHPExcel->setActiveSheetIndex(0);

// Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Bitácora.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;




