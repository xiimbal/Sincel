<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/MiniAlmacen.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "almacen/lista_miniAlmacen.php";
$idcliente = $_POST['id'];
$idminiAlmacen = "";
$nombre = "";
$descripcion = "";
$localidad = "";
$encargado = "";
$read = "";
$activo = "checked='checked'";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_miniAlmacen.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['idM'])) {
                $obj = new MiniAlmacen();
                $obj->getRegistroById($_POST['idM']);
                $read = "readonly='readonly'";
                $idminiAlmacen = $obj->getIdminiAlmacen();
                $nombre = $obj->getNombre();
                $descripcion = $obj->getDescripcion();
                $localidad = $obj->getClaveCentroCosto();
                $encargado = $obj->getClaveEncargado();
                if ($obj->getActivo() == "0") {
                    $activo = "";
                }
            }
            ?>
            <form id="formMiniAlmacen" name="formMiniAlmacen" action="/" method="POST">
                <table style="width: 60%;">
                    <tr>
                        <td>Nombre</td><td><input type="text" name="nombre" id="nombre" value="<?php echo $nombre ?>"</td>
                    </tr>
                    <tr>
                        <td>Descripción</td><td><textarea id='descripcion' name='descripcion' cols="20" rows="5"><?php echo $descripcion; ?></textarea></td>
                    </tr>
                    <tr>
                        <td>Localidad</td>
                        <td>
                            <select id="localidad" name="localidad" style="width: 180px" >
                                <option value="0">Seleccione una localidad</option>
                                <?php
                                $catalogo = new Catalogo();
                                $query = $catalogo->obtenerLista("SELECT * FROM c_centrocosto cc WHERE cc.ClaveCliente='" . $idcliente . "' AND cc.Activo=1 ");
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($localidad != "" && $localidad == $rs['ClaveCentroCosto']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['ClaveCentroCosto'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Encargado</td>
                        <td> 
                            <select id="encargado" name="encargado" style="width: 180px" >
                                <option value="0">Seleccione un encargado</option>
                                <?php
                                $catalogo1 = new Catalogo();
                                $query1 = $catalogo1->obtenerLista("SELECT * FROM c_usuario u WHERE u.IdPuesto=24 ");
                                while ($rs = mysql_fetch_array($query1)) {
                                    $s = "";
                                    if ($encargado != "" && $encargado == $rs['IdUsuario']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdUsuario'] . " " . $s . ">" . $rs['Nombre'] . " - " . $rs['ApellidoPaterno'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo</td><td></td>
                        <td></td><td></td>
                    </tr>
                </table>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="editarRegistro('<?php echo $pagina_lista; ?>', '<?php echo $idcliente ?>');
                return false;"/>
                <input type='hidden' id='id' name='id' value='<?php echo $idminiAlmacen ?>'/>
                <input type='hidden' id='idlocalidad' name='idlocalidad' value='<?php echo $localidad ?>'/>
                <input type='hidden' id='idcliente' name='idcliente' value='<?php echo $idcliente ?>'/>
            </form>
        </div>
    </body>
</html>
