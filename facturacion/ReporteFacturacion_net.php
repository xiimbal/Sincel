<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

if (isset($_GET['cxc']) && $_GET['cxc'] == "1") {
    $cxc = true;
} else {
    $cxc = false;
}

include_once("../WEB-INF/Classes/ReporteFacturacion.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");

$catalogoF = new CatalogoFacturacion();
$catalogo = new Catalogo();
$permisos_grid = new PermisosSubMenu();
$parametros = new Parametros();
$usuario = new Usuario();
$usuario->setId($_SESSION['idUsuario']);

$clientes_permitidos = $usuario->obtenerNegociosDeUsuario();
$array_clientes = implode("','", $clientes_permitidos);
if (!empty($array_clientes)) {
    $array_clientes = "'$array_clientes'";
}

if ($parametros->getRegistroById("7")) {
    $liga = $parametros->getDescripcion();
} else {
    $liga = "http://genesis1.techra.com.mx/";
}
//$liga .= "/cfdi/Facturas/Reportes/reporte_facturacion.csv?uguid=sistemas";

$folio = "";
if (isset($_GET['id'])) {
    $folio = $_GET['id'];
    $activar_submit = true;
}
?>

<script type="text/javascript" language="javascript" src="resources/js/paginas/facturacion/reportefacturacion_net.js"></script>
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
                        if (isset($_GET['param5']) && $_GET['param5'] == "0") {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        } else {
                            $s = "";
                        }
                        echo "<option value='0' $s>Cancelada</option>";

                        if (isset($_GET['param6']) && $_GET['param6'] == "1") {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        } else {
                            $s = "";
                        }
                        echo "<option value='1' $s>No Pagada</option>";

                        if (isset($_GET['param7']) && $_GET['param7'] == "3") {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        } else {
                            $s = "";
                        }
                        echo "<option value='3' $s>Incobrable</option>";

                        if (isset($_GET['param8']) && $_GET['param8'] == "4") {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        } else {
                            $s = "";
                        }
                        echo "<option value='4' $s>Pagadas</option>";

                        if (isset($_GET['param9']) && $_GET['param9'] == "5") {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        } else {
                            $s = "";
                        }
                        echo "<option value='5' $s>Pendientes por cancelar</option>";
                    } else {
                        echo "<option value='1' selected='selected'>No Pagada</option>";
                        echo "<option value='4'>Pagadas</option>";
                    }
                    ?>
                </select>
            </td>       
            <td>Tipo de documento</td>
            <td>
                <select id="docto" name="docto" style="width: 200px;" class="filtroselect">
                    <?php
                    echo "<option value=''>Todos</option>";
                    if (isset($_GET['param10']) && $_GET['param10'] == "0") {
                        $s = "selected = 'selected'";
                        $activar_submit = true;
                    } else {
                        $s = "";
                    }
                    echo "<option value='ingreso' $s>Factura</option>";
                    if (isset($_GET['param10']) && $_GET['param10'] == "1") {
                        $s = "selected = 'selected'";
                        $activar_submit = true;
                    } else {
                        $s = "";
                    }
                    echo "<option value='egreso' $s>Nota de crédito</option>";
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
            <td style="min-width: 100px;">
                <?php if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 6) && !$cxc) { ?>
                    <input type="button" class="boton" value="Marcar pagadas" onclick="marcarPagadas();"/>
                <?php } ?>
            </td>
            <td><input type="submit" id="enviar" value="Mostrar" class="boton"/></td>
        </tr>
        <tr>
            <td><label for="periodoFacturacion">Periodo Facturaci&oacute;n</label></td>
            <td><input type="text" class="periodo_facturacion" id="periodo_facturacion" name="periodo_facturacion" style="width: 225px;"/></td>
        </tr>
    </table>
    <?php
    if (empty($array_clientes)) {
        ?>
        <div style="margin-left: 79%;"><a href="Reporte_facturacion_<?php echo $_SESSION['idEmpresa'] ?>.xlsx" target="_blank">Descargar reporte de facturación global</a></div>    
        <div style="margin-left: 79%;"><a href="Reporte_pagos_timbrados_<?php echo $_SESSION['idEmpresa'] ?>.xlsx" target="_blank">Descargar reporte de pagos parciales timbrados</a></div>
            <?php if ($cxc) { ?>
            <div style="margin-left: 79%;"><a href="Reporte_facturacionCXC_<?php echo $_SESSION['idEmpresa'] ?>.xlsx" target="_blank">Descargar reporte de facturación CXC</a></div>
        <?php
        }
    }
    ?>


    <?php
    if ($cxc) {
        echo "<input type='hidden' id='cxc_activo' name='cxc_activo' value='1'/>";
    }

    if (isset($_GET['param15']) && $_GET['param15'] == "1") {
        echo "<input type='hidden' id='no_pref' name='no_pref' value='1'/>";
        $activar_submit = true;
    }
    ?>
</form>
<div id="tablamensajeinfo"></div>
<div id="tablainfo"></div>

<script>
<?php if ($folio != "" || $activar_submit) { ?>
        //$("#tablamensajeinfo").html("<h2>Cargando ...</h2>");
        $("#rfactura").submit(/*function(){
         $("#tablamensajeinfo").html("");
         }*/);
<?php } ?>
</script>
