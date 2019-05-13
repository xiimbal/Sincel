<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

if (!isset($_POST['id']) || !isset($_GET['Nombre']) || !isset($_GET['Clave'])) {
    header("Location: ../../index.php");
}

$NombreGET = str_replace(" ", "__XX__",  $_GET['Nombre']);

include_once("../../WEB-INF/Classes/CentroCosto.class.php");
include_once("../../WEB-INF/Classes/PermisosSubMenu.class.php");
    
$back = "";
$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/mis_clientes.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="<?php echo $back; ?>resources/js/paginas/alta_validacion.js"></script>        
        <script type="text/javascript" language="javascript" src="<?php echo $back; ?>resources/js/paginas/validacion/lista_localidad.js"></script>        
    </head>
    <body>        
        <fieldset>
            <legend>Localidad</legend>
            <?php if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 25)){ ?>
            <img class="imagenMouse" src="<?php echo $back; ?>resources/images/add.png" title="Nueva localidad" onclick='cambiarContenidoValidaciones("localidad2", "<?php echo $back; ?>ventas/validacion/alta_localidad.php?Nuevo=true&idCliente=<?php echo $_POST['id']; ?>&Nombre=<?php echo $NombreGET; ?>&Clave=<?php echo $_GET['Clave']; ?>", $("#idTicket").val(), null, true);
                return false;' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <?php        
            $centro = new CentroCosto();            
            $query = $centro->getRegistroValidacion($_POST['id']);          
            if(mysql_num_rows($query) > 0){
                echo '<table class="filtro" style="min-width: 100%;">';
                echo '<thead>
                        <tr>
                            <td></td>
                            <td>Clave</td>
                            <td>Nombre</td>                        
                            <td></td>
                        </tr>
                    </thead>';
                while ($rs = mysql_fetch_array($query)) {
                    echo "<tr>";            
                    echo "<td align='center' scope='row'><input type='radio' id='check_cc_".$rs['ClaveCentroCosto']."' name='check_cc' 
                        onclick='";
                    if(isset($_POST['idTicket']) && $_POST['idTicket']!=null){
                        echo "actualizarTicket(\"".$_POST['idTicket']."\",\"".$rs['ClaveCentroCosto']."\",\"centro_costo\");";
                    }
                    echo "$(\"#clave_localidad1\").val(\"".$rs['ClaveCentroCosto']."\");";
                    echo "$(\"#contacto2\").empty();";
                    
                    echo "cargarDependencia(\"contacto2\",\"".$back."ventas/validacion/lista_contacto.php\",\"".$rs['ClaveCentroCosto']."\",\"check_cc_".$rs['ClaveCentroCosto']."\",\"".$_POST["idTicket"]."\");'/></td>";
                    echo "<td align='center' scope='row'>" . $rs['ClaveCentroCosto'] . "</td>";
                    echo "<td align='center' scope='row'>" . $rs['Nombre'] . "</td>";
                    echo "<td align='center' scope='row'>"; 
                    echo "<a href='#' onclick='cambiarContenidoValidaciones(\"localidad2\",\"".$back."ventas/validacion/alta_localidad.php?idCliente=".$_POST['id']."&Nombre=".$NombreGET."&Clave=".$_GET['Clave']."\", $(\"#idTicket\").val(),\"" . $rs['ClaveCentroCosto'] . "\",false); return false;'>";
                    if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],26)){                            
                        echo "<img src=\"".$back."resources/images/Modify.png\"/>"; 
                    }else{
                        echo "<img src=\"".$back."resources/images/Textpreview.png\"/>"; 
                    }
                    echo "</a></td>";
                    echo "</tr>";
                }
                echo '';
                echo '</table>';
                echo '<input type="checkbox" id="filtro_localidad" name="filtro_localidad" style="margin-left: 75%;"/>Utilizar filtro de localidad';
            }else{
                echo "<br/><br/>No se encontraron localidades";
            }
            ?>            
            <br/><br/><br/>
        </fieldset>        
    </body>
</html>