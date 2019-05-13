<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/ReporteFacturacion.class.php");
$parametros;
if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
    $reporte = new ReporteFacturacion();
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
    if (isset($parametros['vendedor']) && $parametros['vendedor'] != "") {
        $reporte->setVendedor($parametros['vendedor']);
        if ($urlextra == "") {
            $urlextra.="?vendedor=" . $parametros['vendedor'];
        } else {
            $urlextra.="&vendedor=" . $parametros['vendedor'];
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
    $query = $reporte->getTabla(true);
    $cabeceras = Array("Folio", "Fecha", "Nombre Receptor", "Nombre Emisor", "Subtotal", "Importe", "Total", "Estado de factura", "RFC", "Tipo de comprobante", "Cancelada", "");
    $columnas = Array("Folio", "FechaFacturacion", "NombreReceptor", "NombreEmisor", "subtotal", "importe", "Total", "EstadoFactura", "RFCReceptor", "TipoComprobante", "Cancelada");
    ?>
    <script type="text/javascript" language="javascript" src="resources/js/paginas/facturacion/tabla_cancelaciones.js"></script>
    <table id="treportfact">
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
                echo "<tr>";
                foreach ($columnas as $value) {
                    echo "<td align='center' scope='row'>" . $rs[$value] . "</td>";
                }
                if ($rs['Cancelada'] == "Activa") {
                    echo "<td><a href='#' onclick='CancelarFactura(\"\",\"\"); return false;'><img src='resources/images/Erase.png' title='Cancelar Factura'/></a></td>";
                } else {
                    echo "<td align='center' scope='row'></td>";
                }
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
    <br/><br/>
    <div id="report"/>
    <?php
} else {
    echo "No se recibieron los datos.";
}
?>
