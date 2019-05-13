<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}
include_once("../../Classes/ReporteFacturacion2.class.php");
include_once("../../Classes/XML_Facturacion.class.php");
include_once("../../Classes/Factura2.class.php");
include_once("../../Classes/FacturaExtra.class.php");
include_once("../../Classes/Localidad.class.php");
include_once("../../Classes/CatalogoFacturacion.class.php");
include_once("../../Classes/Concepto.class.php");
include_once("../../Classes/Empresa.class.php");
include_once("../../Classes/Cliente.class.php");
require_once("../../Classes/nu_soap/nusoap.php");
include_once("../../Classes/PDFFactura.class.php");
include_once("../../Classes/EnLetras.class.php");
include_once("../../Classes/CFDI.class.php");
include_once("../../Classes/Base64Convert.class.php");
include_once("../../Classes/XMLReadSAT.class.php");
include_once("../../Classes/XMLAbraham.class.php");
include_once("../../Classes/XMLReadAbraham2.class.php");
include_once("../../Classes/PAC.class.php");
include_once("../../Classes/phpqrcode/qrlib.php");
include_once("../../Classes/FacturaAbraham.class.php");
include_once("../../Classes/Contrato.class.php");
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/Factura.class.php");

$folios = array();
$folios_string = "";
if(!isset($_GET['folios']) || empty($_GET['folios']) ){
    echo "<br/>No se establecieron ningunos folios";
    return;
}else{
    $folios = explode(",", $_GET['folios']);
    foreach ($folios as $value) {
        $folios_string .= "'$value',";
    }
    if(!empty($folios_string)){
        $folios_string = substr($folios_string, 0, strlen($folios_string)-1);
    }
}

set_time_limit(0);
$catalogofacturacion = new CatalogoFacturacion();
$query = $catalogofacturacion->obtenerLista("SELECT c_factura.*,CONCAT((CASE MONTH(DATE(c_factura.PeriodoFacturacion))
        WHEN '01' THEN 'Enero'
        WHEN '02' THEN 'Febrero'
        WHEN '03' THEN 'Marzo'
        WHEN '04' THEN 'Abril'
        WHEN '05' THEN 'Mayo'
        WHEN '06' THEN 'Junio'
        WHEN '07' THEN 'Julio'
        WHEN '08' THEN 'Agosto'
        WHEN '09' THEN 'Septiembre'
        WHEN '10' THEN 'Octubre'
        WHEN '11' THEN 'Noviembre'
        WHEN '12' THEN 'Diciembre'
        ELSE '' END),' ',YEAR(DATE(c_factura.PeriodoFacturacion))) AS Mes FROM c_factura 
        WHERE !ISNULL(FacturaXML) AND FacturaXML!='' AND Folio IN ($folios_string);");

while ($rsp = mysql_fetch_array($query)) {
    $xml_read = new XMLReadAbraham2();
    
    $consulta =  "SELECT IdFactura FROM `c_factura`  AS f
        LEFT JOIN c_datosfacturacionempresa AS fe ON fe.IdDatosFacturacionEmpresa = f.RFCEmisor
        LEFT JOIN c_folio_prefactura AS fp ON fp.Folio = f.Folio AND f.RFCEmisor = fp.IdEmisor
        WHERE fp.FolioTimbrado = '".$rsp['Folio']."' AND fe.RFC = '".$rsp['RFCEmisor']."';";
    
    $catalogo = new Catalogo();
    $result = $catalogo->obtenerLista($consulta);
    $idPrefactura = "";
    $factura = new Factura();   
    
    while($rs = mysql_fetch_array($result)){
        $idPrefactura = $rs['IdFactura'];
        $factura->setIdFactura($idPrefactura);
        $factura->getRegistrobyID();
    }         
    
    $ndc = false;
    if($rsp['TipoComprobante'] == "egreso"){
        $ndc = true;
    }
    echo "Folio:" . $rsp['Folio'] . "<br/>";
    if(!isset($rsp['cfdiTimbrado']) || $rsp['cfdiTimbrado']==""){
        continue;
    }
    $val = $xml_read->ReadXML(str_replace('<?xml version="1.0" encoding="UTF-8"?>','',str_replace("tfd:", "", str_replace("cfdi:", "", $rsp['cfdiTimbrado'] ))));
    $xml = new XML_Facturacion();
    if ($val && !file_exists("../../../" . str_replace("\\", "/", $rsp['PathPDF']))) {
        echo "No se encontro el archivo " . str_replace("\\", "/", $rsp['PathPDF']) . "<br/>";
        $xml->setFolio($xml_read->getFolio());
        $xml->setFecha($xml_read->getFecha());
        $xml->setFormaDePago($xml_read->getFormaDePago());
        $xml->setSubTotal(((float) ($xml_read->getSubTotal())));
        $xml->setTotal($xml_read->getTotal());
        $xml->setTipoDeComprobante($xml_read->getTipoDeComprobante());
        $xml->setMetodoDePago($xml_read->getMetodoDePago());
        $xml->setRegimen("Régimen General de Ley de Personas Morales");
        $xml->setLugarExpedicion($xml_read->getLugarExpedicion());
        $xml->setEmisor_rfc($xml_read->getEmisor_rfc());
        $xml->setEmisor_Dom_CP($xml_read->getEmisor_codigopostal());
        $xml->setEmisor_Dom_Calle($xml_read->getEmisor_Calle());
        $xml->setEmisor_Dom_Col($xml_read->getEmisor_colonia());
        $xml->setEmisor_Dom_Est($xml_read->getEmisor_estado());
        $xml->setEmisor_Dom_Mun($xml_read->getEmisor_Municipio());
        $xml->setEmisor_Dom_NoExt($xml_read->getEmisor_noExterior());
        $xml->setEmisor_Dom_NoInt($xml_read->getEmisor_noInterior());
        $xml->setEmisor_Dom_Pais($xml_read->getEmisor_pais());
        $xml->setEmisor_nombre($xml_read->getEmisor_nombre());
        $xml->setEmisor_rfc($xml_read->getEmisor_rfc());
        $xml->setExpedido_CP($xml_read->getEmisor_codigopostal_fiscal());
        $xml->setExpedido_Calle($xml_read->getEmisor_Calle_fiscal());
        $xml->setExpedido_Col($xml_read->getEmisor_colonia_fiscal());
        $xml->setExpedido_Estado($xml_read->getEmisor_estado_fiscal());
        $xml->setExpedido_Mun($xml_read->getEmisor_Municipio_fiscal());
        $xml->setExpedido_NoExt($xml_read->getEmisor_noExterior_fiscal());
        $xml->setExpedido_NoInt($xml_read->getEmisor_noInterior_fiscal());
        $xml->setExpedido_Pais($xml_read->getEmisor_pais_fiscal());

        $xml->setReceptor_Dom_CP($xml_read->getReceptor_codigopostal());
        $xml->setReceptor_rfc($xml_read->getReceptor_rfc());
        $xml->setReceptor_nombre($xml_read->getReceptor_nombre());
        $xml->setReceptor_Dom_Calle($xml_read->getReceptor_Calle());
        $xml->setReceptor_Dom_Col($xml_read->getReceptor_colonia());
        $xml->setReceptor_Dom_Est($xml_read->getReceptor_estado());
        $xml->setReceptor_Dom_Mun($xml_read->getReceptor_Municipio());
        $xml->setReceptor_Dom_NoExt($xml_read->getReceptor_noExterior());
        $xml->setReceptor_Dom_NoInt($xml_read->getReceptor_noInterior());
        $xml->setReceptor_Dom_Pais("México");


        $xml->setConceptos($xml_read->getConceptos());
        $xml->setImpuestos_totalImpuestosTrasladados($xml_read->getTotalImpuestosTrasladados());
        $xml->setImpuestos_Trasladado(Array(Array("IVA", "16.00", ((float) ($xml_read->getSubTotal())) * 0.16)));
        $xml->setTipoDeComprobante("ingreso");

        $cadena_original = "||" . $xml->getVersion() . "|" . $xml->getFecha() . "|" . $xml->getTipoDeComprobante() . "|" . $xml->getFormaDePago();
        $cadena_original.="|" . $xml->getSubTotal() . "|" . $xml->getMoneda() . "|" . $xml->getTotal() . "|" . $xml->getMetodoDePago() . "|" . $xml->getLugarExpedicion();
        $cadena_original.="|" . $xml->getEmisor_rfc() . "|" . $xml->getEmisor_nombre() . "|" . $xml->getEmisor_Dom_Calle() . "|" . $xml->getEmisor_Dom_NoExt();
        if ($xml->getEmisor_Dom_NoInt() != "") {
            $cadena_original.="|" . $xml->getEmisor_Dom_NoInt();
        }
        $cadena_original.="|" . $xml->getEmisor_Dom_Col() . "|" . $xml->getEmisor_Dom_Mun() . "|" . $xml->getEmisor_Dom_Est() . "|" . $xml->getEmisor_Dom_Pais() . "|" . $xml->getEmisor_Dom_CP();
        $cadena_original.="|" . $xml->getRegimen();
        $cadena_original.="|" . $xml->getReceptor_rfc() . "|" . $xml->getReceptor_nombre() . "|" . $xml->getReceptor_Dom_Calle() . "|" . $xml->getReceptor_Dom_NoExt();
        if ($xml->getReceptor_Dom_NoInt() != "") {
            $cadena_original.="|" . $xml->getReceptor_Dom_NoInt();
        }
        $cadena_original.="|" . $xml->getReceptor_Dom_Col() . "|" . $xml->getReceptor_Dom_Mun() . "|" . $xml->getReceptor_Dom_Est() . "|" . $xml->getReceptor_Dom_Pais() . "|" . $xml->getReceptor_Dom_CP();

        $array = $xml->getConceptos();
        for ($i = 0; $i < count($array); $i++) {
            $arr = $array[$i];
            $cadena_original.=trim(preg_replace('/\s\s+/', ' ', "|" . $arr[0] . "|" . $arr[1] . "|" . $arr[2] . "|" . $arr[3] . "|" . $arr[4]));
        }
        $array = $xml->getImpuestos_Trasladado();
        for ($i = 0; $i < count($array); $i++) {
            $arr = $array[$i];
            $cadena_original.=trim(preg_replace('/\s\s+/', ' ', "|" . $arr[0] . "|" . $arr[1] . "|" . $arr[2]));
        }
        $cadena_original.="|" . $xml->getImpuestos_totalImpuestosTrasladados();
        $cadena_original.="||";
        $cadena_original = trim(str_replace("| ", "|", str_replace(" |", "|", preg_replace('/\s\s+/', ' ', $cadena_original))));
        $xml->setSello($xml_read->getSello());

        $xmls = new XMLReadSAT();
        $xmls->setString($rsp['cfdiTimbrado']);
        $xmls->LeerXMLString();
        $nombreArchivoPDF = str_replace("\\", "/", $rsp['PathPDF']);
        
        $contrato = new Contrato();
        

        $fechaInicio = "";
        $fechaFin = "";
        $mes_contrato = "";
        $total_meses = "";
        $ccliente = new Cliente();
        //Si en el parametro del cliente está encendio mostrar el mes de contrato y además es una factura de arrendamiento
        if($ccliente->getMostarMesContrato() == "1" && $factura->getTipoArrendamiento() == "1"){
            $result = $contrato->getRegistroValidacion($ccliente->getClaveCliente());    
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
        
        //*AQUI SE CREA EL PDF
        $pdf = new PDFFactura();             //Crea objeto PDF
        $pdf->AddPage('P', 'Legal'); //Agrega hoja, Vertical, Carta
        if (!$ndc) {
            $pdf->setTitulo("FACTURA");
            $pdf->setNdc(false);
        } else {
            $pdf->setTitulo("NOTA DE CRÉDITO");
            $pdf->setNdc(true);
        }
        $pdf->setFolioFiscal($xmls->getUuid());
        $pdf->setFolio($xml->getFolio());
        echo "RFC Emisor: " . $xml_read->getEmisor_rfc() . "<br/>";
        $empresa = new Empresa();
        $empresa->setRFC($xml_read->getEmisor_rfc());
        $empresa->getRegistrobyRFC();
        $pdf->setLogo($empresa->getArchivoLogo());
        $cfdi = new CFDI();
        echo "ID de CFDI es " . $empresa->getId_Cfdi() . "<br/>";
        $cfdi->setId_Cfdi($empresa->getId_Cfdi());
        $cfdi->getRegistrobyID();
        $pdf->setCSD_Emisor($cfdi->getNoCertificado());
        $pdf->setCSD_Sat($cfdi->getNoSAT());
        $pdf->setFecha_Cert(str_replace("T", " ", $xmls->getFecha()));
        $pdf->setLugarExpedicion("MÉXICO, DISTRITO FEDERAL");
        $pdf->setHoraEmision(str_replace("T", " ", $xml->getFecha()));
        $pdf->setFormaPago($xml->getFormaDePago());
        $pdf->setMetodoPago($xml->getMetodoDePago());
        //$pdf->setNumeroCtaPago($xml_read->get());
        $pdf->setNombre_Emisor($empresa->getRazonSocial());
        $pdf->setRFC_Emisor($empresa->getRFC());
        $pdf->setRegimenFiscal_Emisor($empresa->getRegimenFiscal());
        $pdf->setCalle_Emisor($empresa->getCalle());
        $pdf->setNo_Ext_Emisor($empresa->getNoExterior());
        $pdf->setNo_int_Emisor($empresa->getNoInterior());
        $pdf->setColonia_Emisor($empresa->getColonia());
        $pdf->setEstado_Emisor($empresa->getEstado());
        $pdf->setDelegacion_Emisor($empresa->getDelegacion());
        $pdf->setPais_Emisor($empresa->getPais());
        $pdf->setCP_Emisor($empresa->getCP());
        $pdf->setTel_Emisor($empresa->getTelefono());
        $pdf->setPeriodo_Facturacion_Emisor($rsp['Mes']);
        
        $ccliente->setRFC($xml->getReceptor_rfc());
        $ccliente->getRegistroByRFC($xml->getReceptor_rfc());
        $pdf->setNombre_Receptor($ccliente->getNombreRazonSocial());
        $pdf->setRFC_Receptor($ccliente->getRFC());
        $pdf->setRegimenFiscal_Receptor($empresa->getRegimenFiscal());
        $pdf->setCalle_Receptor($xml->getReceptor_Dom_Calle());
        $pdf->setNo_Ext_Receptor($xml->getReceptor_Dom_NoExt());
        $pdf->setNo_int_Receptor($xml->getReceptor_Dom_NoInt());
        $pdf->setColonia_Receptor($xml->getReceptor_Dom_Col());
        $pdf->setEstado_Receptor($xml->getReceptor_Dom_Est());
        $pdf->setDelegacion_Receptor($xml->getReceptor_Dom_Mun());
        $pdf->setPais_Receptor("México");
        $pdf->setCP_Receptor($xml->getReceptor_Dom_CP());
        $pdf->setTel_Receptor("");
        //$pdf->setLocalidad($localidad->getLocalidad());
        $pdf->setPeriodo_Facturacion_Receptor($rsp['Mes']);
        echo "Subtotal es:" . $xml->getSubTotal() . "<br/>";
        $pdf->setSubtotal(number_format(((float) ($xml->getSubTotal())), 2));
        $pdf->setTabla($xml->getConceptos());
        $pdf->setIva(number_format(((float) ($xml->getSubTotal())) * .16, 2));
        $pdf->setTotal(number_format(((float) ($xml->getSubTotal())) * 1.16, 2));
        $letras = new EnLetras();
        $pdf->setNum_Letra(strtoupper($letras->ValorEnLetras(((float) ($xml->getSubTotal())) * 1.16, " PESOS ")));
        $pdf->setSello_Digital($xmls->getSelloCSD());
        $pdf->setSello_Emisor($xmls->getSelloSAT());
        $pdf->setCadena_SAT($cadena_original);
        
        if(!empty($mes_contrato) && is_numeric($mes_contrato) && !empty($total_meses) && is_numeric($total_meses)){
            $pdf->setMes_contrato("$mes_contrato de $total_meses");
        }
        
        //$pdf->setComentarios($comentario);
        $tempDir = "../../../";
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
        $cadena.="&tt=" . $tot . "." . $tod . "&id=" . $pdf->getFolioFiscal();
        QRcode::png($cadena, $tempDir . "temporal" . ".png");
        $pdf->setCbb($tempDir . "temporal" . ".png");
        $pdf->setLeyenda("Esta factura deberá ser pagada en una sola exhibción.Esta factura no libera al cliente de adeudos anteriores o consumos no incluidos en la misma los titulos de crédito dados por el cliente, en los casos autorizados, serán recibidos bajo condición 'salvo buen cobro' con base en el Articulo de la Ley General de Titulos y Operaciones de Crédito, de no verificarse el pago del importe que ampare este documento al vencimiento, el cliente se obliga a pagar el 10% mensual de intereses moratorios, sobre saldos insolutos.");
        //$pdf->CrearPDF();
        if($factura->getMostrarSerie() == "1"){
            echo "<br/>Nuevo formato";
            $pdf->setIdPrefactura($idPrefactura);
            $pdf->CrearPDF_DetalleSerie(false);
        }else{
            echo "<br/>Formato sin detalle";
            $pdf->setIdPrefactura($idPrefactura);                    
            $pdf->CrearPDF();
        }
        $pdf->Output("../../../" . $nombreArchivoPDF, 'F');
        echo "Se creo el archivo " . "../../../" . $nombreArchivoPDF . "<br/>";
    } else {
        echo "Se encontro el archivo " . str_replace("\\", "/", $rsp['PathPDF']) . "<br/>";
    }
}
?>
