<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/ProveedorZona.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Proveedor.class.php");
$prov = new Proveedor();
$pagina_lista = "admin/lista_proveedor.php";
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
    $pagina_lista = "admin/lista_proveedorZona.php";
} else {
    $proveedor = "Alta Zona-Proveedor";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_proveedorZona.js"></script>
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
                <h3><b><?php echo $proveedor; ?></b></h3>
                <br/><br/>
                <table style="width: 80%">
                    <tr>
                        <?php if ($id_proveedor == "") { ?>
                            <td>Proveedor<span class="obligatorio">*</span></td>
                            <td>
                                <select id="sl_proveedor" name="sl_proveedor" style="width:200px" onclick="select_sucursal_zona(this.value)">
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
                            </td>
                        <?php } ?>
                        <td>Sucursal<span class="obligatorio"> *</span></td>
                        <td>
                            <select id="sl_sucursal" name="sl_sucursal" style="width:200px">
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
                        </td>
                        <td></td><td></td>
                    </tr>
                    <tr>
                        <td>Grupo de zona<span class="obligatorio">*</span></td>
                        <td>
                            <select id='gzona'name='gzona' onchange="verZonasOP(this.value);">
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
                        </td>
                        <td>Zonas<span class="obligatorio"> *</span></td>
                        <td>
                            <select id="zona" name="zona" style="width:200px">
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
                        </td>                  
                        <td>Tiempo Maximo Solución<span class="obligatorio"> *</span></td><td><input id='tiempo' name='tiempo' value='<?php echo $cantidad; ?>'/></td>
                    </tr>
                </table>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="editar_suc('<?php echo $pagina_lista; ?>', '<?php echo $id_proveedor ?>');
                        return false;"/>
                <input type='hidden' id='id_prov' name='id_prov' value='<?php echo $id_proveedor ?>'/>
                <input type='hidden' id='idProvSucZona' name='idProvSucZona' value='<?php echo $id ?>'/>
            </form>
        </div>
    </body>
</html>