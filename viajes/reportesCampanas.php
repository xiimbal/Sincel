<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");

$catalogo = new Catalogo();


?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/viajes/reportesCampanas.js"></script>
<br/><br/>
<form id = "formCampanas">
    <table style="width: 100%;">
        <tr>
            <td>Campaña</td>
            <td>
                <select id="Campana" name="Campana[]" class="filtroselectmultiple" multiple="multiple">
                    <option value="">Todas las campañas</option>
                    <?php
                    $query = $catalogo->obtenerLista("SELECT DISTINCT IdArea, Descripcion FROM c_area WHERE !ISNULL(ClaveCentroCosto) ORDER BY Descripcion");
                    while ($rs = mysql_fetch_array($query)) {
                        $s = "";
                        if (isset($_GET['campana']) && $_GET['campana'] != "0" && $_GET['campana'] == $rs['IdArea']) {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        }
                        echo "<option value='" . $rs['IdArea'] . "' $s>" . $rs['Descripcion'] . " </option>";
                    }
                    ?>
                </select>
            </td>
            <td>Nombre Empleado</td>
            <td>
                <select id="nombreE" name="nombreE[]" class="filtroselectmultiple" multiple="multiple">
                    <option value="">Todos los loggin</option>
                    <?php
                    $query = $catalogo->obtenerLista("SELECT DISTINCT ClaveEspEquipo FROM c_pedido");
                    while ($rs = mysql_fetch_array($query)) {
                        $s = "";
                        if (isset($_GET['loggin']) && $_GET['loggin'] != "0" && $_GET['loggin'] == $rs['ClaveEspEquipo']) {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        }
                        echo "<option value='" . $rs['ClaveEspEquipo'] . "' $s>" . $rs['ClaveEspEquipo'] . " </option>";
                    }
                    ?>
                </select>
            </td>
            <td>Operador Unidad</td>
            <td>
                <select id="operador" name="operador[]" class="filtroselectmultiple" multiple="multiple">
                    <option value="">Todos los operadores</option>
                    <?php
                    $query = $catalogo->obtenerLista("SELECT DISTINCT ktk.IdUsuario, CONCAT_WS(' ',u.Nombre,u.ApellidoPaterno, u.ApellidoMaterno) AS NombreOperador 
                            FROM k_tecnicoticket ktk LEFT JOIN c_usuario as u ON u.IdUsuario = ktk.IdUsuario");
                    while ($rs = mysql_fetch_array($query)) {
                        $s = "";
                        if (isset($_GET['IdUsuario']) && $_GET['IdUsuario'] != "0" && $_GET['operador'] == $rs['IdUsuario']) {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        }
                        echo "<option value='" . $rs['IdUsuario'] . "' $s>" . $rs['NombreOperador'] . " </option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Fecha Inicio</td>
            <td><input id="fecha_inicio" name="fecha_inicio" class="fecha" style="width:196px" /></td>
            <td>Fecha Fin</td>
            <td><input id="fecha_fin" name="fecha_fin" class="fecha" style="width:196px"/></td>
            <td>Turno</td>
            <td>
                <select id="turno" name="turno[]" class="filtroselectmultiple" multiple="multiple">
                    <option value="">Todos los turnos</option>
                    <?php
                    $query = $catalogo->obtenerLista("SELECT DISTINCT idTurno, descripcion FROM c_turno ORDER BY descripcion");
                    while ($rs = mysql_fetch_array($query)) {
                        $s = "";
                        if (isset($_GET['turno']) && $_GET['turno'] != "0" && $_GET['turno'] == $rs['idTurno']) {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        }
                        echo "<option value='" . $rs['idTurno'] . "' $s>" . $rs['descripcion'] . " </option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td style="text-align:right">Ordenar por: </td>
            <td><select id="ordenar" name="ordenar" class="filtroselect">
                <option value="">Sin opcion</option>
                <option value="1">Campaña</option>
                <option value="2">Nombre de empleado</option>
                <option value="3">Operador</option>
                <option value="4">Turno</option>
            </select></td>
            <td><input type="checkbox" name="ascendente" value="Ascendente">Orden ascendente</td>
        </tr>
    </table>
    <br/><br/>
    <table>
        <tr>
            <td><input type="button" id="reporteServicios" class="boton" value="Reporte de Servicios"/></td>
            <td><input type="button" id="reporteAdministracion" class="boton" value="Reporte de administración"/></td>
            <td><input type="button" id="miReporte" class="boton" value="Mi plantilla"/></td>
            <td><input type="button" id="reporteMovimientos" class="boton" value="Reporte de campaña"/></td>
            <td><input type="button" id="reporteViajes" class="boton" value="Reporte de viajes"/></td>
        </tr>
    </table>
</form>
<div id="tablamensajeinfo"></div>
<div id="tablainfo"></div>
