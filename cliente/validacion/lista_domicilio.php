<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

if (!isset($_POST['id'])) {
    header("Location: ../../index.php");
}
  
$ClaveCC = $_POST['id'];
$ClaveCliente = $_GET['idCliente'];

include_once("../../WEB-INF/Classes/Localidad.class.php");
include_once("../../WEB-INF/Classes/PermisosSubMenu.class.php");
    
$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/mis_clientes.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/alta_validacion.js"></script>        
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/validacion/lista_domicilio.js"></script>        
    </head>
    <body>        
        <fieldset>
            <?php
                $centro = new Localidad();        
                /* Obtenemos los posibles equipos asociados al ticket */
                $query = $centro->getRegistoValidacion($ClaveCC, null);            
                $numero_domicilios = mysql_num_rows($query);
            ?>
            <legend>Domicilio de localidad</legend>
            <?php if($numero_domicilios == 0 && $permisos_grid->getAlta()){?>
                <img class="imagenMouse" src="../resources/images/add.png" title="Nuevo domicilio" onclick='cambiarContenidoValidaciones("domicilio2", "../cliente/validacion/alta_domicilio.php?Nuevo=true&id=<?php echo $ClaveCC; ?>&idCliente=<?php echo $ClaveCliente ?>", $("#idTicket").val(), null, true);
                    return false;' style="float: right; cursor: pointer;" />  
            <?php                                        
            }
            echo '<table class="filtro" style="min-width: 100%;">';
            echo '<thead>
                    <tr>        
                        <td></td>
                        <td>CC</td>
                        <td>Calle y número</td>
                        <td>Colonia</td>
                        <td>Ciudad</td>
                        <td>Estado</td>
                        <td></td>
                    </tr>
                </thead>';
            while ($rs = mysql_fetch_array($query)) {
                echo "<tr>";  
                echo "<td align='center' scope='row'><input type='radio' id='check_domicilio_".$rs['ClaveEspecialDomicilio']."' name='check_domicilio' 
                    onclick='cargarDependencia(\"contacto2\",\"../cliente/validacion/lista_contacto.php\",\"".$rs['ClaveEspecialDomicilio']."\",\"check_domicilio_".$rs['ClaveEspecialDomicilio']."\",\"".$_POST["idTicket"]."\"); '/></td>";
                echo "<td align='center' scope='row'>" . $rs['ClaveEspecialDomicilio'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['Calle'] . " Ext: ".$rs['NoExterior']." Int:".$rs['NoInterior']."</td>";
                echo "<td align='center' scope='row'>" . $rs['Colonia'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['Ciudad'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['Estado'] . "</td>";
                echo "<td align='center' scope='row'>"; 
                echo "<a href='#' onclick='cambiarContenidoValidaciones(\"domicilio2\",\"../cliente/validacion/alta_domicilio.php?id=".$ClaveCC."&idCliente=".$ClaveCliente."\", $(\"#idTicket\").val(),\"" . $rs['IdDomicilio'] . "\",false); return false;'>";
                if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],16)){                            
                    echo "<img src=\"../resources/images/Modify.png\"/>"; 
                }else{
                    echo "<img src=\"../resources/images/Textpreview.png\"/>"; 
                }
                echo "</a></td>";
                echo "</tr>";
            }
            echo '';
            echo '</table><br/>';
            if($numero_domicilios == 0){
                echo "<a href='#' onclick='copiarDomicilioDeClienteADomicilio(\"".$ClaveCliente."\",\"".$ClaveCC."\"); return false;'>Copiar domicilio de cliente</a>";
            }
            //}
            ?>
            <br/><br/><br/>
        </fieldset>        
    </body>
</html>