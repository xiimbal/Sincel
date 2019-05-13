<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/ContactoEmpleado.class.php");
$pagina_lista = "catalogos/lista_contacto_empleado.php";

$IdTipoContacto = '';
$Nombre = '';
$Descripcion = '';
$Activo = "checked='checked'";


?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_contacto_empleado.js"></script>        
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id'])) {                
                $obj = new ContactoEmpleado();
                if($obj->getRegistroById($_POST['id'])){
                    $IdFormaContacto = $obj->getIdFormaContacto();
                    $Nombre = $obj->getNombre();
                    $Descripcion = $obj->getDescripcion();
                    
                    if ($obj->getActivo() == "0") {
                        $Activo = "";
                    }
                }                                
            }
            ?>
            <form id="formFormaContacto" name="formFormaContacto" action="/" method="POST">
                <table style="min-width: 70%">
                    <tr>
                        <td><label for="nombre">Nombre</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="nombre" name="nombre" value="<?php echo $Nombre; ?>"/></td>
                        <td><label for="descripcion">Descripci&oacute;n</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="descripcion" name="descripcion" value="<?php echo $Descripcion; ?>"/></td>                        
                    </tr>    
                    <tr>
                        <td><input type="checkbox" name="activo" id="activo" <?php echo $Activo; ?>/>Activo</td>
                    </tr>
                </table>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                return false;"/>
                <?php
                    echo "<input type='hidden' id='id' name='id' value='" . $IdFormaContacto . "'/> ";
                ?>
            </form>
        </div>
    </body>
</html>
