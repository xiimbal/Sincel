<?php

/*if(!isset($_GET['metodo']) || $_GET['metodo']!='tarea_programada'){
    echo "No puedes accesar directamente al reporte";
    return;
}*/
require_once('WEB-INF/Classes/Catalogo.class.php');
include_once("WEB-INF/Classes/ConexionFacturacion2.class.php");
include_once("WEB-INF/Classes/CatalogoFacturacion2.class.php");
include_once("WEB-INF/Classes/ReporteFacturacion.class.php");
include_once("WEB-INF/Classes/DatosFacturacionEmpresa.class.php");  
include_once("WEB-INF/Classes/ParametroGlobal.class.php");
require_once('WEB-INF/Classes/PHPExcel/IOFactory.php');
require_once('WEB-INF/Classes/PHPExcel.php');

$parametro_global = new ParametroGlobal();
$objPHPExcel = new PHPExcel();
// Establecer propiedades
$objPHPExcel->getProperties()
        ->setCreator("Techra")
        ->setLastModifiedBy("Techra")
        ->setTitle("Documento Excel")
        ->setSubject("Documento Excel")
        ->setDescription("Concentrado de facturación")
        ->setKeywords("Excel Office 2007 openxml php")
        ->setCategory("Facturación");

if(!isset($_GET['mes'])){
    $month = date('m');
}else{
    $month = $_GET['mes'];
}

if(!isset($_GET['anio'])){
    $year = date('Y');
}else{
    $year = $_GET['anio'];    
}
$ultimo_dia = cal_days_in_month(CAL_GREGORIAN, $month, $year);

$catalogo = new CatalogoFacturacion2();
$reporte = new ReporteFacturacion();
$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$columnas = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
$iva = 0.16;
$usuario = "CRON CONCENTRADO PHP";

//Obtenemos los RFC de los emisores
$consulta = "SELECT RFC FROM c_datosfacturacionempresa WHERE Activo = 1;";
$result = $catalogo->obtenerLista($consulta);
$rfc_emisor = "";
while ($rs = mysql_fetch_array($result)) {
    $rfc_emisor .= "'".$rs['RFC']."',";
}

if(strlen($rfc_emisor) > 0){
    $rfc_emisor = substr($rfc_emisor, 0, strlen($rfc_emisor)-1);
}

/**************     DATOS GENERALES     *********************/
//Obtenemos la suma de los pagos parciales de este año a facturas de este año y del año pasado
$consulta = "
SELECT (SUM(pp.ImportePagado)/".(1+$iva).") AS Cuenta, fe.RazonSocial, MONTH(pp.FechaPago) AS Mes, YEAR(f.FechaFacturacion) AS Anio 
FROM c_factura AS f 
LEFT JOIN c_cliente AS c ON c.ClaveCliente = (SELECT MAX(ClaveCliente) FROM c_cliente WHERE RFC = f.RFCReceptor)
LEFT JOIN c_datosfacturacionempresa AS fe ON fe.RFC = f.RFCEmisor
LEFT JOIN c_pagosparciales AS pp ON pp.IdFactura = f.IdFactura
LEFT JOIN c_factura AS ndc ON ndc.IdFacturaRelacion = f.IdFactura AND ndc.TipoComprobante = 'egreso'
WHERE (f.FechaFacturacion BETWEEN '".($year-1)."-01-01 00:00:00' AND '$year-12-31 23:59:59') 
    AND (pp.FechaPago BETWEEN '$year-$month-01 00:00:00' AND '$year-$month-$ultimo_dia 23:59:59')
AND f.Serie = '' AND f.TipoComprobante = 'ingreso' AND f.EstadoFactura = 1 
AND f.PendienteCancelar = 0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura <> 3) 
AND f.RFCReceptor NOT IN($rfc_emisor)
GROUP BY YEAR(f.FechaFacturacion),fe.IdDatosFacturacionEmpresa, YEAR(pp.FechaPago) ORDER BY YEAR(f.FechaFacturacion),fe.RazonSocial;";
$result = $catalogo->obtenerLista($consulta);
$total = 0;
//Encabezado
$fila = 1;
$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A' . ($fila), "REPORTE DE COBRANZA PAGADO EN ".strtoupper($meses[$month-1]))->mergeCells('A'.$fila.':C'.$fila)
        ->setCellValue('A' . (($fila+1)), "AÑO")
        ->setCellValue('B' . (($fila+1)), "EMPRESA")
        ->setCellValue('C' . (($fila+1)), "TOTALES");
$fila+=2;
while($rs = mysql_fetch_array($result)){    
    $cuenta = (float)$rs['Cuenta'];
    $objPHPExcel->setActiveSheetIndex(0)        
        ->setCellValue('A' . (($fila)), $rs['Anio'])
        ->setCellValue('B' . (($fila)), $rs['RazonSocial'])
        ->setCellValue('C' . (($fila++)), "$ ".number_format($cuenta,2));
    $total += (float)($cuenta);
}
$objPHPExcel->setActiveSheetIndex(0)        
        ->setCellValue('A' . (($fila)), "Total")->mergeCells('A'.$fila.':B'.$fila)        
        ->setCellValue('C' . (($fila++)), "$ ".number_format($total,2)); 

/**************     SALDOS POR COMPAÑIA     *********************/
$fila+=5;
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . (($fila)), "Saldos por compañía")->mergeCells('A'.$fila.':J'.$fila);
$fila++;
$estados_facturas = array('C','NDC','NP','P');
$nombres_estados = array('Canceladas','Notas de Crédito','Pendiente de pago','Recuperados');
//Obtenemos las cuentas por razon social, mes y estado de factura.
$catalogo->obtenerLista($consulta);
$consulta = "SELECT (SUM(f.Total)/".(1+$iva).") AS Cuenta, fe.RazonSocial, fe.IdDatosFacturacionEmpresa, 
MONTH(f.FechaFacturacion) AS Mes, YEAR(f.FechaFacturacion) AS Anio,
(SELECT CASE WHEN f.EstadoFactura = 0 THEN 'C' WHEN f.PendienteCancelar = 1 THEN 'Pendiente Cancelar' WHEN f.TipoComprobante <> 'ingreso' THEN 'NDC' ELSE (SELECT CASE WHEN f.EstatusFactura = 3 THEN 'INC' ELSE (SELECT CASE WHEN f.FacturaPagada = 0 THEN 'NP' ELSE 'P' END) END) END) AS EstadoFacturaPer
FROM c_factura AS f 
LEFT JOIN c_cliente AS c ON c.ClaveCliente = (SELECT MAX(ClaveCliente) FROM c_cliente WHERE RFC = f.RFCReceptor) 
LEFT JOIN c_datosfacturacionempresa AS fe ON fe.RFC = f.RFCEmisor 
WHERE (f.FechaFacturacion BETWEEN '$year-01-01 00:00:00' AND '$year-$month-$ultimo_dia 23:59:59')
AND f.Serie = '' 
AND f.RFCReceptor NOT IN($rfc_emisor)
GROUP BY fe.IdDatosFacturacionEmpresa, MONTH(f.FechaFacturacion), EstadoFacturaPer 
ORDER BY fe.RazonSocial, Mes ,EstadoFacturaPer;";
$result = $catalogo->obtenerLista($consulta);
$array = $reporte->convertirRSIntoArrayConsolidado($result);//Convertimos el resultset en un array
/*Obtenemos la suma de los pagos parciales en el año actual a las facturas del año actual*/
$consulta = "SELECT (SUM(pp.ImportePagado)/".(1+$iva).") AS Cuenta, fe.RazonSocial, fe.IdDatosFacturacionEmpresa, 
GROUP_CONCAT(CONVERT(f.IdFactura,CHAR(8)) ORDER BY f.IdFactura SEPARATOR ',') AS Facturas,
 MONTH(f.FechaFacturacion) AS Mes, YEAR(f.FechaFacturacion) AS Anio, 
(SELECT CASE WHEN f.EstadoFactura = 0 THEN 'C' WHEN f.PendienteCancelar = 1 THEN 'Pendiente Cancelar' WHEN f.TipoComprobante <> 'ingreso' THEN 'NDC' ELSE (SELECT CASE WHEN f.EstatusFactura = 3 THEN 'INC' ELSE (SELECT CASE WHEN f.FacturaPagada = 0 THEN 'NP' ELSE 'P' END) END) END) AS EstadoFacturaPer 
FROM c_factura AS f 
LEFT JOIN c_cliente AS c ON c.ClaveCliente = (SELECT MAX(ClaveCliente) FROM c_cliente WHERE RFC = f.RFCReceptor) 
LEFT JOIN c_datosfacturacionempresa AS fe ON fe.RFC = f.RFCEmisor 
LEFT JOIN c_pagosparciales AS pp ON pp.IdFactura = f.IdFactura
WHERE (f.FechaFacturacion BETWEEN '$year-01-01 00:00:00' AND '$year-$month-$ultimo_dia 23:59:59')
AND f.Serie = '' 
AND f.RFCReceptor NOT IN($rfc_emisor)
GROUP BY fe.IdDatosFacturacionEmpresa, MONTH(f.FechaFacturacion), EstadoFacturaPer 
HAVING EstadoFacturaPer = 'NP'";
$result = $catalogo->obtenerLista($consulta);
$array_pagos = $reporte->convertirRSIntoArrayConsolidado($result);

//Obtenemos todas les empresas que facturan
$consulta = "SELECT IdDatosFacturacionEmpresa, RazonSocial FROM `c_datosfacturacionempresa` WHERE Activo = 1 ORDER BY Orden;";
$result = $catalogo->obtenerLista($consulta);
$saldos_pendientes = array();
$pp_mensuales = array();
$pagos_parciales = 0;
while($rs = mysql_fetch_array($result)){//Recorremos todas las empresas        
    $objPHPExcel->setActiveSheetIndex(0)        
        ->setCellValue('A' . (($fila)), "Concentrado de Facturación $year ".$rs['RazonSocial'])->mergeCells('A'.$fila.':J'.$fila);
    $fila++;
    for($i=0;$i<(int)$month;$i++){//Imprimimos los meses
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i+1]."".(($fila)),$meses[$i]);
    }
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i+1]."".(($fila)),"Totales");
    $fila++;    
    foreach ($nombres_estados as $key => $value) {//Recorremos los estados de las facturas a imprimir                
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".(($fila)),$value);
        $total = 0;
        for($i=1;$i<=(int)$month;$i++){
            $cuenta = (float)$array[$rs['IdDatosFacturacionEmpresa']][$i][$estados_facturas[$key]];
            if($key == 2){                               
                $pagos_parciales = (float)$array_pagos[$rs['IdDatosFacturacionEmpresa']][$i][$estados_facturas[$key]];                
                $cuenta -= $pagos_parciales;
                $pp_mensuales[$i] = $pagos_parciales;
                if(isset($saldos_pendientes[$i])){
                    $saldos_pendientes[$i] += (float)($cuenta); 
                }else{
                    $saldos_pendientes[$i] = (float)($cuenta); 
                } 
            }else if($key == 3){                
                $cuenta += $pp_mensuales[$i];
                $pagos_parciales = 0;
            }else{
                $pagos_parciales = 0;
            }
            
            if(isset($array[$rs['IdDatosFacturacionEmpresa']][$i][$estados_facturas[$key]])){                
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i]."".(($fila)),number_format($cuenta,2));
                $total += (float)($cuenta);
            }else{                
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i]."".(($fila)),"");
            }
        }        
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i]."".(($fila)),number_format($total,2));
        $fila++;        
    }
    /*Imprimimos lo facturado, que es lo No pagado más lo pagado*/        
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".(($fila)),"Facturados");
    $total = 0;
    for($i=1;$i<=(int)$month;$i++){//Recorremos los meses
        if(isset($array[$rs['IdDatosFacturacionEmpresa']][$i]['P']) || isset($array[$rs['IdDatosFacturacionEmpresa']][$i]['NP'])){
            $suma = 0;
            if(isset($array[$rs['IdDatosFacturacionEmpresa']][$i]['P'])){
                $suma += (float)$array[$rs['IdDatosFacturacionEmpresa']][$i]['P'];
            }
            if(isset($array[$rs['IdDatosFacturacionEmpresa']][$i]['NP'])){
                $suma += (float)$array[$rs['IdDatosFacturacionEmpresa']][$i]['NP'];
            }            
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i]."".(($fila)),number_format($suma,2));
            $total += $suma;
        }else{            
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i]."".(($fila)),"");
        }
    }    
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i]."".(($fila)),number_format($total,2));    
    $fila +=3;
}

/*************      Pendientes de pago anio anterior        *************/
//Obtenemos la suma de las facturas que aún están como no pagadas
$consulta = "SELECT (SUM(f.Total)/".(1+$iva).") AS Cuenta, fe.RazonSocial, fe.IdDatosFacturacionEmpresa, YEAR(f.FechaFacturacion) AS Anio 
FROM c_factura AS f 
LEFT JOIN c_cliente AS c ON c.ClaveCliente = (SELECT MAX(ClaveCliente) FROM c_cliente WHERE RFC = f.RFCReceptor)
LEFT JOIN c_datosfacturacionempresa AS fe ON fe.RFC = f.RFCEmisor
WHERE (f.FechaFacturacion BETWEEN '".($year-1)."-01-01 00:00:00' AND '".($year-1)."-12-31 23:59:59')
AND f.Serie = '' AND f.TipoComprobante = 'ingreso' AND f.EstadoFactura = 1 
AND f.PendienteCancelar = 0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura <> 3) AND f.FacturaPagada = 0 
AND f.RFCReceptor NOT IN($rfc_emisor)
GROUP BY YEAR(f.FechaFacturacion),fe.IdDatosFacturacionEmpresa ORDER BY YEAR(f.FechaFacturacion),fe.RazonSocial;";
$result = $catalogo->obtenerLista($consulta);
$array = array();
while($rs = mysql_fetch_array($result)){
    $array[$rs['IdDatosFacturacionEmpresa']] = $rs['Cuenta'];
}
//Ahora obtenemos los pagos parciales que se han hecho este año a facturas del año pasado
$consulta = "SELECT (SUM(pp.ImportePagado)/".(1+$iva).") AS Cuenta, fe.RazonSocial, fe.IdDatosFacturacionEmpresa, MONTH(pp.FechaPago) AS Mes, YEAR(pp.FechaPago) AS Anio, 'P' AS EstadoFacturaPer 
FROM c_factura AS f 
LEFT JOIN c_cliente AS c ON c.ClaveCliente = (SELECT MAX(ClaveCliente) FROM c_cliente WHERE RFC = f.RFCReceptor)
LEFT JOIN c_datosfacturacionempresa AS fe ON fe.RFC = f.RFCEmisor
LEFT JOIN c_pagosparciales AS pp ON pp.IdFactura = f.IdFactura
LEFT JOIN c_factura AS ndc ON ndc.IdFacturaRelacion = f.IdFactura AND ndc.TipoComprobante = 'egreso'
WHERE (f.FechaFacturacion BETWEEN '".($year-1)."-01-01 00:00:00' AND '".($year-1)."-12-31 23:59:59') 
    AND (pp.FechaPago BETWEEN '$year-01-01 00:00:00' AND '$year-$month-$ultimo_dia 23:59:59')
AND f.Serie = '' AND f.TipoComprobante = 'ingreso' AND f.EstadoFactura = 1 
AND f.PendienteCancelar = 0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura <> 3) 
AND f.RFCReceptor NOT IN($rfc_emisor)
GROUP BY MONTH(pp.FechaPago),fe.IdDatosFacturacionEmpresa ORDER BY MONTH(pp.FechaPago),fe.RazonSocial;";
$result = $catalogo->obtenerLista($consulta);
while($rs = mysql_fetch_array($result)){//Sumamos los pagos parciales de este año mas lo que aun se debe del año pasado, para sacer el total que se debia a principio de año
    if(isset($array[$rs['IdDatosFacturacionEmpresa']])){
        $array[$rs['IdDatosFacturacionEmpresa']] += (float)$rs['Cuenta'];
    }else{
        $array[$rs['IdDatosFacturacionEmpresa']] = (float)$rs['Cuenta'];
    }
    
}
//Guardamos en un array los pagos por mes de cada empresa
if(mysql_data_seek($result, 0)){
    $pagos_mensuales = $reporte->convertirRSIntoArrayConsolidado($result);
}else{
    $result = $catalogo->obtenerLista($consulta);
    $pagos_mensuales = $reporte->convertirRSIntoArrayConsolidado($result);
}

/*echo "<br/><br/><h2>Pendiente de pago ".($year-1)."</h2>";
echo "<table style='border: 1px solid #000; text-align: right;'>";
echo "<tr><td></td>";
for($i=0;$i<$month;$i++){
    echo "<td>".$meses[$i]."</td>";
}
echo "</tr>";*/
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . (($fila)), "Saldos por compañía")->mergeCells('A'.$fila.':J'.$fila);
$fila++;
for($i=0;$i<(int)$month;$i++){//Imprimimos los meses
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i+1]."".(($fila)),$meses[$i]);
}
$fila++;
$total_meses = array();
foreach ($array as $key => $value) {
    $datosFacturacion = new DatosFacturacionEmpresa();
    if(isset($_SESSION['idEmpresa'])){
        $datosFacturacion->setEmpresa($_SESSION['idEmpresa']);
    }
    if($datosFacturacion->getRegistroById($key)){
        $empresa = $datosFacturacion->getRazonSocial();
    }else{
        $empresa = $key;
    }
    //echo "<tr><td>$empresa</td>";
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".(($fila)),$empresa);
    for($i=1;$i<=$month;$i++){
        $monto = (float)$array[$key] - (float)$pagos_mensuales[$key][$i]['P'];        
        if(isset($total_meses[$i])){
            $total_meses[$i] += $monto;
        }else{
            $total_meses[$i] = $monto;
        }
        //echo "<td style='border: 1px solid #000; text-align: right;'>". number_format($monto,2)."</td>";
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i]."".(($fila)),number_format($monto,2));
        $array[$key] = $monto;
    }
    $fila++;
    //echo "</tr>";    
}
//echo "<tr>";
//echo "<td>Pendiente de pago</td>";
$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".(($fila)),"Pendiente de pago");
for($i=1;$i<=$month;$i++){
    //echo "<td style='border: 1px solid #000; text-align: right;'>".number_format($total_meses[$i],2)."</td>";
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i]."".(($fila)),number_format($total_meses[$i],2));
}
//echo "</tr>";
//echo "</table>";

// Renombrar Hoja
$objPHPExcel->getActiveSheet()->setTitle('Concentrado');
// Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
$objPHPExcel->setActiveSheetIndex(0);
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
if($parametro_global->getRegistroById("11")){
    $path = $parametro_global->getValor();
}else{
    $path = "/html/www/";
}
$objWriter->save($path.'reportes/concentrado.xls');
?>
