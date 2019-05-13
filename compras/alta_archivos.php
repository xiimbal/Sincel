<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
$catalogo = new Catalogo();
?>
<!DOCTYPE html>
<html>
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/compras/alta_archivos.js"></script>
    </head>
    <body>
        <div class="principal">
            <form id="frmArchivos" name="frmArchivos" method="POST" action="/">
                <table>
                    <tr><td>Orden de compra</td>
                        <td>
                            <select id="slIdOrdenCompra" name="slIdOrdenCompra" style="width: 155px">
                                <option value="0">Selecciona una orden de compra</option>
                                <?php
                                $queryOC = $catalogo->obtenerLista("SELECT c.Id_orden_compra,SUM(k.Cantidad) AS solicitadas, SUM(k.CantidadEntregada) AS entregadas,
                                    c.Factura_Ticket 
                                    FROM c_orden_compra c 
                                    LEFT JOIN k_orden_compra k ON k.IdOrdenCompra = c.Id_orden_compra 
                                    LEFT JOIN c_factura_proveedor AS fp ON fp.IdOrdenCompra = c.Id_orden_compra
                                    WHERE c.Estatus = 70 AND ISNULL(fp.IdFacturaProveedor)
                                    GROUP BY c.Id_orden_compra 
                                    HAVING IF(ISNULL(solicitadas),IF(ISNULL(entregadas),Factura_Ticket = 1,solicitadas = entregadas),solicitadas = entregadas)");
                                while ($rs = mysql_fetch_array($queryOC)) {
                                    echo "<option value='" . $rs['Id_orden_compra'] . "'>" . $rs['Id_orden_compra'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr><td>Archivo Factura</td><td><input type="file" id="factura" name="factura"/></td></tr>
                    <tr><td>Archivo XML</td><td><input type="file" id="xml" name="xml"/></td></tr>             
                </table>     
                <input type="submit" class="button" value="Guardar"/>
            </form>
        </div>
    </body>
</html>