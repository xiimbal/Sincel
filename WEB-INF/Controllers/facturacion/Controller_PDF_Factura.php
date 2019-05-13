<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/ReporteFacturacion2.class.php");
include_once("../../Classes/XML_Facturacion.class.php");
include_once("../../Classes/Factura2.class.php");
include_once("../../Classes/Localidad.class.php");
include_once("../../Classes/Concepto.class.php");
include_once("../../Classes/Empresa.class.php");
include_once("../../Classes/Cliente.class.php");
include_once("../../Classes/ccliente.class.php");
include_once("../../Classes/AddendaDetalle.class.php");
require_once("../../Classes/nu_soap/nusoap.php");
include_once("../../Classes/PDFFactura.class.php");
include_once("../../Classes/EnLetras.class.php");
include_once("../../Classes/CFDI.class.php");
include_once("../../Classes/Base64Convert.class.php");
include_once("../../Classes/XMLReadSAT.class.php");
include_once("../../Classes/PAC.class.php");
include_once("../../Classes/FacturaExtra.class.php");
include_once("../../Classes/Contrato.class.php");
include_once("../../Classes/phpqrcode/qrlib.php");
include_once("../../Classes/UsoCFDI.class.php");
include_once("../../Classes/RegimenFiscal.class.php");
include_once("../../Classes/Factura.class.php");

if (isset($_GET['id']) && $_GET['id'] != "") {
    date_default_timezone_set("America/Mexico_City");
    $reporte = new ReporteFacturacion2();
    $reporte->setFolio($_GET['id']);
    $factura = new Factura();
    $factura->setIdFactura($_GET['id']);
    $factura->getRegistrobyID();
    $factura->setUsuarioUltimaModificacion($_SESSION['user']);
    $factura->setPantalla("PHP Generar Factura");
    
    $empresa = new Empresa();
    $empresa->setId($factura->getRFCEmisor());
    $empresa->getRegistrobyID();
    
    $usoCFDI = new UsoCFDI();
    $usoCFDI->getRegistroById($factura->getIdUsoCFDI());
    $regimenFiscal = new RegimenFiscal();    
    $regimenFiscal->getRegistroById($empresa->getRegimenFiscal());
    
    $cero = "";
    if ((date("i") - 1) < 10) {
        $cero = "0";
    }
    $factExtra = new FacturaExtra();
    $factExtra->setId_factura($_GET['id']);
    $obs_in = "";
    $comentario = "";
    if ($factExtra->getRegistroByFactura()) {
        $obs_in = $factExtra->getObservaciones_dentro_xml();
        $comentario.=$factExtra->getObservaciones_fuera_xml();
        if ($factExtra->getNumero_orden() != "") {
            $comentario.=" Número de orden:" . $factExtra->getNumero_orden();
        }
        if ($factExtra->getNumero_proveedor() != "") {
            $comentario.=" Número de proveedor:" . $factExtra->getNumero_proveedor();
        }
    }
    $concepto = new Concepto();
    $concepto->setObs_in($obs_in);
    if((int)$factura->getCFDI33() == 1){
        $subtotal = $concepto->subtotalbyFacturaObs33($_GET['id']);
    }else{
        $subtotal = $concepto->subtotalbyFacturaObsPDF($_GET['id']);
    }
    $porcentaje_descuento = $factura->getDescuentos();
    $ccliente = new Cliente();
    //$ccliente->setClaveCliente($factura->getRFCReceptor());
    $localidad = new Localidad();    
    if ($factura->getIdDomicilioFiscal() != "") {
        $localidad->getLocalidadById($factura->getIdDomicilioFiscal());
    } else {
        $localidad->getLocalidadByClaveTipo($factura->getRFCReceptor(), "3");
    }
    $ccliente->getRegistroById($factura->getRFCReceptor());
    
    $contrato = new Contrato();
    $catalogo = new Catalogo();
    $pdf = new PDFFactura();             //Crea objeto PDF
    
    $fechaInicio = "";
    $fechaFin = "";
    $mes_contrato = "";
    $total_meses = "";
    //Si en el parametro del cliente está encendio mostrar el mes de contrato y además es una factura de arrendamiento
    if($ccliente->getMostarMesContrato() == "1" && $factura->getTipoArrendamiento() == "1"){
        $result = $contrato->getRegistroValidacion($factura->getRFCReceptor());    
        while($rs = mysql_fetch_array($result)){
            $fechaInicio = $rs['FechaInicio'];
            $fechaFin = $rs['FechaTermino'];
        }

        if(!empty($fechaInicio) && !empty($fechaFin)){
            $consulta = "SELECT (TIMESTAMPDIFF(MONTH,'$fechaInicio','".$factura->getFechaFacturacion()."')+1) AS MesContrato, "
                    . "TIMESTAMPDIFF(MONTH,'$fechaInicio','$fechaFin') AS DuracionMeses;";
            $result = $catalogo->obtenerLista($consulta);
            while($rs = mysql_fetch_array($result)){
                $mes_contrato = $rs['MesContrato'];
                $total_meses = $rs['DuracionMeses'];
            }
        }
    }
    //FERNANDO
    $cons = $contrato->getRegistroValidacionVencidos($factura->getRFCReceptor(), $_GET['id']);
    while($rf = mysql_fetch_array($cons)){
        $ccf = $rf['NoContrato'];
        if(empty($ccf)){
            $ccf = "";
        }
        else{
            $pdf->setContrato("$ccf");
        }
    }
    
    $addendas = array();
    $cliente_detalle = new ccliente();    
    if($cliente_detalle->getregistrobyID($ccliente->getClaveCliente()) && $cliente_detalle->getIdAddenda()!=NULL && $cliente_detalle->getIdAddenda()!=""){        
        $detalle_addenda = new AddendaDetalle();
        $result = $detalle_addenda->getRegistrosByAdenda($cliente_detalle->getIdAddenda());
        while($rs = mysql_fetch_array($result)){            
            $aux = array();
            array_push($aux, $rs['campo']);
            array_push($aux, $rs['valor']);            
            array_push($addendas, $aux);
        }
        if(!empty($addendas) && $cliente_detalle->getMostrarAddenda() == "1"){            
            $pdf->setAddendas($addendas);
        }
    }
    
    //*AQUI SE CREA EL PDF    
    $pdf->AddPage('P', 'Legal'); //Agrega hoja, Vertical, Carta
    if(isset($_GET['ndc']) && $_GET['ndc']=="1"){
        $pdf->setTitulo("PREVIO NOTA DE CRÉDITO");
        $pdf->setNdc(true);
        $pdf->setTipoComprobante("E Egreso");
        
        if (isset($_GET['IdFacturaNET']) && $_GET['IdFacturaNET'] != "0") {        
            $factura_aux = new Factura_NET();
            if($factura_aux->getRegistroById($_GET['IdFacturaNET'])){                
                $pdf->setTipoRelacion( $factura->getClaveTipoRelacion($factura->getTipoRelacion(),true) );
                $facs_relacion = array($factura_aux->getUUID());                
                $pdf->setUUIDRelacionado($facs_relacion);              
            }        
        }
        
    }else{
        $pdf->setTitulo("PREFACTURA");
        $pdf->setNdc(false);
        $pdf->setTipoComprobante("I Ingreso");
    }
    $pdf->setFolioFiscal("");
    $pdf->setFolio($factura->getFolio());
    $pdf->setLogo($empresa->getArchivoLogo());
    $pdf->setCSD_Emisor("");
    $pdf->setCSD_Sat("");
    $pdf->setFecha_Cert("");
    $pdf->setLugarExpedicion(strtoupper($empresa->getPais()).", ".strtoupper($empresa->getEstado()));
    $pdf->setHoraEmision($factura->getFechaCreacion());
    $pdf->setFormaPago($factura->getClaveFormaPago($factura->getFormaPago())." ".$factura->getNombreFormaPago($factura->getFormaPago()));
    if($factura->getDescripcionMetodoPago() != ""){
        $pdf->setMetodoPago($factura->getClaveMetodoPago($factura->getMetodoPago())."-".$factura->getNombreMetodoPago($factura->getMetodoPago()));
    }else{
        $pdf->setMetodoPago($factura->getClaveMetodoPago($factura->getMetodoPago())." ".$factura->getNombreMetodoPago($factura->getMetodoPago()));
    }
    
    if($factura->getDiasCredito() != ""){
       $pdf->setCondicionesPago($factura->getDiasCredito()." días") ;
    }else{
        $pdf->setCondicionesPago("CONTADO");
    }
                    
    $pdf->setNumeroCtaPago($factura->getNumCtaPago());
    $pdf->setNombre_Emisor($empresa->getRazonSocial());
    $pdf->setRFC_Emisor($empresa->getRFC());    
    $pdf->setRegimenFiscal_Emisor("(".$empresa->getRegimenFiscal().") ".$regimenFiscal->getDescripcion());
    $pdf->setCalle_Emisor($empresa->getCalle());
    $pdf->setNo_Ext_Emisor($empresa->getNoExterior());
    $pdf->setNo_int_Emisor($empresa->getNoInterior());
    $pdf->setColonia_Emisor($empresa->getColonia());
    $pdf->setEstado_Emisor(strtoupper($empresa->getEstado()));
    $pdf->setDelegacion_Emisor($empresa->getDelegacion());
    $pdf->setPais_Emisor($empresa->getPais());
    $pdf->setCP_Emisor($empresa->getCP());
    $pdf->setTel_Emisor($empresa->getTelefono());
    
    $pdf->setClaveCliente($ccliente->getClaveCliente());
    if(!empty($mes_contrato) && is_numeric($mes_contrato) && !empty($total_meses) && is_numeric($total_meses)){
        $pdf->setMes_contrato("$mes_contrato de $total_meses");
    }
    
    $resultPeriodo = $factura->getMultiPeriodos();
    if(mysql_num_rows($resultPeriodo) == 0){
        $pdf->setPeriodo_Facturacion_Emisor($factura->getFechaFacturacionNombre());
    }else{
        $periodos = "";        
        while($rs = mysql_fetch_array($resultPeriodo)){
            $periodos .= (str_replace(" de", "", substr($catalogo->formatoFechaReportes($rs['Periodo']),5)).", ");
        }
        if($periodos != ""){
            $periodos = substr($periodos, 0, strlen($periodos)-2);
        }
        $pdf->setPeriodo_Facturacion_Emisor($periodos);
    }

    $pdf->setNombre_Receptor($ccliente->getNombreRazonSocial());
    $pdf->setRFC_Receptor($ccliente->getRFC());
    $pdf->setUsoCFDI("(".$usoCFDI->getClaveCFDI().") ".$usoCFDI->getDescripcion());
    $pdf->setRegimenFiscal_Receptor($empresa->getRegimenFiscal());
    $pdf->setCalle_Receptor($localidad->getCalle());
    $pdf->setNo_Ext_Receptor($localidad->getNoExterior());
    $pdf->setNo_int_Receptor($localidad->getNoInterior());
    $pdf->setColonia_Receptor($localidad->getColonia());
    $pdf->setEstado_Receptor($localidad->getEstado());
    $pdf->setDelegacion_Receptor($localidad->getDelegacion());
    $pdf->setPais_Receptor("México");
    $pdf->setCP_Receptor($localidad->getCodigoPostal());
    $pdf->setTel_Receptor("");
    $pdf->setLocalidad($localidad->getLocalidad());
    $pdf->setPeriodo_Facturacion_Receptor($factura->getFechaFacturacionNombre());
    
    $tabla = $concepto->getConceptos_array();
    $pdf->setTabla($tabla);   
    $descuento = 0;
    
    if($tabla !== null && !empty($tabla)){        
        foreach ($tabla as $datos_partida) {//Recorremos las partidas para ver si hay descuentos            
            $costo_descuento = 0;
            if(isset($datos_partida[8]) && !empty($datos_partida[8])){
                if(isset($datos_partida[9]) && $datos_partida[9] == "1"){//Si el descuento es por porcentaje
                    $costo_descuento = number_format($datos_partida[6] * ($datos_partida[8] / 100),2,".","");
                }else{
                    $costo_descuento = number_format($datos_partida[8],2,".","");
                }                
                $descuento += $costo_descuento;
            }
            //$Suma_iva += number_format( ($datos_partida[6] ) * 0.16,2,".","");
        }
        $subtotal -= $descuento;
    }
    $Suma_iva = $subtotal * 0.16;
    if(!empty($porcentaje_descuento)){
        $descuento += number_format($subtotal * (float)($porcentaje_descuento / 100),2,".","");        
        $subtotal -= $descuento;
        $Suma_iva = $subtotal * 0.16;
    }
    
    if(!empty($descuento)){        
        $pdf->setDescuento($descuento);
    }
    
    $pdf->setSubtotal(number_format($subtotal, 2));
    $pdf->setIva(number_format($Suma_iva, 2));
    $pdf->setTotal( number_format($subtotal + $Suma_iva, 2));
    $letras = new EnLetras();
    $total_letra = str_replace(",", "", number_format($subtotal * 1.16, 2));
    $total_letra_arr = explode(".", $total_letra);
    $pdf->setNum_Letra(strtoupper($letras->ValorEnLetras($total_letra_arr[0], "")) . " PESOS " . $total_letra_arr[1] . "/100 MN");
    $pdf->setSello_Digital("");
    $pdf->setSello_Emisor("");
    $pdf->setCadena_SAT("");
    $pdf->setComentarios($comentario);
    $tempDir = "../../../PDF/";
    $cadena = "?re=" . $pdf->getRFC_Emisor() . "&rr=" . $pdf->getRFC_Receptor();
    $tot = $pdf->getTotal();
    $toa = explode(".", $tot);
    $toa[0] = str_replace(",", "", $toa[0]);
    $tot = $toa[0];
    $tod = $toa[1];
    for ($i = 0; $i < 10 - strlen($toa[0]); $i++) {
        $tot = "0" . $tot;
    }
    for ($i = 0; $i < 6 - strlen($toa[1]); $i++) {
        $tod = $tod . "0";
    }
    $cadena.="&tt=" . $tot . "." . $tod . "&id=" . $pdf->getFolioFiscal(). "&cheto=". $ccf;
    QRcode::png($cadena, $tempDir . $_GET['id'] . ".png");
    $pdf->setCbb("");
    $pdf->setLeyenda("");
    $pdf->setIdPreFactura($factura->getIdFactura());
    if($factura->getMostrarSerie() == "0"){
        if((int)$factura->getCFDI33() == "1"){
            $pdf->CrearPDF_PREF33();
        }else{
            $pdf->CrearPDF_PREF();
        }
    }else{
        if((int)$factura->getCFDI33() == "1"){
            $pdf->CrearPDF_DetalleSerie33(true);
        }else{
            $pdf->CrearPDF_DetalleSerie(true);
        }
    }
    //Creacion de las cabeceras que generarán el archivo pdf
    header("Content-Disposition: attachment; filename=factura-" . $_GET['id'] . ".pdf");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header("Content-Description: File Transfer");
    $pdf->Output();
} else {
    echo "No se recibió el folio";
}
?>