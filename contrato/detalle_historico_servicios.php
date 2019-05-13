<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

if(!isset($_POST['anexo']) || $_POST['anexo'] == "" || $_POST['anexo'] == null){
    echo "<h1>Error: no se ha recibido la clave del anexo</h1>";
}

include_once("../WEB-INF/Classes/ServicioIM.class.php");
include_once("../WEB-INF/Classes/ServicioFA.class.php");
include_once("../WEB-INF/Classes/ServicioGIM.class.php");
include_once("../WEB-INF/Classes/ServicioGFA.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");

$ClaveAnexo = $_POST['anexo'];

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
        <script type="text/javascript" language="javascript" src="resources/js/paginas/detalle_historico_servicios.js"></script>
    </head>
    <body>
        <table id="tHistorico" name="tHistorico" class="filtro" style="min-width: 100%;">
            <thead>
                            <tr>
                                <td>Id</td>
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
                                <td></td>
                            </tr>
                        </thead>
                        <?php
                        $resultim = $servicioim->getServiciosByAnexo($ClaveAnexo);
                        
                        while($rs = mysql_fetch_array($resultim)){
                                echo "<tr title='Usuario que modifico: ".$rs['UsuarioUltimaModificacion']." Fecha de modificación: ".$rs['FechaUltimaModificacion']."'>"; 
                                echo "<td align='center' scope='row'>" . $rs['IdKServicioIM'] . "</td>";
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
                                echo "<td onclick='detalle(".$rs['IdKServicioIM'].",\"im\");' align='center' scope='row'>Ver histórico</td>";
                                echo "</tr>";                            
                            }
                        
                        $resultfa = $serviciofa->getServiciosByAnexo($ClaveAnexo);
                        
                        while($rs = mysql_fetch_array($resultfa)){
                                echo "<tr title='Usuario que modifico: ".$rs['UsuarioUltimaModificacion']." Fecha de modificación: ".$rs['FechaUltimaModificacion']."'>"; 
                                echo "<td align='center' scope='row'>" . $rs['IdKServicioFA'] . "</td>";
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
                                echo "<td onclick='detalle(".$rs['IdKServicioFA'].",\"fa\");' align='center' scope='row'>Ver histórico</td>"; 
                                echo "</tr>";                            
                            }
                        
                        $resultgim = $serviciogim->getServiciosByAnexo($ClaveAnexo);
                        
                        while($rs = mysql_fetch_array($resultgim)){
                                echo "<tr title='Usuario que modifico: ".$rs['UsuarioUltimaModificacion']." Fecha de modificación: ".$rs['FechaUltimaModificacion']."'>"; 
                                echo "<td align='center' scope='row'>" . $rs['IdKServicioGIM'] . "</td>";
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
                                echo "<td onclick='detalle(".$rs['IdKServicioGIM'].",\"gim\");' align='center' scope='row'>Ver histórico</td>"; 
                                echo "</tr>";                            
                            }
                        
                        
                        $resultgfa = $serviciogfa->getServiciosByAnexo($ClaveAnexo);
                        
                        while($rs = mysql_fetch_array($resultgfa)){
                                echo "<tr title='Usuario que modifico: ".$rs['UsuarioUltimaModificacion']." Fecha de modificación: ".$rs['FechaUltimaModificacion']."'>";
                                echo "<td align='center' scope='row'>" . $rs['IdKServicioGFA'] . "</td>";
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
                                echo "<td onclick='detalle(".$rs['IdKServicioGFA'].",\"gfa\");' align='center' scope='row'>Ver histórico</td>"; 
                                echo "</tr>";                            
                            }
                        ?>
        </table>
    </body>
</html>
