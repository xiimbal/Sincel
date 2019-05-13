<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/ProveedorServicio.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Proveedor.class.php");
$prov = new Proveedor();
$catalogo = new Catalogo();
$pagina_lista = "admin/lista_proveedor.php";
$obj = new ProveedorServicio();
$id = "";
$idSucursal = "";
$idServicio = "";
$proveedor = "";
$id_proveedor = "";
if (isset($_POST["id_prov"]) && $_POST["id_prov"] != "") {
    $id_proveedor = $_POST["id_prov"];
    $proveedor = "Alta servicio del proveedor: <b>" . $prov->get_nombre_prov($id_proveedor) . "</b>";
    $pagina_lista = "admin/lista_proveedorServicio.php";
} else {
    $proveedor = "Alta Servicio-Proveedor";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_proveedorServicio.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_proveedorSucursal.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button();
            });
        </script>
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id'])) {
                $obj->getRegistroById($_POST['id']);
                $id = $_POST['id'];
                $id_proveedor = $obj->getIdProveedor();
                $idSucursal = $obj->getIdSucursal();
                $idServicio = $obj->getIdServicio();
            }
            ?>
            <form id="formProvServicio" name="formProvServicio" action="/" method="POST">
                <h3><b><?php echo $proveedor; ?></b></h3>
                <br/><br/>
                <table>
                    <tr>
                        <?php if ($id_proveedor == "") { ?>
                            <td>Proveedor<span class="obligatorio"> *</span></td>
                            <td>
                                <select id="sl_proveedor" name="sl_proveedor" onclick="select_sucursal(this.value)">
                                    <?php
                                    $query_pro = $catalogo->getListaAlta("c_proveedor", "NombreComercial");
                                    echo "<option value='0' >Selecciona una opción</option>";
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
                            <select id="sl_sucursal" name="sl_sucursal">
                                <option value='0' >Selecciona una opción</option>   
                                <?php
                                if ($id_proveedor != "") {
                                    $query_sucursal = $catalogo->obtenerLista("SELECT sp.id_prov_sucursal,sp.NombreComercial FROM k_proveedorsucursal sp WHERE sp.ClaveProveedor='$id_proveedor' AND sp.Activo=1 ORDER BY sp.NombreComercial  ASC");
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
                        <td>Servicio<span class="obligatorio"> *</span></td>
                        <td>
                            <select id="sl_servicio" name="sl_servicio">
                                <?php
                                $query = $catalogo->obtenerLista("SELECT c.NoParte,c.Modelo AS nombre FROM c_componente c WHERE c.IdTipoComponente=7");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($idServicio != "" && $idServicio == $rs['NoParte']) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['NoParte'] . "' " . $s . ">" . $rs['nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>                    
                    </tr>
                </table>
                <br/><br/>
                <input type="submit" class="boton" value="Guardar" />
                <input type="button" class="boton" value="Cancelar" onclick="editar_suc('<?php echo $pagina_lista; ?>', '<?php echo $id_proveedor ?>');
                        return false;"/>           
                <input type='hidden' id='id_prov' name='id_prov' value='<?php echo $id_proveedor ?>'/>
                <input type='hidden' id='idProvSucServ' name='idProvSucServ' value='<?php echo $id ?>'/>

            </form>
        </div>
    </body>
</html>
