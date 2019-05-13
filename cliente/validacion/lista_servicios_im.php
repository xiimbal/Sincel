<?php
session_start();

include_once("../../WEB-INF/Classes/ServicioGIM.class.php");
include_once("../../WEB-INF/Classes/ServicioGFA.class.php");

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

$ClaveAnexo = $_POST['id'];
$CC = $_POST['idTicket'];/*Aprovechamos el parametro post idticket para recibir la clave del centro de costo*/

//$liga = $_SESSION['liga']."/Operacion/Operacion/AnexoTecnico/AltaServicioArrendamiento.aspx?Clave=$CC&TipoCliente=CC&ClaveAnexoTecnico=$ClaveAnexo&Vista=Modificar&uguid=".$_SESSION['user'];
$servicio = new KServicioGIM();
$serviciofa = new ServicioGFA();

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/alta_validacion.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/validacion/lista_servicio_im.js"></script>
        <style>
            .entrada {max-width: 55px;}
        </style>
    </head>
    <body>        
        <fieldset>
            <legend>Servicios globales</legend>
            <!--<a href="<?php //echo $liga; ?>" target="_blank" >Edita los servicios</a>-->
            <fieldset>
                <legend>Impresoras y Multifuncionales</legend>
                <div id="cargando_servicioim" style="width:80%; margin-left: 50%; display: none; ">
                    <img src="../resources/images/cargando.gif"/>                          
                </div>
                <div id="div_serviciogim">
                    <img class="imagenMouse" src="../resources/images/add.png" title="Nuevo servicio" 
                    onclick='cambiarContenidoServicio("div_serviciogim","cliente/validacion/alta_servicio_im.php?prefijo=GIM&CC=<?php echo $CC; ?>&Anexo=<?php echo $ClaveAnexo; ?>",
                                true,"<?php echo $ClaveAnexo; ?>",null);' style="float: right; cursor: pointer;" />
                    <div id="mensaje_gim"></div>
                    <table class="filtro" style="min-width: 100%;">
                        <thead>
                            <tr>
                                <td></td>
                                <td>Id</td>
                                <td>Arrendamiento</td>
                                <td>Renta Mensual</td>
                                <td>Pág. Incluidas BN</td>
                                <td>Pág. Incluidas Color</td>
                                <td>Costo Pág. Excedentes BN</td>
                                <td>Costo Pág. Excedentes Color</td>
                                <td>Costo Pág. Procesadas BN</td>
                                <td>Costo Pág. Procesadas Color</td>  
                                <td></td>
                                <td></td>
                            </tr>
                        </thead>                    
                        <?php
                            $result = $servicio->getServiciosByAnexo($ClaveAnexo);
                            //$contador = 0;
                            while($rs = mysql_fetch_array($result)){
                                echo "<tr>"; 
                                echo "<td align='center' scope='row'>
                                    <input type='radio' id='check_serviciogim_".$rs['IdKServicioGIM']."' name='check_serviciogim' 
                                    onclick='$(\"#mensaje_equipos\").empty();"; 
                                echo "cargarDependencia(\"equipos_p2\",\"../cliente/validacion/lista_equiposServicio.php\",\"".$rs['IdKServicioGIM']."\",\"check_serviciogim_".$rs['IdKServicioGIM']."\",\"gim\");";
                                echo "'/></td>";
                                echo "<td align='center' scope='row'>" . $rs['IdKServicioGIM'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['servicio'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['RentaMensual'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['PaginasIncluidasBN'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['PaginasIncluidasColor'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['CostoPaginasExcedentesBN'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['CostoPaginasExcedentesColor'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['CostoPaginaProcesadaBN'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['CostoPaginaProcesadaColor'] . "</td>";
                                echo "<td align='center' scope='row'><a href='#' 
                                    onclick='cambiarContenidoServicio(\"div_serviciogim\",\"cliente/validacion/alta_servicio_im.php?prefijo=GIM&&CC=$CC&Anexo=$ClaveAnexo\",
                                        true,\"$ClaveAnexo;\",\"".$rs['IdKServicioGIM']."\"); return false;'>
                                    <img src=\"../resources/images/Apply.png\"/></a></td>";
                                echo "<td align='center' scope='row'><a href='#' onclick='eliminarServicioGIM(\"".$rs['IdKServicioGIM']."\",\"servicios_g2\",\"lista_servicios_im.php\",\"$ClaveAnexo\",\"$CC\"); return false;'><img src=\"../resources/images/Erase.png\"/></a></td>";
                                echo "</tr>";                            
                            }
                        ?>
                    </table>
                </div>                
            </fieldset>
            <fieldset>
                <legend>Formato amplio</legend>
                <div id="div_serviciogfa">
                    <img class="imagenMouse" src="../resources/images/add.png" title="Nuevo servicio" 
                    onclick='cambiarContenidoServicio("div_serviciogfa","cliente/validacion/alta_servicio_im.php?prefijo=GFA&CC=<?php echo $CC; ?>&Anexo=<?php echo $ClaveAnexo; ?>",
                                true,"<?php echo $ClaveAnexo; ?>",null);' style="float: right; cursor: pointer;" />
                    <div id="mensaje_gfa"></div>
                    <table class="filtro" style="min-width: 100%;">
                        <thead>
                            <tr>
                                <td></td>
                                <td>Id</td>
                                <td>Arrendamiento</td>
                                <td>Renta Mensual</td>
                                <td>ML. Incluidos BN</td>
                                <td>ML. Incluidos Color</td>
                                <td>Costo ML. Excedentes BN</td>
                                <td>Costo ML. Excedentes Color</td>
                                <td>Costo ML. Procesadas BN</td>
                                <td>Costo ML. Procesadas Color</td>  
                                <td></td>
                                <td></td>
                            </tr>
                        </thead>
                        <?php
                            $result = $serviciofa->getServiciosByAnexo($ClaveAnexo);
                            //$contador = 0;
                            while($rs = mysql_fetch_array($result)){
                                echo "<tr>"; 
                                echo "<td align='center' scope='row'>
                                    <input type='radio' id='check_serviciogfa_".$rs['IdKServicioGFA']."' name='check_serviciogfa' 
                                    onclick='$(\"#mensaje_equipos\").empty();"; 
                                echo "cargarDependencia(\"equipos_p2\",\"../cliente/validacion/lista_equiposServicio.php\",\"".$rs['IdKServicioGFA']."\",\"check_serviciogfa_".$rs['IdKServicioGFA']."\",\"gfa\");";
                                echo"'/></td>";
                                echo "<td align='center' scope='row'>" . $rs['IdKServicioGFA'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['servicio'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['RentaMensual'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['MLIncluidosBN'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['MLIncluidosColor'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['CostoMLExcedentesBN'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['CostoMLExcedentesColor'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['CostoMLProcesadosBN'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['CostoMLProcesadosColor'] . "</td>";
                                echo "<td align='center' scope='row'><a href='#' 
                                    onclick='cambiarContenidoServicio(\"div_serviciogfa\",\"cliente/validacion/alta_servicio_im.php?prefijo=GFA&&CC=$CC&Anexo=$ClaveAnexo\",
                                        true,\"$ClaveAnexo;\",\"".$rs['IdKServicioGFA']."\"); return false;'>
                                    <img src=\"../resources/images/Apply.png\"/></a></td>";
                                echo "<td align='center' scope='row'><a href='#' onclick='eliminarServicioGFA(\"".$rs['IdKServicioGFA']."\",\"servicios_g2\",\"lista_servicios_im.php\",\"$ClaveAnexo\",\"$CC\"); return false;'><img src=\"../resources/images/Erase.png\"/></a></td>";
                                echo "</tr>";                            
                            }
                        ?>
                    </table>
                </div>                
            </fieldset>
        </fieldset>        
    </body>
</html>