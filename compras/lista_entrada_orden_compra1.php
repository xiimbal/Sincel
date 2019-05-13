<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$catalogo = new Catalogo();
$alta = "compras/alta_entrada_orden_compra.php";
$reporte = "compras/reporte_entrada_orden_compra.php";
$same_page = "compras/lista_entrada_orden_compra.php";
$permisos_grid = new PermisosSubMenu();
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$controlador = "WEB-INF/Controllers/compras/Controler_Orden_Compra.php";
$permiso_esp_almacen = $permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 21);
$permiso_esp_recibo = $permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 20);
//$idUser = "106";
//$permiso_esp_almacen = $permisos_grid->tienePermisoEspecial($idUser, 21);
//$permiso_esp_recibo = $permisos_grid->tienePermisoEspecial($idUser, 20);
$entrada = "";
$fInicio = "";
$fFin = "";
$oc = "";
$estatus = "";
$where = "";
$whereFecha = "";
$surtido = "";
$whereEstatus = "";
$having = "";
$no_pedido = "";
$where_prin = "";
//"oc": oc, "fi": fi, "ff": ff, "estatus": estatus
if (isset($_POST['no_pedido']) && $_POST['no_pedido'] != "") {
    $surtido = "checked";
    $no_pedido = $_POST['no_pedido'];
    $where_prin = " WHERE oc.NoPedido='$no_pedido' AND (est.IdEstado=71 OR est.IdEstado=72 OR est.IdEstado=70)";
} else if (isset($_POST['oc']) && $_POST['oc'] != "") {
    $surtido = "checked";
    $oc = $_POST['oc'];
    $where_prin = " WHERE oc.Id_orden_compra='$oc' AND (est.IdEstado=71 OR est.IdEstado=72 OR est.IdEstado=70)";
}
if (isset($_POST['surtido']) && $_POST['surtido'] == "1") {
    $surtido = "checked";
    $whereEstatus = " OR est.IdEstado=70 ";
}
if (isset($_POST['fi']) && $_POST['fi'] != "" && isset($_POST['ff']) && $_POST['ff'] != "") {
    $fFin = $_POST['ff'];
    $fInicio = $_POST['fi'];
    if ($having == "") {
        $having = " HAVING fecha BETWEEN '$fInicio' AND '$fFin'";
    } else {
        $having = " AND  fecha BETWEEN '$fInicio' AND '$fFin'";
    }
}
if (isset($_POST['estatus']) && $_POST['estatus'] != "0") {
    $estatus = $_POST['estatus'];
    $where = "AND oc.Estatus='$estatus'";
}
if ($permiso_esp_almacen && !$permiso_esp_recibo) {
    if ($having == "") {
        $having = " HAVING recibidas <>''";
    } else {
        $having = " AND  recibidas <>''";
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/compras/alta_entrada_orden_compra.js"></script>
        <script>
            $(".button").button();
        </script>
    </head>
    <body>
        <div class="principal">
            <!--<input type="button" value="Ejecutar cron" class="button" onclick="ejecutar_cron()"/>-->
            <table style="width: 100%;">
                <tr>
                    <td>Número de pedido:</td><td><input type="text" id="txt_no_ped" name="txt_no_ped" value="<?php echo $no_pedido ?>"/></td> 
                    <td>Orden de compra:</td><td><input type="text" id="txtOcL" name="txtOcL" value="<?php echo $oc; ?>"/></td> 
                    <td>Estatus:</td>
                    <td>
                        <select id="slEstatusL" name="slEstatusL" style="width: 155px">
                            <option value="0">Todos los estatus</option>
                            <?php
                            $queryEsatus = $catalogo->obtenerLista("SELECT e.IdEstado,e.Nombre FROM c_estado e INNER JOIN k_flujoestado fe ON e.IdEstado=fe.IdEstado INNER JOIN c_flujo f ON fe.IdFlujo=f.IdFlujo WHERE f.IdFlujo=9 ORDER BY e.Nombre ASC");
                            while ($rs = mysql_fetch_array($queryEsatus)) {
                                $s = "";
                                if ($estatus != "" && $estatus == $rs['IdEstado']) {
                                    $s = "selected";
                                }
                                echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                            }
                            ?>
                        </select>
                    </td> 
                    <td>Surtido</td><td><input type="checkbox" id="ckSurtido" name="ckSurtido" <?php echo $surtido; ?>></td>
                </tr>
                <tr>
                    <td>Fecha inicio:</td><td><input type="text" id="txtFechaInicioL" name="txtFechaInicioL" value="<?php echo $fInicio; ?>"/></td>
                    <td>Fecha fin:</td><td><input type="text" id="txtFechaFinL" name="txtFechaFinL" value="<?php echo $fFin; ?>"/></td> 
                    <td></td><td></td>
                </tr>

            </table>
            <br/>
            <input type="button" class="button" style="float: left" value="Buscar" onclick="BuscarEntradaOC();"/>
            <!--<img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>");' style="float: right; cursor: pointer;" />-->  
            <br/><br/><br/>
            <table id="tAlmacen" style="width: 100%;">
                <thead>
                    <tr>
                        <th align='center' scope='row' style="width: 8%">Orden compra</th>
                        <th align='center' scope='row' style="width: 8%">No. pedido</th>
                        <th align='center' scope='row' style="width: 8%">Fecha</th>
                        <th align='center' scope='row' style="width: 25%">Cantidad solicitada</th>
                        <th align='center' scope='row' style="width: 25%">Cantidad entregada </th>
                        <th align='center' scope='row' style="width: 8%">Precio</th>
                        <th align='center' scope='row' style="width: 8%">Estado</th>
                        <th align='center' scope='row' style="width: 5%">Consultar</th>
                        <th align='center' scope='row' style="width: 5%">Editar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($oc != "" || $no_pedido != "") {
                        $consulta = "SELECT oc.Id_orden_compra,oc.NoPedido,kd.Fecha AS fecha,GROUP_CONCAT('(',koc.Cantidad,') - ',IF(ISNULL(koc.NoParteEquipo),cm.Modelo,eq.Modelo) SEPARATOR ' ; ') AS solicitadas,
                                        GROUP_CONCAT('(',kd.CantidadEntrada,') - ',IF(ISNULL(koc.NoParteEquipo),cm.Modelo,eq.Modelo) SEPARATOR ' ; ') AS recibidas,
                                        FORMAT(SUM(kd.CantidadEntrada*koc.PrecioUnitario),2) AS total,est.Nombre AS estado FROM c_orden_compra oc 
                                        LEFT JOIN k_orden_compra koc ON oc.Id_orden_compra=koc.IdOrdenCompra LEFT JOIN c_equipo eq ON koc.NoParteEquipo=eq.NoParte
                                        LEFT JOIN c_componente cm ON koc.NoParteComponente=cm.NoParte LEFT JOIN c_estado est ON est.IdEstado=oc.Estatus
                                        LEFT JOIN k_detalle_entrada_orden_compra kd ON koc.IdDetalleOC=kd.idKOrdenTrabajo  $where_prin HAVING solicitadas<>'' || recibidas<>''";
                    } else {
                        $consulta = "SELECT oc.Id_orden_compra,oc.NoPedido,kd.Fecha AS fecha,	GROUP_CONCAT('(',koc.Cantidad,') - ',IF(ISNULL(koc.NoParteEquipo),cm.Modelo,eq.Modelo) SEPARATOR ' ; ') AS solicitadas,
                                    GROUP_CONCAT('(',kd.CantidadEntrada,') - ',IF(ISNULL(koc.NoParteEquipo),cm.Modelo,eq.Modelo) SEPARATOR ' ; ') AS recibidas,
                                    FORMAT(SUM(kd.CantidadEntrada*koc.PrecioUnitario),2) AS total,est.Nombre AS estado FROM c_orden_compra oc 
                                    LEFT JOIN k_orden_compra koc ON oc.Id_orden_compra=koc.IdOrdenCompra LEFT JOIN c_equipo eq ON koc.NoParteEquipo=eq.NoParte
                                    LEFT JOIN c_componente cm ON koc.NoParteComponente=cm.NoParte LEFT JOIN c_estado est ON est.IdEstado=oc.Estatus
                                    LEFT JOIN k_detalle_entrada_orden_compra kd ON koc.IdDetalleOC=kd.idKOrdenTrabajo WHERE est.IdEstado=71 OR est.IdEstado=72 $whereEstatus
                                    $where  GROUP BY  oc.Id_orden_compra $having";
                    }
                    $query = $catalogo->obtenerLista($consulta);
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td align='center' scope='row'>" . $rs['Id_orden_compra'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['NoPedido'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['fecha'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['solicitadas'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['recibidas'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['total'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['estado'] . "</td>";
                        ?>
                    <td align='center' scope='row'>
                        <a href='#' onclick='imprimirReporteEntradaOC("<?php echo $reporte; ?>", "<?php echo $rs['Id_orden_compra']; ?>");
                                return false;' title='Reporte' ><img src="resources/images/Textpreview.png" width="25" height="25"/></a>
                    </td>
                    <td align='center' scope='row'>
                        <?php if ($permisos_grid->getModificar()) { ?>
                            <a href='#' onclick='
                                    //editarRegistro("<?php //echo $alta; ?>", "<?php //echo $rs['Id_orden_compra']; ?>");
                                    window.location = "principal.php?mnu=compras&action=alta_entrada_orden_compra&id=<?php echo $rs['Id_orden_compra']; ?>";
                                    return false;' title='Reporte' ><img src="resources/images/Modify.png" width="25" height="25"/></a>
                           <?php } ?>
                    </td>
                    <?php
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>               
        </div>
    </body>
</html>
