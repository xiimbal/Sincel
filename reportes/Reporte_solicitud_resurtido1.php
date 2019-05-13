<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/ReporteFacturacion.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

$catalogo = new Catalogo();

?>

<script type="text/javascript" language="javascript" src="resources/js/paginas/reportes/Reporte_solicitud_resurtido.js"></script>
<br/><br/>
<form id="rtoners">
    <table style="width: 100%;">
        <tr>
            <td>Almacén</td>
            <td>
                <select id="almacen" name="almacen">
                    <option value="">Todos los almacenes</option>
                    <?php
                    $query = $catalogo->obtenerLista("SELECT id_almacen,nombre_almacen FROM c_almacen ORDER BY nombre_almacen");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<option value='" . $rs['id_almacen'] . "' selected>" . $rs['nombre_almacen'] . " </option>";
                    }
                    ?>
                </select>
            </td>
            <td>Fecha inicio</td>
            <td>
                <input type="text" id="fecha1" name="fecha1"/>
            </td>
            <td>Fecha Fin</td>
            <td>
                <input type="text" id="fecha2" name="fecha2"/>
            </td>
        </tr>
        <tr>     
            <td>Cliente</td>
            <td>
                <select id="cliente" name="cliente" style="width: 200px;" onchange="cargarlocalidades('cliente','localidad');">
                    <?php
                    echo "<option value=''>Todos los clientes</option>";
                    $query = $catalogo->obtenerLista("SELECT ClaveCliente,NombreRazonSocial FROM c_cliente WHERE Activo=1 ORDER BY NombreRazonSocial");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<option value='" . $rs['ClaveCliente'] . "' >" . $rs['NombreRazonSocial'] . "</option>";
                    }
                    ?>
                </select>
            </td>
            <td>Localidad</td>
            <td>
                <select id="localidad" name="localidad" style="width: 200px;" onchange="cargarequipos('localidad','equipo');">
                    <?php
                    echo "<option value=''>Todos las localidades</option>";
                    ?>
                </select>
            </td>
            <td>Equipo</td>
            <td>
                <select id="equipo" name="equipo" style="width: 200px;">
                    <?php
                    echo "<option value=''>Todos los equipos</option>";
                    ?>
                </select>
            </td>
        </tr>

    </table>
</form>
<br/><br/>
<input type="button" id="enviar" value="Consultar" class="boton" style="margin-left: 83%;" onclick="consultarreporttonner();"/>