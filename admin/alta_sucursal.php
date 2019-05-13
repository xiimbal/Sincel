<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Sucursal.class.php");
$pagina_lista = "admin/lista_sucursal.php";
$claveSucursal = "";
$descripcion = "";
$activo = "checked='checked'";
$read = "";
$proveedor = $_POST['id'];
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_sucursal.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id2'])) {
                $obj = new Sucursal();
                $obj->getRegistroById($_POST['id2']);
                $read = "readonly='readonly'";
                $claveSucursal = $obj->getClaveSucursal();
                $descripcion = $obj->getDescripcion();
                if ($obj->getActivo() == "0") {
                    $activo = "";
                }
            }
            ?>
        </div>
        <form id="formEstado" name="formEstado" action="/" method="POST">
            <table style="min-width: 70%">
                <tr>                    
                    <td><label for="descripcion">Descripción</label><span class="obligatorio"> *</span></td>
                    <td><input type="text" id="descripcion" name="descripcion" value="<?php echo $descripcion; ?>" /></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td><input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo</td><td></td>
                    <td></td><td></td>
                </tr>
            </table>
            <br/><br/>
            <input type="submit" class="boton" value="Guardar" />
            <input type="submit" class="boton" value="Cancelar" onclick="editarRegistro('<?php echo $pagina_lista; ?>','<?php echo $proveedor; ?>');
                return false;"/>
            <input type='hidden' id='id' name='id' value='<?php echo $claveSucursal ?>'/>
            <input type='hidden' id='proveedor' name='proveedor' value='<?php echo $proveedor ?>'/>
        </form>
    </body>
</html>