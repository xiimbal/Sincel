<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");

$catalogo = new Catalogo();
?>
<!DOCTYPE html>
<html lang="es">
    <head>        
        <script type="text/javascript" language="javascript" src="resources/js/paginas/indicadores/ReporteHojas.js"></script>        
    </head>
    <body>
        <form id="formReporteImpresiones" action="indicadores/GenerarReporte.php" target="_blank" method="POST">
            <table style="width: 99%;">
                <tr>
                    <td>Cliente</td>
                    <td>
                        <select id="cliente" name="cliente[]" style="width: 200px;" multiple="multiple" class="multiselect">
                            <?php
                                $query = $catalogo->obtenerLista("SELECT DISTINCT(NombreRazonSocial) AS cliente, ClaveCliente FROM `c_cliente` WHERE Activo = 1 ORDER BY cliente;");
                                while ($rs = mysql_fetch_array($query)) {
                                    echo "<option value='" . $rs['ClaveCliente'] . "'>" . $rs['cliente'] . "</option>";
                                }
                            ?> 
                        </select>
                    </td>
                    <td>Modelo</td>
                    <td>
                        <select id="modelo" name="modelo[]" style="width: 200px;" multiple="multiple" class="multiselect">
                            <?php
                                $query = $catalogo->obtenerLista("SELECT DISTINCT Modelo, NoParte FROM `c_equipo` WHERE Activo = 1 ORDER BY Modelo;");
                                while ($rs = mysql_fetch_array($query)) {
                                    echo "<option value='" . $rs['NoParte'] . "'>" . $rs['NoParte']."-".$rs['Modelo'] . "</option>";
                                }
                            ?> 
                        </select>
                    </td>
                    <td>Fecha inicio</td>
                    <td><input id="fecha_inicio" name="fecha_inicio" class="fecha" style="width:196px" /></td>
                    <td>Fecha final</td>
                    <td><input id="fecha_fin" name="fecha_fin" class="fecha" style="width:196px" /></td>
                </tr>
            </table>
            <br/>
            <input type="submit" id="reporte" name="reporte" value="Reporte" class="boton" />
        </form>
    </body>
</html>