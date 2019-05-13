<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/ReporteFacturacion_net.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
    
$permisos_grid = new PermisosSubMenu();
$same_page = "facturacion/ReporteFacturacion_net.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$copiar = $permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 40);

if (isset($_POST['form'])) {
    $parametros = "";
    
    if (isset($_POST['form'])) {
        parse_str($_POST['form'], $parametros);
    }
    
    $parameter = new Parametros();
    if($parameter->getRegistroById("18") && $parameter->getActivo() == "1"){
        $carpeta_virtual = $parameter->getDescripcion();
    }else{
        $carpeta_virtual = "";
    }
    
    if($parameter->getRegistroById("7")){
        $liga_net = $parameter->getDescripcion();
    }else{
        $liga_net = $_SESSION['liga'];
    }
    
    $reporte = new ReporteFacturacion();
    $urlextra = "";
    $pref = true;
    
    if(isset($parametros['no_pref']) && $parametros['no_pref']=="1"){
        $pref = false;
    }        
    
    if (isset($parametros['ejecutivo']) && $parametros['ejecutivo'] != "") {
        $reporte->setEjecutivo($parametros['ejecutivo']);
        if ($urlextra == "") {
            $urlextra.="?Ejecutivo=" . $parametros['ejecutivo'];
        } else {
            $urlextra.="&Ejecutivo=" . $parametros['ejecutivo'];
        }
    }
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
    
    if (isset($parametros['periodo_facturacion']) && $parametros['periodo_facturacion'] != "") {
        $reporte->setPeriodoFacturacion($parametros['periodo_facturacion']);
        if ($urlextra == "") {
            $urlextra.="?periodo=" . $parametros['periodo_facturacion'];
        } else {
            $urlextra.="&periodo=" . $parametros['periodo_facturacion'];
        }
    }
    
    if (isset($parametros['rfccliente']) && $parametros['rfccliente'] != "") {
        $reporte->setRfccliente($parametros['rfccliente']);
        if ($urlextra == "") {
            $urlextra.="?rfccliente=" . $parametros['rfccliente'];
        } else {
            $urlextra.="&rfccliente=" . $parametros['rfccliente'];
        }
    } else if (isset($parametros['rfc_facturas']) && $parametros['rfc_facturas'] != "") {
        $reporte->setRfccliente($parametros['rfc_facturas']);
        if ($urlextra == "") {
            $urlextra.="?rfccliente=" . $parametros['rfc_facturas'];
        } else {
            $urlextra.="&rfccliente=" . $parametros['rfc_facturas'];
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
    
    if(!isset($parametros['cxc_activo'])){//Si no estamos en la venta de cxc
        $cxc = false;
        $cabeceras = Array("Folio", "Fecha", "Número cliente", "Nombre Receptor", "RFC Emisor", "Subtotal", "Importe", "Total", "Estado", "XML", "PDF", "Genera pre-factura", "NDC", "RFC", "Tipo","Pagos con NDC" ,"Enviado", "Pagado", "");
        
    }else{
        $cxc = true;
        $cabeceras = Array("Folio", "Fecha", "Ejecutivo", "Nombre Receptor", "RFC Emisor", "Pagado", "Por pagar", "Total", "PDF", "RFC", "Pagado", "Pagos");
        $same_page = "facturacion/ReporteFacturacion_net.php?cxc=1";
        $permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
    }
    
    //if(!$cxc){//Si no estamos en la venta de cxc
    if (isset($parametros['status']) && $parametros['status'] != "") {
        $reporte->setStatus($parametros['status']);
        if ($urlextra == "") {
            $ids = "";
            foreach ($parametros['status'] as $value) {
                $ids .= "$value,";
            }
            if($ids != ""){
                $ids = substr($ids, 0, strlen($ids)-1);
            }
            $urlextra.="?status=$ids";
        } else {
            $ids = "";
            foreach ($parametros['status'] as $value) {
                $ids .= "$value,";
            }
            if($ids != ""){
                $ids = substr($ids, 0, strlen($ids)-1);
            }            
            $urlextra.="&status=$ids";
        }
    }else if($cxc){//Si no hay filtros de estatus, pero estamos en cuentas por cobrar, se muestran solo las no pagadas
        $urlextra.="?status=1";
        $array_estatus = array(1);
        $reporte->setStatus($array_estatus);
    }
    
    if(isset($parametros['tipo_facturas']) && $parametros['tipo_facturas'] != ""){
        $reporte->setTipoFactura($parametros['tipo_facturas']);
        if ($urlextra == "") {
            $ids = "";
            foreach ($parametros['tipo_facturas'] as $value) {
                $ids .= "$value,";
            }
            if($ids != ""){
                $ids = substr($ids, 0, strlen($ids)-1);
            }
            $urlextra.="?TF=$ids";
        } else {
            $ids = "";
            foreach ($parametros['tipo_facturas'] as $value) {
                $ids .= "$value,";
            }
            if($ids != ""){
                $ids = substr($ids, 0, strlen($ids)-1);
            }            
            $urlextra.="&TF=$ids";
        }
    }
    /*}else{
        $urlextra.="?status=1";
        $array_estatus = array(1);
        $reporte->setStatus($array_estatus);
    }*/
    
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
    if (isset($_GET['mnu']) && isset($_GET['action']) && isset($_GET['id'])) {
        $reporte->setFolio($_GET['id']);
        if ($urlextra == "") {
            $urlextra.="?folio=" . $_GET['id'];
        } else {
            $urlextra.="&folio=" . $_GET['id'];
        }
    }
    
    if(!$cxc && $pref){        
        $query = $reporte->getTabla(true);        
    }else{        
        $query = $reporte->getTabla(false);        
    }
    
    $num_facturas = 0;
    $total = 0;
    $pagado = 0;
    ?>
    <script type="text/javascript" language="javascript" src="resources/js/paginas/facturacion/facturacion_reporte_tabla_net.js"></script>
    <br/><br/>
    <table style="float: right;">
        <tr>
            <td>
                <a href="facturacion/ReporteFacturacionExcelLight.php<?php echo $urlextra ?>" target="_blank" class="boton">Generar excel</a>
            </td>
            <td>
                <a href="#" onclick="cambiarContenidos('facturacion/alta_factura.php?param1=egreso', 'Facturación'); return false;" class="boton">Nueva NDC</a>
            </td>
        </tr>
    </table>    
    <br/><br/>
    <?php if($cxc){ ?>
        <div class='ui-state-highlight ui-corner-all' style='width: 18%;margin-top:15px; margin-bottom:15px;'>
            <span class='ui-icon ui-icon-info' style='float: left;'></span>
            <div id='resumen_por_pagar' style="display: inline;">Pruebas</div>        
        </div>
        <br/>
    <?php } ?>
    <br/>
    <table id="treportfact" style="max-width: 100%;">
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
                if($cxc && $rs['color'] == "red"){
                    $color = "style='background-color: #A63F40; color: white;'";
                }
                echo "<tr>";
                echo "<td align='center' scope='row' $color>" . $rs['Folio'] . "</td>";
                echo "<td align='center' scope='row' $color>" . $rs['FechaFacturacion'] . "</td>";
                if(!$cxc){
                    echo "<td align='center' scope='row' $color>" . $rs['ClaveCliente'] . "</td>";
                }else{
                    echo "<td align='center' scope='row' $color>" . $rs['ejecutivo'] . "</td>";
                }
                echo "<td align='center' scope='row' $color>" . $rs['NombreReceptor'] . "</td>";
                echo "<td align='center' scope='row' $color>" . $rs['RFCEmisor'] . "</td>";
                if(!$cxc){
                    echo "<td align='center' scope='row' $color>$" . $rs['subtotal'] . "</td>";
                    echo "<td align='center' scope='row' $color>$" . $rs['importe'] . "</td>";
                }else{
                    echo "<td align='center' scope='row' $color>$" . number_format($rs['pagado'],2) . "</td>";
                    echo "<td align='center' scope='row' $color>$" . number_format((float)$rs['TotalSinFormato'] - (float)($rs['pagado']),2) . "</td>";
                }
                echo "<td align='center' scope='row' $color>$" . $rs['Total'] . "</td>";
                if(!$cxc){
                    echo "<td align='center' scope='row' $color>" . $rs['EstadoFactura'] . "</td>";
                    if(empty($rs['XML'])){
                        echo "<td align='center' scope='row' $color><a href='facturacion/archivo_not_found.php' target='_blank'>
                            <img src='resources/images/icono_xml.png' title='XML Factura' style='width: 32px; height: 32px;'/></a></td>";
                    }else{
                        if (strpos($rs['XML'], 'XML/') !== false || strpos($rs['XML'], 'Santi/') !== false) {
                            echo "<td align='center' scope='row' $color><a href='" . $rs['XML'] . "' target='_blank'>
                                <img src='resources/images/icono_xml.png' title='XML Factura' style='width: 32px; height: 32px;'/></a></td>";
                        } else {
                            echo "<td align='center' scope='row' $color>";
                            if($carpeta_virtual != ""){
                                $index = strrpos($rs['XML'], "Facturas");
                                if($index === FALSE){
                                    echo "<a href='$carpeta_virtual/".$rs['XML']."' target='_blank'>";
                                }else{
                                    echo "<a href='$carpeta_virtual/".substr($rs['XML'], $index+9)."' target='_blank'>";
                                }
                            }else{
                                echo "<a href='WEB-INF/Controllers/facturacion/Controller_XML_Factura.php?folio=" . $rs['Folio'] . "' target='_blank'>";
                            }                                                    
                            echo "<img src='resources/images/icono_xml.png' title='XML Factura' style='width: 32px; height: 32px;'/></a></td>";                        
                        }
                    }
                }
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
                
                if(!$cxc){
                    echo "<td align='center' scope='row' $color>"; 
                    if($permisos_grid->getAlta()){
                        echo "<a onclick='generarFacturaLectura(" . $rs['IdFactura'] . ")'><img src='resources/images/cfdi.jpg' title='Crear pre-factura' style='width: 32px; height: 32px;'/></a>"; 
                    }
                    echo "</td>";                
                    echo "<td align='center' scope='row' $color>";
                    if (/* $rs['FolioFiscal'] == "" && */ $permisos_grid->getAlta() && $rs['EstadoFactura'] != 'P' && $rs['EstadoFactura'] != 'C' && $rs['EstadoFactura'] != "INC" && ($rs['TipoComprobante'] != "NDC" || $rs['Serie'] != "")) {
                        //echo "<a href='" . $liga_net . "/cfdi/DatosFactura.aspx?IdFactura=" . $rs['IdFactura'] . "&TipoDocumento=egreso&uguid=" . $_SESSION['user'] . "' target='_blank'><img src='resources/images/facturar2.png' title='Generar NDC' style='width: 32px; height: 32px;'/></a>";
                        echo "<a href='principal.php?mnu=facturacion&action=alta_factura&id=" . $rs['IdFactura'] . "&param1=egreso' target='_blank'><img src='resources/images/facturar2.png' title='Generar NDC' style='width: 32px; height: 32px;'/></a>";
                    }
                    echo "</td>";
                    /*echo "<td align='center' scope='row' $color>";
                    if($permisos_grid->getModificar()){                        
                            echo "<a href='" . $liga_net . "/cfdi/FacturaPDF.aspx?IdFactura=" . $rs['IdFactura'] . "&uguid=" . $_SESSION['user'] . "' target='_blank'><img src='resources/images/Textpreview.png' title='Detalle' style='width: 24px; height: 24px;'/></a>";                            
                    }
                    echo "</td>";*/
                }
                                
                echo "<td align='center' scope='row' $color>" . $rs['RFCReceptor'] . "</td>";
                
                if(!$cxc){
                    echo "<td align='center' scope='row' $color>" . $rs['TipoComprobante'] . "</td>";
                    echo "<td align='center' scope='row' $color>" . $rs['PagadoNDC'] . "</td>";
                    //if (strpos($rs['PDF'], 'PDF/') !== false) {
                    echo "<td align='center' scope='row' $color>"; 
                    if($permisos_grid->getModificar()){
                        echo "<a href='facturacion/enviar_factura_cfdi.php?id=" . $rs['IdFactura'] . "' target='_blank'>"; 
                    }
                    echo $rs['Enviado']; 
                    if($permisos_grid->getModificar()){
                        echo "</a>"; 
                    }
                    echo "</td>";
                    /*} else {
                        echo "<td align='center' scope='row' $color>"; 
                        if($permisos_grid->getModificar()){
                            echo "<a href='" . $liga_net . "/cfdi/EnvioCorreos.aspx?IdFactura=" . $rs['IdFactura'] . "&uguid=" . $_SESSION['user'] . "' target='_blank'>"; 
                        }
                        echo $rs['Enviado']; 
                        if($permisos_grid->getModificar()){
                            echo "</a>"; 
                        }
                        echo "</td>";
                    }*/
                }
                if ($rs['PagadoSiNo'] != 'No') {
                    echo "<td align='center' scope='row' $color>";
                    if(!$cxc){
                         echo $rs['PagadoSiNo'];
                    }else{
                        if($permisos_grid->getModificar()){
                            if($cxc){
                                echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?cxc=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
                            }else{
                                echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?cxc=true&cfdi=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
                            }
                        }
                        echo $rs['PagadoSiNo']; 
                        if($permisos_grid->getModificar()){
                            echo "</a>"; 
                        }
                    }
                    echo "</td>";
                } else {
                    echo "<td align='center' scope='row' $color>";
                    /*if(!$cxc){
                        if($permisos_grid->getModificar()){
                            echo "<a href='" . $_SESSION['liga'] . "/cfdi/Cuentasxcobrar/AltaCxC.aspx?IdFactura=" . $rs['IdFactura'] . "&FechaContactar=&Pago=1&uguid=" . $_SESSION['user'] . "' target='_blank'>"; 
                        }
                        echo $rs['PagadoSiNo']; 
                        if($permisos_grid->getModificar()){
                            echo "</a>"; 
                        }
                    }else{*/
                    if($permisos_grid->getModificar()){                        
                        if($cxc){
                            echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?cxc=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
                        }else{
                            echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?cxc=true&cfdi=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
                        }
                    }
                    echo $rs['PagadoSiNo']; 
                    if($permisos_grid->getModificar()){
                        echo "</a>"; 
                    }
                    //}
                    echo "</td>";
                }
                
                if(!$cxc){
                    if ($rs['CanceladaSAT'] != "" || $rs['EstadoFactura'] == "PAGADA") {                    
                        echo "<td align='center' scope='row' $color></td>";
                    } else {
                        echo "<td align='center' scope='row' $color>"; 
                        if($permisos_grid->getBaja()){
                            echo "<a href='#' onclick=\"cancelarfactura('facturacion/CancelarFactura_net.php?folio=" . $rs['IdFactura'] . "','" . $rs['Folio'] . "'); return false;\"><img src='resources/images/Erase.png' title='Cancelar Factura'/></a>"; 
                        }
                        echo "</td>";
                    }
                }else{
                    echo "<td align='center' scope='row' $color>".str_replace('()', '', $rs['pagos'])."</td>";
                }
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>    
    <div id="report"/>    
    <?php
    if($cxc){
        echo "<input type='hidden' id='total_facturas' name='total_facturas' value='".  number_format($num_facturas)."'/>";
        echo "<input type='hidden' id='total_costo_factura' name='total_costo_factura' value='$".  number_format($total, 2)."'/>";
        echo "<input type='hidden' id='pagado_facturas' name='pagado_facturas' value='$".  number_format($pagado, 2)."'/>";
        echo "<input type='hidden' id='por_pagar_facturas' name='por_pagar_facturas' value='$".  number_format($total - $pagado, 2)."'/>";
    }
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

