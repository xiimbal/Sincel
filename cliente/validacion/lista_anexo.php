<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

if (!isset($_POST['id'])) {
    header("Location: ../../index.php");
}
   
$noContrato = $_POST['id'];
$ClaveCliente = $_GET['idCliente'];

include_once("../../WEB-INF/Classes/Anexo.class.php");
include_once("../../WEB-INF/Classes/PermisosSubMenu.class.php");
    
$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/mis_clientes.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/alta_validacion.js"></script>        
    </head>
    <body>        
        <fieldset>
            <legend>Anexo</legend>
            <?php if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 15)){ ?>
            <img class="imagenMouse" src="../resources/images/add.png" title="Nuevo anexo" onclick='cambiarContenidoValidaciones("anexo2", "../cliente/validacion/alta_anexo.php?Nuevo=true&id=<?php echo $noContrato; ?>&idCliente=<?php echo $ClaveCliente; ?>", $("#idTicket").val(), null, true);
                return false;' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <?php                
                $centro = new Anexo();                        
                $query = $centro->getRegistroValidacionSoloAnexo($noContrato);
                if(mysql_num_rows($query) > 0){
                    echo '<table class="filtro" style="min-width: 100%;">';
                    echo '<thead>
                            <tr>     
                                <td></td>
                                <td>Clave</td>
                                <td>Fecha elaboración</td>
                                <td>Fecha Corte</td>
                                <td>No. contrato</td>                           
                                <td></td>
                            </tr>
                        </thead>';
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>"; 
                        echo "<td align='center' scope='row'>
                            <input type='radio' id='check_anexo_".$rs['ClaveAnexoTecnico']."' name='check_anexo' 
                            onclick='$(\"#servicios_p2\").empty(); $(\"#servicios_g2\").empty(); $(\"#equipos_p2\").empty(); var cc = $(\"#clave_localidad1\").val(); var c = $(\"#clave_cliente1\").val();";                        
                            echo "cargarDependencia(\"servicios_p2\",\"../cliente/validacion/lista_servicios_parti.php\",\"".$rs['ClaveAnexoTecnico']."\",null,cc);";
                            //echo "cargarDependencia(\"servicios_g2\",\"../cliente/validacion/lista_servicios_im.php\",\"".$rs['ClaveAnexoTecnico']."\",null,cc);";
                            echo "'/>
                            </td>";
                        echo "<td align='center' scope='row'>" . $rs['ClaveAnexoTecnico'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['FechaElaboracion'] . "</td>";
                        echo "<td align='center' scope='row'>Día " . $rs['DiaCorte'] . " de cada mes</td>";                    
                        echo "<td align='center' scope='row'>" . $rs['NoContrato'] . "</td>";                    
                        echo "<td align='center' scope='row'>"; 
                        echo "<a href='#' onclick='cambiarContenidoValidaciones(\"anexo2\",\"../cliente/validacion/alta_anexo.php?id=$noContrato&idCliente=$ClaveCliente\", $(\"#idTicket\").val(),\"" . $rs['ClaveAnexoTecnico'] . "\",false); return false;'>";
                        if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],16)){                            
                            echo "<img src=\"../resources/images/Modify.png\"/>"; 
                        }else{
                            echo "<img src=\"../resources/images/Textpreview.png\"/>"; 
                        }
                        echo "</a></td>";
                        echo "</tr>";
                    }
                    echo '';
                    echo '</table>';
                }else{
                    echo "<br/><br/>No se encontraron anexos";
                }
                ?>
                <br/><br/><br/>
        </fieldset>        
    </body>
</html>