<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/Usuario.class.php");
include_once("../../Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/Ventas_Directas.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$urlextra = "";
if (isset($_GET['vendedor'])) {
    $urlextra .= "&vendedor=" . $_GET['vendedor'];
}
$surtida = 0;
$where = "WHERE (c_ventadirecta.Estatus=1 OR c_ventadirecta.Estatus=2) ";
if (isset($_GET['surtida']) && $_GET['surtida'] == 1) {
    $urlextra .= "&surtida=1";
    $surtida = 1;
    $where = "";
}
$usuario = new Usuario();
if ($usuario->isUsuarioPuesto($_SESSION['idUsuario'], "11")) {//si no es vendedor
    if ($where != "") {
        $where.=" AND c_cliente.EjecutivoCuenta=" . $_SESSION['idUsuario'];
    } else {
        $where.="WHERE c_cliente.EjecutivoCuenta=" . $_SESSION['idUsuario'];
    }
}

$catalogo = new Catalogo();
$query = $catalogo->obtenerLista("SELECT
	c_ventadirecta.IdVentaDirecta AS NoVenta,
	c_ventadirecta.Fecha AS Fecha,
        c_ventadirecta.ClaveCliente AS ClaveCliente,
	c_cliente.NombreRazonSocial AS Nombre,
        c_cliente.RFC AS RFC,
        c_cliente.EjecutivoCuenta AS EjecutivoCuenta,
	SUM(IF(c_bitacora.NoSerie!='',1,k_ventadirectadet.Cantidad)) AS Monto,
	SUM(IF(c_bitacora.NoSerie!='',k_ventadirectadet.Costo,k_ventadirectadet.Costo*k_ventadirectadet.Cantidad)) AS Costo,
	CASE c_ventadirecta.Estatus
                WHEN 1 THEN 'Registrada'
                WHEN 2 THEN 'Facturada'
		WHEN 3 THEN 'Cancelada'
		WHEN 4 THEN 'Cerrada'
	END  AS Status,
        IF(autorizada_alm=1 AND autorizada_vd=1,'Autorizada',IF(autorizada_alm=0 OR autorizada_vd=0,'Rechazada','En proceso')) AS Estado,
        c_ventadirecta.id_factura AS Factura,
        c_ventadirecta.id_solicitud AS Solicitud,
        if(c_solicitud.estatus=5,'Surtida','')  AS Solicitud_Estatus,
        GROUP_CONCAT(c_bitacora.NoSerie SEPARATOR ', ') AS Serie,
        c_factura.Folio AS FolioFactura
        FROM c_ventadirecta
        INNER JOIN k_ventadirectadet ON k_ventadirectadet.IdVentaDirecta=c_ventadirecta.IdVentaDirecta
        INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_ventadirecta.ClaveCliente
        LEFT JOIN c_solicitud ON c_solicitud.id_solicitud=c_ventadirecta.id_solicitud
        LEFT JOIN c_bitacora ON c_bitacora.id_solicitud=c_ventadirecta.id_solicitud AND c_bitacora.NoParte = k_ventadirectadet.IdProduto
        LEFT JOIN c_factura ON c_factura.IdFactura=c_ventadirecta.id_prefactura
        " . $where . "
        GROUP BY NoVenta
        ORDER BY Fecha DESC");
?>
<table id="tablavd">
    <thead>
        <tr>
            <th width="2%" align="center" scope="col">No Venta</th>
            <th width="2%" align="center" scope="col">Fecha</th>
            <th width="2%" align="center" scope="col">Cliente</th>
            <th width="2%" align="center" scope="col">Cantidad</th>
            <th width="2%" align="center" scope="col">Monto</th>
            <th width="2%" align="center" scope="col">Status</th>
            <th width="2%" align="center" scope="col">Serie</th>
            <th width="2%" align="center" scope="col">Solicitud</th>
            <th width="2%" align="center" scope="col"></th>
            <th width="2%" align="center" scope="col"></th>
            <th width="2%" align="center" scope="col"></th>
            <th width="2%" align="center" scope="col"></th>
        </tr>
    </thead>
    <tbody>
        <?php
        while ($rs = mysql_fetch_array($query)) {
            echo "<tr>";
            echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['NoVenta'] . "</td><td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Fecha'] . "</td><td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Nombre'] . "</td><td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Monto'] . "</td><td width=\"2%\" align=\"center\" scope=\"col\">$" . number_format($rs['Costo'], 2) . "</td>";
            if ($rs['Solicitud_Estatus'] == "") {
                echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Estado'] . "</td>";
            } else {
                echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Solicitud_Estatus'] . "</td>";
            }
            echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Serie'] . "</td>";
            if ($rs['Solicitud'] != "") {
                echo "<td width=\"2%\" align=\"center\" scope=\"col\">No " . $rs['Solicitud'] . "</td>";
            } else {
                echo "<td width=\"2%\" align=\"center\" scope=\"col\"></td>";
            }
            if ($rs['Status'] == 'Registrada') {
                echo "<td width=\"2%\" align=\"center\" scope=\"col\" >";
                if ($permisos_grid->getModificar() && $rs['Estado'] != 'Rechazada' && $rs['Estado'] != 'Autorizada') {
                    echo"<a href='#' onclick=\"cambiarContenidos('ventas/Editar_vd.php?cliente=" . $rs['ClaveCliente'] . "&vendedor=" . $rs['EjecutivoCuenta'] . "&id=" . $rs['NoVenta'] . $urlextra . "','Editar venta directa'); return false;\" title=''><img src=\"resources/images/Modify.png\" width=\"24\" height=\"24\"/></a>";
                }
                echo"</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"col\"> <a href='ventas/imprimir_ventad.php?id=" . $rs['NoVenta'] . "'  title='Imprimir' target='_blank'><img src=\"resources/images/icono_impresora.jpg\" width=\"24\" height=\"24\"/></a></td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"col\" >";
                if ($permisos_grid->getBaja() && $rs['Estado'] != 'Autorizada') {
                    echo "<a href='#' onclick=\"eliminarvd('WEB-INF/Controllers/Ventas/Controller_eliminar_vd.php?id=" . $rs['NoVenta'] . "'); return false;\" title=''><img src=\"resources/images/Erase.png\" width=\"24\" height=\"24\"/></a>";
                }
                echo "</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"col\" > ";
                if ($rs['Factura'] == "") {
                    echo "<a  onclick=\"facturarvd('" . $rs['NoVenta'] . "');\" title=''>
                    <img src=\"resources/images/facturar.png\" width=\"24\" height=\"24\"/></a>";
                } else {
                    if (strpos($rs['Factura'], "REMIS") !== false) {
                        echo "Nota de Remisión No <a href='#'> " . $rs['Factura'] . "</a>";
                    } else {
                        echo "Factura No <a href='#'> " . $rs['Factura'] . "</a>";
                    }
                }
                echo "</td>";
            } else {
                echo "<td width=\"2%\" align=\"center\" scope=\"col\" ></td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"col\"> <a href='ventas/imprimir_ventad.php?id=" . $rs['NoVenta'] . "'  title='Imprimir' target='_blank'><img src=\"resources/images/icono_impresora.jpg\" width=\"24\" height=\"24\"/></a></td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"col\" ></td>";
                if ($rs['FolioFactura'] == "") {
                    echo "<td width=\"2%\" align=\"center\" scope=\"col\" ></td>";
                } else {
                    echo "<td width=\"2%\" align=\"center\" scope=\"col\" >Prefactura Folio No <a href='#'> " . $rs['FolioFactura'] . "</a></td>";
                }
            }
            echo "</tr>";
        }
        ?>
    </tbody>
</table>


