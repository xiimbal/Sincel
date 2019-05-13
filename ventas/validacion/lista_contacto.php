<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

if (!isset($_POST['id'])) {
    header("Location: ../../index.php");
}
    
$ClaveCentro = $_POST['id'];


include_once("../../WEB-INF/Classes/Contacto.class.php");
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
    </head>
    <body>        
        <fieldset>
            <legend>Contactos</legend>
            <?php if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 25)){ ?>
            <img class="imagenMouse" src="<?php echo $back; ?>resources/images/add.png" title="Nuevo cliente" onclick='cambiarContenidoValidaciones("contacto2", "<?php echo $back; ?>ventas/validacion/alta_contacto.php?Nuevo=true&id=<?php echo $_POST['id'] ?>", $("#idTicket").val(), null, true);
                return false;' style="float: right; cursor: pointer;" /> 
            <?php } ?>
            <?php        
            $centro = new Contacto();                    
            $query = $centro->getRegistrosLocalidadCliente($ClaveCentro);
            if(mysql_num_rows($query) > 0){
                echo '<table class="filtro" style="min-width: 100%;">';
                echo '<thead>
                        <tr>                                            
                            <td>Clave domicilio</td>
                            <td>Nombre</td>
                            <td>Correo Electrónico</td>
                            <td>Teléfono</td>
                            <td>Celular</td>
                            <td>Tipo contacto</td>
                            <td></td>
                        </tr>
                    </thead>';
                while ($rs = mysql_fetch_array($query)) {
                    echo "<tr>";            
                    echo "<td align='center' scope='row'>" . $rs['ClaveEspecialContacto'] . "</td>";
                    echo "<td align='center' scope='row'>" . $rs['Nombre'] . "</td>";
                    echo "<td align='center' scope='row'>" . $rs['CorreoElectronico'] . "</td>";
                    echo "<td align='center' scope='row'>" . $rs['Telefono'] . "</td>";
                    echo "<td align='center' scope='row'>" . $rs['Celular'] . "</td>";
                    echo "<td align='center' scope='row'>" . $rs['TipoContacto'] . "</td>";
                    echo "<td align='center' scope='row'>"; 
                    echo "<a href='#' onclick='cambiarContenidoValidaciones(\"contacto2\",\"".$back."ventas/validacion/alta_contacto.php?&id=".$_POST['id']."\", $(\"#idTicket\").val(),\"" . $rs['IdContacto'] . "\",false); return false;'>";
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
            }else{
                echo "<br/><br/>No se encontraron contactos";
            }
            ?>
            <br/><br/><br/>
        </fieldset>        
    </body>
</html>