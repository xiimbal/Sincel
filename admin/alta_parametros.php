<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Parametros.class.php");
$pagina_lista = "admin/lista_parametros.php";
$idParametro = "";
$descripcion = "";
$valor = "";
$activo = "checked='checked'";
$read = "";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_parametros.js"></script>
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
                $id = $_POST['id'];
                $obj = new Parametros();
                $obj->getRegistroById($_POST['id']);
                $read = "readonly='readonly'";
                $idParametro = $obj->getIdParametro();
                $descripcion = $obj->getDescripcion();
                $valor = $obj->getValor();
                if ($obj->getActivo() == "0") {
                    $activo = "";
                }
            }
            ?>
            <form id="formParametros" name="formParametros" action="/" method="POST">
                <table style="min-width: 70%">
                    <tr>
                        <td><label for="descripcion">Descripcion</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="descripcion" name="descripcion" value="<?php echo $descripcion; ?>"/></td>
                        <td><label for="valor">Valor</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="valor" name="valor" value="<?php echo $valor; ?>"/></td>
                    </tr>    
                    <tr>
                        <td><input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo</td>
                    </tr>
                </table>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                return false;"/>
                       <?php
                       echo "<input type='hidden' id='id' name='id' value='" . $idParametro . "'/> ";
                       ?>
            </form>
        </div>
    </body>
</html>
