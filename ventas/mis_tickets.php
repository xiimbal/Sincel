<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/ParametroGlobal.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/mis_tickets.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$parametroGlobal = new ParametroGlobal();
$pantalla_edicion = "mesa/alta_ticketphp.php";
if($parametroGlobal->getRegistroById(28) && $parametroGlobal->getActivo() == "1"){
    $pantalla_edicion = $parametroGlobal->getValor();
}

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
         <!--link responsivo-->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <link href="resources/css/Bootstrap 4/fontawesome/css/all.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="principal">            
            <br/><br/><br/>
            <table id="tAlmacen" class="table-responsive">
                <thead>
                    <tr>
                        <?php
                        $cabeceras = array("Ticket", "Fecha", "No Serie", "Cliente", "Localidad", "Área de atención", "Ubicación", "Falla", "Último estatus ticket", "Última Nota", "Fecha nota", "", "", "");
                        for ($i = 0; $i < (count($cabeceras)); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }
                        ?>                        
                    </tr>
                </thead>
                <tbody>
                    <?php
                    /* Inicializamos la clase */
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT
                    t.IdTicket,
                    t.FechaHora,
                    t.DescripcionReporte,
                    t.NombreCentroCosto, 
                    t.TipoReporte,
                    (SELECT CASE WHEN e2.Nombre = 'Suministro' THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie,
                    DATEDIFF(NOW(),t.FechaHora) AS diferencia,
                    t.NombreCliente,
                    e.Nombre AS estado,
                    e.IdEstadoTicket AS estadoTicket,
                    e1.Nombre AS tipo,
                    t.ClaveCentroCosto,
                    cl.ClaveCliente,	
                    cee.color,
                    tc.Nombre AS tipoCliente,
                    (CASE WHEN !ISNULL(ar.IdArea) THEN ar.Descripcion ELSE ar2.Descripcion END) AS area, 
                    (CASE WHEN !ISNULL(ar.IdArea) THEN ar.IdArea ELSE ar2.IdArea END) AS idArea, 
                    u.Nombre AS ubicacion,
                    nt.IdEstatusAtencion,
                    (SELECT CONCAT(ce.Nombre,\"**__**\",nt2.DiagnosticoSol,\"**__**\",nt2.FechaHora) FROM c_estado AS ce INNER JOIN c_notaticket AS nt2 ON nt2.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket) AND ce.IdEstado = nt2.IdEstatusAtencion) AS UltimoEstatus
                    FROM c_ticket AS t
                    INNER JOIN c_cliente AS c ON t.ClaveCliente = c.ClaveCliente
                    INNER JOIN c_usuario AS usu ON usu.IdUsuario = " . $_SESSION['idUsuario'] . " AND usu.IdUsuario = c.EjecutivoCuenta
                    INNER JOIN c_estadoticket AS e ON e.IdEstadoTicket = t.EstadoDeTicket
                    INNER JOIN c_estado AS e1 ON e1.IdEstado = t.TipoReporte
                    INNER JOIN c_cliente AS cl ON cl.ClaveCliente = t.ClaveCliente
                    INNER JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
                    INNER JOIN c_estado AS e2 ON e2.IdEstado = t.AreaAtencion
                    LEFT JOIN c_area AS ar2 ON ar2.IdArea = e2.IdArea
                    LEFT JOIN c_ubicacionticket AS u ON u.IdUbicacion = t.Ubicacion
                    LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)
                    LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado 
                    LEFT JOIN c_area AS ar ON ar.IdArea = e3.IdArea
                    LEFT JOIN c_escalamientoEstado AS cee ON (cee.idEstado = nt.IdEstatusAtencion AND cee.prioridad = t.Prioridad)
                    WHERE c.Suspendido = 0
                    HAVING (IdEstatusAtencion <> 16 AND IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion)
                    ORDER BY IdTicket;");
                    while ($rs = mysql_fetch_array($query)) {
                        /*                         * *********************    Obtenemos el color de la fila   ******************************** */
                        $datos = explode("**__**", $rs['UltimoEstatus']);
                        $color = "#F7F7DE";
                        if (isset($datos[0]) && isset($datos[1])) {/* Si hay estado de la ultima nota */
                            if (strtoupper($datos[0]) != "CERRADO") {/* Si el ticket no esta cerrado */
                                if (strtoupper($rs['tipoCliente']) == "VIP") {/* Si el cliente es VIP */
                                    if (number_format($rs['diferencia']) >= 2) {/* Si ya van mas de 7 dias que se levanto el ticket */
                                        $color = "#DC381F";
                                    } else {
                                        $color = "#FFF380";
                                    }
                                } else {/* Si no es cliente VIP */
                                    if (number_format($rs['diferencia']) >= 7) {/* Si ya van mas de 7 dias que se levanto el ticket */
                                        $color = "#DC381F";
                                    }
                                }
                            }
                        } else {/* Si no hay notas, vemos el estado del ticket */
                            if (strtoupper($rs['estado']) != "CERRADO") {/* Si el ticket no esta cerrado */
                                if (strtoupper($rs['tipoCliente']) == "VIP") {/* Si el cliente es VIP */
                                    if (number_format($rs['diferencia']) >= 2) {/* Si ya van mas de 7 dias que se levanto el ticket */
                                        $color = "#DC381F";
                                    } else {
                                        $color = "#FFF380";
                                    }
                                } else {/* Si no es cliente VIP */
                                    if (number_format($rs['diferencia']) >= 7) {/* Si ya van mas de 7 dias que se levanto el ticket */
                                        $color = "#DC381F";
                                    }
                                }
                            }
                        }

                        echo "<tr style='background-color: $color; color:black;'>";
                        echo "<td align='center' scope='row'>" . $rs['IdTicket'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['FechaHora'] . "</td>";
                        if (isset($rs['id_bitacora'])) {
                            echo "<td align='center' scope='row'><a href='#' onclick='editarRegistro(\"almacen/alta_bitacora.php?consulta_tiquet=ventas/mis_tickets.php\",\"" . $rs['id_bitacora'] . "\"); return false;'>" . $rs['NumSerie'] . "</a></td>";
                        } else {
                            $series = explode(",", $rs['NumSerie']);
                            $texto = "";
                            foreach ($series as $value) {
                                $texto.= "<a href='#' onclick='cambiarContenidos(\"almacen/alta_bitacora.php?consulta_tiquet=ventas/mis_tickets.php&NoSerie=$value\"); return false;'>" . $value . "</a>,";
                            }
                            $texto = substr($texto, 0, strlen($texto) - 1);
                            echo "<td align='center' scope='row'>$texto</td>";
                        }
                        echo "<td align='center' scope='row'>" . $rs['NombreCliente'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['NombreCentroCosto'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['area'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['ubicacion'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['DescripcionReporte'] . "</td>";

                        if (isset($datos[0])) {
                            $c = "";
                            if(isset($rs['color'])){
                                $c = "bgcolor = '".$rs['color']."'";
                            }
                            echo "<td align='center' scope='row' $c>" . $datos[0] . "</td>";
                        } else {
                            echo "<td align='center' scope='row'></td>";
                        }
                        if (isset($datos[1])) {
                            echo "<td align='center' scope='row'>" . $datos[1] . "</td>";
                        } else {
                            echo "<td align='center' scope='row'></td>";
                        }
                        if (isset($datos[2])) {
                            echo "<td align='center' scope='row'>" . $datos[2] . "</td>";
                        } else {
                            echo "<td align='center' scope='row'></td>";
                        }
                        if ($rs['idArea'] == "2") {
                            $src = $_SESSION['liga'] . "/Operacion/MesaServicio/ConsultaDetalleTicketToner.aspx?Operacion=&IdTicket=" . $rs['IdTicket'] . "&Vista=Detalle&uguid=" . $_SESSION['user'];
                        } else {
                            $src = $_SESSION['liga'] . "/Operacion/MesaServicio/ConsultaDetalleTicketFalla.aspx?Operacion=&IdTicket=" . $rs['IdTicket'] . "&Vista=Detalle&uguid=" . $_SESSION['user'];
                        }
                        $fecha_limite = strtotime("2014-03-31");
                        $fecha_ticket = strtotime($rs['FechaHora']);
                        if ($fecha_ticket >= $fecha_limite) {
                            $nuevo = true;
                        } else {
                            $nuevo = false;
                        }
                        ?>                    
                    <td align='center' scope='row'> 
                        <?php if ($permisos_grid->getConsulta()) { ?>
                            <?php
                            if ($nuevo) {
                                ?>
                                <a href='#' onclick='detalleTicket("mesa/alta_ticketphp.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['TipoReporte']; ?>", "1");
                                                    return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                               <?php } else { ?>
                                <a href='#' onclick='lanzarPopUp("Detalle", "<?php echo $src; ?>");
                                                    return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                                   <?php
                               }
                               ?>
                           <?php } ?>
                    </td>

                    <?php
                    if ($rs['estadoTicket'] != "2" && $rs['estadoTicket'] != "4" && $rs['IdEstatusAtencion'] != "16" && $rs['IdEstatusAtencion'] != "59" && $permisos_grid->getModificar()) {
                        ?>
                        <td align='center' scope='row'>
                            <?php
                            if ($nuevo) {
                                ?>
                                <a href='#' onclick='editarTicket("<?php echo $pantalla_edicion; ?>", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['idArea']; ?>", "0");
                                                    return false;' title='Modificar' ><img src="resources/images/Modify.png"/></a>
                               <?php } else { ?>
                                <a href='#' onclick='lanzarPopUp("Modificar", "<?php echo $src; ?>");
                                                    return false;' title='Modificar' ><img src="resources/images/Modify.png"/></a>
                                   <?php
                               }
                               ?>
                        </td>
                        <?php
                    } else {
                        echo "<td align='center' scope='row'></td>";
                    }
                    ?>
                    <td align='center' scope='row'>
                        <?php
                        if ($nuevo) {
                            ?>
                            <a href='#' onclick='detalleReporte("reportes/reporte_ticket.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['idArea']; ?>", "0");
                                            return false;' title='Reporte' ><img src="resources/images/icono_impresora.png" width="35" height="35"/></a>
                           <?php } else { ?>
                            <a href='<?php echo $_SESSION['liga']; ?>/operacion/MesaServicio/ReporteTicket.aspx?IdTicket=<?php echo $rs['IdTicket']; ?>&uguid=<?php echo $_SESSION['user']; ?>' target="_blank" title='Reporte' ><img src="resources/images/icono_impresora.png" width="35" height="35"/></a>
                            <?php
                        }
                        ?>
                    </td>
                    <?php
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
        <input type="hidden" id="regresar" name="regresar" value="<?php echo $same_page; ?>"/>
    </body>
</html>