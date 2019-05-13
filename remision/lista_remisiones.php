<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");

$catalogoF = new CatalogoFacturacion();
$catalogo = new Catalogo();
$permisos_grid = new PermisosSubMenu();
$usuario = new Usuario();

$folio = "";
if (isset($_GET['id'])) {
    $folio = $_GET['id'];
    $activar_submit = true;
}
?>

<script type="text/javascript" language="javascript" src="resources/js/paginas/remision/lista_remisiones.js"></script>
<br/><br/>
<form id="rfactura">
    <table style="width: 100%;">
        <tr>
            <td>RFC Emisor</td>
            <td>
                <select id="RFC" name="RFC" class="filtroselect">
                    <option value="">Todos los emisores</option>
                    <?php
                    $query = $catalogoF->obtenerLista("SELECT DISTINCT c_factura.RFCEmisor FROM c_factura");
                    while ($rs = mysql_fetch_array($query)) {
                        $s = "";
                        if (isset($_GET['param1']) && $_GET['param1'] != "0" && $_GET['param1'] == $rs['RFCEmisor']) {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        }
                        echo "<option value='" . $rs['RFCEmisor'] . "' $s>" . $rs['RFCEmisor'] . " </option>";
                    }
                    ?>
                </select>
            </td>
            <td>Fecha inicio</td>
            <td>
                <input type="text" class="fecha" id="fecha1" name="fecha1" value="<?php
                if (isset($_GET['param2']) && $_GET['param2'] != "0" && $_GET['param2'] != "") {
                    echo $_GET['param2'];
                    $activar_submit = true;
                } else if (isset($_GET['fecha1']) && $_GET['fecha1'] != "") {
                    $llamar = true;
                    echo $_GET['fecha1'];
                }
                ?>"/>
            </td>
            <td>Fecha Fin</td>
            <td>
                <input type="text" id="fecha2" class="fecha" name="fecha2" value="<?php
                if (isset($_GET['param3']) && $_GET['param3'] != "0" && $_GET['param3'] != "") {
                    echo $_GET['param3'];
                    $activar_submit = true;
                } else if (isset($_GET['fecha2']) && $_GET['fecha2'] != "") {
                    $llamar = true;
                    echo $_GET['fecha2'];
                }
                ?>"/>
            </td>
        </tr>
        <tr>
            <td>RFC Cliente</td>
            <td>
                <select id="rfccliente" name="rfccliente" style="width: 200px;" class="filtroselect">
                    <?php
                    if (empty($array_clientes)) {
                        $query = $catalogo->obtenerLista("SELECT DISTINCT(RFC) AS RFC FROM c_cliente WHERE Activo = 1 AND RFC<>\"\" ORDER BY RFC");
                    } else {
                        $query = $catalogo->obtenerLista("SELECT DISTINCT(RFC) AS RFC FROM c_cliente WHERE ClaveCliente IN($array_clientes) AND Activo = 1 ORDER BY RFC");
                    }

                    echo "<option value=''>RFC Cliente</option>";
                    while ($rs = mysql_fetch_array($query)) {
                        $s = "";
                        if (isset($_GET['param4']) && $_GET['param4'] != "0" && $_GET['param4'] == $rs['RFC']) {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        }
                        echo "<option value='" . $rs['RFC'] . "' $s>" . $rs['RFC'] . "</option>";
                    }
                    ?> 
                </select>
            </td>       
            <td>Cliente</td>
            <td>
                <select id="cliente" name="cliente" style="width: 200px;" class="filtroselect">
                    <?php
                    echo "<option value=''>Todos los clientes</option>";
                    if (empty($array_clientes)) {
                        $query = $catalogo->obtenerLista("SELECT DISTINCT(RFC) AS RFC, NombreRazonSocial AS Nombre FROM c_cliente WHERE Activo = 1 AND RFC<>\"\" ORDER BY Nombre");
                    } else {
                        $query = $catalogo->obtenerLista("SELECT DISTINCT(RFC) AS RFC, NombreRazonSocial AS Nombre FROM c_cliente WHERE ClaveCliente IN($array_clientes) AND Activo = 1 ORDER BY Nombre");
                    }

                    while ($rs = mysql_fetch_array($query)) {
                        echo "<option value='" . $rs['RFC'] . "' >" . $rs['Nombre'] . "</option>";
                    }
                    ?>
                </select>
            </td>
            <td>Folio</td>
            <td>
                <input type="text" id="folio" name="folio" value="<?php echo $folio; ?>"/>
            </td>
        </tr>
        <tr>
            <td>Estado</td>
            <td>
                <select id="status" name="status[]" style="width: 200px;" class="filtroselectmultiple" multiple="multiple">
                    <?php
                    if (!$cxc) {//Si no estamos en la venta de cxc                        
                        if (isset($_GET['param6']) && $_GET['param6'] == "1") {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        } else {
                            $s = "";
                        }
                        echo "<option value='1' $s>No Pagada</option>";

                        if (isset($_GET['param8']) && $_GET['param8'] == "4") {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        } else {
                            $s = "";
                        }
                        echo "<option value='4' $s>Pagadas</option>";
                    } else {
                        echo "<option value='1' selected='selected'>No Pagada</option>";
                        echo "<option value='4'>Pagadas</option>";
                    }
                    ?>
                </select>
            </td>  
            <td>Tipo facturas</td>
            <td>
                <select id="tipo_facturas" name="tipo_facturas[]" style="width: 200px;" class="filtroselectmultiple" multiple="multiple">
                    <?php
                    $consulta = "SELECT IdTipoFactura, TipoFactura FROM `c_tipofacturaexp` where Activo = 1;";
                    $result = $catalogoF->obtenerLista($consulta);
                    if (isset($_GET['param12']) && $_GET['param12'] == "0") {
                        $s = "selected = 'selected'";
                        $activar_submit = true;
                    } else {
                        $s = "";
                    }
                    echo "<option value='0' $s>Sin identificar</option>";
                    while ($rs = mysql_fetch_array($result)) {
                        $s = "";
                        if (isset($_GET['param13']) && $_GET['param13'] == "1" && $rs['IdTipoFactura'] == 1) {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        }
                        if (isset($_GET['param14']) && $_GET['param14'] == "2" && $rs['IdTipoFactura'] >= 2) {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        }
                        echo "<option value='" . $rs['IdTipoFactura'] . "' $s>" . $rs['TipoFactura'] . "</option>";
                    }
                    ?>
                </select>
            </td>
            <td>RFC Facturas</td>
            <td><input type="text" id="rfc_facturas" name="rfc_facturas"/></td>
        </tr>
        <tr>
            <td><label for="ejecutivo">Ejecutivo: </label></td>
            <td>
                <select id="ejecutivo" name="ejecutivo" style="max-width: 200px;" class="filtroselect">
                    <option value="">Todos los ejecutivos</option>
                    <?php
                    $result = $usuario->getUsuariosByPuesto("11");
                    while ($rs = mysql_fetch_array($result)) {
                        $s = "";
                        if (isset($_POST['ejecutivo']) && $_POST['ejecutivo'] == $rs['IdUsuario']) {
                            $s = "selected='selected'";
                        } else if (isset($_GET['param11']) && $_GET['param11'] != "0" && $_GET['param11'] == $rs['IdUsuario']) {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        }
                        echo "<option value='" . $rs['IdUsuario'] . "' $s>" . $rs['Nombre'] . " " . $rs['ApellidoPaterno'] . " " . $rs['ApellidoMaterno'] . "</option>";
                    }
                    ?>
                </select>
            </td>
            <td></td>
            <td></td>
            <td style="min-width: 100px;">

            </td>
            <td><input type="submit" id="enviar" value="Mostrar" class="boton"/></td>
        </tr>
    </table>
</form>
<div id="tablamensajeinfo"></div>
<div id="tablainfo"></div>