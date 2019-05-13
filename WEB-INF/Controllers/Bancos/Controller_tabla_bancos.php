<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
include_once("../../Classes/CatalogoFacturacion.class.php");
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "Bancos/lista_movimientos.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$cabeceras = array("Factura", "Tipo", "Total","Cuenta","Banco","Fecha","Referencia","Comentario");

$fechaI = "2000-01";
$fechaF = "2100-01";
$limit = "limit 500";
if (isset($_POST['fecha_ini']) && $_POST['fecha_ini'] != ""){
    $fechaI = $_POST['fecha_ini'];
    $limit = null; 
}
if(isset($_POST['fecha_f']) && $_POST['fecha_f'] != ""){
    $fechaF = $_POST['fecha_f'];
    $limit = null;
}
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/banco/MovimientoBancos.js"></script>
<br/><br/><br/>
<table id=tlistaMovimientos>
<thead><tr>
<?php
    for ($i = 0; $i < (count($cabeceras)); $i++) {
        echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
    }
?>
</tr></thead>
    <tbody>
    <?php
            //Obtenemos datos como el nombre del banco, noCuenta y fecha de corte de la base de datos normal
            $arrayBanco = array();
            $arrayNoCuenta = array();
            $arrayFechas = array();
            $catalogoN = new Catalogo();
            $query = $catalogoN->obtenerLista("SELECT cb.idCuentaBancaria, cb.noCuenta, cb.FechaCorte, b.Nombre FROM c_cuentaBancaria cb"
                    . " LEFT JOIN c_banco b ON b.IdBanco = cb.idBanco");
            while ($rs = mysql_fetch_array($query))
            {
                $arrayBanco[$rs['idCuentaBancaria']] = $rs['Nombre'];
                $arrayNoCuenta[$rs['idCuentaBancaria']] = $rs['noCuenta'];
                $arrayFechas[$rs['idCuentaBancaria']] = $rs['FechaCorte'];
            }
            
            $catalogo = new CatalogoFacturacion();
            $query = $catalogo->obtenerLista("SELECT pp.*, (CASE WHEN ISNULL(pp.idCuentaBancaria) THEN c.idCuentaBancaria ELSE pp.idCuentaBancaria END) as num FROM c_pagosparciales pp "
                    . "LEFT JOIN c_factura f ON pp.IdFactura = f.IdFactura "
                    . "LEFT JOIN c_cliente c ON c.RFC = f.RFCReceptor "
                    . "WHERE pp.FechaPago > concat('" . $fechaI. "-',01) and pp.FechaPago <= concat('" . $fechaF . "-',01) $limit");
            while ($rs = mysql_fetch_array($query)) {
                if($catalogoN->obtenerLista("SELECT date('".$rs['FechaPago']."') > date('" . $fechaI. "-".$arrayFechas[$rs['idCuentaBancaria']]."') and date('".$rs['FechaPago']."') <= date('" . $fechaF. "-".$arrayFechas[$rs['idCuentaBancaria']]."')")){   
                    echo "<tr>";
                    echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['IdFactura'] . "</td>";
                    echo "<td width=\"2%\" align=\"center\" scope=\"row\">Deposito</td>";
                    echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['ImportePagado'] . "</td>";
                    echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $arrayNoCuenta[$rs['num']] . "</td>";
                    echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $arrayBanco[$rs['num']] . "</td>";
                    echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['FechaPago'] . "</td>";
                    echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Referencia'] . "</td>";
                    echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Observaciones'] . "</td>";
                    echo "</tr>";
                }
            }
            $query = $catalogoN->obtenerLista("SELECT pp.*, c.noCuenta, b.Nombre FROM c_pagosparciales_proveedor pp "
                    . "LEFT JOIN c_factura_proveedor f ON pp.id_factura = f.IdFacturaProveedor "
                    . "LEFT JOIN c_proveedor p ON p.ClaveProveedor = f.IdEmisor "
                    . "LEFT JOIN c_cuentaBancaria c ON (CASE WHEN ISNULL(pp.idCuentaBancaria) THEN p.RFC = c.RFC ELSE pp.idCuentaBancaria = c.idCuentaBancaria END) "
                    . "LEFT JOIN c_banco b ON c.idBanco = b.IdBanco "
                    . "WHERE pp.fechapago > concat('" . $fechaI. "-',c.FechaCorte) and pp.fechapago <= concat('" . $fechaF . "-',c.FechaCorte) $limit");
            while ($rs = mysql_fetch_array($query)) {
                echo "<tr>";
                echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['id_factura'] . "</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"row\">Retiro</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['importe'] . "</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['noCuenta'] . "</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Nombre'] . "</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['fechapago'] . "</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['referencia'] . "</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['observaciones'] . "</td>";
                echo "</tr>";
            }
            ?>
    </tbody>
</table>