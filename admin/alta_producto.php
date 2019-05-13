<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Producto.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "admin/lista_producto.php";

$id = "";
$nombre = "";
$descripcion = "";
$orden = "";
$activo = "checked='checked'";

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_producto.js"></script>
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
                $obj = new Producto();
                $obj->getRegistroById($_POST['id']);

                $id = $obj->getId();
                $nombre = $obj->getNombre();
                $descripcion = $obj->getDescripcion();
                $orden = $obj->getOrden();
                if($obj->getActivo()=="0"){
                    $activo = "";
                }
            }
            ?>

            <form id="formProducto" name="formProducto" action="/" method="POST">
                <table style="min-width: 90%">
                    <tr>
                        <td><label for="nombre">Nombre</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>"/></td> 
                        <td><label for="descripcion">Descripci&oacute;n</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="descripcion" name="descripcion" value="<?php echo $descripcion; ?>"/></td>                                               
                        <td><label for="orden">Orden</label></td>
                        <td><input type="text" id="orden" name="orden" value="<?php echo $orden; ?>"/></td>
                        <td><input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo</td>                        
                    </tr>                                      
                </table>
                <br/><br/>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                return false;"/>
                <?php
                echo "<input type='hidden' id='id' name='id' value='" . $id . "'/> ";
                ?>
            </form>
        </div>
    </body>
</html>