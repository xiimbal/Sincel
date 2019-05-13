<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_GET['id'])) {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Lectura.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");

$permisos_grid = new PermisosSubMenu();
$lectura = new Lectura();
$parametros = new Parametros();
$pedir_contador = 1;
if ($parametros->getRegistroById("13")) {//Pedir contadores
    if ($parametros->getValor() == "0") {
        $pedir_contador = "0";
    }
}

$idSolicitud = $_GET['id'];
$cabeceras = array("Modelo", "Estado equipo", "Localidad", "Almacén", "No Serie");
$catalogo = new Catalogo();
$query = $catalogo->obtenerLista("SELECT c_solicitud.comentario, c_cliente.NombreRazonSocial, c_solicitud.id_almacen, c_solicitud.ClaveCliente 
    FROM c_solicitud INNER JOIN c_cliente ON c_cliente.ClaveCliente = c_solicitud.ClaveCliente WHERE id_solicitud = " . $idSolicitud . ";");
$comentario = "";
$cliente = "";
$ClaveCliente = "";
$IdAlmacenDestino = "";
while ($rs = mysql_fetch_array($query)) {
    $comentario = $rs['comentario'];
    $cliente = $rs['NombreRazonSocial'];
    $ClaveCliente = $rs['ClaveCliente'];
    $IdAlmacenDestino = $rs['id_almacen'];
}

$consulta = "SELECT ks.*, e.Modelo AS modelo_equipo, e.NoParte AS parte, c.ClaveCentroCosto ,c.Nombre, cli.NombreRazonSocial, ti.Nombre AS estadoEquipo, ti.idTipo,k_enviosmensajeria.NoSerie AS Serie,calm.nombre_almacen AS Almacen FROM `k_solicitud` AS ks
    INNER JOIN c_equipo AS e ON ks.id_solicitud = $idSolicitud AND ks.tipo = 0 AND e.NoParte = ks.Modelo LEFT JOIN c_centrocosto AS c ON c.ClaveCentroCosto = ks.ClaveCentroCosto LEFT JOIN c_cliente AS cli ON cli.ClaveCliente = c.ClaveCliente 
    LEFT JOIN c_tipoinventario AS ti ON ti.idTipo = ks.TipoInventario INNER JOIN k_enviosmensajeria ON k_enviosmensajeria.IdSolicitud=$idSolicitud 
    LEFT JOIN k_almacenequipo AS kale ON kale.NoSerie=k_enviosmensajeria.NoSerie LEFT JOIN c_almacen AS calm ON calm.id_almacen=kale.id_almacen
    ORDER BY ks.ClaveCentroCosto;";
$query = $catalogo->obtenerLista($consulta);

//echo $consulta;
?>
<!DOCTYPE html>
<html lang="es">
    <head>        
        <script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/lista_solicitudes_cancelaciones.js"></script>        
    </head>
    <body>
        <div class="principal">     
            <a href='reportes/SolicitudEquipo.php?noSolicitud=<?php echo $idSolicitud; ?>' target="_blank" title='Reporte' style="float: right;"><img src="resources/images/icono_impresora.png" width="30" height="30" /></a>
            <br/><br/>
            Solicitud <b>#<?php echo $idSolicitud; ?></b> para el cliente: <h2><?php echo $cliente; ?></h2><br/>            
            <form id='formSerie' name='formSeries'>                
                Comentarios:
                <textarea id="comentario_solicitud" name="comentario_solicitud" style="resize: none; width: 100%; height: 40px;"><?php echo $comentario ?></textarea>
                <br/>
                <h3>Equipos</h3>
                <table id="tAlmacen" style="max-width: 100%;">
                    <thead>
                        <tr>
                            <?php
                            for ($i = 0; $i < (count($cabeceras)); $i++) {
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                            }
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<tr>";
                            echo "<td align='center' scope='row'>" . $rs['modelo_equipo'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['estadoEquipo'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['Nombre'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['Almacen'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['Serie'] . "</td>";
                            echo "<td align='center' scope='row'>
                                            <a href='#' onclick='CancelarSerie(\"WEB-INF/Controllers/Ventas/Controller_Cambio_Equipo_Solicitud.php?serie=" . $rs['Serie'] . "&almacen=" . $rs['Almacen'] . "&loc=" . $rs['ClaveCentroCosto'] . "\",\"\"); return false'><img src='resources/images/Erase.png' title='Cancelar el equipo'/></a>
                                           </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <input type='submit' class='button' id='submit_series' name='submit_series' onclick="cambiarContenidos('ventas/list_sol_equipo.php', 'Solicitudes');" value='Terminar' style='float: right;'/>
                <br/><br/><br/><br/>
            </form>
        </div>
    </body>
</html>