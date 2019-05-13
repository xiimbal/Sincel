<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
header("Content-type: application/vnd.ms-excel; name='excel'");
header("Content-Disposition: filename=orden_compra.xls");
header("Pragma: no-cache");
header("Expires: 0");
$catalogo = new Catalogo();
$alta = "compras/alta_orden_compra.php";
$same_page = "compras/lista_orden_compra.php";
$controlador = "WEB-INF/Controllers/compras/Controler_Orden_Compra.php";
$proveedor = "";
$modelo = "";
$fechaInicio = "";
$fechaFin = "";
$oc = "";
$estatus = "";
$where = "";
$surtido = "";
$cancelados = "";
$reporte = "compras/reporte_orden_compra.php";
if (isset($_POST['slProveedorL']) && $_POST['slProveedorL'] != "0") {
    $proveedor = $_POST['slProveedorL'];
    $where .= " AND oc.FacturaEmisor='$proveedor'";
}
if (isset($_POST['txtModeloL']) && $_POST['txtModeloL'] != "") {
    $modelo = $_POST['txtModeloL'];
    $where .= " AND (c.Modelo LIKE '%$modelo%' OR eq.Modelo LIKE '%$modelo%' )";
}
if (isset($_POST['txtFechaInicioL']) && $_POST['txtFechaInicioL'] != "" && isset($_POST['txtFechaFinL']) && $_POST['txtFechaFinL'] != "") {
    $fechaInicio = $_POST['txtFechaInicioL'];
    $fechaFin = $_POST['txtFechaFinL'];
    $where .= " AND oc.FechaOrdenCompra BETWEEN '$fechaInicio' AND '$fechaFin'";
}
if (isset($_POST['txtOrdenCompraL']) && $_POST['txtOrdenCompraL'] != "") {
    $oc = $_POST['txtOrdenCompraL'];
    $where .= " AND oc.Id_orden_compra='$oc'";
    $surtido = "checked";
    $cancelados = "checked";
}
if (isset($_POST['slEstatusL']) && $_POST['slEstatusL'] != "0") {
    $estatus = $_POST['slEstatusL'];
    $where .= " AND oc.Estatus='$estatus'";
}
if (isset($_POST['ckSurtido']) && $_POST['ckSurtido'] == "on") {
    $surtido = "checked";
} else {
    if ($estatus != "70") {
        $where .= " AND oc.Estatus<>70";
    }
}
if (isset($_POST['ckCancelados']) && $_POST['ckCancelados'] == "on") {
    $cancelados = "checked";
} else {
    if ($estatus != "59") {
        $where .= " AND oc.Estatus<>59";
    }
}
$tickets = "0";
if(isset($_POST['ckTickets']) && $_POST['ckTickets'] == "on"){
    $tickets = "1";
}
/* //"cancelados": cancelados, "cerrados"
  if (isset($_POST['ckCancelados']) && $_POST['ckCancelados'] == "on") {
  $cancelados = "checked";
  $where .= " OR oc.Estatus=59";
  } else {
  if ($estatus != "59") {
  $where .= " AND oc.Estatus<>59";
  }
  }

  if (isset($_POST['ckSurtido']) && $_POST['ckSurtido'] == "on") {
  $surtido = "checked";
  $where .= " OR oc.Estatus=70";
  } else {
  if ($estatus != "70") {
  $where .= " AND oc.Estatus<>70";
  }
  } */
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/compras/alta_orden_compra.js"></script>
        <script>
            $(".button").button();
        </script>
    </head>
    <body>
        <div class="principal"> 
            <table id="tAlmacen">
                <thead>
                    <tr>
                        <th align='center' scope='row' style="width: 10%">Orden de compra <?php echo $tickets?></th><th align='center' scope='row' style="width: 15%">Fecha</th>
                        <th align='center' scope='row'  style="width: 30%">Proveedor</th><th align='center' scope='row' style="width: 20%">Estatus</th>
                        <th align='center' scope='row' style="width: 10%">Cantidad Eq/Comp</th>
                        <?php
                        $arreglo = array();
                        $pagos = array();
                        if($tickets == "1"){
                            $consultaTHEAD = "SELECT tv.idTipoViatico, tv.nombre AS viatico FROM c_tipoviatico tv;";
                            $resultTHEAD = $catalogo->obtenerLista($consultaTHEAD);
                            while($rs = mysql_fetch_array($resultTHEAD)){
                                echo "<th align='center' scope='row' style='width: 10%'>" . $rs['viatico'] . "</th>";
                                //$arreglo[$rs['idTipoViatico']] = $rs['viatico'];
                                $pagos[$rs['idTipoViatico']] = 0;
                            }
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($tickets == "1"){
                        $consulta = "SELECT oc.Id_orden_compra, oc.FechaOrdenCompra, p.NombreComercial, e.Nombre AS estatus,
                        oc.Descripcion_Ticket AS cantModelo, k.IdTicket FROM c_orden_compra oc 
                        LEFT JOIN c_proveedor p ON p.ClaveProveedor = oc.FacturaEmisor LEFT JOIN c_estado e ON e.IdEstado = oc.Estatus 
                        LEFT JOIN k_tickets_oc k ON k.IdOrdenCompra = oc.Id_orden_compra WHERE oc.Factura_Ticket = 1";
                    }else{
                        if ($oc == "") {
                            $consulta = "SELECT oc.Id_orden_compra,oc.FechaOrdenCompra,p.NombreComercial,e.Nombre AS estatus,SUM(koc.Cantidad) as total,
                                        GROUP_CONCAT('(',koc.Cantidad,')',(SELECT CASE WHEN koc.NoParteComponente IS NOT NULL THEN c.Modelo ELSE eq.Modelo END)) AS cantModelo
                                        FROM c_orden_compra oc LEFT JOIN k_orden_compra koc ON oc.Id_orden_compra=koc.IdOrdenCompra 
                                        LEFT JOIN c_proveedor p ON p.ClaveProveedor=oc.FacturaEmisor LEFT JOIN c_estado e ON oc.Estatus=e.IdEstado 
                                        LEFT JOIN c_componente c ON c.NoParte=koc.NoParteComponente LEFT JOIN c_equipo eq ON eq.NoParte=koc.NoParteEquipo
                                        WHERE oc.Activo=1 $where GROUP BY koc.IdOrdenCompra";
                        } else {
                            $consulta = "SELECT oc.Id_orden_compra,oc.FechaOrdenCompra,p.NombreComercial,e.Nombre AS estatus,SUM(koc.Cantidad) as total,
                                        GROUP_CONCAT('(',koc.Cantidad,')',(SELECT CASE WHEN koc.NoParteComponente IS NOT NULL THEN c.Modelo ELSE eq.Modelo END))AS cantModelo
                                        FROM c_orden_compra oc LEFT JOIN k_orden_compra koc ON oc.Id_orden_compra=koc.IdOrdenCompra 
                                        LEFT JOIN c_proveedor p ON p.ClaveProveedor=oc.FacturaEmisor LEFT JOIN c_estado e ON oc.Estatus=e.IdEstado 
                                        LEFT JOIN c_componente c ON c.NoParte=koc.NoParteComponente LEFT JOIN c_equipo eq ON eq.NoParte=koc.NoParteEquipo
                                        WHERE oc.Activo=1 AND oc.Id_orden_compra='$oc' GROUP BY koc.IdOrdenCompra";
                        }
                    }
                    $query = $catalogo->obtenerLista($consulta);
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td align='center' scope='row'>" . $rs['Id_orden_compra'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['FechaOrdenCompra'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['NombreComercial'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['estatus'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['cantModelo'] . "</td>";
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
                        LEFT JOIN c_usuario AS u2 ON u2.IdUsuario = tt.IdUsuario WHERE k.FacturoViaticos = 1 AND kve.Validado = 1 AND k.IdOrdenCompra = " . $rs['Id_orden_compra'];
                        $query3 = $catalogo->obtenerLista($consulta3);
                        while($rs3 = mysql_fetch_array($query3)){
                            $pagos[$rs3['IdViatico']] += $rs3['monto'];
                        }
                        //imprimimos los valores
                        foreach($pagos as $clave => $valor){
                            echo "<td align='center' scope='row'>$valor</td>";
                            $pagos[$clave] = 0;//para limpiar
                        }
                        echo "</tr>";
                    }
                    ?>                    
                </tbody>
            </table>
        </div>
    </body>
</html>