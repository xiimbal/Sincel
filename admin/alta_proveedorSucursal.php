<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/ProveedorSucursal.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Proveedor.class.php");
$prov = new Proveedor();
$pagina_lista = "admin/lista_proveedor.php";

$catalogo = new Catalogo();
$id = "";
$id_proveedor = "";
$id_sucursal = "";
$nombre = "";
$activo = "checked='checked'";
$proveedor = "";
if (isset($_POST["id_prov"]) && $_POST["id_prov"] != "") {
    $id_proveedor = $_POST["id_prov"];
    $proveedor = "Alta sucursal del proveedor: <b>" . $prov->get_nombre_prov($id_proveedor) . "</b>";
    $pagina_lista = "admin/lista_proveedorSucursal.php";
}else{
    $proveedor="Alta Sucursal-Proveedor";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_proveedorSucursal.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div class="principal">
            <?php
            $obj = new ProveedorSucursal();
            if (isset($_POST['id'])) {
                $obj->getRegistroById($_POST['id']);
                $id = $_POST['id'];
                $id_proveedor = $obj->getIdProveedor();
                $nombre = $obj->getNombre();
                if ($obj->getActivo() == "0") {
                    $activo = "";
                }
            }
            ?>
            <form id="formProvSucursal" name="formProvSucursal" action="/" method="POST">
                <h3><b><?php echo $proveedor; ?></b></h3>
               <br/><br/>
                <table style="width: 60%">
                    <tr>
                        <?php if ($id_proveedor == "") { ?>
                            <td><label for="proveedor">Proveedor</label><span class="obligatorio"> *</span></td>
                            <td>
                                <select id="proveedor" name="proveedor" style="width: 250px">
                                    <?php
                                    $query = $catalogo->getListaAlta("c_proveedor", "NombreComercial");
                                    echo "<option value='0' >Selecciona una opción</option>";
                                    while ($rs = mysql_fetch_array($query)) {
                                        $s = "";
                                        if ($id_proveedor != "" && $id_proveedor == $rs['ClaveProveedor']) {
                                            $s = "selected";
                                        }
                                        echo "<option value='" . $rs['ClaveProveedor'] . "' " . $s . ">" . $rs['NombreComercial'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>    
                        <?php } ?>                    
                        <td><label for="nombre">Nombre</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>"</td>
                        <td><input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo</td><td></td>
                    </tr>
                </table>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="editar_suc('<?php echo $pagina_lista; ?>','<?php echo $id_proveedor ?>');
                        return false;"/>
                <input type='hidden' id='id_prov' name='id_prov' value='<?php echo $id_proveedor ?>'/>
                <input type='hidden' id='id_prov_suc' name='id_prov_suc' value='<?php echo $id ?>'/>
            </form>
        </div>
    </body>
</html>
