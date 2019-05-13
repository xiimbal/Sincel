<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/ProveedorZona.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Proveedor.class.php");
$prov = new Proveedor();
$pagina_lista = "admin/lista_proveedor_pakal.php";
$catalogo = new Catalogo();
$id_proveedor = "";
$idSucursal = "";
$gzona = "";
$zona = "";
$cantidad = "";
$proveedor = "";
$id = "";

if (isset($_POST["id_prov"]) && $_POST["id_prov"] != "") {
    $id_proveedor = $_POST["id_prov"];
    $proveedor = "Alta zona del proveedor: <b>" . $prov->get_nombre_prov($id_proveedor) . "</b>";
    $pagina_lista = "admin/lista_proveedorZona_pakal.php";
} else {
    $proveedor = "Alta Zona-Proveedor";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_proveedorZona_pakal.js"></script>
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
            $obj = new ProveedorZona();
            if (isset($_POST['id'])) {
                $id = $_POST['id'];
                $obj->getRegistroById($id);
                $id_proveedor = $obj->getIdProveedor();
                $idSucursal = $obj->getIdSucursal();
                $gzona = $obj->getGZona();
                $zona = $obj->getIdZona();
                $cantidad = $obj->getTiempoMaxSolucion();
            }
            ?>
            <form id="formProvZona" name="formProvZona" action="/" method="POST">
                <h3 class="text-dark"><b><?php echo $proveedor; ?></b></h3>
                <div class="form-row">
                    <?php if ($id_proveedor == "") { ?>
                        <div class="form-group col-md-2">
                        
                            <label for="sl_proveedor" class="text-dark" >Proveedor<strong class="obligatorio text-danger">*</strong></label>
                            <select id="sl_proveedor" name="sl_proveedor" onclick="select_sucursal_zona(this.value)" class="custom-select">
                                <option value="0">Selecccione un proveedor</option>
                                <?php
                                $query_pro = $catalogo->getListaAlta("c_proveedor", "NombreComercial");
                                while ($rs = mysql_fetch_array($query_pro)) {
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
                    <div class="form-group col-md-2">
                        <label for="sl_sucursal" class="text-dark">Sucursal<strong class="obligatorio text-danger"> *</strong></label>
                        <select id="sl_sucursal" name="sl_sucursal"  class="custom-select">
                        <option value='0' >Selecciona una opción</option>   
                        <?php
                        if ($id_proveedor != "") {
                            $query_sucursal = $catalogo->obtenerLista("SELECT sp.id_prov_sucursal,sp.NombreComercial FROM k_proveedorsucursal sp WHERE sp.ClaveProveedor='$id_proveedor' ORDER BY sp.NombreComercial ASC");
                            while ($rs = mysql_fetch_array($query_sucursal)) {
                                $s = "";
                                if ($idSucursal != "" && $idSucursal == $rs['id_prov_sucursal']) {
                                    $s = "selected";
                                }
                                echo "<option value='" . $rs['id_prov_sucursal'] . "' " . $s . ">" . $rs['NombreComercial'] . "</option>";
                            }
                        }
                        ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="gzona" class="text-dark">Grupo de zona<strong class="obligatorio text-danger">*</strong></label>
                        <select id='gzona'name='gzona' onchange="verZonasOP(this.value);"   class="custom-select    ">
                                <?php
                                $query_gzona = $catalogo->getListaAlta("c_gzona", "nombre");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query_gzona)) {
                                    $s = "";
                                    if ($gzona == $rs['id_gzona']) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['id_gzona'] . "' " . $s . ">" . $rs['nombre'] . "</option>";
                                }
                                ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="zona" class="text-dark">Zonas<strong class="obligatorio text-danger"> *</strong></label>
                        <select id="zona" name="zona"   class="custom-select">
                            <option value="0">Selecciona una zona</option>
                            <?php
                            $query_zona = $catalogo->obtenerLista("SELECT * FROM c_zona WHERE fk_id_gzona='" . $gzona . "'");
                            while ($rs = mysql_fetch_array($query_zona)) {
                                $s = "";
                                if ($zona == $rs['ClaveZona']) {
                                    $s = "selected";
                                }
                                echo "<option value='" . $rs['ClaveZona'] . "' " . $s . ">" . $rs['NombreZona'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="tiempo" class="text-dark">Tiempo Maximo Solución<strong class="obligatorio text-danger"> *</strong></label>
                        <input id='tiempo' name='tiempo' value='<?php echo $cantidad; ?>'   class="form-control"/>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <input type="submit" class="btn btn-success form-control" value="Guardar" />
                    </div>
                    <div class="form-group col-md-3">
                        <input type="submit" class="btn btn-danger form-control" value="Cancelar" onclick="editar_suc('<?php echo $pagina_lista; ?>', '<?php echo $id_proveedor ?>');
                        return false;"/>
                        <input type='hidden' id='id_prov' name='id_prov' value='<?php echo $id_proveedor ?>'/>
                        <input type='hidden' id='idProvSucZona' name='idProvSucZona' value='<?php echo $id ?>'/> 
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>