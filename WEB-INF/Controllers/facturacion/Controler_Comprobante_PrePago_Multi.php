<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
ini_set('error_reporting', E_ALL);
error_reporting(-1);

include_once("../../Classes/Factura.class.php");
include_once("../../Classes/Factura2.class.php");
//include_once("../../Classes/PagoParcial.class.php");
include_once("../../Classes/MultiPagosParciales.class.php");
include_once("../../Classes/XMLPago.class.php");
include_once("../../Classes/CFDI.class.php");
include_once("../../Classes/Empresa.class.php");
require_once("../../Classes/nu_soap/nusoap.php");
include_once("../../Classes/Base64Convert.class.php");
include_once("../../Classes/ccliente.class.php");
include_once("../../Classes/PAC.class.php");
include_once("../../Classes/XMLReadSAT.class.php");
include_once("../../Classes/UsoCFDI.class.php");
include_once("../../Classes/PDFFactura.class.php");
include_once("../../Classes/ParametroGlobal.class.php");
include_once("../../Classes/SaldosAFavor.class.php");
include_once("../../Classes/RegimenFiscal.class.php");
include_once("../../Classes/Serie.class.php");
include_once("../../Classes/TipoCadena.class.php");
include_once("../../Classes/phpqrcode/qrlib.php");
if (!isset($_POST['pago'])) {
    echo "No se ha recibido el parámetro del pago";
    return;
}

$idPago = $_POST['pago'];
//echo "ID PAGO ". $idPago."\n";
//Facturas pertenecientes al pago
$facturas = $_POST['factura'];
$pagosFac = ""; //para guardar los pagos de las facturas
$saldoAntFac = ""; //para guardar los saldos anteriores de las facturas
$saldoResFac = ""; //para guardar los restantes a pagar de las facturas
$arr_fac = explode("_", $facturas);
$tam = sizeof($arr_fac);
$tam= $tam - 1;
//fin de facturas
//echo "Facturas ".$arr_fac[0]. "\n";
$xml = new XMLPago();
$factura = new Factura_NET();
$aux = new Factura();
$pagoParcial = new MultiPagosParciales();
$RegimenFiscal = new RegimenFiscal();
$saldosAFavor = new SaldosAFavor();
$pdf = new PDFFactura();
$serie = new Serie();
$tipoCadena = new TipoCadena();

for ($i=0; $i <$tam ; $i++) { 
    $monto = 0;
    $montoXfac = 0;
    $extra = "";

    $pagoParcial->setId_pago($idPago);
    $pagoParcial->getRegistrobyID(true);
    $pagoParcial->getRegistrobyPagoID();
    $pagoParcial->getFacturasbyID($arr_fac[$i],$idPago);


    if ($serie->getRegistroPagoById($pagoParcial->getIdSerie())) {
        $xml->setSeriePago($serie->getPrefijo());
    } else {
        $xml->setSeriePago("");
    }
     
    //$factura->getRegistroById($pagoParcial->getId_factura());
    $factura->getRegistroById($arr_fac[$i]);
    $monto = $pagoParcial->getImporte(); //importe total del pago
    $montoXfac = $pagoParcial->getImportePagadoXFactura();//importe por factura
    //echo "PAGO POR FACTURA". $montoXfac;
    $timestamp = strtotime(date('H:i')) - (3 * 60); //El número de minutos que se quiere retrasar se multiplica por los 60 segundos.
    $time = date('H:i', $timestamp);

    $empresa = new Empresa();
    $empresa->setRFC($factura->getRFCEmisor());
    $empresa->getRegistroByRFC($empresa->getRFC());

    $usoCFDI = new UsoCFDI();
    $usoCFDI->getRegistroById(22); //El SAT define que para el complmento de pago, sólo se puede usar P01 por definir
    //$usoCFDI->getRegistroById($factura->getIdUsoCFDI());

    $ccliente = new ccliente();
    $ccliente->getregistrobyRFC($factura->getRFCReceptor());

    $cfdi = new CFDI();
    $cfdi->setId_Cfdi($empresa->getId_Cfdi());
    $cfdi->getRegistrobyID();

    $base = new Base64Convert();
    $base->setFile("../../../CSD/" . $cfdi->getCsd());

    $saldosAFavor->setIdPagoParcial($idPago);
    $monto = $monto - $saldosAFavor->obtenerPagadoConSaldoAFavorPorPago(); //Vamos a quitar del pago el saldo a favor ya que el resto se quedo como saldo a favor
    $montoXfac = $montoXfac - $saldosAFavor->obtenerPagadoConSaldoAFavorPorPago(); //Vamos a quitar del pago el saldo a favor ya que el resto se quedo como saldo a favor por cada factura.

    $RegimenFiscal->getRegistroById($empresa->getRegimenFiscal());

    if (!$pagoParcial->getNumeroParcialidadPago()) {                //*** obtiene el saldo anterior de los pagos. Nota:Es diferente el metodo al Controler_Comprobante_Pago *JT 03/10/18
        echo "Error: No se pudo obtener el número de parcialidad, contacte con el administrador del sistema";
        return;
    }

    $numParcialidad = $pagoParcial->getNumParcialidad();

    //****************************************************** *JT 04/10/18
    if($numParcialidad>1){                  
        if (!$pagoParcial->getRegistrosPagoParcial()) {                //*** 
        echo "Error: No se pudo obtener los registros del Pago Parcial, contacte con el administrador del sistema";
        return;
        }
        else
        {
            $ImpSaldoAnt = number_format($factura->getTotal() - $pagoParcial->getSaldoAnteriorE(), 2, ".", "");
            //echo "SALDO ANTERIOR ".$ImpSaldoAnt."\n";
            $numParcialidad = $pagoParcial->getNumParcialidad();
            //echo "Numero Parcialidad". $numParcialidad. "\n";
        }
        
    }else{
            $ImpSaldoAnt = number_format($factura->getTotal(), 2, ".", "");
            //echo "Impor Saldo anterior ".$ImpSaldoAnt."\n";
    }

    $ImpPagado = number_format($montoXfac, 2, ".", "");
    //echo "ImportePagado ". $ImpPagado."\n";
    $pagosFac = $pagosFac. $ImpPagado ."_";
    $saldoAntFac = $saldoAntFac. $ImpSaldoAnt. "_";
}
//echo " PAGOS FAC ". $pagosFac;
//echo "Saldo Anterior Fac ". $saldoAntFac;
$arr_pagos = explode("_", $pagosFac);//arreglo de los pagos de facturas
$arr_salAnt = explode("_", $saldoAntFac);//arreglo de los saldos anteriores de facturas

for ($i=0; $i < $tam ; $i++) { 
    //$ImpSaldoInsoluto = number_format($ImpSaldoAnt - $monto, 2, ".", "");
    $ImpSaldoInsoluto = number_format($arr_salAnt[$i] - $arr_pagos[$i], 2, ".", "");
    //echo "IMPORTE SALDO INSOLUTO ". $ImpSaldoInsoluto."\n";
    $saldoResFac = $saldoResFac. $ImpSaldoInsoluto. "_";
}//fin del for
//echo "Saldos restantes ".$saldoResFac;
$arr_saldoRes = explode("_", $saldoResFac); //arreglo de los restantes a pagar de cada factura


$xml->setNumParcialidad($numParcialidad);
$xml->setImpSaldoAnt($saldoAntFac);
$xml->setImpPagado($pagosFac);
$xml->setImpSaldoInsoluto($saldoResFac);
/*
$xml->setNumParcialidad($numParcialidad);
$xml->setImpSaldoAnt($ImpSaldoAnt);
$xml->setImpPagado($ImpPagado);
$xml->setImpSaldoInsoluto($ImpSaldoInsoluto);*/
$anadirImpPagado = "";


if (!empty($ImpPagado) && $ImpPagado != "0.00") {
    $anadirImpPagado = "|$ImpPagado";
}
//$extra = "|" . $xml->getNumParcialidad() . "|" . $xml->getImpSaldoAnt() . $anadirImpPagado . "|" . $xml->getImpSaldoInsoluto();

$folio = $pagoParcial->getFolio();

$xml->setFolio($folio);
$xml->setFecha(date("Y-m-d") . "T" . $time . date(":s"));
$xml->setNoCertificado($cfdi->getNoCertificado());
$xml->setCertificado($base->Convertbase64File());
$xml->setLugarExpedicion($empresa->getCP());
$xml->setEmisor_rfc($empresa->getRFC());
$xml->setEmisor_nombre($empresa->getRazonSocial());
$xml->setEmisor_rfc($empresa->getRFC());
$xml->setRegimen($empresa->getRegimenFiscal());
$xml->setReceptor_rfc($ccliente->getRFCD());
$xml->setReceptor_nombre($ccliente->getRazonSocial());
$xml->setUsoCFDI($usoCFDI->getClaveCFDI());
$xml->setFechaPago(date("Y-m-d", strtotime($pagoParcial->getFechapago())) . "T" . date("H:m:s", strtotime($pagoParcial->getFechapago())));
$xml->setMetodoDePagoDR($aux->getClaveMetodoPago($factura->getMetodoPago()));
$xml->setMonedaP("MXN");
$xml->setMonto($monto);
$xml->setNumOperacion($pagoParcial->getReferencia());
$xml->setRfcEmisorCtaOrd($pagoParcial->getRFCBancoEmisorOrd());
$xml->setNomBancoOrdExt($pagoParcial->getNomBancoEmisorOrd());
$xml->setCtaOrdenante($pagoParcial->getCtaOrdenante());
$xml->setIdDocumento($factura->getFolioFiscal());
$xml->setSerie($factura->getSerie());
$xml->setFolioDR($factura->getFolio());
$xml->setMonedaDR("MXN");
$xml->setFormaDePagoP($aux->getClaveFormaPago($pagoParcial->getIdFormaPago()));


$anadirSeriePago = "";
if ($xml->getSeriePago() != "") {
    $anadirSeriePago = "|" . $xml->getSeriePago();
}


        $nombreArchivoPDF = "PDF/Pagos/" . $empresa->getRFC() . "/" . $xml->getEmisor_rfc() . "Pre_" . $pagoParcial->getId_pago() . ".pdf";
        ;
        $pagoParcial->setPathPDF($nombreArchivoPDF);
        
        //$pagoParcial->actualizarInfoPrepago();
        
        if (!$pagoParcial->actualizarInfoPrepago()) {
            //echo "Hubo un error al actualizar la información del pago, favor de reportar con el administrador";
            //return;
        }
        
        $pdf = new PDFFactura('P', 'mm', 'Letter');             //Crea objeto PDF
        $pdf->AddPage('P', 'Legal'); //Agrega hoja, Vertical, Carta

        //$pdf->setConceptos($factura->getConceptosPDF());
        //$pdf->setFolioFiscal($pagoParcial->getFolioFiscal());
        $pdf->setFolio($xml->getSeriePago() . $pagoParcial->getFolio());
        $pdf->setReferencia($pagoParcial->getReferencia());
        $pdf->setLogo($empresa->getArchivoLogo());
        $pdf->setCSD_Emisor($cfdi->getNoCertificado());
        $pdf->setCSD_Sat($cfdi->getNoSAT());
        $pdf->setFecha_Cert(str_replace("T", " ", $pagoParcial->getFechaTimbrado()));
        $pdf->setNombre_Emisor($empresa->getRazonSocial());
        $pdf->setRFC_Emisor($empresa->getRFC());
        $pdf->setRFC_Receptor($xml->getReceptor_rfc());
        $pdf->setNombre_Receptor($xml->getReceptor_nombre());
        $pdf->setRegimenFiscal_Emisor($RegimenFiscal->getDescripcion());
        $pdf->setCalle_Emisor($empresa->getCalle());
        $pdf->setNo_Ext_Emisor($empresa->getNoExterior());
        $pdf->setNo_int_Emisor($empresa->getNoInterior());
        $pdf->setColonia_Emisor($empresa->getColonia());
        $pdf->setEstado_Emisor($empresa->getEstado());
        $pdf->setDelegacion_Emisor($empresa->getDelegacion());
        $pdf->setPais_Emisor($empresa->getPais());
        $pdf->setCP_Emisor($empresa->getCP());
        $pdf->setTel_Emisor($empresa->getTelefono());

        $pdf->setNombre_Receptor($ccliente->getRazonSocial());
        $pdf->setClave_receptor($ccliente->getClaveCliente());
        $pdf->setLugarExpedicion($empresa->getCP());
        //$pdf->setHoraEmision($pdf->formatoFechaHora(str_replace("T", " ", $pagoParcial->getFechaTimbrado())));
        $pdf->setHoraEmision(str_replace("T", " ", $xml->getFecha()));
        
        $arrayPago = array();
        array_push($arrayPago, $pagoParcial->getFechapago());
        array_push($arrayPago, $pagoParcial->getFormaDePagoP());
        array_push($arrayPago, "MXN");
        array_push($arrayPago, "$" . number_format($monto, 2, ".", ","));
        array_push($arrayPago, $xml->getRfcEmisorCtaOrd());
        array_push($arrayPago, $xml->getNomBancoOrdExt());
        array_push($arrayPago, $xml->getCtaOrdenante());

        $pdf->setArrayPago($arrayPago);
        $arrayDocumentosRelacionados = array();

    for ($i=0; $i < $tam ; $i++) { 
        $fac = new Factura_NET();
        $fac->getRegistroById($arr_fac[$i]);
        
        $arrayDocumento = array();
        //echo "Valor ". $fac->getFolioFiscal();
        array_push($arrayDocumento, $fac->getFolioFiscal());
        array_push($arrayDocumento, $fac->getSerie() . $fac->getFolio());
        array_push($arrayDocumento, "MXN");
        array_push($arrayDocumento, $aux->getClaveMetodoPago($fac->getMetodoPago()) . " " . $aux->getNombreMetodoPago($fac->getMetodoPago()));
        array_push($arrayDocumento, $numParcialidad);
        array_push($arrayDocumento, "$" . number_format($arr_pagos[$i], 2, ".", ","));
        /*array_push($arrayDocumento, "$" . number_format($xml->getImpSaldoAnt(), 2, ".", ","));
        array_push($arrayDocumento, "$" . number_format($xml->getImpSaldoInsoluto(), 2, ".", ","));*/
        array_push($arrayDocumento, "$" . number_format($arr_salAnt[$i], 2, ".", ","));
        array_push($arrayDocumento, "$" . number_format($arr_saldoRes[$i], 2, ".", ","));
        array_push($arrayDocumentosRelacionados, $arrayDocumento);
       
    }  

        $pdf->setArrayDocumentosRelacionados($arrayDocumentosRelacionados);
        

        $tempDir = "../../../PDF/Pagos/" . $empresa->getRFC() . "/";
        
        $pdf->CrearPDFPrePago(true);
        $pdf->Output("../../../".$nombreArchivoPDF, 'F');
//*/
?>

