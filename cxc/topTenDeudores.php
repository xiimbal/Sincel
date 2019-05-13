<?php

session_start();
    
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}

$iva = 1.16;
$hayIVA = false;

if(isset($parametros['iva4']) && $parametros['iva4'] != ""){
    $iva = 1;
    $hayIVA = true;
}

$catalogo = new CatalogoFacturacion();
$catalogoN = new Catalogo();
$consultaDeudores = "SELECT f.RFCReceptor, c.NombreRazonSocial, DATE(f.FechaFacturacion) AS Fecha,
    SUM(f.Total) AS Total, f.Folio,
    SUM((SELECT (CASE WHEN ISNULL(SUM(pp.ImportePagado)) THEN 0 ELSE SUM(pp.ImportePagado) END)
        FROM c_pagosparciales AS pp
        WHERE pp.IdFactura = f.IdFactura
        GROUP BY(pp.IdFactura)
    )) AS restar
    FROM c_factura f
    LEFT JOIN c_cliente AS c ON c.RFC = f.RFCReceptor
    WHERE f.EstadoFactura = 1 AND f.TipoComprobante = 'ingreso' 
    AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura <> 3) AND f.FacturaPagada = 0
    GROUP BY f.RFCReceptor ORDER BY Total DESC LIMIT 10";
$result = $catalogo->obtenerLista($consultaDeudores);
?>
<script type="text/javascript" src="resources/js/paginas/cxc/topTenDeudores.js"></script>
<form id="formTopDeudores" name="formTopDeudores">
    <table style="width: 90%">
        <tr>
            <td style="width: 50%"><h2><b>Top Ten Deudores</b></h2></td>
            <td style="width: 30%">
                <?php if($hayIVA){ ?>
                <input type="checkbox" name="iva4" id="iva4" value="IVA" checked>Incluye I.V.A
                <?php }else{ ?>
                <input type="checkbox" name="iva4" id="iva4" value="IVA">Incluye I.V.A
                <?php } ?>
            </td>
            <td style="width: 20%">
                <input type="button" class="button" onclick="recargarDeudores();" value="Recalcular" style="margin-left: 85%;"/>
            </td>
            <td></td>
        </tr>
    </table>
</form>
<br/>
<table style="background-color: #f3f3f3; width: 100%;">
    <tr>
        <td style="width: 43%;" valign="top">
            <div id="graficaDeudores" name="graficaDeudores"></div>
        </td>
        <td style="background-color: white; width: 3%;"></td>
        <td style="width: 54%;" valign="top">
            <table style="width: 100%;">
                <tr>
                    <td><h2>RFC cliente</h2></td>
                    <td><h2>Nombre cliente</h2></td>
                    <td><h2>Total Facturas</h2></td>
                    <td><h2>Pagos parciales</h2></td>
                    <td><h2>Total</h2></td>
                    <td><h2>Fac con mayor retraso</h2></td>
                </tr>
                <?php
                    $total = 0;
                    $totalPagos = 0;
                    $totalMenosPagos = 0;
                    $chartJQuery = "";
                    while($rs = mysql_fetch_array($result)){
                        $totalParcial = ((float)$rs['Total']) / $iva;
                        $totalParcialRestar = 0;
                        echo "<tr>";
                        echo "<td>".$rs['RFCReceptor']."</td>";
                        echo "<td>".$rs['NombreRazonSocial']."</td>";
                        echo "<td>$".number_format($totalParcial,2)."</td>";
                        if(isset($rs['restar'])){
                            $totalParcialRestar = ((float)$rs['restar']) / $iva;
                            echo "<td>$".number_format($totalParcialRestar,2)."</td>";
                            $totalPagos += $totalParcialRestar;
                        }else{
                            echo "<td>$0.00</td>";
                        }
                        $totalMenosPagos = $totalParcial - $totalParcialRestar;
                        echo "<td>$".number_format($totalMenosPagos,2)."</td>";
                        echo "<td><h3>".$catalogoN->formatoFechaReportes(date($rs['Fecha']))." (".$rs['Folio'].")</h3></td>";
                        $total += $totalParcial;
                        echo "</tr>";
                        $chartJQuery .= "{" .
                            "name: '".$rs['RFCReceptor']."'," .
                            "y: " . $totalMenosPagos .
                            "},";
                    }
                ?>
                <tr>
                    <td></td>
                    <td><h3>Totales</h3></td>
                    <td><h3>$<?php echo number_format($total,2) ?></h3></td>
                    <td><h3>$<?php echo number_format($totalPagos,2) ?></h3></td>
                    <td><h3>$<?php
                        $totalSinPagos = $total - $totalPagos;
                        echo number_format($totalSinPagos,2) 
                    ?></h3></td>
                    <td></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<script>
$(function () {
    $('#graficaDeudores').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'column'
        },
        title: {
            text: 'Top Ten Deudores'
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
            name: 'Cliente',
            colorByPoint: true,
            data: [
                <?php echo $chartJQuery?>
            ]
        }]
    });
});
</script>