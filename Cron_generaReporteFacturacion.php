<?php
ini_set("memory_limit","1024M");
set_time_limit (0);
require_once('WEB-INF/Classes/PHPExcel/CachedObjectStorageFactory.php');
require_once('WEB-INF/Classes/PHPExcel/Settings.php');
$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
$cacheSettings = array( 'memoryCacheSize' => '32MB');
PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

header('Content-Type: text/html; charset=utf-8');
include_once('WEB-INF/Classes/CatalogoFacturacion.class.php');
include_once('WEB-INF/Classes/ConexionMultiBD.class.php');
require_once('WEB-INF/Classes/PHPExcel/IOFactory.php');
require_once('WEB-INF/Classes/PHPExcel.php');

$con = new ConexionMultiBD();         
$result_bases = mysql_query("SELECT id_empresa, nombre_empresa FROM `c_empresa` WHERE Activo = 1;");
$con->Desconectar();
while($rs_multi = mysql_fetch_array($result_bases)){
    echo "<br/><br/>Procesando empresa ".$rs_multi['nombre_empresa']."<br/>";
    $empresa = $rs_multi['id_empresa'];
    $cabeceras = array("Factura" => "Factura", "Fecha" => "Fecha", "Número_de_cliente" => "Numero de cliente", "Nombre_cliente" => "Nombre cliente", 
       "Subtotal" => "Subtotal", "IVA" => "IVA", "Importe_Total" => "Importe Total", "Importe_Pagado" => "Importe pagado", "Importe_Por_Pagar" => "Importe por pagar", 
       "Fecha_Pago" => "Fecha de pago","Estado" => "Estado","RFC" => "RFC","Razón_Social_del_Emisor" => "Razón Social", 
       "Tipo_de_factura" => "Tipo de factura", "Ejecutivo_de_cuenta" => "Ejecutivo", "Periodo_Facturacion" => "Periodo", 
       "NDC" => "Pagos con NDC", "Categoría" => "Categoría");
    $aplicar_format_numero = array(false, false, false, false, true, true, true, true, true, false, false, false, false, false, false, false, false, false);
    $columnas_excel = array("A","B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");    
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
    $fila = 1;
    
    $catalogo = new CatalogoFacturacion();    
    $catalogo->setEmpresa($empresa);        
    $consulta = "select `f`.`Folio` AS `Factura`,cast(`f`.`FechaFacturacion` as date) AS `Fecha`,`c`.`ClaveCliente` AS `Número_de_cliente`,
        `f`.`NombreReceptor` AS `Nombre_cliente`,
        (`f`.`Total` / 1.16) AS `Subtotal`,
        ((`f`.`Total` / 1.16) * 0.16) AS `IVA`,
        `f`.`Total` AS `Importe_Total`,
        (select (case when (`f`.`EstadoFactura` = 0) then 0 when ((`f`.`PendienteCancelar` = 1) and (`pp`.`ImportePagado` is not null)) then (select sum(`pp`.`ImportePagado`)) when ((`f`.`PendienteCancelar` = 1) and isnull(`pp`.`ImportePagado`)) then 0 when ((`f`.`TipoComprobante` <> 'ingreso') and (`pp`.`ImportePagado` is not null)) then (select sum(`pp`.`ImportePagado`)) when ((`f`.`TipoComprobante` <> 'ingreso') and isnull(`pp`.`ImportePagado`)) then 0 else (select (case when (`f`.`EstatusFactura` = 3) then 0 else (select (case when ((`f`.`FacturaPagada` = 0) and (`pp`.`ImportePagado` is not null)) then (select sum(`pp`.`ImportePagado`)) when ((`f`.`FacturaPagada` = 0) and isnull(`pp`.`ImportePagado`)) then 0 else `f`.`Total` end)) end)) end)) AS `Importe_Pagado`,
        (select (case when (`f`.`EstadoFactura` = 0) then 0 when ((`f`.`PendienteCancelar` = 1) and (`pp`.`IdPagoParcial` is not null)) then (`f`.`Total` - (select sum(`pp`.`ImportePagado`))) when ((`f`.`PendienteCancelar` = 1) and isnull(`pp`.`IdPagoParcial`)) then `f`.`Total` when (`f`.`TipoComprobante` <> 'ingreso') then (`f`.`Total` - (select sum(`pp`.`ImportePagado`))) else (select (case when (`f`.`EstatusFactura` = 3) then 0 else (select (case when ((`f`.`FacturaPagada` = 0) and (`pp`.`IdPagoParcial` is not null)) then (`f`.`Total` - (select sum(`pp`.`ImportePagado`))) when ((`f`.`FacturaPagada` = 0) and isnull(`pp`.`IdPagoParcial`)) then `f`.`Total` else 0 end)) end)) end)) AS `Importe_Por_Pagar`,
        (case when (`pp`.`IdPagoParcial` is not null) then (select max(`c_pagosparciales`.`FechaPago`) from `c_pagosparciales` where (`c_pagosparciales`.`IdFactura` = `f`.`IdFactura`)) else `f`.`FechaPago` end) AS `Fecha_Pago`,
        (select (case when (`f`.`EstadoFactura` = 0) then 'Cancelado' when (`f`.`PendienteCancelar` = 1) then 'Pendiente Cancelar' when (`f`.`TipoComprobante` <> 'ingreso') then 'Nota de crédito' else (select (case when (`f`.`EstatusFactura` = 3) then 'Incobrable' else (select (case when (`f`.`FacturaPagada` = 0) then 'No pagado' else 'Pagado' end)) end)) end)) AS `Estado`,
        `f`.`RFCReceptor` AS `RFC`,`f`.`NombreEmisor` AS `Razón_Social_del_Emisor`,
        (select (case when (`f`.`TipoComprobante` = 'ingreso') then 'Factura' else 'Nota de crédito' end)) AS `Tipo_de_factura`,
        (select (case when (`u`.`IdUsuario` is not null) then concat(`u`.`Nombre`,' ',`u`.`ApellidoPaterno`,' ',`u`.`ApellidoMaterno`) else 'GERARDO BELTRAN' end)) AS `Ejecutivo_de_cuenta`,
        cast(`f`.`PeriodoFacturacion` as date) AS `Periodo_Facturacion`,
        group_concat(`fndc`.`Folio` separator ', ') AS `NDC`,`tf`.`TipoFactura` AS `Categoría` 
        from (((((`c_factura` `f` left join `c_cliente` `c` on((`c`.`ClaveCliente` = (select min(`c_cliente`.`ClaveCliente`) from `c_cliente` where ((`c_cliente`.`RFC` = `f`.`RFCReceptor`) and (`c_cliente`.`Activo` = 1)))))) left join `c_usuario` `u` on((`u`.`IdUsuario` = `c`.`EjecutivoCuenta`))) left join `c_factura` `fndc` on(((`fndc`.`IdFacturaRelacion` = `f`.`IdFactura`) and (`fndc`.`TipoComprobante` = 'egreso')))) left join `c_pagosparciales` `pp` on((`pp`.`IdFactura` = `f`.`IdFactura`))) left join `c_tipofacturaexp` `tf` on((`f`.`TipoArrendamiento` = `tf`.`IdTipoFactura`))) 
        where (`f`.`Serie` <> 'PREF') group by `f`.`IdFactura`;";
    
    $result = $catalogo->obtenerLista($consulta);
    
    //Escribimos el titulo de las columnas
    $contador_columnas = 0;    
    foreach ($cabeceras as $key => $value) {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas_excel[$contador_columnas].$fila, $value);
        $contador_columnas++;
    }    
    $fila++;
        
    if($contador_columnas > 0){        
        while( $rs = mysql_fetch_array($result)){
            $contador_columnas = 0;
            foreach ($cabeceras as $key => $value) {
                if(isset($aplicar_format_numero[$contador_columnas]) && !$aplicar_format_numero[$contador_columnas]){
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas_excel[$contador_columnas].$fila, $rs[$key]);
                }else{
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnas_excel[$contador_columnas].$fila, number_format((float)$rs[$key],2,".",""));
                    //$objPHPExcel->getActiveSheet()->getStyle($columnas_excel[$contador_columnas].$fila)->getNumberFormat()->setFormatCode('#.##');
                }
                $contador_columnas++;
            }
            $fila++;
        }
    }
    
    foreach ($columnas_excel as $value) {
        $objPHPExcel->getActiveSheet()->getColumnDimension($value)->setAutoSize(true);
    }
    
    $nombre = "Reporte_facturacion_".$empresa;
    // Renombrar Hoja
    $objPHPExcel->getActiveSheet()->setTitle($nombre);
    // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
    $objPHPExcel->setActiveSheetIndex(0);
    
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');    
    $objWriter->save($nombre.'.xls');
}

?>