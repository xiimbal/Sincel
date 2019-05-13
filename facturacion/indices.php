<?php

session_start();
    
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$catalogo = new CatalogoFacturacion();
$catalogo2 = new Catalogo();

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}

$iva = 1.16;
$hayIVA = false;
if(isset($parametros['iva2']) && $parametros['iva2'] != ""){
    $iva = 1;
    $hayIVA = true;
}

$mesAnterior = "'".date("Y-m",strtotime("-1 month"))."-01'";//Para precargar mes anterior
$mesAnteriorText = date("Y-m",strtotime("-1 month"))."-01"; //Para precargar mes anterior
$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Otubre","Noviembre","Diciembre");
$anio = date("Y");
$mes = date("m"); 
$dia = date("d");   
if(isset($parametros['fecha2']) && $parametros['fecha2'] != ""){
    $anio = (int)$parametros['fecha2'];
}
//Inicializar array de resultados
foreach ($meses as $key => $value) {
    $resultados[$value] = 0;
}
$contador = 0;
$chartJQuery = "";
$facturadoDia = 0;
$facturadoMesAnterior = 0;
$totalAnio = 0;

$consulta = "SELECT SUM(f.Total) AS total, MONTH(f.FechaFacturacion) AS Mes
    FROM `c_factura` AS f
    WHERE f.EstadoFactura = 1 AND f.TipoComprobante = 'ingreso' 
    AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura <> 3) AND YEAR(f.FechaFacturacion) = $anio
    GROUP BY Mes
    UNION
    SELECT SUM(f.Total), 13 AS Mes
    FROM c_factura AS f 
    WHERE f.EstadoFactura = 1 AND f.TipoComprobante = 'ingreso' 
    AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura <> 3) AND f.FechaFacturacion = DATE(NOW())
    UNION 
    SELECT SUM(f.Total), 
    14 AS Mes 
    FROM c_factura AS f 
    WHERE f.EstadoFactura = 1 AND f.TipoComprobante = 'ingreso' 
    AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura <> 3) 
    AND MONTH(f.FechaFacturacion) = MONTH($mesAnterior) AND YEAR(f.FechaFacturacion) = YEAR($mesAnterior)
    ORDER BY Mes;";
//echo $consulta. "<br/>";
$result = $catalogo->obtenerLista($consulta);
while ($rs = mysql_fetch_array($result)) {
    $totalMes = 0;
    if((int)$rs['Mes'] == 13){  //Facturación del día de hoy
        if(isset($rs['total']) && !empty($rs['total'])){
            $facturadoDia = ($rs['total'] / $iva);
        }
        continue;
    }else if((int)$rs['Mes'] == 14){    //Facturación del mes anterior
        if(isset($rs['total']) && !empty($rs['total'])){
            $facturadoMesAnterior = ($rs['total'] / $iva);
        }
        break;
    }
    if(isset($rs['total']) && !empty($rs['total'])){
        $totalMes = ($rs['total'] / $iva);
        $totalAnio += $totalMes;
        $resultados[$meses[$contador]] = $totalMes;
    }
    $contador++;
}

foreach($resultados as $key => $value){
    $chartJQuery .= "{" .
        "name: '".$key."'," .
        "y: " . $value .
        "},";
}

arsort($resultados); //Ordena de menor a mayor los valores del arreglo conservando la relación con la llave

$contador = 0;
$mesMenor = "";
$mesMayor = "";
foreach($resultados as $key => $value){
    if($contador == 0){
        $mesMayor = $key;
    }
    $mesMenor = $key;
    if($anio == date("Y")){
        if(($contador + 1) == date("n")){
            break;
        }
    }    
    $contador++;
}

?>
<script type="text/javascript" src="resources/js/paginas/facturacion/indices.js"></script>
<form id="formIndicadores" name="formIndicadores">
    <table style="width: 90%">
        <tr>
            <td style="width: 30%"><h2><b>Indice de ingresos</b></h2></td>
            <td style="width: 30%">
                <label for="fecha">Año: </label>
                <input type="text" class="date-picker-year" id="fecha2" name="fecha2" value="<?php echo $anio;?>" />
            </td>
            <td style="width: 10%">
                <?php if($hayIVA){ ?>
                <input type="checkbox" name="iva2" id="iva2" value="IVA" checked>Incluye I.V.A
                <?php }else{ ?>
                <input type="checkbox" name="iva2" id="iva2" value="IVA">Incluye I.V.A
                <?php } ?>
            </td>
            <td style="width: 20%">
                <input type="button" class="button" onclick="recargarIndices();" value="Recalcular" style="margin-left: 85%;"/>
            </td>
            <td></td>
        </tr>
    </table>
</form>
<br/>
<table style="background-color: #f3f3f3; width: 100%;">
    <tr>
        <td style="width: 55%;">
            <div id="graficaIndices" name="graficaIndices"></div>
        </td>
        <td style="background-color: white; width: 3%;"></td>
        <td style="width: 42%;" valign="top">
            <table style="width: 100%;">
                <tr>
                    <td><h2>Facturación</h2></td>
                    <td><h2>Mes</h2></td>
                    <td><h2>Pesos</h2></td>
                </tr>
                <tr>
                    <td><h3>Mayor nivel de facturación</h3></td>
                    <td><h3><?php echo $mesMayor?></h3></td>
                    <td><h3><?php echo "$".number_format($resultados[$mesMayor],2); ?></h3></td>
                </tr>
                <tr>
                    <td><h3>Menor nivel de facturación</h3></td>
                    <td><h3><?php echo $mesMenor?></h3></td>
                    <td><h3><?php echo "$".number_format($resultados[$mesMenor],2); ?></h3></td>
                </tr>
                <tr>
                    <td><h3>Facturación del año</h3></td>
                    <td><h3>N/A</h3></td>
                    <td><h3><?php echo "$".number_format($totalAnio,2); ?></h3></td>
                </tr>
                <tr>
                    <td><h3>Facturación del mes anterior</h3></td>
                    <td><h3><?php echo strtolower(substr($catalogo2->formatoFechaReportes($mesAnteriorText),5)); ?></h3></td>
                    <td><h3><?php echo "$".number_format($facturadoMesAnterior,2); ?></h3></td>
                </tr>
                <tr>
                    <td><h3>Facturación del día</h3></td>
                    <td><h3>N/A</h3></td>
                    <td><h3><?php echo "$".number_format($facturadoDia,2); ?></h3></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<script>
$(function () {
    $('#graficaIndices').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'column'
        },
        title: {
            text: 'Indices Facturación'
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
            name: 'Indice de Facturación',
            colorByPoint: true,
            data: [
                <?php echo $chartJQuery?>
            ]
        }]
    });
});
</script>