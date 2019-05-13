<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['fecha_inicial']) || !isset($_POST['fecha_final'])) {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/ReporteUso.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$obj = new ReporteUso();
$catalogo = new Catalogo();

$directorio = "upload";
$archivosParaProcesar = $obj->getFilesLOGFromDirectory($directorio);
$obj->procesarArchivosNuevos($archivosParaProcesar);

$order_by = "Impresora";
$fechaInicial = $_POST['fecha_inicial'];
$fechaFinal = $_POST['fecha_final'];

$consulta = "SELECT DISTINCT($order_by) FROM k_reporteuso WHERE Fecha BETWEEN '$fechaInicial' AND '$fechaFinal' ORDER BY $order_by;";
$query = $catalogo->obtenerLista($consulta);
$secciones = array();

$i=0;
while($rs = mysql_fetch_array($query)){
    $secciones[$i] = $rs[$order_by];
    $i++;
}

$consulta = "SELECT SUM(Color) AS color, SUM(BN) AS bn FROM `k_reporteuso` WHERE (Fecha BETWEEN '$fechaInicial' AND '$fechaFinal');";
$query = $catalogo->obtenerLista($consulta);

$total = 0;
while($rs = mysql_fetch_array($query)){
    $total = intval($rs['color']) + intval($rs['bn']);
}

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Reporte de uso</title>
        <style>
            table{
                border-collapse:collapse;
            }            
            .borde{border: 1px solid #000;}
        </style>
        <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
		
        <script type="text/javascript">
            $(function() {                
                <?php
                    $i = 0;
                    foreach ($secciones as $value) {
                        $i++;
                        echo "$('#container".$i."').highcharts({
                            chart: {
                                plotBackgroundColor: null,
                                plotBorderWidth: null,
                                plotShadow: false
                            },
                            title: {
                                text: 'Equipo: $value'
                            },
                            credits: {
                                enabled: false,
                                href: 'http://caomi.com.mx/',
                                text: 'CAOMI',
                                position: {
                                    align: 'right',
                                    x: -20,
                                    verticalAlign: 'bottom',
                                    y: -1
                                }
                            },
                            exporting: {
                                enabled: false
                            },
                            tooltip: {
                                enabled: false,
                                pointFormat: '{series.name}: <b>{ point.percentage }%</b>',
                                percentageDecimals: 1
                            },
                            plotOptions: {
                                pie: {
                                    size:'70%',
                                    allowPointSelect: true,
                                    cursor: 'pointer',
                                    dataLabels: {
                                        enabled: true,
                                        color: '#000000',
                                        connectorColor: '#000000',
                                        formatter: function() {
                                            return '<b>' + this.point.name + '</b>: ' + Highcharts.numberFormat(this.percentage,2) + ' %';
                                        }
                                    }
                                }
                            },
                            series: [{
                                    type: 'pie',
                                    name: 'Porcentaje',
                                    data: [";
                        $consulta = "SELECT $order_by, (SELECT CASE WHEN tipo = 101 THEN 'Copia' WHEN tipo = 102 THEN 'Digitalización' ELSE 'Impresión' END) AS tipo_letra, tipo, SUM(Color) AS color, SUM(BN) AS bn FROM `k_reporteuso` WHERE 
                         $order_by LIKE '%$value' AND (Fecha BETWEEN '$fechaInicial' AND '$fechaFinal') GROUP BY $order_by, Tipo ORDER BY $order_by, Tipo;";
                        //echo $consulta;
                        $query = $catalogo->obtenerLista($consulta);
                        while($rs = mysql_fetch_array($query)){
                            if($rs['tipo'] != "102"){ //Si no es scan
                                $porcentaje = (intval($rs['bn'])*100) / $total;
                                if($porcentaje > 0){
                                    echo "['".$rs['tipo_letra']." BYN', $porcentaje],";
                                }
                                $porcentaje = (intval($rs['color'])*100) / $total;
                                if($porcentaje>0){
                                    echo "['".$rs['tipo_letra']." a color', $porcentaje],";
                                }
                            }else{// Si es scan                                                                
                                $porcentaje = ((intval($rs['bn'])*100) / $total) + ((intval($rs['color'])*100) / $total);
                                echo "['".$rs['tipo_letra']."', $porcentaje],";
                            }
                        }
                        echo "]
                        }]
                        });";
                    }
                ?>                                
            });            
        </script>
    </head>
    <body>
        <script src="../resources/js/highcharts/highcharts.js"></script>
        <script src="../resources/js/highcharts/modules/exporting.js"></script>
        <a href=javascript:window.print(); style="margin-left: 85%;">Imprimir PDF</a>
        <div style="font-weight: bold; font-size: 20px;"><?php echo "Reporte de uso de ".$_POST['fecha_inicial']." a ".$_POST['fecha_final']; ?></div>
        <?php
        $i = 0;
        foreach ($secciones as $value) {
            echo "<table style='width: 90%;'><tr><td>";
            echo "<table>";
            $i++;
            $consulta = "SELECT $order_by, (SELECT CASE WHEN tipo = 101 THEN 'Copia' WHEN tipo = 102 THEN 'Digitalización' ELSE 'Impresión' END) AS tipo_letra, tipo, SUM(Color) AS color, SUM(BN) AS bn FROM `k_reporteuso` WHERE 
             $order_by LIKE '%$value' AND (Fecha BETWEEN '$fechaInicial' AND '$fechaFinal') GROUP BY $order_by, Tipo ORDER BY $order_by, Tipo;";
            //echo $consulta;
            $query = $catalogo->obtenerLista($consulta);
            echo "<br/>";
            echo "<table class='borde'>";
            echo "<tr style='background: #F2DCDB'>";
            echo "<td class='borde'>Tipo de trabajo</td>";
            echo "<td class='borde'>Consumo</td>";
            echo "<td class='borde'>Precio U</td>";
            echo "<td class='borde'>Total ($)</td>";
            echo "</tr>";           
            $facturar = 0;
            while($rs = mysql_fetch_array($query)){
                if($rs['tipo'] != "102"){
                    /* B/N */
                    if($rs['bn'] > 0){
                        echo "<tr style='text-align:right;'>";
                        echo "<td class='borde' style='text-align:left;'>".$rs['tipo_letra']." BYN</td>";
                        echo "<td class='borde'>".number_format($rs['bn'],0)."</td>";
                        /*El costo unitario depende del tipo (Scan, impresion o copia)*/
                        if($rs['tipo'] == "101"){
                             $costou = (float)$_POST['copia_bn'];                    
                        }else if($rs['tipo'] == "103"){
                            $costou = (float)$_POST['impresion_bn']; 
                        }else{
                            $costou = 0;
                        }

                        $total = (($costou) * (intval($rs['bn'])));
                        echo "<td class='borde'>".number_format($costou,2)."</td>";
                        echo "<td class='borde'>".number_format($total,2)."</td>";
                        echo "</tr>";

                        $facturar += $total;
                    }

                    /*Color*/
                    if($rs['color'] > 0){
                        echo "<tr style='text-align:right;'>";
                        echo "<td class='borde' style='text-align:left;'>".$rs['tipo_letra']." a color</td>";
                        echo "<td class='borde'>".number_format($rs['color'],0)."</td>";

                        /*El costo unitario depende del tipo (Scan, impresion o copia)*/
                        if($rs['tipo'] == "101"){
                             $costou = (float)$_POST['copia_color'];                    
                        }else if($rs['tipo'] == "103"){
                            $costou = (float)$_POST['impresion_color'];                                        
                        }else{
                            $costou = 0;
                        }

                        $total = (($costou) * (intval($rs['color'])));
                        echo "<td class='borde'>".number_format($costou,2)."</td>";
                        echo "<td class='borde'>".number_format($total,2)."</td>";
                        echo "</tr>";
                        $facturar += $total;
                    }
                }else{ /*Scan*/
                    $suma = intval($rs['bn']) + intval($rs['color']);
                    if($suma > 0){
                        echo "<tr style='text-align:right;'>";                    
                        echo "<td class='borde' style='text-align:left;'>".$rs['tipo_letra']."</td>";
                        echo "<td class='borde'>".number_format($suma,0)."</td>";
                        /*El costo unitario depende del tipo (Scan, impresion o copia)*/
                        if($rs['tipo'] == "101"){
                             $costou = (float)$_POST['copia_bn'];                    
                        }else if($rs['tipo'] == "102"){
                            $costou = (float)$_POST['scan'];                                        
                        }else if($rs['tipo'] == "103"){
                            $costou = (float)$_POST['impresion_bn']; 
                        }else{
                            $costou = 0;
                        }

                        $total = (($costou) * (intval($suma)));
                        echo "<td class='borde'>".number_format($costou,2)."</td>";
                        echo "<td class='borde'>".number_format($total,2)."</td>";
                        echo "</tr>";
                        $facturar += $total;
                    }
                }
            }
            echo "<tr><td colspan='3' class='borde' style='text-align:right;'>Total a facturar =></td>
                <td style='background: #F2DCDB; text-align:right;'>".number_format($facturar,2)."</td></tr>";
            echo "</table></td>";
            echo '<td><div id="container'.$i.'" style="width: 560px; max-height: 400px;"></div></td></table>';
            if($i%2 == 0){
                echo "<div style='page-break-after: always;'></div>";
            }
        }
        ?>
    </body>
</html>
