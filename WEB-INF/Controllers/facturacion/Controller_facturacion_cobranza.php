<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    echo "Es necesario iniciar sesión en el sistema";
    return;
}    

if(isset($_POST['sistema']) || isset($_GET['sistema'])){
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        echo "Es necesario iniciar sesión en el sistema";
        return;
    }        
    include_once("../Classes/CatalogoFacturacion.class.php");
    include_once("../Classes/ReporteFacturacion.class.php");
    include_once("../Classes/DatosFacturacionEmpresa.class.php");
    $usuario = $_SESSION['user'];
}else{    
    include_once("../../Classes/CatalogoFacturacion.class.php");
    include_once("../../Classes/ReporteFacturacion.class.php");
    include_once("../../Classes/DatosFacturacionEmpresa.class.php");
    $usuario = "CRON PHP";
}

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

$catalogo = new CatalogoFacturacion();
$reporte = new ReporteFacturacion();
$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$iva = 0.16;

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
echo "<table style='border: 1px solid #000; text-align: right;'>"; 
echo "<tr><td colspan='3'>REPORTE DE COBRANZA PAGADO EN ".strtoupper($meses[$month-1])."</td></tr>";
echo "<tr><td>AÑO</td><td>EMPRESA</td><td>TOTALES</td></tr>";
while($rs = mysql_fetch_array($result)){
    $cuenta = (float)$rs['Cuenta'];
    echo "<tr>";
    echo "<td style='border: 1px solid #000; text-align: right;'>".$rs['Anio']."</td>";
    echo "<td style='border: 1px solid #000; text-align: right;'>".$rs['RazonSocial']."</td>";
    echo "<td style='border: 1px solid #000; text-align: right;'>$ ".number_format($cuenta,2)."</td>";
    $total += (float)($cuenta);
    echo "</tr>";
}
echo "<tr>";
echo "<td colspan='2'>Total</td>";
echo "<td style='border: 1px solid #000; text-align: right;'>$ ".number_format($total,2)."</td>";
echo "</tr>";
echo "</table>";

/**************     SALDOS POR COMPAÑIA     *********************/
echo "<h1>Saldos por compañía</h1>";
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
//echo $consulta;
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
//echo "<br/><br/>".$consulta;
$result = $catalogo->obtenerLista($consulta);
$array_pagos = $reporte->convertirRSIntoArrayConsolidado($result);

//Obtenemos todas les empresas que facturan
$consulta = "SELECT IdDatosFacturacionEmpresa, RazonSocial FROM `c_datosfacturacionempresa` WHERE Activo = 1 ORDER BY Orden;";
$result = $catalogo->obtenerLista($consulta);
$saldos_pendientes = array();
$pp_mensuales = array();
$pagos_parciales = 0;
while($rs = mysql_fetch_array($result)){//Recorremos todas las empresas    
    echo "<h2>Concentrado de Facturación $year ".$rs['RazonSocial']." </h2>";
    echo "<table style='border: 1px solid #000; text-align: right; width: 90%;'>";
    echo "<tr><td></td>";
    for($i=0;$i<(int)$month;$i++){//Imprimimos los meses
        echo "<td>".$meses[$i]."</td>";
    }
    echo "<td>Totales</td>";
    echo "</tr>";
    foreach ($nombres_estados as $key => $value) {//Recorremos los estados de las facturas a imprimir        
        echo "<tr><td>$value</td>";
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
                echo "<td style='border: 1px solid #000; text-align: right;'>".  number_format($cuenta,2)."</td>";
                $total += (float)($cuenta);
            }else{
                echo "<td style='border: 1px solid #000; text-align: right;'></td>";
            }
        }
        echo "<td style='border: 1px solid #000; text-align: right;'>".  number_format($total,2)."</td>";
        echo "</tr>";
    }
    /*Imprimimos lo facturado, que es lo No pagado más lo pagado*/    
    echo "<tr><td>Facturados</td>";
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
            echo "<td style='border: 1px solid #000; text-align: right;'>". number_format($suma,2)."</td>";
            $total += $suma;
        }else{
            echo "<td style='border: 1px solid #000; text-align: right;'></td>";
        }
    }
    echo "<td style='border: 1px solid #000; text-align: right;'>".  number_format($total,2)."</td>";
    echo "</tr>";
    echo "</table>";
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

echo "<br/><br/><h2>Pendiente de pago ".($year-1)."</h2>";
echo "<table style='border: 1px solid #000; text-align: right;'>";
echo "<tr><td></td>";
for($i=0;$i<$month;$i++){
    echo "<td>".$meses[$i]."</td>";
}
echo "</tr>";
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
    echo "<tr><td>$empresa</td>";
    for($i=1;$i<=$month;$i++){
        $monto = (float)$array[$key] - (float)$pagos_mensuales[$key][$i]['P'];        
        if(isset($total_meses[$i])){
            $total_meses[$i] += $monto;
        }else{
            $total_meses[$i] = $monto;
        }
        echo "<td style='border: 1px solid #000; text-align: right;'>". number_format($monto,2)."</td>";
        $array[$key] = $monto;
    }
    echo "</tr>";    
}
echo "<tr>";
echo "<td>Pendiente de pago</td>";
for($i=1;$i<=$month;$i++){
    echo "<td style='border: 1px solid #000; text-align: right;'>".number_format($total_meses[$i],2)."</td>";
}
echo "</tr>";
echo "</table>";
/*****************  Totales de todas las CIAS  por recuperar por mes    *******************/
echo "<br/><h2>Saldos Totales de todas las CIAS  por recuperar por mes.</h2>";
echo "<table style='border: 1px solid #000; text-align: right; width:90%;'>";
echo "<tr><td></td><td>".($year-1)."</td>";
for($i=0;$i<(int)$month;$i++){//Imprimimos los meses
    echo "<td>".$meses[$i]."</td>";
}
echo "<td>Totales</td>";
echo "</tr>";
echo "<tr><td>Saldos</td><td style='border: 1px solid #000; text-align: right;'>".  number_format($total_meses[count($total_meses)],2)."</td>";
$total = $total_meses[count($total_meses)];
for($i=1;$i<=(int)$month;$i++){
    echo "<td style='border: 1px solid #000; text-align: right;'>".  number_format($saldos_pendientes[$i],2)."</td>";
    $total += $saldos_pendientes[$i];
}
echo "<td style='border: 1px solid #000; text-align: right;'>".  number_format($total,2)."</td>";
echo "</tr>";
echo "</table>";
?>
