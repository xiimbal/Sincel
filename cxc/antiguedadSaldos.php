<?php

session_start();
    
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/CatalogoFacturacion.class.php");

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}

$catalogo = new CatalogoFacturacion();
$iva = 1.16;
$hayIVA = false;
$where = "";
if(isset($parametros['iva3']) && $parametros['iva3'] != ""){
    $iva = 1;
    $hayIVA = true;
}

if(isset($parametros['anio']) && $parametros['anio'] != ""){
    $anio = (int)$parametros['anio'];
    $where = "  AND YEAR(f.FechaFacturacion) = $anio";
}

$arrayDiasCantidad = array(0 => 0, 30 => 0, 60 => 0, 90 => 0, 100 => 0);
$arrayDiasCantidadRestar = array(0 => 0, 30 => 0, 60 => 0, 90 => 0, 100 => 0);
$arrayTotal = array(0 => 0, 30 => 0, 60 => 0, 90 => 0, 100 => 0);
$consultaAntiguedad = "SELECT
    (CASE 
        WHEN datediff(DATE(NOW()), DATE_ADD(f.FechaFacturacion,INTERVAL (SELECT (CASE WHEN ISNULL(f.DiasCredito) THEN 30 ELSE f.DiasCredito END)) DAY)) <= 30 THEN 30
        WHEN datediff(DATE(NOW()), DATE_ADD(f.FechaFacturacion,INTERVAL (SELECT (CASE WHEN ISNULL(f.DiasCredito) THEN 30 ELSE f.DiasCredito END)) DAY)) <= 60 THEN 60
        WHEN datediff(DATE(NOW()), DATE_ADD(f.FechaFacturacion,INTERVAL (SELECT (CASE WHEN ISNULL(f.DiasCredito) THEN 30 ELSE f.DiasCredito END)) DAY)) <= 90 THEN 90
        ELSE 100 END
    ) AS diasPasados,
    SUM(f.Total) AS Total,
    SUM((SELECT (CASE WHEN ISNULL(SUM(pp.ImportePagado)) THEN 0 ELSE SUM(pp.ImportePagado) END)
        FROM c_pagosparciales AS pp
        WHERE pp.IdFactura = f.IdFactura
        GROUP BY(pp.IdFactura)
    )) AS restar 
    FROM c_factura f
    WHERE DATE(NOW()) > DATE_ADD(f.FechaFacturacion,INTERVAL (SELECT (CASE WHEN ISNULL(f.DiasCredito) THEN 30 ELSE f.DiasCredito END)) DAY)
    AND f.EstadoFactura = 1 AND f.TipoComprobante = 'ingreso' 
    AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura <> 3) AND f.FacturaPagada = 0$where
    GROUP BY diasPasados
    UNION
    SELECT 0 AS diasPasados,
    SUM(f.Total) AS Total,
    SUM((SELECT (CASE WHEN ISNULL(SUM(pp.ImportePagado)) THEN 0 ELSE SUM(pp.ImportePagado) END)
        FROM c_pagosparciales AS pp
        WHERE pp.IdFactura = f.IdFactura
        GROUP BY(pp.IdFactura)
    )) AS restar 
    FROM c_factura f
    WHERE DATE(NOW()) <= DATE_ADD(f.FechaFacturacion,INTERVAL (SELECT (CASE WHEN ISNULL(f.DiasCredito) THEN 30 ELSE f.DiasCredito END)) DAY)
    AND f.EstadoFactura = 1 AND f.TipoComprobante = 'ingreso' 
    AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura <> 3) AND f.FacturaPagada = 0$where
    GROUP BY diasPasados";
$total = 0;
$totalRestar = 0;
$result = $catalogo->obtenerLista($consultaAntiguedad);
while($rs = mysql_fetch_array($result)){
    $llave = (int)$rs['diasPasados'];
    $parcial = (float)$rs['Total'] / $iva;
    $arrayDiasCantidad[$llave] = $parcial;
    if(isset($rs['restar'])){
        $parcialRestar = (float)$rs['restar'] / $iva;
        $arrayDiasCantidadRestar[$llave] = $parcialRestar;
        $totalRestar += $parcialRestar;
    }
    $total += $parcial;
    $arrayTotal[$llave] = $arrayDiasCantidad[$llave] - $arrayDiasCantidadRestar[$llave];
}

foreach($arrayTotal as $key => $value){
    if((int)$key == 100){
        $chartJQuery .= "{" .
        "name: 'Más de 90 días'," .
        "y: " . $value .
        "},";
        continue;
    }
    $chartJQuery .= "{" .
        "name: '".$key." días'," .
        "y: " . $value .
        "},";
}

?>

<script type="text/javascript" src="resources/js/paginas/cxc/antiguedadSaldos.js"></script>
<form id="formAntiguedadSaldos" name="formAntiguedadSaldos">
    <table style="width: 90%">
        <tr>
            <td style="width: 30%"><h2><b>Antigüedad de saldos CXC</b></h2></td>
            <td style="width: 30%">
                <label for="fecha">Año: </label>
                <input type="text" class="date-picker-year" id="anio" name="anio" value="<?php echo $anio;?>" />
            </td>
            <td style="width: 10%">
                <?php if($hayIVA){ ?>
                <input type="checkbox" name="iva3" id="iva3" value="IVA" checked>Incluye I.V.A
                <?php }else{ ?>
                <input type="checkbox" name="iva3" id="iva3" value="IVA">Incluye I.V.A
                <?php } ?>
            </td>
            <td style="width: 20%">
                <input type="button" class="button" onclick="recargarAntiguedad();" value="Recalcular" style="margin-left: 85%;"/>
            </td>
            <td></td>
        </tr>
    </table>
</form>
<br/>
<table style="background-color: #f3f3f3; width: 100%;">
    <tr>
        <td style="width: 48%;">
            <div id="graficaAntiguedad" name="graficaAntiguedad"></div>
        </td>
        <td style="background-color: white; width: 3%;"></td>
        <td style="width: 49%;" valign="top">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <td></td>
                        <td><h2>Total factura</h2></td>
                        <td><h2>Pagos parciales</h2></td>
                        <td><h2>Total restante</h2></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><h3>Corriente</h3></td>
                        <td><?php echo "$".number_format($arrayDiasCantidad[0],2); ?></td>
                        <td align="center"><?php echo "$".number_format($arrayDiasCantidadRestar[0],2); ?></td>
                        <td><?php echo "$".number_format($arrayTotal[0],2); ?></td>
                    </tr>
                    <tr>
                        <td><h3>1-30 días</h3></td>
                        <td><?php echo "$".number_format($arrayDiasCantidad[30],2); ?></td>
                        <td align="center"><?php echo "$".number_format($arrayDiasCantidadRestar[30],2); ?></td>
                        <td><?php echo "$".number_format($arrayTotal[30],2); ?></td>
                    </tr>
                    <tr>
                        <td><h3>31-60 días</h3></td>
                        <td><?php echo "$".number_format($arrayDiasCantidad[60],2); ?></td>
                        <td align="center"><?php echo "$".number_format($arrayDiasCantidadRestar[60],2); ?></td>
                        <td><?php echo "$".number_format($arrayTotal[60],2); ?></td>
                    </tr>
                    <tr>
                        <td><h3>61-90 días</h3></td>
                        <td><?php echo "$".number_format($arrayDiasCantidad[90],2); ?></td>
                        <td align="center"><?php echo "$".number_format($arrayDiasCantidadRestar[90],2); ?></td>
                        <td><?php echo "$".number_format($arrayTotal[90],2); ?></td>
                    </tr>
                    <tr>
                        <td><h3>Más de 90 días</h3></td>
                        <td><?php echo "$".number_format($arrayDiasCantidad[100],2); ?></td>
                        <td align="center"><?php echo "$".number_format($arrayDiasCantidadRestar[100],2); ?></td>
                        <td><?php echo "$".number_format($arrayTotal[100],2); ?></td>
                    </tr>
                    <tr>
                        <td><h3>Total</h3></td>
                        <td><?php echo "$".number_format($total,2); ?></td>
                        <td align="center"><?php echo "$".number_format($totalRestar,2); ?></td>
                        <td><?php echo "$".number_format(($total - $totalRestar),2); ?></td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>
<script>
$(function () {
    $('#graficaAntiguedad').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'column'
        },
        title: {
            text: 'Antigüedad de Saldos'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>${point.y:,.2f}</b>'
        },
        xAxis: {
            type: "category",
            labels: {
                //enabled:false,//default is true
                y : 20, rotation: -45, align: 'right' 
            }
        },
        plotOptions: {
            pie: {
                size:'70%',
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'

                    }
                }
            }
        },
        credits: {
            text: "",
            href: ""
        },
        series: [{
            name: 'Días',
            colorByPoint: true,
            data: [
                <?php echo $chartJQuery?>
            ]
        }]
    });
});
</script>

