<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
//include_once("../WEB-INF/Classes/ReporteFacturacion_net.class.php");
include_once("../WEB-INF/Classes/ReporteFacturacion_net_kike.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("Catalogo.class.php");
include_once("../WEB-INF/Classes/Contrato.class.php");
    
$catalogo = new Catalogo();
$contrato = new Contrato();
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
        $cabeceras = Array("Folio", "Fecha", "NĂşmero cliente", "Nombre Receptor", "RFC Emisor", "Contrato", "Subtotal", "Importe", "Total", "Estado", "XML", "PDF", "Genera pre-factura","Sustituir", "NDC", "RFC", "Tipo","Pagos con NDC" ,"Enviado", "Pagado", "");
        
    }else{
        $cxc = true;
        $cabeceras = Array("Folio", "Fecha", "Ejecutivo", "Nombre Receptor", "RFC Emisor", "Contrato", "Pagado", "Por pagar", "Total", "PDF", "RFC", "Pagado", "Pagos");
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
        $query1 = $reporte->getTabla(true);
        $query2 = $reporte->getTabla(true);
        $query3 = $reporte->getTabla(true);
        $query4 = $reporte->getTabla(true);
    }else{        
        $query = $reporte->getTabla(false);
        $query1= $reporte->getTabla(false);
        $query2= $reporte->getTabla(false);
        $query3= $reporte->getTabla(false);
        $query4= $reporte->getTabla(false);
       }
    
    $num_facturas = 0;
    $total = 0;
    $pagado = 0;
    $total1 = 0;
    $pagado1 = 0;
    $total2 = 0;
    $pagado2 = 0;
    $total3 = 0;
    $pagado3 = 0;
    $total4 = 0;
    $pagado4 = 0;
    //Fernando
    $saldos_vencidos= 0;
    $saldos_vencidos_30= 0;
    $saldos_vencidos_60= 0;
    $saldos_vencidos_90= 0;
    //Fernando
    ?>
    <script type="text/javascript" src="resources/js/paginas/resumen.js"></script> <!-- se agrego esta linea para hacer los tableros-->
        <link href="resources/css/resumen.css" rel="stylesheet" type="text/css"/> <!-- se agrego esta linea para hacer los tableros-->

      
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script type="text/javascript" language="javascript" src="resources/js/paginas/facturacion/facturacion_reporte_tabla_net.js"></script>
    <br/><br/>
    <table style="float: right;">
        <tr>
            <td>
                <a href="facturacion/ReporteFacturacionExcelLight.php<?php echo $urlextra ?>" target="_blank" class="boton">Generar excel</a>
            </td>
            <td>
                <a href="#" onclick="cambiarContenidos('facturacion/alta_factura_33.php?param1=egreso', 'FacturaciĂłn'); return false;" class="boton">Nueva NDC</a>
            </td>
        </tr>
    </table>    
    <br/><br/>
    <?php if($cxc){ ?>
    <!--<table style='width:100%; border: 1px solid;'>
        <tr>
        <td style='width: 50%;'>
        <div class='ui-state-highlight ui-corner-all' style='width: 20%;'>
            <span class='ui-icon ui-icon-info' style='float: left;'></span>
            <div id='resumen_por_pagar' style="display: block;">Pruebas</div>        
        </div>
        </td>
        <td style='width: 50%;'>
        <div class='ui-state-highlight ui-corner-all' style='width: 20%;'>
            <span class='ui-icon ui-icon-info' style='float: left;'></span>
            <div id='resumen_prueba' style="display: inline;">Aqui no hay nada</div>         
        </td>
        </tr>
        </table>
        <br/>-->
    <?php } ?>
    <br/>
    <div id="main_panel" style="clear: both;overflow: hidden;height: 1%;">
        <h1 style="border-bottom: 1px solid black;">Indicador de Saldos</h1><br/>
            
        <div id="tabs">

            <ul>
                <li><a href="#tabs-1-Saldos"><span aria-hidden="true"><!--<i class="material-icons">view_list</i>--></span> Saldos <i id="num"><?php echo "$num_facturas"; ?></i> </a></li>
                <li><a href="#tabs-2-Saldos_Vencidos"><span aria-hidden="true"><!--<i class="material-icons">view_list</i>--></span> Saldos vencidos <i id="num_1"><?php echo "$saldos_vencidos"; ?></i></a></li>
                <li><a href="#tabs-3-Saldos_Vencidos30"><span aria-hidden="true"><!--<i class="material-icons">view_list</i>--></span> Saldos por Vencer (30 días) <i id="num_2"><?php echo "$saldos_vencidos_30"; ?></i></a></li>
                <li><a href="#tabs-4-Saldos_Vencidos60"><span aria-hidden="true"><!--<i class="material-icons">view_list</i>--></span> Saldos por Vencer (60 días) <i id="num_3"><?php echo "$saldos_vencidos_60"; ?></i></a></li>
                <li><a href="#tabs-5-Saldos_Vencidos90"><span aria-hidden="true"><!--<i class="material-icons">view_list</i>--></span> Saldos por Vencer (90 días) <i id="num_4"><?php echo "$saldos_vencidos_90"; ?></i></a></li>  
            </ul>  

            <div id="tabs-1-Saldos">
                <table id="treportfact" style="max-width: 100%;">
                    <div id="seleccionar" style="position: absolute; left: 3%;">
                        <font size="4" width="33%" align="center" scope="col">
                            
                                <script type="text/javascript">
                                    function obtenerNombre() {
                                        var checks = 0;
                                        var idS = "";
                                        var link = "";
                                        var numeroCheck = $("#numDocumentos").val();//  Numero de documentos    
                                        var RFC = "<?php echo $parametros['cliente'];?>";
                                        if (RFC == "") {
                                            console.log("no se obtuvo el RFC");
                                        }else{
                                        console.log("RFC obtenido "+RFC); 
                                        }
                                        
                                        
                                        for (i = 0; i < numeroCheck; i++) {
                                            if (($('input:checkbox[name=check_'+i+']:checked').val()) == null) {
                                            }else{
                                                    idS = idS + $('input:checkbox[name=check_'+i+']:checked').val() + "_";
                                                checks = checks + 1;
                                            }
                                        }
                                        if (idS == ""){
                                            idS = "No_hay";
                                            alert("No hay seleccionados");
                                        }else{
                                            link = "facturacion/pagos_parciales.php?RFC="+RFC+"&factura="+idS+"&cxc=true";
                                            lanzarPopUp('Pagos parciales',link);
                                            return true;
                                            
                                        }
                                    }                           
                                </script>
                            
                            <input style="cursor: pointer;" type="checkbox" onclick="marcarTodos();" id="marcarTodo" name="marcarTodo" />
                            <label style="cursor: pointer; color: gray;" id="all" for="marcarTodo">Seleccionar todos</label>


                            <button style="cursor: pointer;" name="enviar" id="enviar" onclick="alert('Enviar');"><font>Enviar</font></button>

                            <button style="cursor: pointer;" name="enviar" id="enviar" onclick="obtenerNombre()"><font>Pagar</font></button>

                            <br><font><p id="demo"></p></font>
                        
                        </font>
                    </div>
                    <br><br>
                    <thead>
                        <tr>
                            <?php
                            for ($i = 0; $i < count($cabeceras); $i++) {
                                echo "<th width=\"auto\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                            }
                            ?>                        
                        </tr>
                    </thead>
                    <tbody>
                    <div id="Saldo">
                    </div>          
                        <?php
                        //FER
                        $fecha_emision = date("Y-m-d");
                        //FER
                        $pago = "";
                        $xpagar="";
                        $com_multi= "";
                        $com_indi= "";
                        while ($rs = mysql_fetch_array($query)) {
                            
                            $pagado_f= $reporte->getTabla_kike(true,$rs['IdFactura']);
                            while ($lol = mysql_fetch_array($pagado_f)) {
                                $pago = $lol['t_pagos'];
                                $xpagar = $lol['porPagar'];
                                $com_multi= $lol['pagosMulti'];
                                $com_indi= $lol['pagosIndi'];
                            }

                            $total += (float)$rs['TotalSinFormato'];
                            $pagado += (float)$pago;
                            $color = "";
                            //FER
                            //FER
                            if($cxc && $rs['color'] == "red"){
                                $color = "style='background-color: #A63F40; color: white;'";
                            }
                            echo "<tr>";
                            echo "<td align='center' scope='row' $color><input name='check_".$num_facturas."' class='check_".$num_facturas."' type='checkbox' value='".$rs['IdFactura']."'>" . $rs['Fol_1'] . "</td>";
                            echo "<td align='center' scope='row' $color>" . $rs['FechaFacturacion'] . "</td>";
                            if(!$cxc){
                                echo "<td align='center' scope='row' $color>" . $rs['ClaveCliente'] . "</td>";
                            }else{
                                echo "<td align='center' scope='row' $color>" . $rs['ejecutivo'] . "</td>";
                            }
                            echo "<td align='center' scope='row' $color>" . $rs['NombreReceptor'] . "</td>";
                            echo "<td align='center' scope='row' $color>" . $rs['RFCEmisor'] . "</td>";
                            echo "<td align='center' scope='row' $color>" . $rs['NoContrato'] . "</td>";
                            
                            if(!$cxc){
                                echo "<td align='center' scope='row' $color>$" . $rs['subtotal'] . "</td>";
                                echo "<td align='center' scope='row' $color>$" . $rs['importe'] . "</td>";
                            }else{
                                echo "<td align='center' scope='row' $color>$" . number_format($pago,2) . "</td>";
                                //echo "<td align='center' scope='row' $color>$" . number_format((float)$rs['TotalSinFormato'] - (float)($rs['pagado']),2) . "</td>";
                                echo "<td align='center' scope='row' $color>$" . number_format($xpagar,2) . "</td>";
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
                                if($permisos_grid->getAlta() && $copiar){
                                    echo "<a onclick='generarFacturaLectura(" . $rs['IdFactura'] . ")'><img src='resources/images/cfdi.jpg' title='Crear pre-factura' style='width: 32px; height: 32px;'/></a>"; 
                                }
                                echo "</td>";     
                                echo "<td align='center' scope='row' $color>"; 
                                //Si el estado de la prefactura es cancelada encontes se muestra la columna para sustituir la prefactura
                                if($permisos_grid->getAlta() && $rs['EstadoFactura'] == 'C'){
                                echo "<a onclick='generarFacturaLecturaSustituir(" . $rs['IdFactura'] . ")'><img src='resources/images/cfdi.jpg' title='Sustitución de CFDI' style='width: 32px; height: 32px;'/></a>"; 
                                }
                                echo "</td>";            
                                echo "<td align='center' scope='row' $color>";
                                if ($permisos_grid->getAlta() && $rs['EstadoFactura'] != 'P' && $rs['EstadoFactura'] != 'C' && $rs['EstadoFactura'] != "INC" && ($rs['TipoComprobante'] != "NDC" || $rs['Serie'] != "")) {
                                    echo "<a href='principal.php?mnu=facturacion&action=alta_factura_33&id=" . $rs['IdFactura'] . "&param1=egreso' target='_blank'><img src='resources/images/facturar2.png' title='Generar NDC' style='width: 32px; height: 32px;'/></a>";                 
                                }
                            echo "</td>";                    
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
                            
                            }
                            if ($rs['PagadoSiNo'] != 'No') {
                                echo "<td align='center' scope='row' $color>";
                                if(!$cxc){
                                    echo $rs['PagadoSiNo'];
                                }else{
                                    if($permisos_grid->getModificar()){
                                        if($cxc){
                                            echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
                                        }else{
                                            echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&cfdi=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
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
                                
                                if($permisos_grid->getModificar()){                        
                                    if($cxc){
                                        echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
                                    }else{
                                        echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&cfdi=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
                                    }
                                }
                                echo $rs['PagadoSiNo']; 
                                if($permisos_grid->getModificar()){
                                    echo "</a>"; 
                                }
                                
                                echo "</td>";
                            }
                            
                            if(!$cxc){
                                if ($rs['CanceladaSAT'] != "" || $rs['EstadoFactura'] == "PAGADA") {                    
                                    echo "<td align='center' scope='row' $color>";
                                    if($rs['CanceladaSAT'] != ""){
                                        echo $rs['FechaCancelacion'];
                                    }
                                    echo "</td>";
                                } else {
                                    echo "<td align='center' scope='row' $color>"; 
                                    if($permisos_grid->getBaja()){
                                        echo "<a href='#' onclick=\"cancelarfactura('facturacion/CancelarFactura_net.php?folio=" . $rs['IdFactura'] . "','" . $rs['Folio'] . "'); return false;\"><img src='resources/images/Erase.png' title='Cancelar Factura'/></a>"; 
                                    }
                                    echo "</td>";
                                }
                            }else{
                                echo "<td align='center' scope='row' $color>".str_replace('()', '', $com_multi)."||".str_replace('()', '', $com_indi)."</td>";
                            }
                            echo "</tr>";
                            $num_facturas++;
                        }
                        
                        $saldo = $total-$pagado;
                        ?>
                    </tbody>
                </table>
                <input type="hidden" id="numDocumentos" name="numDocumentos" value="<?php echo $num_facturas; ?>"/>
                <div id="Saldo_1"></div>
            </div>

            <div id="tabs-2-Saldos_Vencidos">
                <font size="4" width="33%" align="center" scope="col">
                    <input style="cursor: pointer;" type="checkbox" onclick="marcarTodos2(2);" id="marcarTodo2" name="marcarTodo2" />
                    <label style="cursor: pointer; color: gray;" id="all" for="marcarTodo2">Seleccionar todos</label>
                </font>
                <div id="Saldo1"></div>

                <table id="treportfact1" style="max-width: 100%;">
                    <thead>
                        <tr>
                            <?php
                            for ($i = 0; $i < count($cabeceras); $i++) {
                                echo "<th width=\"1%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                            }
                            ?>                        
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $num_facturas = 0;
                        while ($rs = mysql_fetch_array($query1)) {
                            $clave = $rs['ClaveCliente'];
                            $consulta_pagos = $catalogo->obtenerLista("SELECT DiasCredito AS Dias from c_contrato where ClaveCliente = '".$clave."'");
                            while ($ru = mysql_fetch_array($consulta_pagos)){
                                $Dias1 = $ru['Dias'];
                                //$Limitede -> add(new DateInterval('P10D'));
                            }
                            $fecha_emision = date("Y-m-d");
                            $Limitede = $rs['FechaFacturacion'];
                            $Limitede1 = date('Y-m-d', strtotime("$Limitede + ".$Dias1 ." day"));
                            if($fecha_emision > $Limitede1){
                            $total1 += (float)$rs['TotalSinFormato'];
                            $pagado1 += (float)$rs['pagado'];
                            $color = "";
                            //FER

                            if($fecha_emision > $Limitede1){
                                $saldos_vencidos++;
                            }

                            //FER
                            if($cxc && $rs['color'] == "red"){
                                $color = "style='background-color: #A63F40; color: white;'";
                            }       
                            echo "<tr>";                        
                            echo "<td align='center' scope='row' $color>
                            <input name='check_".$num_facturas."' class='check2".$num_facturas."' type='checkbox' value='".$rs['IdFactura']."'>".$rs['Fol_1'] . "</td>";
                            echo "<td align='center' scope='row' $color>" . $rs['FechaFacturacion'] . "</td>";
                            if(!$cxc){
                                echo "<td align='center' scope='row' $color>" . $rs['ClaveCliente'] . "</td>";
                            }else{
                                echo "<td align='center' scope='row' $color>" . $rs['ejecutivo'] . "</td>";
                            }
                            echo "<td align='center' scope='row' $color>" . $rs['NombreReceptor'] . "</td>";
                            echo "<td align='center' scope='row' $color>" . $rs['RFCEmisor'] . "</td>";
                            echo "<td align='center' scope='row' $color>" . $rs['NoContrato'] . "</td>";
                            /*$cons = $contrato->getRegistroValidacionVencidos($rs['ClaveCliente']);
                            while($rf = mysql_fetch_array($cons)){
                                $ccf = $rf['NoContrato'];
                                if(empty($ccf)){
                                    $ccf = "Algo";
                                }
                                echo "<td align='center' scope='row' $color>" . $ccf . "</td>";
                            }*/
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
                                if($permisos_grid->getAlta() && $copiar){
                                    echo "<a onclick='generarFacturaLectura(" . $rs['IdFactura'] . ")'><img src='resources/images/cfdi.jpg' title='Crear pre-factura' style='width: 32px; height: 32px;'/></a>"; 
                                }
                                echo "</td>";
                                echo "<td align='center' scope='row' $color>"; 
                                //Si el estado de la prefactura es cancelada encontes se muestra la columna para sustituir la prefactura
                                if($permisos_grid->getAlta() && $rs['EstadoFactura'] == 'C'){
                                    echo "<a onclick='generarFacturaLecturaSustituir(" . $rs['IdFactura'] . ")'><img src='resources/images/cfdi.jpg' title='Sustitución de CFDI' style='width: 32px; height: 32px;'/></a>"; 
                                }
                                echo "</td>";                 
                                echo "<td align='center' scope='row' $color>";
                                if ($permisos_grid->getAlta() && $rs['EstadoFactura'] != 'P' && $rs['EstadoFactura'] != 'C' && $rs['EstadoFactura'] != "INC" && ($rs['TipoComprobante'] != "NDC" || $rs['Serie'] != "")) {
                                    echo "<a href='principal.php?mnu=facturacion&action=alta_factura_33&id=" . $rs['IdFactura'] . "&param1=egreso' target='_blank'><img src='resources/images/facturar2.png' title='Generar NDC' style='width: 32px; height: 32px;'/></a>";                   
                            }
                            echo "</td>";                    
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
                                            echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
                                        }else{
                                            echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&cfdi=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
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
                                        echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
                                    }else{
                                        echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&cfdi=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
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
                                    echo "<td align='center' scope='row' $color>";
                                    if($rs['CanceladaSAT'] != ""){
                                        echo $rs['FechaCancelacion'];
                                    }
                                    echo "</td>";
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
                        $num_facturas ++;
                        }
                        $saldo1 = $total1-$pagado1;
                        ?>
                    </tbody>
                </table>
                <input type="hidden" id="numDocumentos2" name="numDocumentos2" value="<?php echo $num_facturas; ?>"/>
                <div id="Saldo1_1"></div>
            </div>

            <div id="tabs-3-Saldos_Vencidos30">
                <div id="Saldo2"></div>
                <table id="treportfact2" style="max-width: 100%;">
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
                        //FER
                        $fecha_emision = date("Y-m-d");
                        //FER
                        while ($rs = mysql_fetch_array($query2)) {
                            $clave = $rs['ClaveCliente'];
                            $consulta_pagos = $catalogo->obtenerLista("SELECT DiasCredito AS Dias from c_contrato where ClaveCliente = '".$clave."'");
                            while ($ru = mysql_fetch_array($consulta_pagos)){
                                $Dias1 = $ru['Dias'];
                                //$Limitede -> add(new DateInterval('P10D'));
                            }
                            $fecha_emision = date("Y-m-d");
                            $Limitede = $rs['FechaFacturacion'];
                            $Limitede1 = date('Y-m-d H:i:s', strtotime("$Limitede + ".$Dias1 ." day"));
                            $Limite30 = date('Y-m-d H:i:s', strtotime("$limitede + 30 day"));
                            if($Limitede1 > $fecha_emision && $Limitede1 <= $Limite30){

                            $total2 += (float)$rs['TotalSinFormato'];
                            $pagado2 += (float)$rs['pagado'];
                            $color = "";
                            //FER

                            if($Limitede1 > $fecha_emision && $Limitede1 <= $Limite30){
                                $saldos_vencidos_30++;
                            }

                            //FER
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
                            echo "<td align='center' scope='row' $color>" . $rs['NoContrato'] . "</td>";
                            /*$cons = $contrato->getRegistroValidacionVencidos($rs['ClaveCliente']);
                                    while($rf = mysql_fetch_array($cons)){
                                        $ccf = $rf['NoContrato'];
                                        if(empty($ccf)){
                                            $ccf = "Algo";
                                        }
                                        echo "<td align='center' scope='row' $color>" . $ccf . "</td>";
                                    }*/
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
                                if($permisos_grid->getAlta() && $copiar){
                                    echo "<a onclick='generarFacturaLectura(" . $rs['IdFactura'] . ")'><img src='resources/images/cfdi.jpg' title='Crear pre-factura' style='width: 32px; height: 32px;'/></a>"; 
                                }
                                echo "</td>"; 
                                echo "<td align='center' scope='row' $color>"; 
                                //Si el estado de la prefactura es cancelada encontes se muestra la columna para sustituir la prefactura
                                        if($permisos_grid->getAlta() && $rs['EstadoFactura'] == 'C'){
                                            echo "<a onclick='generarFacturaLecturaSustituir(" . $rs['IdFactura'] . ")'><img src='resources/images/cfdi.jpg' title='Sustitución de CFDI' style='width: 32px; height: 32px;'/></a>"; 
                                        }
                                        echo "</td>";                
                                echo "<td align='center' scope='row' $color>";
                                if ($permisos_grid->getAlta() && $rs['EstadoFactura'] != 'P' && $rs['EstadoFactura'] != 'C' && $rs['EstadoFactura'] != "INC" && ($rs['TipoComprobante'] != "NDC" || $rs['Serie'] != "")) {
                                            echo "<a href='principal.php?mnu=facturacion&action=alta_factura_33&id=" . $rs['IdFactura'] . "&param1=egreso' target='_blank'><img src='resources/images/facturar2.png' title='Generar NDC' style='width: 32px; height: 32px;'/></a>";                       
                                    }
                                    echo "</td>";                    
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
                                            echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
                                        }else{
                                            echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&cfdi=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
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
                                        echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
                                    }else{
                                        echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&cfdi=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
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
                                    echo "<td align='center' scope='row' $color>";
                                    if($rs['CanceladaSAT'] != ""){
                                        echo $rs['FechaCancelacion'];
                                    }
                                    echo "</td>";
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
                        }
                        $saldo2 = $total2-$pagado2;
                        ?>
                    </tbody>
                </table>
                <div id="Saldo2_1"></div>
            </div>
    
            <div id="tabs-4-Saldos_Vencidos60">
                <div id="Saldo3"></div>
                <table id="treportfact3" style="max-width: 100%;">
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
                        //FER
                        $fecha_emision = date("Y-m-d");
                        //FER
                        while ($rs = mysql_fetch_array($query3)) {
                            $clave = $rs['ClaveCliente'];
                            $consulta_pagos = $catalogo->obtenerLista("SELECT DiasCredito AS Dias from c_contrato where ClaveCliente = '".$clave."'");
                            while ($ru = mysql_fetch_array($consulta_pagos)){
                                $Dias1 = $ru['Dias'];
                                //$Limitede -> add(new DateInterval('P10D'));
                            }
                            $fecha_emision = date("Y-m-d");
                            $Limitede = $rs['FechaFacturacion'];
                            $Limitede1 = date('Y-m-d H:i:s', strtotime("$Limitede + ".$Dias1 ." day"));
                            $Limite30 = date('Y-m-d H:i:s', strtotime("$limitede + 30 day"));
                            $Limite60 = date('Y-m-d H:i:s', strtotime("$limitede + 60 day"));
                            if($Limitede1 > $Limite30 && $Limitede1 <= $Limite60){

                            $total3 += (float)$rs['TotalSinFormato'];
                            $pagado3 += (float)$rs['pagado'];
                            $color = "";
                            //FER

                            if($Limitede1 > $Limite30 && $Limitede1 <= $Limite60){
                                $saldos_vencidos_60++;
                            }

                            //FER
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
                            echo "<td align='center' scope='row' $color>" . $rs['NoContrato'] . "</td>";
                            /*$cons = $contrato->getRegistroValidacionVencidos($rs['ClaveCliente']);
                                    while($rf = mysql_fetch_array($cons)){
                                        $ccf = $rf['NoContrato'];
                                        if(empty($ccf)){
                                            $ccf = "Algo";
                                        }
                                        echo "<td align='center' scope='row' $color>" . $ccf . "</td>";
                                    }*/
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
                                if($permisos_grid->getAlta() && $copiar){
                                    echo "<a onclick='generarFacturaLectura(" . $rs['IdFactura'] . ")'><img src='resources/images/cfdi.jpg' title='Crear pre-factura' style='width: 32px; height: 32px;'/></a>"; 
                                }
                                echo "</td>";  
                                echo "<td align='center' scope='row' $color>"; 
                                //Si el estado de la prefactura es cancelada encontes se muestra la columna para sustituir la prefactura
                                        if($permisos_grid->getAlta() && $rs['EstadoFactura'] == 'C'){
                                            echo "<a onclick='generarFacturaLecturaSustituir(" . $rs['IdFactura'] . ")'><img src='resources/images/cfdi.jpg' title='Sustitución de CFDI' style='width: 32px; height: 32px;'/></a>"; 
                                        }
                                        echo "</td>";               
                                echo "<td align='center' scope='row' $color>";
                                if ($permisos_grid->getAlta() && $rs['EstadoFactura'] != 'P' && $rs['EstadoFactura'] != 'C' && $rs['EstadoFactura'] != "INC" && ($rs['TipoComprobante'] != "NDC" || $rs['Serie'] != "")) {
                                            echo "<a href='principal.php?mnu=facturacion&action=alta_factura_33&id=" . $rs['IdFactura'] . "&param1=egreso' target='_blank'><img src='resources/images/facturar2.png' title='Generar NDC' style='width: 32px; height: 32px;'/></a>";                      
                                    }
                                    echo "</td>";                    
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
                                            echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
                                        }else{
                                            echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&cfdi=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
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
                                        echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
                                    }else{
                                        echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&cfdi=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
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
                                    echo "<td align='center' scope='row' $color>";
                                    if($rs['CanceladaSAT'] != ""){
                                        echo $rs['FechaCancelacion'];
                                    }
                                    echo "</td>";
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
                        }
                        $saldo3 = $total3-$pagado3;
                        ?>
                    </tbody>
                </table>
                <div id="Saldo3_1"></div>
            </div>


            <div id="tabs-5-Saldos_Vencidos90">
                <div id="Saldo4"></div>
                <table id="treportfact4" style="max-width: 100%;">
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
                        //FER
                        $fecha_emision = date("Y-m-d");
                        //FER
                        while ($rs = mysql_fetch_array($query4)) {
                            $clave = $rs['ClaveCliente'];
                            $consulta_pagos = $catalogo->obtenerLista("SELECT DiasCredito AS Dias from c_contrato where ClaveCliente = '".$clave."'");
                            while ($ru = mysql_fetch_array($consulta_pagos)){
                                $Dias1 = $ru['Dias'];
                                //$Limitede -> add(new DateInterval('P10D'));
                            }
                            $fecha_emision = date("Y-m-d");
                            $Limitede = $rs['FechaFacturacion'];
                            $Limitede1 = date('Y-m-d H:i:s', strtotime("$Limitede + ".$Dias1 ." day"));
                            $Limite60 = date('Y-m-d H:i:s', strtotime("$Limitede + 60 day"));
                            if($Limitede1 > $Limite60){

                            $total4 += (float)$rs['TotalSinFormato'];
                            $pagado4 += (float)$rs['pagado'];
                            $color = "";
                            //FER

                            if($Limitede1 > $Limite60){
                                $saldos_vencidos_90++;
                            }

                            //FER
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
                    echo "<td align='center' scope='row' $color>" . $rs['NoContrato'] . "</td>";
                    /*$cons = $contrato->getRegistroValidacionVencidos($rs['ClaveCliente']);
                            while($rf = mysql_fetch_array($cons)){
                                $ccf = $rf['NoContrato'];
                                if(empty($ccf)){
                                    $ccf = "Algo";
                                }
                                echo "<td align='center' scope='row' $color>" . $ccf . "</td>";
                            }*/
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
                        if($permisos_grid->getAlta() && $copiar){
                            echo "<a onclick='generarFacturaLectura(" . $rs['IdFactura'] . ")'><img src='resources/images/cfdi.jpg' title='Crear pre-factura' style='width: 32px; height: 32px;'/></a>"; 
                        }
                        echo "</td>"; 
                        echo "<td align='center' scope='row' $color>"; 
                        //Si el estado de la prefactura es cancelada encontes se muestra la columna para sustituir la prefactura
                                if($permisos_grid->getAlta() && $rs['EstadoFactura'] == 'C'){
                                    echo "<a onclick='generarFacturaLecturaSustituir(" . $rs['IdFactura'] . ")'><img src='resources/images/cfdi.jpg' title='Sustitución de CFDI' style='width: 32px; height: 32px;'/></a>"; 
                                }
                                echo "</td>";                
                        echo "<td align='center' scope='row' $color>";
                        if ($permisos_grid->getAlta() && $rs['EstadoFactura'] != 'P' && $rs['EstadoFactura'] != 'C' && $rs['EstadoFactura'] != "INC" && ($rs['TipoComprobante'] != "NDC" || $rs['Serie'] != "")) {
                                    echo "<a href='principal.php?mnu=facturacion&action=alta_factura_33&id=" . $rs['IdFactura'] . "&param1=egreso' target='_blank'><img src='resources/images/facturar2.png' title='Generar NDC' style='width: 32px; height: 32px;'/></a>";                       
                            }
                            echo "</td>";                    
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
                                    echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
                                }else{
                                    echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&cfdi=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
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
                                echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
                            }else{
                                echo "<a href='#' onclick='lanzarPopUp(\"Pagos parciales\",\"facturacion/pago_parcial.php?RFC=".$rs['RFCReceptor']."&cxc=true&cfdi=true&factura=".$rs['IdFactura']."\"); return false;'>"; 
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
                            echo "<td align='center' scope='row' $color>";
                            if($rs['CanceladaSAT'] != ""){
                                echo $rs['FechaCancelacion'];
                            }
                            echo "</td>";
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
                    }
                    $saldo4 = $total4-$pagado4;
                    ?>
                    </tbody>
                </table>
                <div id="Saldo4_1"></div>
            </div>
        </div>
    </div>

    <div id="report"/>    
    <?php
    if($cxc){
        echo "<input type='hidden' id='total_facturas' name='total_facturas' value='".  number_format($num_facturas)."'/>";
        echo "<input type='hidden' id='pagado' name='pagado' value='$".  number_format($pagado, 2)."'/>";
        echo "<input type='hidden' id='total' name='total' value='$".  number_format($total, 2)."'/>";
        echo "<input type='hidden' id='saldo' name='saldo' value='$".  number_format($saldo, 2)."'/>";

        echo "<input type='hidden' id='pagado1' name='pagado1' value='$".  number_format($pagado1, 2)."'/>";
        echo "<input type='hidden' id='total1' name='total1' value='$".  number_format($total1, 2)."'/>";
        echo "<input type='hidden' id='saldo1' name='saldo1' value='$".  number_format($saldo1, 2)."'/>";

        echo "<input type='hidden' id='pagado2' name='pagado2' value='$".  number_format($pagado2, 2)."'/>";
        echo "<input type='hidden' id='total2' name='total2' value='$".  number_format($total2, 2)."'/>";
        echo "<input type='hidden' id='saldo2' name='saldo2' value='$".  number_format($saldo2, 2)."'/>";

        echo "<input type='hidden' id='pagado3' name='pagado3' value='$".  number_format($pagado3, 2)."'/>";
        echo "<input type='hidden' id='total3' name='total3' value='$".  number_format($total3, 2)."'/>";
        echo "<input type='hidden' id='saldo3' name='saldo3' value='$".  number_format($saldo3, 2)."'/>";

        echo "<input type='hidden' id='pagado4' name='pagado4' value='$".  number_format($pagado4, 2)."'/>";
        echo "<input type='hidden' id='total4' name='total4' value='$".  number_format($total4, 2)."'/>";
        echo "<input type='hidden' id='saldo4' name='saldo4' value='$".  number_format($saldo4, 2)."'/>";
        //Fernando
        echo "<input type='hidden' id='total_facturas_vencidas' name='total_facturas_vencidas' value='".  number_format($saldos_vencidos)."'/>";
        echo "<input type='hidden' id='total_facturas_vencidas_30' name='total_facturas_vencidas_30' value='".  number_format($saldos_vencidos_30)."'/>";
        echo "<input type='hidden' id='total_facturas_vencidas_60' name='total_facturas_vencidas_60' value='".  number_format($saldos_vencidos_60)."'/>";
        echo "<input type='hidden' id='total_facturas_vencidas_90' name='total_facturas_vencidas_90' value='".  number_format($saldos_vencidos_90)."'/>";
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