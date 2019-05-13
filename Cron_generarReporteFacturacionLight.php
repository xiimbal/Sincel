<?php
ini_set("memory_limit","1024M");
set_time_limit (0);

include_once("WEB-INF/Classes/PHP_XLSXWriter-master/xlsxwriter.class.php");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

include_once('WEB-INF/Classes/CatalogoFacturacion.class.php');
include_once('WEB-INF/Classes/Catalogo.class.php');
include_once('WEB-INF/Classes/ConexionMultiBD.class.php');
include_once("WEB-INF/Classes/ParametroGlobal.class.php");

$con = new ConexionMultiBD();         
$result_bases = mysql_query("SELECT id_empresa, nombre_empresa FROM `c_empresa` WHERE Activo = 1;");
$con->Desconectar();
while($rs_multi = mysql_fetch_array($result_bases)){
    $parametro_global = new ParametroGlobal();
    echo "<br/><br/>Procesando empresa ".$rs_multi['nombre_empresa']."<br/>";
    $empresa = $rs_multi['id_empresa'];
    $parametro_global->setEmpresa($empresa);
    /*Ponemos las cabeceras*/
    $cabeceras = array("Factura" => "number", "Fecha" => "date", "Número_de_cliente" => "string", "Nombre_cliente" => "string", 
       "Subtotal" => "money", "IVA" => "money", "Importe_Total" => "money", "Importe_Pagado" => "money", "Importe_Por_Pagar" => "money", 
       "Fecha_Pago" => "date","Estado" => "string","RFC" => "string","Razón_Social_del_Emisor" => "string", 
       "Tipo_de_factura" => "string", "Ejecutivo_de_cuenta" => "string", "Ejecutivo_atencion" => "string","Periodo_Facturacion" => "date", 
       "Pagos_con_NDC" => "string", "Categoría" => "string", "Pagos" => "string","Método_de_pago" => "string");
    $writer = new XLSXWriter();//Nuevo libro
    $writer->setAuthor('Techra');
    $hoja = "Reporte";
    $writer->writeSheetHeader($hoja, $cabeceras );
    //Ponemos la data
    $catalogo = new CatalogoFacturacion();
    $catalogoOperacion = new Catalogo();
    $catalogoOperacion->setEmpresa($empresa);
    $catalogo->setEmpresa($empresa);        
    $consulta = "select `f`.`MetodoPago`, f.IdFactura, `f`.`Folio` AS `Factura`,cast(`f`.`FechaFacturacion` as date) AS `Fecha`,`c`.`ClaveCliente` AS `Número_de_cliente`,
        `f`.`NombreReceptor` AS `Nombre_cliente`,
        (GROUP_CONCAT(CONCAT('',CONCAT('$', FORMAT(pp.ImportePagado, 2)),' [',CAST(DATE(pp.FechaPago) AS CHAR),']  (', pp.Observaciones, ') (',pp.Referencia,') / ',pp.UsuarioCreacion) SEPARATOR ',')) AS Pagos, 
        (CASE WHEN f.TipoComprobante = 'ingreso' THEN (`f`.`Total` / 1.16) WHEN f.TipoComprobante = 'egreso' THEN (`f`.`Total` / 1.16) * (-1) ELSE (`f`.`Total` / 1.16) END) AS `Subtotal`,
        (CASE WHEN f.TipoComprobante = 'ingreso' THEN (`f`.`Total` / 1.16) * 0.16 WHEN f.TipoComprobante = 'egreso' THEN ((`f`.`Total` / 1.16) * 0.16) * (-1) ELSE (`f`.`Total` / 1.16) * 0.16 END) AS `IVA`,
        (CASE WHEN f.TipoComprobante = 'ingreso' THEN `f`.`Total` WHEN f.TipoComprobante = 'egreso' THEN `f`.`Total` * (-1) ELSE `f`.`Total` END) AS `Importe_Total`,
        CONCAT(u2.Nombre,' ',u2.ApellidoPaterno,' ',u2.ApellidoMaterno) AS Ejecutivo_atencion,

        (select (
        case when (`f`.`EstadoFactura` = 0) then 0 
        when ((`f`.`PendienteCancelar` = 1) and (`pp`.`ImportePagado` is not null)) then (select sum(`pp`.`ImportePagado`)) 
        when ((`f`.`PendienteCancelar` = 1) and isnull(`pp`.`ImportePagado`)) then 0 
        when ((`f`.`TipoComprobante` <> 'ingreso') and (`pp`.`ImportePagado` is not null)) then (select sum(`pp`.`ImportePagado`)) 
        when ((`f`.`TipoComprobante` <> 'ingreso') and isnull(`pp`.`ImportePagado`)) then 0 
        else (select (case 
        when (`f`.`EstatusFactura` = 3) then 0 
        else (select (case 
        when ((`f`.`FacturaPagada` = 0) and (`pp`.`ImportePagado` is not null)) then (select sum(`pp`.`ImportePagado`)) 
        when ((`f`.`FacturaPagada` = 0) and isnull(`pp`.`ImportePagado`)) then 0 
        else 
        (
        CASE WHEN isnull(`pp`.`ImportePagado`) THEN `f`.`Total` 
        ELSE sum(`pp`.`ImportePagado`) END
        )end)) end)) end)) AS `Importe_Pagado`,

        (select 
        (case when (`f`.`EstadoFactura` = 0) then 0 
        when ((`f`.`PendienteCancelar` = 1) and (`pp`.`IdPagoParcial` is not null)) then (`f`.`Total` - (select sum(`pp`.`ImportePagado`))) 
        when ((`f`.`PendienteCancelar` = 1) and isnull(`pp`.`IdPagoParcial`)) then `f`.`Total` 
        when (`f`.`TipoComprobante` <> 'ingreso') then (`f`.`Total` - (select sum(`pp`.`ImportePagado`))) 
        else (select 
        (case when (`f`.`EstatusFactura` = 3) then 0 
        else (select (case 
        when ((`f`.`FacturaPagada` = 0) and (`pp`.`IdPagoParcial` is not null)) then (`f`.`Total` - (select sum(`pp`.`ImportePagado`))) 
        when ((`f`.`FacturaPagada` = 0) and isnull(`pp`.`IdPagoParcial`)) then `f`.`Total` 
        else 
        (
                                CASE WHEN isnull(`pp`.`ImportePagado`) THEN 0 
                                ELSE f.Total - sum(`pp`.`ImportePagado`) END
        )end)) end)) end)) AS `Importe_Por_Pagar`,

        (case when (`pp`.`IdPagoParcial` is not null) then (select max(`c_pagosparciales`.`FechaPago`) from `c_pagosparciales` where (`c_pagosparciales`.`IdFactura` = `f`.`IdFactura`)) else `f`.`FechaPago` end) AS `Fecha_Pago`,
        (select (case when (`f`.`EstadoFactura` = 0) then 'Cancelado' when (`f`.`PendienteCancelar` = 1) then 'Pendiente Cancelar' when (`f`.`TipoComprobante` <> 'ingreso') then 'Nota de crÃ©dito' else (select (case when (`f`.`EstatusFactura` = 3) then 'Incobrable' else (select (case when (`f`.`FacturaPagada` = 0) then 'No pagado' else 'Pagado' end)) end)) end)) AS `Estado`,
        `f`.`RFCReceptor` AS `RFC`,`f`.`NombreEmisor` AS `Razón_Social_del_Emisor`,
        (select (case when (`f`.`TipoComprobante` = 'ingreso') then 'Factura' else 'Nota de crédito' end)) AS `Tipo_de_factura`,
        (select (case when (`u`.`IdUsuario` is not null) then concat(`u`.`Nombre`,' ',`u`.`ApellidoPaterno`,' ',`u`.`ApellidoMaterno`) else 'GERARDO BELTRAN' end)) AS `Ejecutivo_de_cuenta`,
        cast(`f`.`PeriodoFacturacion` as date) AS `Periodo_Facturacion`,
        (SELECT GROUP_CONCAT(Folio SEPARATOR ', ') FROM c_factura WHERE IdFacturaRelacion = f.IdFactura AND TipoComprobante = 'egreso' GROUP BY IdFacturaRelacion) AS Pagos_con_NDC,
        `tf`.`TipoFactura` AS `Categoría` 
        from (((((`c_factura` `f` 
        left join `c_cliente` `c` on((`c`.`ClaveCliente` = (select min(`c_cliente`.`ClaveCliente`) from `c_cliente` where ((`c_cliente`.`RFC` = `f`.`RFCReceptor`) and (`c_cliente`.`Activo` = 1)))))) 
        left join `c_usuario` `u` on((`u`.`IdUsuario` = `c`.`EjecutivoCuenta`))) ) 
        left join `c_pagosparciales` `pp` on((`pp`.`IdFactura` = `f`.`IdFactura`))) 
        left join `c_tipofacturaexp` `tf` on((`f`.`TipoArrendamiento` = `tf`.`IdTipoFactura`))) 
        LEFT JOIN c_usuario AS u2 ON u2.IdUsuario = c.EjecutivoAtencionCliente
        where (`f`.`Serie` <> 'PREF') 
        group by `f`.`IdFactura`;";
    
    $result = $catalogo->obtenerLista($consulta);
    while($rs = mysql_fetch_array($result)){
        $array_valores = array();
        foreach ($cabeceras as $key => $value) {
            if($key != "Método_de_pago"){
            array_push($array_valores, $rs[$key]);
            }else{
                if($rs['MetodoPago'] != "" && $rs['MetodoPago'] != null){
                    $consultaMetodo = "SELECT ClaveMetodoPago FROM c_metodopago WHERE IdMetodoPago = ".$rs['MetodoPago'].";";
                    $resultMetodo = $catalogoOperacion->obtenerLista($consultaMetodo);
                    while ($row = mysql_fetch_array($resultMetodo)) {
                        array_push($array_valores, $row['ClaveMetodoPago']);
                    }
                }else{
                    array_push($array_valores, "");
                }
            }
        }
        $writer->writeSheetRow($hoja, $array_valores);
    }
    if($parametro_global->getRegistroById("11")){
        $path = $parametro_global->getValor();
    }else{
        $path = "/html/www/";
    }
    $nombre = $path."Reporte_facturacion_".$empresa.".xlsx";
    $writer->writeToFile($nombre);
    echo '#'.floor((memory_get_peak_usage())/1024/1024)."MB"."\n";
}
?>