<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_GET['RFC'])) {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/ReporteFacturacion_net.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");

$facturas = new ReporteFacturacion();
$facturas->setRfccliente($_GET['RFC']);
$estatus = array(1);
$facturas->setStatus($estatus);/*Para que muestre solo las facturas no pagadas*/
$result = $facturas->getTabla(false);

$parameter = new Parametros();
if($parameter->getRegistroById("18") && $parameter->getActivo() == "1"){
    $carpeta_virtual = $parameter->getDescripcion();
}else{
    $carpeta_virtual = "";
}
?>
<!DOCTYPE>
<html>
    <head>
        <title>Facturas no pagadas</title>
        <!-- JS -->
        <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
        <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
        <script src="../resources/js/jquery/jquery-ui.min.js"></script>
        <!-- Tables -->
        <script type="text/javascript" language="javascript" src="../resources/media/js/jquery.dataTables.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/media/js/TableTools.min.js"></script>
        <link href="../resources/css/table/demo_page.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/demo_table_jui.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/TableTools.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/sicop.css" rel="stylesheet" type="text/css">
        
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/facturacion/facturacion_reporte_tabla.js"></script>
    </head>
    <body>
        <?php
            $cabeceras = Array("Folio", "Fecha", "Nombre Receptor", "RFC Emisor", "Subtotal", "Importe", "Total", "Estado", "Detalle");
            echo "<table id='treportfact' name='treportfact'>";
            echo "<thead><tr>";                            
            for ($i = 0; $i < count($cabeceras); $i++) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
            }                
            echo "</tr></thead>";
            while($rs = mysql_fetch_array($result)){
                echo "<tr>";
                echo "<td align='center' scope='row'>" . $rs['Folio'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['FechaFacturacion'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['NombreReceptor'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['RFCEmisor'] . "</td>";
                echo "<td align='center' scope='row'>$" . $rs['subtotal'] . "</td>";
                echo "<td align='center' scope='row'>$" . $rs['importe'] . "</td>";
                echo "<td align='center' scope='row'>$" . $rs['Total'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['EstadoFactura'] . "</td>";
                //echo "<td align='center' scope='row'><a href='" . $_SESSION['liga'] . "/cfdi/FacturaPDF.aspx?IdFactura=" . $rs['IdFactura'] . "&uguid=" . $_SESSION['user'] . "' target='_blank'><img src='../resources/images/Textpreview.png' title='Detalle' style='width: 24px; height: 24px;'/></a></td>";
                if(empty($rs['PDF'])){
                    echo "<td align='center' scope='row'><a href='facturacion/archivo_not_found.php' target='_blank'>
                        <img src='../resources/images/pdf_descarga.png' title='PDF Factura' style='width: 32px; height: 32px;'/></a></td>";
                }else{
                    if (strpos($rs['PDF'], 'PDF/') !== false) {
                        echo "<td align='center' scope='row'><a href='../" . $rs['PDF'] . "' target='_blank'>
                            <img src='../resources/images/pdf_descarga.png' title='PDF Factura' style='width: 32px; height: 32px;'/></a></td>";
                    } else {
                        echo "<td align='center' scope='row'>";
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
                        echo "<img src='../resources/images/pdf_descarga.png' title='PDF Factura' style='width: 32px; height: 32px;'/></a></td>";
                    }
                }
                echo "</tr>";
            }
            //Tambien imprimimos las incobrables
            $estados = array(3);
            $facturas->setStatus($estados);            
            $result = $facturas->getTabla(false);
            while($rs = mysql_fetch_array($result)){
                echo "<tr>";
                echo "<td align='center' scope='row'>" . $rs['Folio'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['FechaFacturacion'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['NombreReceptor'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['RFCEmisor'] . "</td>";
                echo "<td align='center' scope='row'>$" . $rs['subtotal'] . "</td>";
                echo "<td align='center' scope='row'>$" . $rs['importe'] . "</td>";
                echo "<td align='center' scope='row'>$" . $rs['Total'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['EstadoFactura'] . "</td>";
                //echo "<td align='center' scope='row'><a href='" . $_SESSION['liga'] . "/cfdi/FacturaPDF.aspx?IdFactura=" . $rs['IdFactura'] . "&uguid=" . $_SESSION['user'] . "' target='_blank'><img src='../resources/images/Textpreview.png' title='Detalle' style='width: 24px; height: 24px;'/></a></td>";
                if(empty($rs['PDF'])){
                    echo "<td align='center' scope='row'><a href='facturacion/archivo_not_found.php' target='_blank'>
                        <img src='../resources/images/pdf_descarga.png' title='PDF Factura' style='width: 32px; height: 32px;'/></a></td>";
                }else{
                    if (strpos($rs['PDF'], 'PDF/') !== false) {
                        echo "<td align='center' scope='row'><a href='../" . $rs['PDF'] . "' target='_blank'>
                            <img src='../resources/images/pdf_descarga.png' title='PDF Factura' style='width: 32px; height: 32px;'/></a></td>";
                    } else {
                        echo "<td align='center' scope='row'>";
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
                        echo "<img src='../resources/images/pdf_descarga.png' title='PDF Factura' style='width: 32px; height: 32px;'/></a></td>";
                    }
                }
                echo "</tr>";
            }
            echo "</table>";
        ?>
    </body>
</html>