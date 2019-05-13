<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/ReporteFacturacionProveedor.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "facturacion/ReporteFacturacionProveedores.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$catalogo = new Catalogo();
if (isset($_POST['form'])) {
    $parametros = "";
    
    if (isset($_POST['form'])) {
        parse_str($_POST['form'], $parametros);
    }
    
    $reporte = new ReporteFacturacionProveedor();
    
    if(isset($parametros['RFCProveedor']) && $parametros['RFCProveedor'] != "")
    {
        $reporte->setRFCProveedor($parametros['RFCProveedor']);
    }
    if(isset($parametros['fecha1']) && $parametros['fecha1'] != "")
    {
        $reporte->setFechaInicio($parametros['fecha1']);
    }
    if(isset($parametros['fecha2']) && $parametros['fecha2'] != "")
    {
        $reporte->setFechaInicio($parametros['fecha2']);
    }
    if(isset($parametros['proveedor']) && $parametros['proveedor'] != "")
    {
        $reporte->setRFCProveedor($parametros['proveedor']);
    }
    if(isset($parametros['status']) && $parametros['status'] != "")
    {
        $reporte->setEstado($parametros['status']);
    }
    if(isset($parametros['folio']) && $parametros['folio'] != "")
    {
        $reporte->setFolio($parametros['folio']);
    }

$query = $reporte->getTabla();
$cabeceras = Array("Folio", "Fecha", "Nombre Proveedor", "RFC Proveedor", "Pagado", "Por pagar", "Subtotal", "IVA","Total", "PDF", "Pagado", "Pagos", "Viáticos");

?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/facturacion/facturacion_reporte_tabla_facturacion.js"></script>
    <br/><br/>   
    <br/><br/>

    <div class='ui-state-highlight ui-corner-all' style='width: 18%;margin-top:15px; margin-bottom:15px;'>
        <span class='ui-icon ui-icon-info' style='float: left;'></span>
        <div id='resumen_por_pagar' style="display: inline;">Pruebas</div>        
    </div>
    <br/>
    <br/>
    <table id="treportfactprov" style="max-width: 100%;">
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
                echo "<td align='center' scope='row' $color>" . $rs['Folio'] . "</td>";
                echo "<td align='center' scope='row' $color>" . $rs['FechaFacturacion'] . "</td>";
                echo "<td align='center' scope='row' $color>" . $rs['NombreProveedor'] . "</td>";
                echo "<td align='center' scope='row' $color>" . $rs['RFCProveedor'] . "</td>";
                echo "<td align='center' scope='row' $color>$" . number_format($rs['pagado'],2) . "</td>";
                echo "<td align='center' scope='row' $color>$" . number_format((float)$rs['TotalSinFormato'] - (float)($rs['pagado']),2) . "</td>";
                echo "<td align='center' scope='row' $color>$" . $rs['subtotal'] . "</td>";
                echo "<td align='center' scope='row' $color>$" . $rs['importe'] . "</td>";
                echo "<td align='center' scope='row' $color>$" . $rs['Total'] . "</td>";
                if(empty($rs['PDF'])){
                    echo "<td align='center' scope='row' $color><a href='facturacion/archivo_not_found.php' target='_blank'>
                        <img src='resources/images/pdf_descarga.png' title='XML Factura' style='width: 32px; height: 32px;'/></a></td>";
                }else{
                    if (strpos($rs['PDF'], 'PDF/') !== false) {
                        echo "<td align='center' scope='row' $color><a href='" . $rs['PDF'] . "' target='_blank'>
                            <img src='resources/images/pdf_descarga.png' title='PDF Factura' style='width: 32px; height: 32px;'/></a></td>";
                    } else {
                        echo "<td align='center' scope='row' $color>";
                        if($carpeta_virtual != ""){
                            $index = strrpos($rs['PDF'], "Facturas");
                            if($index === FALSE){                            
                                echo "<a href='$carpeta_virtual/".$rs['PDF']."' target='_blank'>";
                            }else{
                                echo "<a href='$carpeta_virtual/".substr($rs['PDF'], $index+9)."' target='_blank'>";
                            }
                        }else{
                            echo "<a href='" . $liga_net . "/cfdi/" . $rs['PDF'] . "?uguid=" . $_SESSION['user'] . "' target='_blank'>";
                        }
                        echo "<img src='resources/images/pdf_descarga.png' title='PDF Factura' style='width: 32px; height: 32px;'/></a></td>";
                    }
                }
                if ($rs['pagadoSN'] != 'No') {
                    echo "<td align='center' scope='row' $color>";
                    if($permisos_grid->getModificar()){
                        echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial_proveedor.php?cxc=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
                    }
                    echo "Si"; 
                    if($permisos_grid->getModificar()){
                        echo "</a>"; 
                    }
                    echo "</td>";
                } else {
                    echo "<td align='center' scope='row' $color>";
                    if($permisos_grid->getModificar()){                        
                        echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial_proveedor.php?cxc=true&RFC=". $rs['RFCProveedor'] ."&factura=".$rs['IdFactura']."\"); return false;'>"; 
                    }
                    echo "No"; 
                    if($permisos_grid->getModificar()){
                        echo "</a>"; 
                    }
                    echo "</td>";
                }
                echo "<td align='center' scope='row' $color>".str_replace('()', '', $rs['pagos'])."</td>";       
                echo "<td align='center' scope='row'>";
                if(isset($rs['IdOrdenCompra']) && !empty($rs['IdOrdenCompra'])){//Vamos a poner los viáticos con un query muy padre :D
                    $consulta2 = "SELECT IF(ISNULL(nt.IdViatico),e.Nombre, tv.nombre) AS viatico, nt.DiagnosticoSol FROM k_tickets_oc k
                    INNER JOIN c_ticket t ON t.IdTicket = k.IdTicket INNER JOIN c_notaticket nt ON nt.IdTicket = t.IdTicket
                    LEFT JOIN c_tipoviatico tv ON tv.idTipoViatico = nt.IdViatico INNER JOIN c_estado e ON e.IdEstado = nt.IdEstatusAtencion 
                    INNER JOIN k_serviciove ve ON ve.IdNotaTicket = nt.IdNotaTicket WHERE k.IdOrdenCompra = " . $rs['IdOrdenCompra'] . " AND k.FacturoViaticos = 1 AND ve.Validado = 1";
                    $query2 = $catalogo->obtenerLista($consulta2);
                    $viaticos = "";
                    while($rs2 = mysql_fetch_array($query2)){
                        $viaticos .= "<b>" . $rs2['viatico'] . "</b> (" . $rs2['DiagnosticoSol'] . "),";
                    }
                    echo trim($viaticos, ",");
                }
                echo "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>    
    <div id="report"/>    
    <?php
        echo "<input type='hidden' id='total_facturas' name='total_facturas' value='".  number_format($num_facturas)."'/>";
        echo "<input type='hidden' id='total_costo_factura' name='total_costo_factura' value='$".  number_format($total, 2)."'/>";
        echo "<input type='hidden' id='pagado_facturas' name='pagado_facturas' value='$".  number_format($pagado, 2)."'/>";
        echo "<input type='hidden' id='por_pagar_facturas' name='por_pagar_facturas' value='$".  number_format($total - $pagado, 2)."'/>";
} else {
    echo "No se recibieron los datos.";
}

