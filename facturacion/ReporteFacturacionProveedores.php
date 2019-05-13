<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
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
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/facturacion/ReporteFacturacionProveedores.js"></script>
<br/><br/>
<form id="rfacturaProveedor" name="rfacturaProveedor" method="POST" target="_blank" action="facturacion/XML_reporte_facturacion_proveedores.php">
    <table style="width: 100%;">
        <tr>
            <td>RFC Proveedor</td>
            <td>
                <select id="RFCProveedor" name="RFCProveedor" class="filtroselect">
                    <option value="">Todos los proveedores</option>
                    <?php
                    $query = $catalogo->obtenerLista("SELECT RFC FROM c_proveedor WHERE Activo = 1");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<option value='" . $rs['RFC'] . "' $s>" . $rs['RFC'] . " </option>";
                    }
                    ?>
                </select>
            </td>
            <td>Fecha inicio</td>
            <td>
                <input type="text" class="fecha" id="fecha1" name="fecha1" />
            </td>
            <td>Fecha Fin</td>
            <td>
                <input type="text" id="fecha2" class="fecha" name="fecha2"/>
            </td>
        </tr>
        <tr>
            <td>Proveedor</td>
            <td>
                <select id="proveedor" name="proveedor" class="filtroselect">
                    <option value="">Todos los proveedores</option>
                    <?php
                    $query = $catalogo->obtenerLista("SELECT RFC,NombreComercial FROM c_proveedor WHERE Activo = 1");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<option value='" . $rs['RFC'] . "' $s>" . $rs['NombreComercial'] . " </option>";
                    }
                    ?>
                </select>
            </td>     
            <td>Estado</td>
            <td>
                <select id="status" name="status[]" style="width: 200px;" class="filtroselectmultiple" multiple="multiple">
                    <?php
                        echo "<option value='1' selected='selected'>No Pagada</option>";
                        echo "<option value='4'>Pagadas</option>";
                    ?>
                </select>
            </td>
            <td>Folio</td>
            <td>
                <input type="text" id="folio" name="folio" value="<?php echo $folio; ?>"/>
            </td>
        </tr>
        <td>
            <input type="button" onclick="BuscarCxP()" id="enviar" value="Mostrar" class="boton"/>
        </td>
        <td>
            <input type="submit" class="botonExcel button" title="Exportar a excel" id="excelSubmit" name="excelSubmit" value="Exportar a excel"/>
        </td>
        </tr>
    </table>
</form>
<div id="tablamensajeinfo"></div>
<div id="tablainfo"></div>