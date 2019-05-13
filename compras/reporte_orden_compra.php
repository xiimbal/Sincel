<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Orden_Compra.class.php");
$catalogo = new Catalogo();
$ordenCompra = new Orden_Compra();
$idOrdenCompra = "";
$facturaEmisor = "";
$facturaReceptors = "";
$fecha = "";
$noOrden = "";
$estatus = "";
$condicion = "";
$disabled = "";
$noCliente = "";
$noprov = "";
$notas = "";
$trasp = "";
$peso = "";
$metros = "";
$origen = "";
$metodo = "";
$embarque = "";
$direccionProv = "";
$direccionRazon = "";
$proveedor = "";
$nomFacturacion = "";
$observacion = "";
$direccionFac = "";
$nomEstatus = "";
$imagenFacturacion = "";
$precioDolar = "";
$idAlmacen = "";
$nombrealmacen = "";
$telefono_proveedor = "";
$no_pedido = "";
$facturaTicket = "";
if (isset($_GET['id']) && $_GET['id'] != "") {
    $idOrdenCompra = $_GET['id'];
    $ordenCompra->getRegistroById($idOrdenCompra);
    $facturaEmisor = $ordenCompra->getFacturaEmisor();
    $facturaReceptors = $ordenCompra->getFacturaRecptor();
    $fecha = $ordenCompra->getFechaOC();
    $condicion = $ordenCompra->getNom_condicionPago();
    $estatus = $ordenCompra->getEstatus();
    $embarque = $ordenCompra->getEmbarca();
    $noCliente = $ordenCompra->getNoCliente();
    $noprov = $ordenCompra->getNoPedidoProv();
    $notas = $ordenCompra->getNotas();
    $trasp = $ordenCompra->getTransportista();
    $peso = $ordenCompra->getPeso();
    $metros = $ordenCompra->getMetros();
    $origen = $ordenCompra->getOrigen();
    $metodo = $ordenCompra->getMetodoEntrega();
    $observacion = $ordenCompra->getObservacion();
    $precioDolar = $ordenCompra->getTipoCambio();
    $idAlmacen = $ordenCompra->getAlmacen();
    $no_pedido = $ordenCompra->getNo_pedido();
    $facturaTicket = $ordenCompra->getFactura_Ticket();
    if ($peso == "0") {
        $peso = "";
    }
    if ($metros == "0") {
        $metros = "";
    }
    $queryProveedor = $catalogo->obtenerLista("SELECT p.NombreComercial,p.Telefono FROM c_proveedor p WHERE p.ClaveProveedor='$facturaEmisor'");
    while ($rs = mysql_fetch_array($queryProveedor)) {
        $proveedor = $rs['NombreComercial'];
        $telefono_proveedor = $rs['Telefono'];
    }
    $queryFactura = $catalogo->obtenerLista("SELECT df.RazonSocial,CONCAT(df.Calle,' ',df.NoExterior,', ',df.Colonia,', ',df.Delegacion,', ',df.Estado,', ',df.CP) AS direccion,ImagenPHP FROM c_datosfacturacionempresa df WHERE df.IdDatosFacturacionEmpresa='$facturaReceptors'");
    while ($rs = mysql_fetch_array($queryFactura)) {
        $nomFacturacion = $rs['RazonSocial'];
        $direccionFac = $rs['direccion'];
        $imagenFacturacion = $rs['ImagenPHP'];
    }
    $queryestatus = $catalogo->obtenerLista("SELECT e.Nombre FROM c_estado e WHERE e.IdEstado=$estatus");
    while ($rs = mysql_fetch_array($queryestatus)) {
        $nomEstatus = $rs['Nombre'];
    }
    $queryAlamcen = $catalogo->obtenerLista("SELECT a.nombre_almacen FROM c_almacen a WHERE a.id_almacen='$idAlmacen'");
    while ($rs = mysql_fetch_array($queryAlamcen)) {
        $nombrealmacen = $rs['nombre_almacen'];
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style>
            .border{border: 1px solid black;text-align: center;padding: 0}
            table{border-collapse:collapse;}
        </style>
        <title>Reporte de orden de compra</title>
        <link rel="shortcut icon" href="../resources/images/logos/ra4.png" type="image/x-icon"/>
    </head>
    <body>
        <div style="width:100%;">
            <div style="margin-left: 90%;"><a href=javascript:window.print();><img src="../resources/images/icono_impresora.png" width="35" height="35"/></a></div>
            <table style="width: 100%">
                <tr>
                    <td style="width: 30%" align='center' scope='row'><image src="<?php echo "../" . $imagenFacturacion ?>" style="width:200px;height: 100px"/></td>
                    <td style="width: 40%;font-size: 20px" align='center' scope='row'><b>Orden de compra</b></td>
                    <td style="width: 20%" align='center' scope='row'>No. orden:<?php echo "  " . $idOrdenCompra ?></td>
                    <td style="width:10%" align='center' scope='row'></td>
                </tr>
            </table>
            <table style="width: 100%">
                <tr>
                    <td style="width: 35%;font-size: 12px" rowspan="2" class="border"><b>Razón social: </b><br/><?php echo $nomFacturacion ?><br/><?php echo $direccionFac ?></td>
                    <td style="width: 5%;font-size: 12px"></td>
                    <td style="width: 15%;font-size: 12px" class="border">Condición de pago:<br/><?php echo $condicion ?></td>
                    <td style="width: 15%;font-size: 12px" class="border">No. Cliente:<br/><?php echo $noCliente ?></td>
                    <td style="width: 15%;font-size: 12px" class="border">No.pedido: <?php echo $no_pedido ?></td>
                    <td style="width: 15%;font-size: 12px" class="border">Fecha pedido<br/><?php echo $fecha ?></td>
                </tr>
                <tr>                   
                    <td style="width: 5%;font-size: 12px"></td>
                    <td style="width: 30%;font-size: 12px" class="border" colspan="2">Transportista:<br/><?php echo $trasp ?></td>
                    <td style="width: 15%;font-size: 12px" class="border">Peso Kg<br/><?php echo $peso ?></td>
                    <td style="width: 15%;font-size: 12px" class="border">Metros cúbicos<br/><?php echo $metros ?></td>
                </tr>
                <tr>  
                    <td style="width: 35%;font-size: 12px;" class="border" ><b>Proveedor: </b><?php echo $proveedor ?><br/><b>Teléfono</b>: <?php echo $telefono_proveedor ?></td>
                    <td style="width: 5%;font-size: 12px"></td>
                    <td style="width: 30%;font-size: 12px" class="border" colspan="2">Estatus:<br/><?php echo $nomEstatus ?></td>
                    <td style="width: 30%;font-size: 12px" class="border" colspan="2">Notas:<br/><?php echo $noCliente ?></td>                   
                </tr>
                <tr>  
                    <td style="width: 35%;font-size: 12px" rowspan="2" class="border">
                        <?php
                        if ($embarque != "") {
                            echo "<b>Embarca a:</b> $nombrealmacen<br/>" . $embarque;
                        }
                        ?></td>
                    <td style="width: 5%;font-size: 12px"></td>
                    <td style="width: 30%;font-size: 12px" class="border"colspan="2">Origen:<br/><?php echo $origen ?></td>
                    <td style="width: 30%;font-size: 12px" class="border" colspan="2">Método de entrega:<br/><?php echo $metodo ?></td>                                      
                </tr>
                <tr>                    
                    <td style="width: 5%;font-size: 12px"></td>
                    <td style="width: 30%;font-size: 12px"colspan="2"></td>
                    <td style="width: 30%;font-size: 12px" colspan="2"></td>                   
                </tr>
            </table>
            <br/><br/><br/>
            <table style="width: 100%">
                <tr>
                    <th class="border" style="width:7%;font-size: 12px">Tipo</th>
                    <th class="border" style="width:17%;font-size: 12px">No. parte / Modelo</th>
                    <th class="border" style="width:10%;font-size: 12px">No. parte anterior</th>
                    <th class="border" style="width:30%;font-size: 12px">Descripción</th>
                    <th class="border" style="width:9%;font-size: 12px">Cant. solicitada</th>
                    <th class="border" style="width:9%;font-size: 12px">Cant. recibida</th>
                    <th class="border" style="width:9%;font-size: 12px">Precio unitario</th>
                    <th class="border" style="width:9%;font-size: 12px">Precio total</th>
                </tr>
                <?php
                if($facturaTicket == "0"){
                    $consulta = "SELECT e.NoParte AS eNoParte,e.Modelo AS modeloEquipo,SUBSTRING(e.Descripcion,1,40) AS desEquipo,c.NoParte AS cNoParte,c.Modelo AS modeloComp,c.NoParteAnterior,c.Descripcion AS desComp,koc.Cantidad,koc.CantidadEntregada,
                                (SELECT CASE WHEN koc.NoParteComponente IS NOT NULL THEN 'C' ELSE 'E' END) AS tipo,koc.PrecioUnitario,koc.PrecioTotal,koc.Dolar
                                FROM k_orden_compra koc LEFT JOIN c_equipo e ON koc.NoParteEquipo=e.NoParte LEFT JOIN c_componente c ON c.NoParte=koc.NoParteComponente WHERE koc.IdOrdenCompra=$idOrdenCompra ORDER BY tipo ASC";
                    $queryOC = $catalogo->obtenerLista($consulta);
                    $SubTotal = 0;
                    while ($rs = mysql_fetch_array($queryOC)) {
                        $precionUnitario = "";
                        $precioTotal = "";
                        if ($rs['Dolar'] == "1") {
                            $precionUnitario = (float) $precioDolar * $rs['PrecioUnitario'];
                            $precioTotal = (float) $precioDolar * $rs['PrecioTotal'];
                        } else {
                            $precionUnitario = $rs['PrecioUnitario'];
                            $precioTotal = $rs['PrecioTotal'];
                        }
                        $SubTotal = (float) $SubTotal + (float) $precioTotal;
                        echo "<tr>";
                        if ($rs['tipo'] == "C") {
                            echo "<td align='center' scope='row' style='font-size: 12px' class='border'>Componente</td>";
                            echo "<td align='center' scope='row' style='font-size: 12px' class='border'>" . $rs['cNoParte'] . " / " . $rs['modeloComp'] . "</td>";
                            echo "<td align='center' scope='row' style='font-size: 12px' class='border'>" . $rs['NoParteAnterior'] . "</td>";
                            echo "<td align='left' scope='row' style='font-size: 12px;border: 1px solid black;' >" . $rs['desComp'] . "</td>";
                        } else if ($rs['tipo'] == "E") {
                            echo "<td align='center' scope='row' style='font-size: 12px' class='border'>Equipo</td>";
                            echo "<td align='center' scope='row' style='font-size: 12px' class='border'>" . $rs['eNoParte'] . " / " . $rs['modeloEquipo'] . "</td>";
                            echo "<td align='center' scope='row' style='font-size: 12px' class='border'></td>";
                            echo "<td align='left' scope='row' style='font-size: 12px;border: 1px solid black;' >" . $rs['desEquipo'] . "</td>";
                        }
                        echo "<td align='center' scope='row' style='font-size: 12px' class='border'>" . $rs['Cantidad'] . "</td>";
                        echo "<td align='center' scope='row' style='font-size: 12px' class='border'>" . $rs['CantidadEntregada'] . "</td>";
                        echo "<td align='right' scope='row' style='font-size: 12px;border: 1px solid black;'>" . number_format((float) $precionUnitario, 2) . "</td>";
                        echo "<td align='right' scope='row' style='font-size: 12px;border: 1px solid black;'>" . number_format((float) $precioTotal, 2) . "</td>";
                        echo "</tr>";
                    }
                    $iva = (float) $SubTotal * .16;
                    $total = $SubTotal + $iva;
                    echo "<tr>";
                    echo "<td></td><td></td><td></td><td></td><td></td><td></td>";
                    echo "<td align='right' scope='row' style='font-size: 12px' class='border'><b>Sub total</b></td>";
                    echo "<td align='right' scope='row' style='font-size: 12px;border: 1px solid black;'>" . number_format((float) $SubTotal, 2) . "</td>";
                    echo "</tr>";
                    echo "<tr>";
                    echo "<td></td><td></td><td></td><td></td><td></td><td></td>";
                    echo "<td align='right' scope='row' style='font-size: 12px' class='border'><b>IVA</b></td>";
                    echo "<td align='right' scope='row' style='font-size: 12px;border: 1px solid black;'>" . number_format((float) $iva, 2) . "</td>";
                    echo "</tr>";
                    echo "<tr>";
                    echo "<td></td><td></td><td></td><td></td><td></td><td></td>";
                    echo "<td align='right' scope='row' style='font-size: 12px' class='border'><b>Total</b></td>";
                    echo "<td align='right' scope='row' style='font-size: 12px;border: 1px solid black;'>" . number_format((float) $total, 2) . "</td>";
                    echo "</tr>";
                }else{
                    $consulta = "SELECT CONCAT('Ticket número ', t.IdTicket) AS descripcion, k.PagoChofer FROM c_orden_compra oc INNER JOIN k_tickets_oc k ON k.IdOrdenCompra = oc.Id_orden_compra
                    INNER JOIN c_ticket t ON t.IdTicket = k.IdTicket WHERE oc.Id_orden_compra = $idOrdenCompra";
                    $queryOC = $catalogo->obtenerLista($consulta);
                    $SubTotal = 0;
                    while ($rs = mysql_fetch_array($queryOC)) {
                        echo "<tr>";
                        echo "<td align='center' scope='row' style='font-size: 12px' class='border'>Ticket</td>";
                        echo "<td align='center' scope='row' style='font-size: 12px' class='border'></td>";
                        echo "<td align='center' scope='row' style='font-size: 12px' class='border'></td>";
                        echo "<td align='left' scope='row' style='font-size: 12px;border: 1px solid black;' >" . $rs['descripcion'] . "</td>";
                        echo "<td align='center' scope='row' style='font-size: 12px' class='border'>1</td>";
                        echo "<td align='center' scope='row' style='font-size: 12px' class='border'>1</td>";
                        echo "<td align='right' scope='row' style='font-size: 12px;border: 1px solid black;'>" . number_format((float) $rs['PagoChofer'], 2) . "</td>";
                        echo "<td align='right' scope='row' style='font-size: 12px;border: 1px solid black;'>" . number_format((float) $rs['PagoChofer'], 2) . "</td>";
                        echo "</tr>";
                        $SubTotal = (float) $SubTotal + (float) $rs['PagoChofer'];
                    }
                    $iva = (float) $SubTotal * .16;
                    $total = $SubTotal + $iva;
                    echo "<tr>";
                    echo "<td></td><td></td><td></td><td></td><td></td><td></td>";
                    echo "<td align='right' scope='row' style='font-size: 12px' class='border'><b>Sub total</b></td>";
                    echo "<td align='right' scope='row' style='font-size: 12px;border: 1px solid black;'>" . number_format((float) $SubTotal, 2) . "</td>";
                    echo "</tr>";
                    echo "<tr>";
                    echo "<td></td><td></td><td></td><td></td><td></td><td></td>";
                    echo "<td align='right' scope='row' style='font-size: 12px' class='border'><b>IVA</b></td>";
                    echo "<td align='right' scope='row' style='font-size: 12px;border: 1px solid black;'>" . number_format((float) $iva, 2) . "</td>";
                    echo "</tr>";
                    echo "<tr>";
                    echo "<td></td><td></td><td></td><td></td><td></td><td></td>";
                    echo "<td align='right' scope='row' style='font-size: 12px' class='border'><b>Total</b></td>";
                    echo "<td align='right' scope='row' style='font-size: 12px;border: 1px solid black;'>" . number_format((float) $total, 2) . "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
            <br/><br/>
            <table style="width:100%">
                <tr>
                    <td style="width:100%;height: 50px;font-size: 12px;border: 1px solid black;text-align: left;vertical-align: top;"><b>Observaciones:</b> <?php echo " " . $observacion ?></td>
                </tr>
            </table>
            <br/><br/>
        </div>
    </body>
</html>