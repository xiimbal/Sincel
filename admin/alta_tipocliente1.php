<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/TipoCliente.class.php");
$pagina_lista = "admin/lista_tipocliente.php";

$IdTipoCliente = '';
$Nombre = '';
$Descripcion = '';
$Orden = '';
$IdVersion = '';
$Activo = "checked='checked'";
$Radio = "";

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_tipocliente.js"></script>        
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id'])) {                
                $obj = new TipoCliente();
                if($obj->getRegistroById($_POST['id'])){
                    $IdTipoCliente = $obj->getIdTipoCliente();
                    $Nombre = $obj->getNombre();
                    $Descripcion = $obj->getDescripcion();
                    $Orden = $obj->getOrden();
                    $IdVersion = $obj->getIdVersion();
                    $Radio = $obj->getRadio();
                    if ($obj->getActivo() == "0") {
                        $Activo = "";
                    }
                }                                
            }
            ?>
            <form id="formTipoCliente" name="formTipoCliente" action="/" method="POST">
                <table style="min-width: 70%">
                    <tr>
                        <td><label for="nombre">Nombre</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="nombre" name="nombre" value="<?php echo $Nombre; ?>"/></td>
                        <td><label for="descripcion">Descripci&oacute;n</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="descripcion" name="descripcion" value="<?php echo $Descripcion; ?>"/></td>
                        <td><label for="radio">Radio de búsqueda</label><span class="obligatorio"> *</span></td>
                        <td><input type="number" id="radio" name="radio" value="<?php echo $Radio; ?>" step="any"/></td>
                    </tr>    
                    <tr>
                        <td><input type="checkbox" name="activo" id="activo" <?php echo $Activo; ?>/>Activo</td>
                    </tr>
                </table>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                return false;"/>
                <?php
                    echo "<input type='hidden' id='id' name='id' value='" . $IdTipoCliente . "'/> ";
                ?>
            </form>
        </div>
    </body>
</html>
