<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/ReporteFacturacion2.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "facturacion/ReporteFacturacion.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
    $reporte = new ReporteFacturacion2();
    $urlextra = "";
    if (isset($parametros['RFC']) && $parametros['RFC'] != "") {
        $reporte->setRFC($parametros['RFC']);
        if ($urlextra == "") {
            $urlextra.="?RFC=" . $parametros['RFC'];
        } else {
            $urlextra.="&RFC=" . $parametros['RFC'];
        }
    }
    if (isset($parametros['fecha1']) && $parametros['fecha1'] != "") {
        $reporte->setFechaInicial($parametros['fecha1']);
        if ($urlextra == "") {
            $urlextra.="?fecha1=" . $parametros['fecha1'];
        } else {
            $urlextra.="&fecha1=" . $parametros['fecha1'];
        }
    }
    if (isset($parametros['fecha2']) && $parametros['fecha2'] != "") {
        $reporte->setFechaFinal($parametros['fecha2']);
        if ($urlextra == "") {
            $urlextra.="?fecha2=" . $parametros['fecha2'];
        } else {
            $urlextra.="&fecha2=" . $parametros['fecha2'];
        }
    }
    if (isset($parametros['rfccliente']) && $parametros['rfccliente'] != "") {
        $reporte->setRfccliente($parametros['rfccliente']);
        if ($urlextra == "") {
            $urlextra.="?rfccliente=" . $parametros['rfccliente'];
        } else {
            $urlextra.="&rfccliente=" . $parametros['rfccliente'];
        }
    }
    if (isset($parametros['cliente']) && $parametros['cliente'] != "") {
        $reporte->setCliente($parametros['cliente']);
        if ($urlextra == "") {
            $urlextra.="?cliente=" . $parametros['cliente'];
        } else {
            $urlextra.="&cliente=" . $parametros['cliente'];
        }
    }
    if (isset($parametros['status']) && $parametros['status'] != "") {
        $reporte->setStatus($parametros['status']);
        if ($urlextra == "") {
            $urlextra.="?status=" . $parametros['status'];
        } else {
            $urlextra.="&status=" . $parametros['status'];
        }
    }
    if (isset($parametros['docto']) && $parametros['docto'] != "") {
        $reporte->setDocto($parametros['docto']);
        if ($urlextra == "") {
            $urlextra.="?docto=" . $parametros['docto'];
        } else {
            $urlextra.="&docto=" . $parametros['docto'];
        }
    }
    if (isset($parametros['folio']) && $parametros['folio'] != "") {
        $reporte->setFolio($parametros['folio']);
        if ($urlextra == "") {
            $urlextra.="?folio=" . $parametros['folio'];
        } else {
            $urlextra.="&folio=" . $parametros['folio'];
        }
    }
    $query = $reporte->getTabla(true);
    $cabeceras = Array("Folio", "Factura", "Fecha", "Nombre Receptor", "RFC Receptor", "Subtotal", "Descuento", "Importe", "Total", "Estado", "PDF", "Crear Otra Factura", "Editar", "RFC", "Tipo de comprobante", "Factura enviada", "");
    ?>
    <script type="text/javascript" language="javascript" src="resources/js/paginas/facturacion/facturacion_reporte_tabla.js"></script>
    <a href="facturacion/ReporteFacturacionExcel.php<?php echo $urlextra ?>" target="_blank" class="boton" style="float: right">Generar excel</a>
    <style>
        .fg-toolbar.ui-toolbar.ui-widget-header.ui-corner-tl.ui-corner-tr.ui-helper-clearfix,
        .fg-toolbar.ui-toolbar.ui-widget-header.ui-corner-bl.ui-corner-br.ui-helper-clearfix{
            min-width: 100%;
        }
    </style>
    <div>
        <table id="treportfact" class="reporte table-responsive dataTable">
            <thead class="thead-dark">
                <tr>
                    <?php
                    for ($i = 0; $i < count($cabeceras); $i++) {
                        echo "<th>" . $cabeceras[$i] . "</th>";
                    }
                    ?>                        
                </tr>
            </thead>
            <tbody> 
                <?php
                while ($rs = mysql_fetch_array($query)) {
                    echo "<tr>
                            <td>" . $rs['Folio'] . "</td>
                            <td>" . $rs['TipoFactura'] . " " . $rs['TipoFacturaNombre'] . "</td>
                            <td>" . $rs['FechaFacturacion'] . "</td>
                            <td>" . $rs['NombreReceptor'] . "</td>
                            <td>" . $rs['RFCReceptor'] . "</td>
                            <td>$ " . number_format($rs['Subtotal'],2) . "</td>
                            <td>$ " . number_format($rs['Descuento'],2) . "</td>
                            <td>$ " . number_format($rs['Importe'],2) . "</td>
                            <td>$ " . number_format($rs['Total'],2) . "</td>";
                    if ($rs['Generada'] == 0) {
                        echo "<td></td>";
                    } else {
                        echo "<td>" . $rs['EstadoFactura'] . "</td>";
                    }
                    /* if ($rs['Generada'] == 1) {
                    echo "<td align='center' scope='row'><a href='XML/XML-" . $rs['IdFactura'] . ".xml' target='_blank'><img src='resources/images/icono_xml.png' title='XML Factura' style='width: 32px; height: 32px;'/></a></td>";
                    echo "<td align='center' scope='row'><a href='PDF/PDF-" . $rs['IdFactura'] . ".pdf' target='_blank'><img src='resources/images/pdf_descarga.png' title='PDF Factura' style='width: 32px; height: 32px;'/></a></td>";
                    } else { 
                    echo "<td align='center' scope='row'></td>"; */
                    echo "<td>
                            <a href='WEB-INF/Controllers/facturacion/Controller_PDF_Factura.php?id=" . $rs['IdFactura'] . "' target='_blank'>
                                <img src='resources/images/pdf_descarga.png' title='PDF Factura' style='width: 32px; height: 32px;'/>
                            </a>
                        </td>";
                    //}
                    /* if ($rs['Generada'] == 0) {
                    //echo "<td align='center' scope='row'><a href='#' onclick=\"lanzarPopUp('Generar Factura', 'contrato/alta_localidad.php?id=" + n.substring(4, n.length) + "'); return false;\" ><img src='resources/images/facturar.png' title='Generar Factura' style='width: 32px; height: 32px;'/></a></td>";
                    echo "<td align='center' scope='row'><a href='#' onclick='GenerarFactura(" . $rs['IdFactura'] . "," . $rs['Folio'] . "); return false;' ><img src='resources/images/facturar.png' title='Generar Factura' style='width: 32px; height: 32px;'/></a></td>";
                    } else {
                    echo "<td align='center' scope='row'></td>";
                    } */
                    echo "<td>";
                    if ($permisos_grid->getAlta()) {
                        echo"<a href='#' onclick='CopiaFactura(" . $rs['IdFactura'] . "," . $rs['Folio'] . "); return false;' >
                                <img src='resources/images/copyfactura.png' title='Copiar factura' style='width: 32px; height: 32px;'/>
                            </a>";
                    }
                    echo "</td>";
                    if ($rs['Generada'] == 0 /* && $rs['FechaFacturacion'] > '2014-06-17' */) {
                        //if ($rs['Generada'] == 0 ) {
                        echo "<td align='center' scope='row'>";
                        if ($permisos_grid->getModificar()) {
                            if ($rs['CFDI33'] == 0) {
                                echo "<a href='#' onclick='modificarFactura(" . $rs['IdFactura'] . ",\"" . $rs['Folio'] . "\"); return false;'>
                                        <img src='resources/images/Modify.png' title='Modificar Factura' style='width: 28px; height: 28px;'/>
                                    </a>";
                            } else {
                                echo "<a href='#' onclick='cambiarContenidos(\"facturacion/alta_factura_33.php?id=" . $rs['IdFactura'] . "\",\"Facturación 3.3\"); return false;'>
                                        <img src='resources/images/Modify.png' title='Modificar Factura' style='width: 28px; height: 28px;'/>
                                    </a>";
                            }
                        }
                        echo "</td>";
                    } else {
                        echo "<td></td>";
                    }
                    echo "<td>" . $rs['RFCEmisor'] . "</td>
                            <td>" . $rs['TipoComprobante'] . "</td>
                            <td>";
                    if ($permisos_grid->getModificar()) {
                        echo "<a href='facturacion/enviar_factura.php?id=" . $rs['IdFactura'] . "' target='_blank' return false;'>";
                    }
                    echo $rs['Enviado'];
                    if ($permisos_grid->getModificar()) {
                        echo "</a>";
                    }
                    echo "</td>";

                    /* if ($rs['Generada'] == 0) {
                    echo "<td align='center' scope='row'></td>";
                    } else {
                    if ($rs['PagadoSiNo'] == 'Si' || $rs['EstadoFactura'] == "P") {
                    echo "<td align='center' scope='row'>";
                    if($permisos_grid->getModificar()){
                    echo "<a href='#' onclick=\"lanzarPopUp('Pago parcial factura " . $rs['Folio'] . "', 'facturacion/pago_parcial.php?factura=" . $rs['IdFactura'] . "&folio=" . $rs['Folio'] . "&pagado=true'); return false;\">";
                    }
                    echo "Si";
                    if($permisos_grid->getModificar()){
                    echo "</a>";
                    }
                    echo "</td>";
                    } else {
                    echo "<td align='center' scope='row'>";
                    if($permisos_grid->getModificar()){
                    echo "<a href='#' onclick=\"lanzarPopUp('Pago parcial factura " . $rs['Folio'] . "', 'facturacion/pago_parcial.php?factura=" . $rs['IdFactura'] . "&folio=" . $rs['Folio'] . "'); return false;\">";
                    }
                    echo "No";
                    if($permisos_grid->getModificar()){
                    echo "</a>";
                    }
                    echo "</td>";
                    }
                    } */


                    echo "<td>";
                    if ($permisos_grid->getBaja()) {
                        echo "<a href='#' onclick=\"Eliminarfactura('facturacion/Eliminarfactura.php','" . $rs['IdFactura'] . "','" . $rs['Folio'] . "'); return false;\">
                                <img src='resources/images/Erase.png' title='Eliminar Prefactura'/>
                            </a>";
                    }
                    echo "</td>
                        </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    
    <div id="report"></div>
    <div id="dialog" title="Leyenda de factura" >
        <!--<textarea id="text_factura" >Esta factura deber&aacute; ser pagada en una sola exhibción.Esta factura no libera al cliente de adeudos anteriores o consumos no incluidos en la misma los titulos de crédito dados por el cliente, en los casos autorizados, serán recibidos bajo condición 'salvo buen cobro' con base en el Articulo de la Ley General de Titulos y Operaciones de Crédito, de no verificarse el pago del importe que ampare este documento al vencimiento, el cliente se obliga a pagar el 10% mensual de intereses moratorios, sobre saldos insolutos.</textarea>-->
        <textarea id="text_factura" ></textarea>
    </div>
    <?php
} else {
    echo "No se recibieron los datos.";
}
if (isset($_GET['page']) && $_GET['page'] != 0) {
    ?>
    <script type="text/javascript" language="javascript">
        ponerpagina(<?php echo $_GET['page']; ?>);
    </script>
    <?php
}
?>

