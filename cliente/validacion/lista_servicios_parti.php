<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../../WEB-INF/Classes/ServicioIM.class.php");
include_once("../../WEB-INF/Classes/ServicioFA.class.php");
include_once("../../WEB-INF/Classes/ServicioGIM.class.php");
include_once("../../WEB-INF/Classes/ServicioGFA.class.php");
include_once("../../WEB-INF/Classes/ServiciosVE.class.php");
include_once("../../WEB-INF/Classes/Parametros.class.php");

$ClaveAnexo = $_POST['id'];
$CC = $_POST['idTicket'];/*Aprovechamos el parametro post idticket para recibir la clave del centro de costo*/

//$liga = $_SESSION['liga']."/Operacion/Operacion/AnexoTecnico/AltaServicioArrendamiento.aspx?Clave=$CC&TipoCliente=CC&ClaveAnexoTecnico=$ClaveAnexo&Vista=Modificar&uguid=".$_SESSION['user'];
$servicio = new ServicioIM();
$serviciofa = new ServicioFA();
$serviciogim = new KServicioGIM();
$serviciogfa = new ServicioGFA();
$serviciosve = new ServiciosVE();

include_once("../../WEB-INF/Classes/PermisosSubMenu.class.php");
    
$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/mis_clientes.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$parametros = new Parametros();
$mostrarContadores = true;
if($parametros->getRegistroById("13") && $parametros->getValor() == "0"){
    $mostrarContadores = false;
}
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
            <legend>Servicios</legend>
            <!--<a href="<?php //echo $liga; ?>" target="_blank" >Edita los servicios</a>-->            
                <div id="cargando_servicioim" style="width:80%; margin-left: 50%; display: none; ">
                    <img src="../resources/images/cargando.gif"/>                          
                </div>
                <div id="div_servicioim">
                    <?php if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 15)){ ?>
                    <img class="imagenMouse" src="../resources/images/add.png" title="Nuevo servicio" 
                    onclick='cambiarContenidoServicio("div_servicioim","cliente/validacion/alta_servicio_im.php?CC=<?php echo $CC; ?>&Anexo=<?php echo $ClaveAnexo; ?>",
                                true,"<?php echo $ClaveAnexo; ?>",null);' style="float: right; cursor: pointer;" />
                    <?php } ?>
                    <div id="mensaje_im"></div>
                    <table class="filtro" style="min-width: 100%;">
                        <thead>
                            <tr>
                                <td></td>
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
                                <td></td>
                            </tr>
                        </thead>                    
                        <?php
                            $result = $servicio->getServiciosByAnexo($ClaveAnexo);
                            //$contador = 0;
                            while($rs = mysql_fetch_array($result)){
                                echo "<tr title='Usuario que modifico: ".$rs['UsuarioUltimaModificacion']." Fecha de modificación: ".$rs['FechaUltimaModificacion']."'>"; 
                                echo "<td align='center' scope='row'>
                                    <input type='radio' id='check_servicioim_".$rs['IdKServicioIM']."' name='check_servicio' 
                                    onclick='$(\"#mensaje_equipos\").empty();";
                                echo "cargarDependencia(\"equipos_p2\",\"../cliente/validacion/lista_equiposServicio.php\",\"".$rs['IdKServicioIM']."\",\"check_servicioim_".$rs['IdKServicioIM']."\",\"im\");";
                                echo "'/></td>";
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
                                echo "<td align='center' scope='row'>"; 
                                echo "<a href='#' 
                                        onclick='cambiarContenidoServicio(\"div_servicioim\",\"cliente/validacion/alta_servicio_im.php?prefijo=IM&CC=$CC&Anexo=$ClaveAnexo\",
                                        false,\"$ClaveAnexo;\",\"".$rs['IdKServicioIM']."\"); return false;'>";
                                if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],16)){                            
                                    echo "<img src=\"../resources/images/Modify.png\"/>"; 
                                }else{
                                    echo "<img src=\"../resources/images/Textpreview.png\"/>"; 
                                }
                                echo "</a></td>";
                                echo "<td align='center' scope='row'>"; 
                                if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],17)){
                                    echo "<a href='#' onclick='eliminarServicioIM(\"".$rs['IdKServicioIM']."\",\"servicios_p2\",\"lista_servicios_parti.php\",\"$ClaveAnexo\",\"$CC\"); return false;'><img src=\"../resources/images/Erase.png\"/></a>"; 
                                }
                                echo "</td>";
                                echo "</tr>";                            
                            }
                        ?>
                        <?php
                            $result = $serviciofa->getServiciosByAnexo($ClaveAnexo);
                            //$contador = 0;
                            while($rs = mysql_fetch_array($result)){
                                echo "<tr title='Usuario que modifico: ".$rs['UsuarioUltimaModificacion']." Fecha de modificación: ".$rs['FechaUltimaModificacion']."'>"; 
                                echo "<td align='center' scope='row'>
                                    <input type='radio' id='check_serviciofa_".$rs['IdKServicioFA']."' name='check_servicio' 
                                    onclick='$(\"#mensaje_equipos\").empty();"; 
                                echo "cargarDependencia(\"equipos_p2\",\"../cliente/validacion/lista_equiposServicio.php\",\"".$rs['IdKServicioFA']."\",\"check_serviciofa_".$rs['IdKServicioFA']."\",\"fa\");";
                                echo "'/></td>";
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
                                echo "<td align='center' scope='row'>"; 
                                echo "<a href='#' 
                                        onclick='cambiarContenidoServicio(\"div_servicioim\",\"cliente/validacion/alta_servicio_im.php?prefijo=FA&CC=$CC&Anexo=$ClaveAnexo\",
                                        false,\"$ClaveAnexo;\",\"".$rs['IdKServicioFA']."\"); return false;'>";
                                if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],16)){                            
                                    echo "<img src=\"../resources/images/Modify.png\"/>"; 
                                }else{
                                    echo "<img src=\"../resources/images/Textpreview.png\"/>"; 
                                }
                                echo "</a></td>";
                                echo "<td align='center' scope='row'>"; 
                                if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],17)){
                                    echo "<a href='#' onclick='eliminarServicioFA(\"".$rs['IdKServicioFA']."\",\"servicios_p2\",\"lista_servicios_parti.php\",\"$ClaveAnexo\",\"$CC\"); return false;'><img src=\"../resources/images/Erase.png\"/></a>"; 
                                }
                                echo "</td>";
                                echo "</tr>";                            
                            }
                        ?>
                        <?php
                            $result = $serviciogim->getServiciosByAnexo($ClaveAnexo);
                            //$contador = 0;
                            while($rs = mysql_fetch_array($result)){
                                echo "<tr title='Usuario que modifico: ".$rs['UsuarioUltimaModificacion']." Fecha de modificación: ".$rs['FechaUltimaModificacion']."'>"; 
                                echo "<td align='center' scope='row'>
                                    <input type='radio' id='check_serviciogim_".$rs['IdKServicioGIM']."' name='check_servicio' 
                                    onclick='$(\"#mensaje_equipos\").empty();"; 
                                echo "cargarDependencia(\"equipos_p2\",\"../cliente/validacion/lista_equiposServicio.php\",\"".$rs['IdKServicioGIM']."\",\"check_serviciogim_".$rs['IdKServicioGIM']."\",\"gim\");";
                                echo "'/></td>";
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
                                echo "<td align='center' scope='row'>"; 
                                echo "<a href='#' 
                                        onclick='cambiarContenidoServicio(\"div_servicioim\",\"cliente/validacion/alta_servicio_im.php?prefijo=GIM&&CC=$CC&Anexo=$ClaveAnexo\",
                                        false,\"$ClaveAnexo;\",\"".$rs['IdKServicioGIM']."\"); return false;'>";
                                if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],16)){                            
                                    echo "<img src=\"../resources/images/Modify.png\"/>"; 
                                }else{
                                    echo "<img src=\"../resources/images/Textpreview.png\"/>"; 
                                }
                                echo "</a></td>";
                                echo "<td align='center' scope='row'>"; 
                                if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],17)){
                                    echo "<a href='#' onclick='eliminarServicioGIM(\"".$rs['IdKServicioGIM']."\",\"servicios_p2\",\"lista_servicios_parti.php\",\"$ClaveAnexo\",\"$CC\"); return false;'><img src=\"../resources/images/Erase.png\"/></a>"; 
                                }
                                echo "</td>";
                                echo "</tr>";                            
                            }
                        ?>
                        <?php
                            $result = $serviciogfa->getServiciosByAnexo($ClaveAnexo);
                            //$contador = 0;
                            while($rs = mysql_fetch_array($result)){
                                echo "<tr title='Usuario que modifico: ".$rs['UsuarioUltimaModificacion']." Fecha de modificación: ".$rs['FechaUltimaModificacion']."'>";
                                echo "<td align='center' scope='row'>
                                    <input type='radio' id='check_serviciogfa_".$rs['IdKServicioGFA']."' name='check_servicio' 
                                    onclick='$(\"#mensaje_equipos\").empty();"; 
                                echo "cargarDependencia(\"equipos_p2\",\"../cliente/validacion/lista_equiposServicio.php\",\"".$rs['IdKServicioGFA']."\",\"check_serviciogfa_".$rs['IdKServicioGFA']."\",\"gfa\");";
                                echo"'/></td>";
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
                                echo "<td align='center' scope='row'>"; 
                                echo "<a href='#' 
                                        onclick='cambiarContenidoServicio(\"div_servicioim\",\"cliente/validacion/alta_servicio_im.php?prefijo=GFA&&CC=$CC&Anexo=$ClaveAnexo\",
                                        false,\"$ClaveAnexo;\",\"".$rs['IdKServicioGFA']."\"); return false;'>";
                                if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],16)){                            
                                    echo "<img src=\"../resources/images/Modify.png\"/>"; 
                                }else{
                                    echo "<img src=\"../resources/images/Textpreview.png\"/>"; 
                                }
                                echo "</a></td>";
                                echo "<td align='center' scope='row'>"; 
                                if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],17)){
                                    echo "<a href='#' onclick='eliminarServicioGFA(\"".$rs['IdKServicioGFA']."\",\"servicios_p2\",\"lista_servicios_parti.php\",\"$ClaveAnexo\",\"$CC\"); return false;'><img src=\"../resources/images/Erase.png\"/></a>"; 
                                }
                                echo "</td>";
                                echo "</tr>";                            
                            }
                        ?>
                    </table>
                    <br/><br/>
                    <?php 
                        echo "<a href='#' 
                            onclick='cambiarContenidoServicio(\"div_servicioim\",\"cliente/validacion/serviciosEspeciales.php?prefijo=GFA&&CC=$CC&Anexo=$ClaveAnexo\",
                            false,\"$ClaveAnexo;\",\"".$rs['IdKServicioGFA']."\"); return false;'><img src=\"../resources/images/Modify.png\" style='float: right; cursor: pointer;'/>"; 
                        echo "</a></td>";
                    
                    ?>
                    <table class="filtro" style="min-width: 100%;">
                        <thead>
                            <tr>
                                <td>Nombre Servicio</td>
                                <td>Precio</td>
                                <td>Tarifa</td>
                                <td>Tipo</td>
                                <td>Estado</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $serviciosve->setIdAnexoClienteCC($ClaveAnexo);
                                $result = $serviciosve->getServiciosVEByAnexo();
                                while($rs = mysql_fetch_array($result)){
                                    echo "<tr>";
                                    echo "<td align='center'>".$rs['NombreServicio']."</td>";
                                    echo "<td align='center'>".$rs['PrecioUnitario']."</td>";
                                    echo "<td align='center'>".$rs['Tarifa']."</td>";
                                    if((int)$rs['Tipo'] == 1){
                                        echo "<td align='center'>Variable</td>";
                                    }else{
                                        echo "<td align='center'>Fijo</td>";
                                    }
                                    echo "<td align='center'>".$rs['Nombre']."</td>";
                                    echo "</tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </div>          
                <input type="hidden" name="independiente" id="independiente" value="true"/>
        </fieldset>        
    </body>
</html>