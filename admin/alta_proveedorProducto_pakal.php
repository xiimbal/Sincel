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
$pagina_lista = "admin/lista_proveedor_pakal.php";
$id = "";
$idProducto = "";
$idSucursal = "";
$id_proveedor = "";
$read = "";
$proveedor = "";
if (isset($_POST["id_prov"]) && $_POST["id_prov"] != "") {
    $id_proveedor = $_POST["id_prov"];
    $proveedor = "Alta producto del proveedor: <b>" . $prov->get_nombre_prov($id_proveedor) . "</b>";
    $pagina_lista = "admin/lista_proveedorProducto_pakal.php";
} else {
    $proveedor = "Alta Producto-Proveedor";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_proveedorProducto_pakal.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_proveedorSucursal_pakal.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div class="principal bg-white">
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
                
                     <div class="title text-dark form-group col-md-12" align="center">
                        <h3><b><?php echo $proveedor; ?></b></h3>
                     </div>
                <div class="form-row">     
                     <?php if ($id_proveedor == "") { ?>
                        <div class="form-group col-md-4">
                           
                            <label class="text-dark"  for="proveedor">Proveedor<strong class="obligatorio text-danger">*</strong></label>
                            <select id="proveedor" name="proveedor" onclick="select_sucursal_pro(this.value)"   class="form-control">
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
                        </div>
                    <?php } ?>
                        <div class="form-group col-md-4">
                            <label class="text-dark"  for="sucursal">Sucursal<strong class="obligatorio text-danger"> *</strong></label>
                            <select id="sucursal" name="sucursal"   class="custom-select">
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
                        </div>
                        <div class="form-group col-md-4">
                            <label class="text-dark"  for="producto">Producto<strong class="obligatorio text-danger"> *</strong></label>
                            <select id="producto" name="producto"    class="custom-select">
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
                        </div>
                        
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                            <input type="submit" class="btn btn-success btn-lg btn-block" value="Guardar" />
                            <input type='hidden' id='id_prov' name='id_prov' value='<?php echo $id_proveedor ?>'/>                            
                        </div>
                        <div class="form-group col-md-3">
                            <input type="button" class="btn btn-danger btn-lg btn-block" value="Cancelar" onclick="editar_suc('<?php echo $pagina_lista; ?>', '<?php echo $id_proveedor ?>');
                            return false;"/>
                            <input type="hidden" id="txt_prov_suc_prod" name="txt_prov_suc_prod" value="<?php echo $id ?>"/>
                        </div>
                </div>               
            </form>
        </div>
    </body>    
</html>
