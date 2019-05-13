<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

if (!isset($_POST['id'])) {
    header("Location: ../../index.php");
}

$ClaveCliente = $_POST['id'];

include_once("../../WEB-INF/Classes/Contrato.class.php");
include_once("../../WEB-INF/Classes/PermisosSubMenu.class.php");
    
$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/mis_clientes.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/alta_validacion.js"></script>        
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/validacion/lista_contrato.js"></script>        
    </head>
    <body>        
        <fieldset>
            <legend>Contrato</legend>
            <?php if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 15)){ ?>
            <img class="imagenMouse" src="../resources/images/add.png" title="Nuevo cliente" onclick='cambiarContenidoValidaciones("contrato2", "../cliente/validacion/alta_contrato.php?Nuevo=true&idCliente=<?php echo $ClaveCliente; ?>", $("#idTicket").val(), null, true);
                return false;' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <?php                
            $centro = new Contrato();                    
            $query = $centro->getRegistroValidacionVencidos($ClaveCliente);
            if(mysql_num_rows($query) > 0){
                echo '<table class="filtro" style="min-width: 100%;">';
                echo '<thead>
                                                <tr>  
                                                    <td></td>
                                                    <td>No. Contrato</td>
                                                    <td>Fecha inicio</td>
                                                    <td>Fecha termino</td>
                                                    <td>Fecha firma</td>
                                                    <td></td>
                                                </tr>
                                            </thead>';
                while ($rs = mysql_fetch_array($query)) {
                    echo "<tr>";      
                    echo "<td align='center' scope='row'><input type='radio' id='check_contrato_".$rs['NoContrato']."' name='check_contrato' 
                        onclick='$(\"#equipos_p2\").empty(); $(\"#servicios_p2\").empty(); $(\"#servicios_g2\").empty(); cargarDependencia(\"anexo2\",\"../cliente/validacion/lista_anexo.php?idCliente=".$ClaveCliente."\",\"".$rs['NoContrato']."\",\"check_contrato_".$rs['NoContrato']."\",\"".$_POST["idTicket"]."\"); '/></td>";
                    echo "<td align='center' scope='row'>" . $rs['NoContrato'] . "</td>";
                    echo "<td align='center' scope='row'>" . $rs['FechaInicio'] . "</td>";
                    echo "<td align='center' scope='row'>" . $rs['FechaTermino'] . "</td>";
                    echo "<td align='center' scope='row'>" . $rs['FechaFirma'] . "</td>";
                    echo "<td align='center' scope='row'>"; 
                    echo "<a href='#' onclick='cambiarContenidoValidaciones(\"contrato2\",\"../cliente/validacion/alta_contrato.php?idCliente=$ClaveCliente\", $(\"#idTicket\").val(),\"" . $rs['NoContrato'] . "\",false); return false;'>";
                    if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],16)){                            
                        echo "<img src=\"../resources/images/Modify.png\"/>"; 
                    }else{
                        echo "<img src=\"../resources/images/Textpreview.png\"/>"; 
                    }
                    echo "</a></td>";
                    echo "</tr>";
                }
                echo '</table>';
            }else{
                echo "<br/><br/>No se encontraron contratos";
            }
            ?>
            <br/><br/><br/>
        </fieldset>        
    </body>
</html>