<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/ProveedorProducto.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Proveedor.class.php");
$prov = new Proveedor();
$catalogo = new Catalogo();
$pagina_lista = "admin/lista_proveedor.php";
$id = "";
$idProducto = "";
$idSucursal = "";
$id_proveedor = "";
$read = "";
$proveedor = "";
if (isset($_POST["id_prov"]) && $_POST["id_prov"] != "") {
    $id_proveedor = $_POST["id_prov"];
    $proveedor = "Alta producto del proveedor: <b>" . $prov->get_nombre_prov($id_proveedor) . "</b>";
    $pagina_lista = "admin/lista_proveedorProducto.php";
} else {
    $proveedor = "Alta Producto-Proveedor";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_proveedorProducto.js"></script>
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
            $obj = new ProveedorProducto();
            if (isset($_POST['id'])) {
                $id = $_POST['id'];
                $obj->getRegistroById($id);
                $id_proveedor = $obj->getIdProveedor();
                $idSucursal = $obj->getIdSucursal();
                $idProducto = $obj->getIdProducto();
            }
            ?>
            <form id="formProvProducto" name="formProvProducto" action="/" method="POST">
                <h3><b><?php echo $proveedor; ?></b></h3>
                <br/><br/>
                <table style="width: 90%;">
                    <tr>
                        <?php if ($id_proveedor == "") { ?>
                            <td>Proveedor<span class="obligatorio">*</span></td>
                            <td>
                                <select id="proveedor" name="proveedor" onclick="select_sucursal_pro(this.value)" style="width: 250px;">
                                    <?php
                                    $query_proveedor = $catalogo->getListaAlta("c_proveedor", "NombreComercial");
                                    echo "<option value='0'>Selecciona una opción</option>";
                                    while ($rs = mysql_fetch_array($query_proveedor)) {
                                        $s = "";
                                        if ($id_proveedor != "" && $id_proveedor == $rs['IdProducto']) {
                                            $s = "selected";
                                        }
                                        echo "<option value='" . $rs['ClaveProveedor'] . "' " . $s . ">" . $rs['NombreComercial'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td> 
                        <?php } ?>
                        <td>Sucursal<span class="obligatorio"> *</span></td>
                        <td>
                            <select id="sucursal" name="sucursal">
                                <option>Selecciona una opción</option>
                                <?php
                                if ($id_proveedor != "") {
                                    $query_sucursal = $catalogo->obtenerLista("SELECT sp.id_prov_sucursal,sp.NombreComercial AS nombre FROM k_proveedorsucursal sp WHERE sp.ClaveProveedor='$id_proveedor' ORDER BY sp.NombreComercial ASC    ");
                                    while ($rs = mysql_fetch_array($query_sucursal)) {
                                        $s = "";
                                        if ($idSucursal != "" && $idSucursal == $rs['id_prov_sucursal']) {
                                            $s = "selected";
                                        }
                                        echo "<option value='" . $rs['id_prov_sucursal'] . "' " . $s . ">" . $rs['nombre'] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td> 
                        <td>Producto<span class="obligatorio"> *</span></td>
                        <td>
                            <select id="producto" name="producto" style="width: 550px;">
                                <?php
                                $query_producto = $catalogo->obtenerLista("SELECT c.NoParte,c.Modelo AS nombre, c.Descripcion 
                                    FROM c_componente c ORDER BY nombre;");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query_producto)) {
                                    $s = "";
                                    if ($idProducto != "" && $idProducto == $rs['NoParte']) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['NoParte'] . "' $s>" . $rs['nombre'] . " // ".$rs['NoParte']." // ".$rs['Descripcion']."</option>";
                                }
                                ?>
                            </select>
                        </td>                    
                    </tr>
                </table>
                <input type="submit" class="boton" value="Guardar" />
                <input type="button" class="boton" value="Cancelar" onclick="editar_suc('<?php echo $pagina_lista; ?>', '<?php echo $id_proveedor ?>');
                        return false;"/>
                <input type='hidden' id='id_prov' name='id_prov' value='<?php echo $id_proveedor ?>'/>
                <input type="hidden" id="txt_prov_suc_prod" name="txt_prov_suc_prod" value="<?php echo $id ?>"/>
            </form>
        </div>
    </body>    
</html>
