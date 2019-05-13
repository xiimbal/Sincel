<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/ReporteFacturacionProveedor.class.php");
header("Content-type: application/vnd.ms-excel; name='excel'");
header("Content-Disposition: filename=orden_compra.xls");
header("Pragma: no-cache");
header("Expires: 0");
$catalogo = new Catalogo();


$reporte = new ReporteFacturacionProveedor();
if(isset($_POST['RFCProveedor']) && $_POST['RFCProveedor'] != ""){
    $reporte->setRFCProveedor($_POST['RFCProveedor']);
}
if(isset($_POST['fecha1']) && $_POST['fecha1'] != ""){
    $reporte->setFechaInicio($_POST['fecha1']);
}
if(isset($_POST['fecha2']) && $_POST['fecha2'] != ""){
    $reporte->setFechaInicio($_POST['fecha2']);
}
if(isset($_POST['proveedor']) && $_POST['proveedor'] != ""){
    $reporte->setRFCProveedor($_POST['proveedor']);
}
if(isset($_POST['status']) && $_POST['status'] != ""){
    $reporte->setEstado($_POST['status']);
}
if(isset($_POST['folio']) && $_POST['folio'] != ""){
    $reporte->setFolio($_POST['folio']);
}

$query = $reporte->getTabla();
$cabeceras = Array("Folio", "Fecha", "Nombre Proveedor", "RFC Proveedor", "Pagado", "Por pagar", "Subtotal", "IVA","Total", "Pagado", "Pagos");
$consultaTHEAD = "SELECT tv.idTipoViatico, tv.nombre AS viatico FROM c_tipoviatico tv;";
$resultTHEAD = $catalogo->obtenerLista($consultaTHEAD);
$pagos = array();
while($rs = mysql_fetch_array($resultTHEAD)){
    array_push($cabeceras, $rs['viatico']);
    $pagos[$rs['idTipoViatico']] = 0;
}
?>
<html lang="es">
    <head>
        <meta charset="UTF-8">
    </head>
    <body>
        <div class="principal">
            <table>
                <thead>
                    <tr>
                        <?php
                        for ($i = 0; $i < count($cabeceras); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        while ($rs = mysql_fetch_array($query)) {
                            $num_facturas++;
                            $total += (float)$rs['TotalSinFormato'];
                            $pagado += (float)$rs['pagado'];
                            $color = "";
                            echo "<tr>";
                            echo "<td align='center' scope='row' >" . $rs['Folio'] . "</td>";
                            echo "<td align='center' scope='row' >" . $rs['FechaFacturacion'] . "</td>";
                            echo "<td align='center' scope='row' >" . $rs['NombreProveedor'] . "</td>";
                            echo "<td align='center' scope='row' >" . $rs['RFCProveedor'] . "</td>";
                            echo "<td align='center' scope='row' >$" . number_format($rs['pagado'],2) . "</td>";
                            echo "<td align='center' scope='row' >$" . number_format((float)$rs['TotalSinFormato'] - (float)($rs['pagado']),2) . "</td>";
                            echo "<td align='center' scope='row' >$" . $rs['subtotal'] . "</td>";
                            echo "<td align='center' scope='row' >$" . $rs['importe'] . "</td>";
                            echo "<td align='center' scope='row' >$" . $rs['Total'] . "</td>";                           
                            echo "<td align='center' scope='row' >";
                            echo $rs['pagadoSN']; 
                            echo "</td>";
                            echo "<td align='center' scope='row' >".str_replace('()', '', $rs['pagos'])."</td>";       
                            //Aquí ya van los viáticos
                            if(isset($rs['IdOrdenCompra'])){
                                $consulta3 = "SELECT nt.IdViatico,(CASE WHEN ! ISNULL(e.PrecioParticular) THEN (e.PrecioParticular)
                                WHEN ! ISNULL(u.CostoFijo) THEN (u.CostoFijo) WHEN ! ISNULL(tr.IdTarifa) THEN (ktr.Costo)
                                WHEN cve.IdServicioVE = 74 THEN kve.Cantidad ELSE (kve.cantidad * cve.PrecioUnitario) END) AS monto
                                FROM k_tickets_oc k INNER JOIN c_ticket t ON t.IdTicket = k.IdTicket INNER JOIN c_cliente AS c ON c.ClaveCliente = t.ClaveCliente
                                INNER JOIN c_notaticket nt ON nt.IdTicket = t.IdTicket INNER JOIN k_serviciove kve ON kve.IdNotaTicket = nt.IdNotaTicket
                                INNER JOIN c_serviciosve cve ON cve.IdServicioVE = kve.IdServicioVE LEFT JOIN c_tarifarango AS tr ON tr.IdTarifa = cve.IdTarifa
                                LEFT JOIN k_tarifarango AS ktr ON ktr.IdDetalleTarifa = (SELECT MAX(ktr2.IdDetalleTarifa) FROM k_tarifarango AS ktr2
                                WHERE ktr2.IdTarifa = tr.IdTarifa AND kve.cantidad >= ktr2.RangoInicial AND kve.cantidad <= ktr2.RangoFinal)
                                INNER JOIN c_contrato AS co ON co.ClaveCliente = c.ClaveCliente LEFT JOIN c_especial AS e ON e.idTicket = t.IdTicket
                                LEFT JOIN c_usuario AS u ON u.IdUsuario = e.idUsuario LEFT JOIN k_tecnicoticket AS tt ON tt.IdTicket = t.IdTicket
                                LEFT JOIN c_usuario AS u2 ON u2.IdUsuario = tt.IdUsuario WHERE k.FacturoViaticos = 1 AND kve.Validado = 1 AND k.IdOrdenCompra = " . $rs['IdOrdenCompra'];
                                $query3 = $catalogo->obtenerLista($consulta3);
                                while($rs3 = mysql_fetch_array($query3)){
                                    $pagos[$rs3['IdViatico']] += $rs3['monto'];
                                }
                                //imprimimos los valores
                                foreach($pagos as $clave => $valor){
                                    echo "<td align='center' scope='row'>$" . number_format($valor,2) . "</td>";
                                    $pagos[$clave] = 0;//para limpiar
                                }                                
                            }
                            echo "</tr>";
                        }
                        ?>
                </tbody>
            </table>
        </div>
    </body>
</html>

