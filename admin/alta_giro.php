<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Giro.class.php");
$pagina_lista = "admin/lista_giro.php";

$IdGiro = '';
$Nombre = '';
$Descripcion = '';
$Activo = "checked='checked'";


?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_giro.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id'])) {                
                $obj = new Giro();
                if($obj->getRegistroById($_POST['id'])){
                    $IdGiro = $obj->getIdGiro();
                    $Nombre = $obj->getNombre();                    
                    $Descripcion = $obj->getDescripcion();
                    if ($obj->getActivo() == "0") {
                        $Activo = "";
                    }
                }                                
            }
            ?>
            <form id="formGiro" name="formGiro" action="/" method="POST">
                <table style="min-width: 70%">
                    <tr>
                        <td><label for="nombre">Nombre</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="nombre" name="nombre" value="<?php echo $Nombre; ?>"/></td>                        
                    </tr>   
                    <tr>
                        <td><label for="descripcion">Descripci&oacute;n</label></td>
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
                    echo "<input type='hidden' id='id' name='id' value='" . $IdGiro . "'/> ";
                ?>
            </form>
        </div>
    </body>
</html>