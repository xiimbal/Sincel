<?php
ini_set("memory_limit","1024M");
set_time_limit (0);

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
        )/*,
        'alignment' => array(
            'wrap' => true,
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
        )*/
    );    
    return $styleArray;
}

function getStyleCenter() {
    $styleArray = array(        
        'alignment' => array(
            'wrap' => true,
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
        )
    );    
    return $styleArray;
}

function getBorder(){
    $styleArray = array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    );
    return $styleArray;
}

require_once('WEB-INF/Classes/Catalogo.class.php');
include_once("WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("WEB-INF/Classes/ReporteFacturacion.class.php");
include_once("WEB-INF/Classes/DatosFacturacionEmpresa.class.php");  
include_once("WEB-INF/Classes/Mail.class.php");  
require_once('WEB-INF/Classes/PHPExcel/IOFactory.php');
require_once('WEB-INF/Classes/PHPExcel.php');
include_once("WEB-INF/Classes/ParametroGlobal.class.php");
include_once("WEB-INF/Classes/ConexionMultiBD.class.php");

$con = new ConexionMultiBD();         
$result_bases = mysql_query("SELECT id_empresa, nombre_empresa FROM `c_empresa` WHERE Activo = 1 /*AND id_empresa = 8*/;");
$con->Desconectar();
while($rs_multi = mysql_fetch_array($result_bases)){
    echo "<br/><br/>Procesando empresa ".$rs_multi['nombre_empresa'];
    $empresa = $rs_multi['id_empresa'];    
    
    $parametroGlobal = new ParametroGlobal();
    $parametroGlobal->setEmpresa($empresa);
    $catalogo = new CatalogoFacturacion();    
    $catalogo->setEmpresa($empresa);
    $reporte = new ReporteFacturacion();
    $mail = new Mail();
    $mail->setEmpresa($empresa);
    $objPHPExcel = new PHPExcel();    
    
    // Establecer propiedades
    $objPHPExcel->getProperties()
            ->setCreator("")
            ->setLastModifiedBy("")
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
//    if(date('d') != $ultimo_dia){
//        echo "<br/>Este cron solo se activa el dia $ultimo_dia de este mes ($month - $year), hoy es ".date('d');
//        return;
//    }
    
    $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
    $columnas = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
    $iva = 0.16;
    $usuario = "CRON CONCENTRADO PHP";

    //Obtenemos los RFC de los emisores
    $consulta = "SELECT RFC FROM c_datosfacturacionempresa WHERE Activo = 1;";
    $result = $catalogo->obtenerLista($consulta);
    $rfc_emisor = "";
    
    if($result == "No database selected"){
        continue;
    }
    
    while ($rs = mysql_fetch_array($result)) {
        $rfc_emisor .= "'".$rs['RFC']."',";
    }

    if(strlen($rfc_emisor) > 0){
        $rfc_emisor = substr($rfc_emisor, 0, strlen($rfc_emisor)-1);
    }

    if($rfc_emisor != ""){
        $rfc_emisor = " AND f.RFCReceptor NOT IN($rfc_emisor) ";
    }else{
        $rfc_emisor = "";
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
    AND f.TipoComprobante = 'ingreso' AND f.EstadoFactura = 1 
    AND f.PendienteCancelar = 0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura <> 3) 
    $rfc_emisor 
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
            ->setCellValue('C' . (($fila)), $cuenta);    
        $objPHPExcel->getActiveSheet()->getStyle('C'.$fila)->getNumberFormat()->setFormatCode("$#,##0.00");    
        $fila++;
        $total += (float)($cuenta);
    }
    $objPHPExcel->setActiveSheetIndex(0)        
            ->setCellValue('A' . (($fila)), "Total")        
            ->setCellValue('C' . (($fila)), $total); 
    $objPHPExcel->getActiveSheet()->getStyle('C'.$fila)->getNumberFormat()->setFormatCode("$#,###,##0.00");

    $fila++;
    /*Colores de celdas*/
    $fila--; //Esto es solo temporal, para manejar los estilos
    cellColor($objPHPExcel, 'A1:C2', 'E7E6E6');
    cellColor($objPHPExcel, 'A3:C'.($fila), '305496');
    cellColor($objPHPExcel, 'A'.$fila.':B'.$fila, 'FFFFFF');
    //Estilo de letras
    $styleArray = getStyle(true, "000000", 12, "Times New Roman", false); $objPHPExcel->getActiveSheet()->getStyle('A1:C2')->applyFromArray($styleArray);
    $styleArray = getStyle(true, "FFFFFF", 11, "Calibri", false); $objPHPExcel->getActiveSheet()->getStyle('A3:C'.($fila))->applyFromArray($styleArray);
    $styleArray = getStyle(true, "000000", 11, "Times New Roman", false);$objPHPExcel->getActiveSheet()->getStyle('A'.$fila.':B'.$fila)->applyFromArray($styleArray);
    //Agregamos bordes
    $styleArray = getBorder();$objPHPExcel->getActiveSheet()->getStyle('A3:C'.$fila)->applyFromArray($styleArray);
    $fila++;

    /**************     SALDOS POR COMPAÑIA     *********************/
    $filas_totales = $fila + 1;
    $fila+=5;
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . (($fila)), "Saldos por compañía")->mergeCells('A'.$fila.':J'.$fila);
    //Estilo de letras
    $styleArray = getStyle(true, "000000", 18, "Calibri", true); $objPHPExcel->getActiveSheet()->getStyle('A'.$fila.':J'.$fila)->applyFromArray($styleArray);
    $styleArray = getStyleCenter();$objPHPExcel->getActiveSheet()->getStyle('A'.$fila.':J'.$fila)->applyFromArray($styleArray);

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
    
    $rfc_emisor 
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
    
    $rfc_emisor 
    GROUP BY fe.IdDatosFacturacionEmpresa, MONTH(f.FechaFacturacion), EstadoFacturaPer 
    HAVING EstadoFacturaPer IN ('NP','P');";
    $result = $catalogo->obtenerLista($consulta);
    $array_pagos = $reporte->convertirRSIntoArrayConsolidado($result);

    //Obtenemos todas les empresas que facturan
    $consulta = "SELECT IdDatosFacturacionEmpresa, RazonSocial FROM `c_datosfacturacionempresa` WHERE Activo = 1 ORDER BY Orden;";
    $result = $catalogo->obtenerLista($consulta);
    $saldos_pendientes = array();
    $pp_mensuales = array();
    $pagos_parciales = 0;
    $fila++;
    while($rs = mysql_fetch_array($result)){//Recorremos todas las empresas   
        if($rs['IdDatosFacturacionEmpresa'] == "1001"){//Por instrucciones del administrador, nos saltamos la emprese 1001 del sistema de genesis
            continue;
        }
        $objPHPExcel->setActiveSheetIndex(0)        
            ->setCellValue('A' . (($fila)), "Concentrado de Facturación $year ".$rs['RazonSocial'])->mergeCells('A'.$fila.':J'.$fila);
        //Estilo de letras
        $styleArray = getStyle(true, "000000", 11, "Calibri", true); $objPHPExcel->getActiveSheet()->getStyle('A'.$fila.':J'.$fila)->applyFromArray($styleArray);
        $styleArray = getStyleCenter();$objPHPExcel->getActiveSheet()->getStyle('A'.$fila.':J'.$fila)->applyFromArray($styleArray);
        $fila++;
        for($i=0;$i<(int)$month;$i++){//Imprimimos los meses
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i+1]."".(($fila)),$meses[$i]);
        }
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i+1]."".(($fila)),"Totales");
        //Color de celdas
        cellColor($objPHPExcel, 'B'.$fila.':'.$columnas[$i+1].''.$fila, '305496');
        //Estilo de letras
        $styleArray = getStyle(true, "FFFFFF", 11, "Calibri", false); $objPHPExcel->getActiveSheet()->getStyle('B'.$fila.':'.$columnas[$i+1].''.$fila)->applyFromArray($styleArray);
        $fila++;    
        foreach ($nombres_estados as $key => $value) {//Recorremos los estados de las facturas a imprimir             
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".(($fila)),$value);
            //Color de celdas
            cellColor($objPHPExcel, "A".($fila), '305496');
            //Estilo de letras
            if($key==2){            
                $styleArray = getStyle(true, "FF003C", 14, "Calibri", false); $objPHPExcel->getActiveSheet()->getStyle("A".($fila).":".$columnas[$month+2]."".$fila)->applyFromArray($styleArray);
            }else if($key == 3){
                $styleArray = getStyle(true, "FFFFFF", 14, "Calibri", false); $objPHPExcel->getActiveSheet()->getStyle("A".($fila))->applyFromArray($styleArray);
                $styleArray = getStyle(true, "000000", 14, "Calibri", false); $objPHPExcel->getActiveSheet()->getStyle("B".($fila).":".$columnas[$month+2]."".$fila)->applyFromArray($styleArray);
            }else{
                $styleArray = getStyle(true, "FFFFFF", 11, "Calibri", false); $objPHPExcel->getActiveSheet()->getStyle("A".($fila))->applyFromArray($styleArray);
            }
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
                    if((float)$array_pagos[$rs['IdDatosFacturacionEmpresa']][$i][$estados_facturas[$key]] > (float)$array[$rs['IdDatosFacturacionEmpresa']][$i][$estados_facturas[$key]]){
                        $cuenta += ( (float)$array_pagos[$rs['IdDatosFacturacionEmpresa']][$i][$estados_facturas[$key]] - (float)$array[$rs['IdDatosFacturacionEmpresa']][$i][$estados_facturas[$key]]);
                    }
                }else{
                    $pagos_parciales = 0;
                }

                if(isset($array[$rs['IdDatosFacturacionEmpresa']][$i][$estados_facturas[$key]])){                                
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i]."".(($fila)),  number_format($cuenta,2));
                    if(isset($cuenta) && $cuenta!=""){                    
                        //$objPHPExcel->getActiveSheet()->getStyle($columnas[$i]."".(($fila)))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    }
                    $total += (float)($cuenta);
                }else{                
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i]."".(($fila)),"");                
                }
            }
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i]."".($fila),  number_format($total,2));        
            /*$objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i]."".($fila),  $total);        
            $objPHPExcel->getActiveSheet()->getStyle($columnas[$i]."".(($fila)))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);*/
            $fila++;        
        }    
        /*Imprimimos lo facturado, que es lo No pagado más lo pagado*/        
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".(($fila)),"Facturados");
        //Color de celdas
        cellColor($objPHPExcel, "A".($fila), '305496');
        //Estilo de letras        
        $styleArray = getStyle(true, "FFFFFF", 11, "Calibri", false); $objPHPExcel->getActiveSheet()->getStyle("A".($fila))->applyFromArray($styleArray);

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
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i]."".(($fila)),  number_format($suma,2));   
                //$objPHPExcel->getActiveSheet()->getStyle($columnas[$i]."".(($fila)))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $total += $suma;
            }else{            
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i]."".(($fila)),"");            
            }
        }    
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i]."".(($fila)),  number_format($total,2));     
        /*$objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i]."".($fila),  $total);        
        $objPHPExcel->getActiveSheet()->getStyle($columnas[$i]."".(($fila)))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);*/
        $fila +=3;
    }

    /*************      Pendientes de pago anio anterior        *************/
    //Obtenemos la suma de las facturas que aún están como no pagadas
    $consulta = "SELECT (SUM(f.Total)/".(1+$iva).") AS Cuenta, 
        (SELECT SUM(ImportePagado)/1.16 FROM c_pagosparciales WHERE IdFactura = f.IdFactura) AS CuentaPagos,
        fe.RazonSocial, fe.IdDatosFacturacionEmpresa, YEAR(f.FechaFacturacion) AS Anio 
    FROM c_factura AS f 
    LEFT JOIN c_cliente AS c ON c.ClaveCliente = (SELECT MAX(ClaveCliente) FROM c_cliente WHERE RFC = f.RFCReceptor)
    LEFT JOIN c_datosfacturacionempresa AS fe ON fe.RFC = f.RFCEmisor    
    WHERE (f.FechaFacturacion BETWEEN '".($year-1)."-01-01 00:00:00' AND '".($year-1)."-12-31 23:59:59')
    AND f.TipoComprobante = 'ingreso' AND f.EstadoFactura = 1 
    AND f.PendienteCancelar = 0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura <> 3) AND f.FacturaPagada = 0 
    $rfc_emisor 
    GROUP BY f.IdFactura ORDER BY f.Folio;";
    $result = $catalogo->obtenerLista($consulta);
    $array = array();
    while($rs = mysql_fetch_array($result)){
        if(isset($array[$rs['IdDatosFacturacionEmpresa']])){
            $array[$rs['IdDatosFacturacionEmpresa']] += $rs['Cuenta'];
        }else{
            $array[$rs['IdDatosFacturacionEmpresa']] = $rs['Cuenta'];
        }
        
        if(isset($rs['CuentaPagos']) && !empty($rs['CuentaPagos'])){
            $array[$rs['IdDatosFacturacionEmpresa']] -= $rs['CuentaPagos']; 
        }
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
    AND f.TipoComprobante = 'ingreso' AND f.EstadoFactura = 1 
    AND f.PendienteCancelar = 0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura <> 3) AND f.FacturaPagada = 0 
    $rfc_emisor 
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
    if(mysql_num_rows($result) && mysql_data_seek($result, 0)){
        $pagos_mensuales = $reporte->convertirRSIntoArrayConsolidado($result);
    }else{
        $result = $catalogo->obtenerLista($consulta);
        $pagos_mensuales = $reporte->convertirRSIntoArrayConsolidado($result);
    }

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . (($fila)), "Pendiente de pago ".($year-1))->mergeCells('A'.$fila.':J'.$fila);
    //Estilo de letras
    $styleArray = getStyle(true, "000000", 14, "Calibri", true); $objPHPExcel->getActiveSheet()->getStyle('A'.$fila.':J'.$fila)->applyFromArray($styleArray);
    $styleArray = getStyleCenter();$objPHPExcel->getActiveSheet()->getStyle('A'.$fila.':J'.$fila)->applyFromArray($styleArray);
    $fila++;
    for($i=0;$i<(int)$month;$i++){//Imprimimos los meses
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i+1]."".(($fila)),$meses[$i]);
    }
    //Color de celdas
    cellColor($objPHPExcel, 'B'.$fila.':'.$columnas[$i+1].''.$fila, '305496');
    //Estilo de letras
    $styleArray = getStyle(true, "FFFFFF", 11, "Calibri", false); $objPHPExcel->getActiveSheet()->getStyle('B'.$fila.':'.$columnas[$i+1].''.$fila)->applyFromArray($styleArray);
    $fila++;
    $total_meses = array();
    foreach ($array as $key => $value) {
        $datosFacturacion = new DatosFacturacionEmpresa();       
        $datosFacturacion->setEmpresa($empresa);        
        if($datosFacturacion->getRegistroById($key)){
            $empresa_fact = $datosFacturacion->getRazonSocial();
        }else{
            $empresa_fact = $key;
        }

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".(($fila)),$empresa_fact);
        //Color de celdas
        cellColor($objPHPExcel, "A".($fila), '305496');
        //Estilo de letras        
        $styleArray = getStyle(true, "FFFFFF", 11, "Calibri", false); $objPHPExcel->getActiveSheet()->getStyle("A".($fila))->applyFromArray($styleArray);
        for($i=1;$i<=$month;$i++){
            $monto = (float)$array[$key] - (float)$pagos_mensuales[$key][$i]['P'];        
            //$monto = (float)$array[$key];
            if(isset($total_meses[$i])){
                $total_meses[$i] += $monto;
            }else{
                $total_meses[$i] = $monto;
            }        
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i]."".(($fila)),$monto);
            $objPHPExcel->getActiveSheet()->getStyle($columnas[$i]."".(($fila)))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $array[$key] = $monto;
        }
        $fila++;    
    }

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".(($fila)),"Pendiente de pago");
    //Color de celdas
    cellColor($objPHPExcel, "A".($fila), '305496');
    //Estilo de letras        
    $styleArray = getStyle(true, "FFFFFF", 11, "Calibri", false); $objPHPExcel->getActiveSheet()->getStyle("A".($fila))->applyFromArray($styleArray);
    $styleArray = getStyle(true, "FF003C", 14, "Calibri", false); $objPHPExcel->getActiveSheet()->getStyle("B".($fila).":".$columnas[$month+2]."".$fila)->applyFromArray($styleArray);
    for($i=1;$i<=$month;$i++){    
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i]."".(($fila)),$total_meses[$i]);
        $objPHPExcel->getActiveSheet()->getStyle($columnas[$i]."".(($fila)))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    }

    /*****************  Totales de todas las CIAS  por recuperar por mes    *******************/
    $fila = $filas_totales;
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . (($fila)), "Saldos Totales de todas las CIAS  por recuperar por mes. ")->mergeCells('A'.$fila.':J'.$fila);
    //Estilo de letras
    $styleArray = getStyle(true, "000000", 14, "Calibri", true); $objPHPExcel->getActiveSheet()->getStyle('A'.$fila.':J'.$fila)->applyFromArray($styleArray);
    $styleArray = getStyleCenter();$objPHPExcel->getActiveSheet()->getStyle('A'.$fila.':J'.$fila)->applyFromArray($styleArray);
    $fila++;
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B' . ($fila), ($year-1));
    for($i=1;$i<=(int)$month;$i++){//Imprimimos los meses
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i+1]."".(($fila)), $meses[$i-1]);    
    }
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i+1]."".(($fila)), "Totales");   
    //Color de celdas
    cellColor($objPHPExcel, 'B'.$fila.':'.$columnas[$i+1].''.$fila, '305496');
    //Estilo de letras
    $styleArray = getStyle(true, "FFFFFF", 11, "Times New Roman", false); 
    $objPHPExcel->getActiveSheet()->getStyle('A'.$fila.':'.$columnas[$i+1].''.$fila)->applyFromArray($styleArray);

    $fila++;
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . ($fila), "Saldos");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B' . ($fila), $total_meses[count($total_meses)]);
    $objPHPExcel->getActiveSheet()->getStyle('B' . ($fila))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

    $total = $total_meses[count($total_meses)];
    for($i=1;$i<=(int)$month;$i++){
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i+1]."".(($fila)), $saldos_pendientes[$i]);    
        $objPHPExcel->getActiveSheet()->getStyle($columnas[$i]."".(($fila)))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $total += $saldos_pendientes[$i];
    }
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas[$i+1]."".(($fila)), $total);
    $objPHPExcel->getActiveSheet()->getStyle($columnas[$i]."".(($fila)))->getNumberFormat()->setFormatCode("$#,###,##0.00");
    //$objPHPExcel->getActiveSheet()->getStyle($columnas[$i]."".(($fila)))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    //Color de celdas
    cellColor($objPHPExcel, 'A'.$fila.':A'.$fila, '305496');
    cellColor($objPHPExcel, 'B'.$fila.':'.$columnas[$i+1].''.$fila, 'FFFF00');
    //Estilo de letras
    $styleArray = getStyle(true, "FFFFFF", 11, "Times New Roman", false); $objPHPExcel->getActiveSheet()->getStyle('A'.$fila.':A'.$fila)->applyFromArray($styleArray);
    $styleArray = getStyle(true, "000000", 11, "Calibri", false); $objPHPExcel->getActiveSheet()->getStyle('B'.$fila.':'.$columnas[$i+1].''.$fila)->applyFromArray($styleArray);

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

    $nombre = $empresa.'_Concentrado'.$meses[$month-1].$year;
    // Renombrar Hoja
    $objPHPExcel->getActiveSheet()->setTitle($nombre);
    // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
    $objPHPExcel->setActiveSheetIndex(0);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    
    if($parametroGlobal->getRegistroById("11")){
        $path = $parametroGlobal->getValor();
    }else{
        $path = "/html/www/";
    }
    
    //$ruta_server = $path.'reportes/'.$nombre.'.xls';
    $ruta_server = $path.$nombre.'.xls';
    $objWriter->save($ruta_server);
    $mail->setAttachPDF($ruta_server);
    
    if($parametroGlobal->getRegistroById("8")){
        $mail->setFrom($parametroGlobal->getValor());
    }else{
        $mail->setFrom("scg-salida@scgenesis.mx");
    }
    $mail->setSubject($rs_multi['nombre_empresa'].": Concentrado de facturación ".$meses[$month-1]);
    $message = "<br/>A continuación se adjunta el concentrado de facturación correspondiente al mes de ".$meses[$month-1].": <br/>";
    $mail->setBody($message);
    /* Obtenemos los correos a quien mandaremos el mail */
    
    $catalogo = new Catalogo();
    $catalogo->setEmpresa($empresa);
    $query4 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 11;");
    $correos = array();
    $z = 0;
    while ($rs = mysql_fetch_array($query4)) {
        $correos[$z] = $rs['correo'];
        $z++;
    }
    foreach ($correos as $value) {
        if (isset($value) && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
            $mail->setTo($value);
            if ($mail->enviarMailPDF() == "1") {
                echo "<br/>Un correo fue enviado a $value.";
            } else {
                echo "<br/>Error: No se pudo enviar el correo para autorizar.";
            }
        }
    }
}
?>