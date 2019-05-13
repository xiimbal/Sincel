<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/list_sol_equipo.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$usuario = new Usuario();

$mostrar = 0;
if (isset($_GET['mostrar'])) {
    $mostrar = $_GET['mostrar'];
}
$catalogo = new Catalogo();
$query = $catalogo->obtenerLista("SELECT c_puesto.IdPuesto FROM `c_usuario` INNER JOIN c_puesto ON c_usuario.IdPuesto=c_puesto.IdPuesto WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario']);
$rs = mysql_fetch_array($query);

if ($usuario->isUsuarioPuesto($_SESSION['idUsuario'], "24") || $usuario->isUsuarioPuesto($_SESSION['idUsuario'], "27")) {/* Si es de almacen o gerente de almacen */
    if ($mostrar == 1) {
        $where = "WHERE (c_solicitud.estatus=1 OR c_solicitud.estatus=5) OR (id_crea = " . $_SESSION['idUsuario'] . ")";
    } else {
        $where = "WHERE (c_solicitud.estatus=1) OR (id_crea = " . $_SESSION['idUsuario'] . ")";
    }
} else {/* Si no es de almacen */
    if ($mostrar == 1) {
        $where = "";
    } else {
        $where = " WHERE (c_solicitud.estatus=0 OR c_solicitud.estatus=1 OR c_solicitud.estatus=2) ";
    }

    if ($rs['IdPuesto'] == 11) {
        if ($mostrar == 1) {
            $where = "WHERE c_solicitud.id_crea=" . $_SESSION['idUsuario'];
        } else {
            $where .= "  AND c_solicitud.id_crea=" . $_SESSION['idUsuario'];
        }
    }
}

$cabeceras = array("Número de solicitud", "Fecha", "Cliente", "Localidades", "Número de equipos", "Número de componentes", "Tipo", "Venta directa", "Status", "Editar", "Imprimir", "", "");
$columnas = array("ID", "Fecha", "Cliente", "localidades", "NumEquipos", "NumCompo", "TipoSolicitud", "VentaDirecta");

$consulta = "SELECT
    c_solicitud.fecha_solicitud AS Fecha,
    c_cliente.NombreRazonSocial AS Cliente,
    c_tiposolicitud.Nombre AS TipoSolicitud,
    (CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno)) AS UsuarioAutorizo,
    c_solicitud.id_tiposolicitud AS IdTipoSolicitud,
    (SELECT CASE WHEN !ISNULL(c_ventadirecta.IdVentaDirecta) THEN c_ventadirecta.IdVentaDirecta ELSE 'N/A' END) AS VentaDirecta,
    (SELECT CASE WHEN !ISNULL(c_ventadirecta.id_prefactura) THEN c_ventadirecta.id_prefactura ELSE '' END) AS Prefactura,
    SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) AS NumEquipos,
    SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) AS NumCompo,
    es.NombreEstatus  AS Status,
    c_solicitud.id_solicitud AS ID,
    c_solicitud.estatus AS idEstatus,
    (SELECT group_concat(Nombre separator ', ') FROM c_centrocosto AS cc, k_solicitud AS ks
    WHERE ks.id_solicitud = c_solicitud.id_solicitud AND cc.ClaveCentroCosto = ks.ClaveCentroCosto GROUP BY k_solicitud.id_solicitud) AS localidades
    FROM c_solicitud
    INNER JOIN k_solicitud ON k_solicitud.id_solicitud = c_solicitud.id_solicitud
    INNER JOIN c_cliente ON c_solicitud.ClaveCliente = c_cliente.ClaveCliente
    INNER JOIN c_tiposolicitud ON c_tiposolicitud.IdTipoMovimiento = c_solicitud.id_tiposolicitud
    LEFT JOIN c_usuario AS u ON u.IdUsuario = c_solicitud.id_autoriza
    LEFT JOIN c_ventadirecta ON c_ventadirecta.id_solicitud = c_solicitud.id_solicitud
    LEFT JOIN c_estatussolicitud AS es ON es.IdEstatusSolicitud = c_solicitud.estatus
    $where
    GROUP BY ID DESC;";
$query = $catalogo->obtenerLista($consulta);

$alta = "ventas/NuevaSolicitud.php";

$permiso_serie = false;
if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 1) || $permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 2)) {
    $permiso_serie = true;
}

$permiso_imprimir = false;
if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 3)) {
    $permiso_imprimir = true;
}

$factura_vd = false;
if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 24)) {
    $factura_vd = true;
}
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/lista_sol_equipo.js"></script>
<?php if ($permisos_grid->getAlta()) { ?>
    <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("ventas/NuevaSolicitud.php", "Solicitudes");' style="float: right; cursor: pointer;" />  
<?php } ?>
<br/><br/><br/>
<div style="float: right;">
    <a href="ventas/list_sol_equipoXLS.php?mostrar=<?php echo $mostrar; ?>" target="_blank" class="boton"><img src="resources/images/excel.png"></a>
    <label for="checksc">Surtidas y Rechazadas</label><input type="checkbox" id="checksc" value="1" onchange="surtidasycanceladas();" <?php
    if ($mostrar == 1) {
        echo "checked";
    }
    ?>/>  
</div><br/><br/>
<table id="tsolequipo" style="width: 100%;">
    <thead>
        <tr>
            <?php
            for ($i = 0; $i < (count($cabeceras) - 2); $i++) {
                echo "<th align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
            }
            echo "<th align=\"center\" scope=\"col\">Series</th>";
            echo "<th align=\"center\" scope=\"col\">Facturar</th>";
            echo "<th align=\"center\" scope=\"col\">Cancelar</th>";
            ?>                        
        </tr>
    </thead>
    <tbody>
        <?php
        $contador = 0;
        if ($mostrar == 0) {
            while ($rs = mysql_fetch_array($query)) {
                if ($rs['Status'] != "Surtida" || $rs['Status'] != "Cancelada") {
                    echo "<tr>";
                    for ($i = 0; $i < count($columnas); $i++) {
                        echo "<td align='center' scope='row'>" . $rs[$columnas[$i]] . "</td>";
                    }
                    if ($rs['idEstatus'] != "0") {
                        echo "<td align='center' scope='row'>" . $rs['Status'] . "/" . $rs['UsuarioAutorizo'] . "</td>";
                    } else {
                        echo "<td align='center' scope='row'>" . $rs['Status'] . "</td>";
                    }
                    if ($rs['Status'] != "Surtida" || $rs['Status'] != "Cancelada" || $rs['Status'] != "Autorizada") {
                        if ($rs['Status'] != "Surtida" && $permisos_grid->getModificar()) {
                            echo "<td align='center' scope='row'> <a href='#' onclick='editarRegistro(\"" . $alta . "\", \"" . $rs[$columnas[0]] . "\");return false;' title='Editar Registro' ><img src=\"resources/images/Modify.png\"/></a></td>";
                        } else {
                            echo "<td></td>";
                        }
                    } else {
                        echo "<td></td>";
                    }
                    ?>                
                <td align='center' scope='row' style="min-width: 30px;"> 
                    <?php
                    if ($permiso_imprimir && $rs['idEstatus'] <> "0") {
                        if ($rs['VentaDirecta'] == "N/A") {
                            ?>
                            <a href='reportes/SolicitudEquipo.php?noSolicitud=<?php echo $rs['ID']; ?>' target="_blank" title='Reporte' ><img src="resources/images/icono_impresora.png" width="24" height="24"/></a>
                        <?php } else {
                            ?>
                            <a href='ventas/imprimir_ventad.php?id=<?php echo $rs['VentaDirecta']; ?>' target="_blank" title='Reporte' ><img src="resources/images/icono_impresora.png" width="24" height="24"/></a>
                            <?php
                        }
                    }
                    ?>
                </td>  
                <?php
                if (($rs['idEstatus'] == "1" || $rs['idEstatus'] == "5") && $permiso_serie /* && $rs['VentaDirecta'] == "N/A" */) {/* Si no hay equipos, no pone series */
                    if ($rs['idEstatus'] == "1") {
                        ?>
                        <td align='center' scope='row'> <a href='#' onclick="cambiarContenidos('ventas/lista_solicitud_series.php?id=<?php echo $rs['ID']; ?>', 'Serie de equipos');
                                                    return false;" title='Agregar series'><img src="resources/images/Apply.png" width="24" height="24"/></a></td>  
                            <?php
                        }
                        if ($rs['idEstatus'] == "5") {
                            ?>
                        <td align='center' scope='row'> 
                            <!--<a href='#' onclick="cambiarContenidos('ventas/CancelacionSeries.php?id=<?php //echo $rs['ID'];   ?>', 'Cancelacion Serie de equipos');
                            return false;" title='Cancelar series'><img src="resources/images/Apply.png" width="24" height="24"/></a>-->
                        </td>  
                        <?php
                    }
                } else {
                    echo "<td></td>";
                }
                ?>
                <?php
            }
            echo "<td align='center' scope='row' style=\"max-width: 10px;\">";
            if($factura_vd && $rs['VentaDirecta'] != "N/A" && ($rs['idEstatus'] == "1" || $rs['idEstatus'] == "5")){
                echo "<a onclick=\"facturarvd('" . $rs['VentaDirecta'] . "','".$rs['ID']."');\" title='' style='cursor:pointer;'>
                    <img src=\"resources/images/facturar.png\"/></a>";
            }            
            echo "</td>";
            echo "<td align='center' scope='row' style=\"max-width: 10px;\">";
            if ($permisos_grid->getBaja()) {
                echo "<a  onclick=\"cancelarsolicitud('" . $rs['ID'] . "');\" title=''>
                    <img src=\"resources/images/Erase.png\" style=\"cursor: pointer;\"/></a>";
            }
            echo "</td>";

            echo "</tr>";
            $contador++;
        }
    } else {
        while ($rs = mysql_fetch_array($query)) {
            echo "<tr>";
            for ($i = 0; $i < count($columnas); $i++) {
                echo "<td align='center' scope='row'>" . $rs[$columnas[$i]] . "</td>";
            }
            if ($rs['idEstatus'] != "0") {
                echo "<td align='center' scope='row'>" . $rs['Status'] . "/" . $rs['UsuarioAutorizo'] . "</td>";
            } else {
                echo "<td align='center' scope='row'>" . $rs['Status'] . "</td>";
            }

            if ($rs['Status'] != "Surtida" || $rs['Status'] != "Cancelada" || $rs['Status'] != "Autorizada") {
                if ($rs['Status'] != "Surtida" && $permisos_grid->getModificar()) {
                    echo "<td align='center' scope='row'> <a href='#' onclick='editarRegistro(\"" . $alta . "\", \"" . $rs[$columnas[0]] . "\");return false;' title='Editar Registro' ><img src=\"resources/images/Modify.png\"/></a></td>";
                } else {
                    echo "<td></td>";
                }
            } else {
                echo "<td></td>";
            }
            ?>            
            <td align='center' scope='row'>
                <?php
                if ($permiso_imprimir) {
                    if ($rs['VentaDirecta'] == "N/A") {
                        ?>
                        <a href='reportes/SolicitudEquipo.php?noSolicitud=<?php echo $rs['ID']; ?>' target="_blank" title='Reporte' ><img src="resources/images/icono_impresora.png" width="24" height="24"/></a>
                    <?php } else {
                        ?>
                        <a href='ventas/imprimir_ventad.php?id=<?php echo $rs['VentaDirecta']; ?>' target="_blank" title='Reporte' ><img src="resources/images/icono_impresora.png" width="24" height="24"/></a>
                        <?php
                    }
                }
                ?>
            </td>  
            <?php
            if (($rs['idEstatus'] == "1" || $rs['idEstatus'] == "5") && $permiso_serie && $rs['VentaDirecta'] == "N/A") {/* Si no hay equipos, no pone series */
                if ($rs['idEstatus'] == "1") {
                    ?>
                    <td align='center' scope='row'> <a href='#' onclick="cambiarContenidos('ventas/lista_solicitud_series.php?id=<?php echo $rs['ID']; ?>', 'Serie de equipos');
                                            return false;" title='Agregar series'><img src="resources/images/Apply.png" width="24" height="24"/></a></td>  
                        <?php
                    }
                    if ($rs['idEstatus'] == "5") {
                        ?>
                    <td align='center' scope='row'> 
                        <!--<a href='#' onclick="cambiarContenidos('ventas/CancelacionSeries.php?id=<?php //echo $rs['ID'];   ?>', 'Cancelacion Serie de equipos');
                            return false;" title='Agregar series'><img src="resources/images/Apply.png" width="24" height="24"/></a>-->
                    </td>  
                    <?php
                }
            } else {
                echo "<td></td>";
            }
            ?>
            <?php
            echo "<td align='center' scope='row' style=\"max-width: 10px;\">";
            if($factura_vd && $rs['VentaDirecta'] != "N/A" && ($rs['idEstatus'] == "1" || $rs['idEstatus'] == "5")){
                echo "<a  onclick=\"facturarvd('" . $rs['VentaDirecta'] . "','".$rs['ID']."');\" title=''>
                    <img src=\"resources/images/facturar.png\"/></a>";
            }            
            echo "</td>";
            echo "<td align='center' scope='row' style=\"max-width: 10px;\">";
            if ($permisos_grid->getBaja()) {
                echo "<a  onclick=\"cancelarsolicitud('" . $rs['ID'] . "');\" title=''>
                    <img src=\"resources/images/Erase.png\"/></a>";
            }
            echo "</td>";
            echo "</tr>";
            $contador++;
        }
    }
    ?>
</tbody>
</table>