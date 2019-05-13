<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/GZona.class.php");
$pagina_lista = "admin/lista_gZona.php";
$id="";
$idgZona="";
$nombre="";
$descripcion="";
$activo="checked='checked'";
$read = "";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_gZona.js"></script>
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
                $id=$_POST['id'];
                $obj = new GZona();
                $obj->getRegistroById($_POST['id']);
                $read = "readonly='readonly'";
                $idgZona = $obj->getIdGZona();
                $nombre = $obj->getNombre();
                $descripcion = $obj->getDescripcion();
                if($obj->getActivo()=="0"){
                    $activo = "";
                }
            }
            ?>
            <form id="formGZona" name="formGZona" action="/" method="POST">
                <table style="min-width: 85%;">
                    <tr>
                        <td><label for="nombre">Nombre</label><span class="obligatorio"> *</span></td><td><input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>"/></td>
                        <td><label for="descripcion">Descripción</label><span class="obligatorio"> *</span></td><td><input type="text" id="descripcion" name="descripcion" value="<?php echo $descripcion; ?>"/></td>
                        <td><input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo</td>
                    </tr>                      
                </table>
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
