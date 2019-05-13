<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/ProveedorSucursal.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Proveedor.class.php");
$prov = new Proveedor();
$pagina_lista = "admin/lista_proveedor_pakal.php";

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
    $pagina_lista = "admin/lista_proveedorSucursal_pakal.php";
}else{
    $proveedor="Alta Sucursal-Proveedor";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
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
            <div class="col-lg-6 offset-3 title text-dark" align="center"><h3><b><?php echo $proveedor; ?></b></h3></div>
            <form id="formProvSucursal" name="formProvSucursal" action="/" method="POST">
                <div class="form-row">
                        
                        <?php if ($id_proveedor == "") { ?>
                        <div class="form-group col-md-4">
                            <label class="text-dark"  for="proveedor">Proveedor</label><span class="obligatorio text-danger" > *</span>
                            <select class="form-control" id="proveedor" name="proveedor">
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
                        </div>
                        <?php } ?> 
                        <div class="form-group col-md-4">
                            <label class="text-dark"  for="nombre">Nombre</label><span class="obligatorio text-danger" > *</span>
                            <input  class="form-control" type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <br>
                            <label class="text-dark"  for="activo">Activo</label>
                            <input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>
                        
                            <input type="submit" class="btn btn-success" value="Guardar" />
                            <input type="submit" class="btn btn-danger" value="Cancelar" onclick="editar_suc('<?php echo $pagina_lista; ?>','<?php echo $id_proveedor ?>');
                            return false;"/>
                            <input type='hidden' id='id_prov' name='id_prov' value='<?php echo $id_proveedor ?>'/>
                            <input type='hidden' id='id_prov_suc' name='id_prov_suc' value='<?php echo $id ?>'/>
                        </div>
                </div>
                
            </form>
        </div>
    </body>
</html>
