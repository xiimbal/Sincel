<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

if(!isset($_GET['k']) || $_GET['k'] == "" || $_GET['k'] == null){
    echo "<h1>Error: no se ha recibido la clave del servicio</h1>";
}

if(!isset($_GET['p']) || $_GET['p'] == "" || $_GET['p'] == null){
    echo "<h1>Error: no se ha recibido el tipo de servicio</h1>";
}

include_once("../WEB-INF/Classes/ServicioIM.class.php");
include_once("../WEB-INF/Classes/ServicioFA.class.php");
include_once("../WEB-INF/Classes/ServicioGIM.class.php");
include_once("../WEB-INF/Classes/ServicioGFA.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");



$servicioim = new ServicioIM();
$serviciofa = new ServicioFA();
$serviciogim = new KServicioGIM();
$serviciogfa = new ServicioGFA();

$parametros = new Parametros();
$mostrarContadores = true;
if($parametros->getRegistroById("13") && $parametros->getValor() == "0"){
    $mostrarContadores = false;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="../../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
        <script src="../../resources/js/jquery/jquery-1.11.3.min.js"></script>
        <script src="../../resources/js/jquery/jquery-ui.min.js"></script>  
        <script type="text/javascript" language="javascript" src="../../resources/media/js/jquery.dataTables.js"></script>
        <script type="text/javascript" language="javascript" src="../../resources/media/js/TableTools.min.js"></script>
        <link href="../../resources/css/table/demo_page.css" rel="stylesheet" type="text/css">
        <link href="../../resources/css/table/demo_table_jui.css" rel="stylesheet" type="text/css">
        <link href="../../resources/css/table/TableTools.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" language="javascript" src="../../resources/js/paginas/detalle_historico_servicios.js"></script>
    </head>
    <body>
        <table id="tHistoricoDetalle" name="tHistoricoDetalle" class="filtro" style="min-width: 100%;">
            <thead>
                            <tr>
                                <td>Arrendamiento</td>
                                <td>Renta Mensual</td>
                                <?php if($mostrarContadores){ 
                                    echo "
                                        <td>Incluidas BN</td>
                                        <td>Incluidas Color</td>
                                        <td>Costo Excedentes BN</td>
                                        <td>Costo Excedentes Color</td>
                                        <td>Costo Procesadas BN</td>
                                        <td>Costo Procesadas Color</td>"; 
                                } ?>
                                <td>Fecha</td>
                                <td>Usuario</td>
                            </tr>
                        </thead>
                        <?php
                        
                        switch($_GET['p']){
                            case 'im':
                                $resultim = $servicioim->getHistoricoServicios($_GET['k']);
                        
                        while($rs = mysql_fetch_array($resultim)){
                                echo "<tr title='Usuario que modifico: ".$rs['UsuarioUltimaModificacion']." Fecha de modificación: ".$rs['FechaUltimaModificacion']."'>"; 
                                echo "<td align='center' scope='row'>" . $rs['servicio'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['RentaMensual'] . "</td>";
                                if($mostrarContadores){
                                    echo "<td align='center' scope='row'>" . $rs['PaginasIncluidasBN'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['PaginasIncluidasColor'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['CostoPaginasExcedentesBN'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['CostoPaginasExcedentesColor'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['CostoPaginaProcesadaBN'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['CostoPaginaProcesadaColor'] . "</td>";
                                }
                                echo "<td align='center' scope='row'>" . $rs['FechaUltimaModificacion'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['UsuarioUltimaModificacion'] . "</td>";
                                echo "</tr>";                            
                            }
                                break;
                            case 'fa':
                                $resultfa = $serviciofa->getHistoricoServicios($_GET['k']);
                        
                        while($rs = mysql_fetch_array($resultfa)){
                                echo "<tr title='Usuario que modifico: ".$rs['UsuarioUltimaModificacion']." Fecha de modificación: ".$rs['FechaUltimaModificacion']."'>"; 
                                echo "<td align='center' scope='row'>" . $rs['servicio'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['RentaMensual'] . "</td>";
                                if($mostrarContadores){
                                    echo "<td align='center' scope='row'>" . $rs['MLIncluidosBN'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['MLIncluidosColor'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['CostoMLExcedentesBN'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['CostoMLExcedentesColor'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['CostoMLProcesadosBN'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['CostoMLProcesadosColor'] . "</td>";
                                }
                                echo "<td align='center' scope='row'>" . $rs['FechaUltimaModificacion'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['UsuarioUltimaModificacion'] . "</td>";
                                echo "</tr>";                            
                            }
                                break;
                            case 'gim':
                                $resultgim = $serviciogim->getHistoricoServicios($_GET['k']);
                        
                        while($rs = mysql_fetch_array($resultgim)){
                                echo "<tr title='Usuario que modifico: ".$rs['UsuarioUltimaModificacion']." Fecha de modificación: ".$rs['FechaUltimaModificacion']."'>"; 
                                echo "<td align='center' scope='row'>" . $rs['servicio'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['RentaMensual'] . "</td>";
                                if($mostrarContadores){
                                    echo "<td align='center' scope='row'>" . $rs['PaginasIncluidasBN'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['PaginasIncluidasColor'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['CostoPaginasExcedentesBN'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['CostoPaginasExcedentesColor'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['CostoPaginaProcesadaBN'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['CostoPaginaProcesadaColor'] . "</td>";
                                }
                                echo "<td align='center' scope='row'>" . $rs['FechaUltimaModificacion'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['UsuarioUltimaModificacion'] . "</td>";
                                echo "</tr>";                            
                            }
                                break;
                            case 'gfa':
                                $resultgfa = $serviciogfa->getHistoricoServicios($_GET['k']);
                        
                        while($rs = mysql_fetch_array($resultgfa)){
                                echo "<tr title='Usuario que modifico: ".$rs['UsuarioUltimaModificacion']." Fecha de modificación: ".$rs['FechaUltimaModificacion']."'>";
                                echo "<td align='center' scope='row'>" . $rs['servicio'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['RentaMensual'] . "</td>";
                                if($mostrarContadores){
                                    echo "<td align='center' scope='row'>" . $rs['MLIncluidosBN'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['MLIncluidosColor'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['CostoMLExcedentesBN'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['CostoMLExcedentesColor'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['CostoMLProcesadosBN'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['CostoMLProcesadosColor'] . "</td>";
                                }
                                echo "<td align='center' scope='row'>" . $rs['FechaUltimaModificacion'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['UsuarioUltimaModificacion'] . "</td>";
                                echo "</tr>";                            
                            }
                                break;
                        }
                        
                        ?>
        </table>
    </body>
</html>
