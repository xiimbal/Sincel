<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/PermisosSubMenu.class.php");
include_once("../../Classes/Retiro.class.php");
$catalogo = new Catalogo();
$id_noserie = "";
if (isset($_POST['id'])) {
    $id_noserie = $_POST['id'];
} else {
    $id_noserie = $_SESSION['idUsuario'];
}
$llamadas = ""; //script llamando funciones para que se valide el minimo que debe ingresar
$cliente = "";
if (isset($_POST['cliente'])) {
    $cliente = $_POST['cliente'];
}
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/MovimientoEquipo.js"></script>
<script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
<script>
    $(document).ready(function() {
        $('.boton').button().css('margin-top', '20px');
    });
</script>
<style type="text/css">
    .tamanoinput {width: 95px;}
</style>
<?php
$retiro = new Retiro();
$permiso = new PermisosSubMenu();
if ($retiro->tieneRetiro($_POST['nserie']) && $permiso->tienePermisoEspecial($_SESSION['idUsuario'], 11)) {
    ?>
    <button style="float: right; margin-right: 7px;" class="boton" name="botonretiro" id="botonretiro" onclick="cambiarEquipo('<?php echo $_POST['nserie'] ?>')">Aceptar Retiro</button>
    <br/>
    <br/>
    <?php
}
?>
<!--    MAGG: Historico de movimientos   
<a href="#" id="muestra_historico" onclick="mostrarDetalle();
        return false;" style="float: right; margin-right: 7px;">Mostrar hist&oacute;rico de movimientos</a>-->
   <?php
   $consulta = "SELECT 
        meq.comentario AS Comentario,meq.id_movimientos, meq.tipo_movimiento, meq.NoSerie, meq.pendiente, meq.Fecha, CONCAT(c1.ClaveCliente,' - ',c1.NombreRazonSocial) AS cliente_anterior, CONCAT(cc1.ClaveCentroCosto, ' - ' ,cc1.Nombre) AS centro_anterior,
        CONCAT(c2.ClaveCliente,' - ',c2.NombreRazonSocial) AS cliente_nuevo, CONCAT(cc2.ClaveCentroCosto, ' - ' ,cc2.Nombre) AS centro_nuevo,
        alm1.nombre_almacen AS almacen_anterior, alm2.nombre_almacen AS almacen_nuevo,
        reportes_movimientos.id_reportes as noReporte,
        cl.ContadorBNML, cl.ContadorBNPaginas, cl.ContadorColorML, cl.ContadorColorPaginas, cl.NivelTonAmarillo, cl.NivelTonCian, cl.NivelTonMagenta, cl.NivelTonNegro
        FROM `movimientos_equipo` AS meq 
        inner join reportes_movimientos on reportes_movimientos.id_movimientos=meq.id_movimientos
        LEFT JOIN c_cliente AS c1 ON c1.ClaveCliente = meq.clave_cliente_anterior
        LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = meq.clave_cliente_nuevo
        LEFT JOIN c_centrocosto AS cc1 ON cc1.ClaveCentroCosto = meq.clave_centro_costo_anterior
        LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = meq.clave_centro_costo_nuevo
        LEFT JOIN c_almacen AS alm1 ON alm1.id_almacen = meq.almacen_anterior
        LEFT JOIN c_almacen AS alm2 ON alm2.id_almacen = meq.almacen_nuevo
        LEFT JOIN c_lectura AS cl ON cl.IdLectura = meq.id_lectura
        WHERE meq.NoSerie = '" . $_POST['nserie'] . "';";
   $query = $catalogo->obtenerLista($consulta);
   ?>
<br/><br/>
<div class="info-extra table-responsive" style="display: none;">
    <table id="tAlmacen" style="width: 100%;" class="table">
        <thead class="thead-dark">
            <tr>
                <?php
                $cabeceras = array("No serie", "Fecha", "Cliente anterior", "Localidad anterior", "Cliente nuevo", "Localidad nueva", "Almacen anterior", "Almacen nuevo", "Comentario", "");
                for ($i = 0; $i < (count($cabeceras)); $i++) {
                    echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                }
                ?>                        
            </tr>
        </thead>
        <tbody>
            <?php
            while ($rs = mysql_fetch_array($query)) {/* Recorremos todos los tickets resultantes del query */
                echo "<tr>";
                echo "<td>" . $rs['NoSerie'] . "</td>";
                echo "<td>" . $rs['Fecha'] . "</td>";
                echo "<td>" . $rs['cliente_anterior'] . "</td>";
                echo "<td>" . $rs['centro_anterior'] . "</td>";
                echo "<td>" . $rs['cliente_nuevo'] . "</td>";
                echo "<td>" . $rs['centro_nuevo'] . "</td>";
                echo "<td>" . $rs['almacen_anterior'] . "</td>";
                echo "<td>" . $rs['almacen_nuevo'] . "</td>";
                echo "<td>" . $rs['Comentario'] . "</td>";
                //if ($rs['pendiente'] == "0") {/* Ya no está pendiente por autorizar */
                echo "<td align='center' scope='row'><a href='WEB-INF/Controllers/Ventas/Controller_Reporte_Historico.php?noSolicitud=" . $rs['noReporte'] . "' target='_blank'>Generar reporte</a></td>";
                //} else {/* Esta pendiente por autorizar */
                //    echo "<td align='center' scope='row'>Pendiente por autorizar</td>";
                //}
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>    
<!--    Fin de MAGG -->
<script>
    mostrarDetalle();
</script>