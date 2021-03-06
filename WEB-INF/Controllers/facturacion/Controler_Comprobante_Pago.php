<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
ini_set('error_reporting', E_ALL);
error_reporting(-1);

include_once("../../Classes/Factura.class.php");
include_once("../../Classes/Factura2.class.php");
include_once("../../Classes/PagoParcial.class.php");
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
$xml = new XMLPago();
$factura = new Factura_NET();
$aux = new Factura();
$pagoParcial = new PagoParcial();
$RegimenFiscal = new RegimenFiscal();
$saldosAFavor = new SaldosAFavor();
$pdf = new PDFFactura();
$serie = new Serie();
$tipoCadena = new TipoCadena();

$monto = 0;
$extra = "";

$pagoParcial->setId_pago($idPago);
$pagoParcial->getRegistrobyID(true);
$pagoParcial->getRegistrobyPagoID();

if ($serie->getRegistroPagoById($pagoParcial->getIdSerie())) {
    $xml->setSeriePago($serie->getPrefijo());
}else{
    $xml->setSeriePago("");
}

$factura->getRegistroById($pagoParcial->getId_factura());
$monto = $pagoParcial->getImporte();

$timestamp = strtotime(date('H:i')) - (3*60); //El número de minutos que se quiere retrasar se multiplica por los 60 segundos.
$time = date('H:i', $timestamp);

$empresa = new Empresa();
$empresa->setRFC($factura->getRFCEmisor());
$empresa->getRegistroByRFC($empresa->getRFC());

$usoCFDI = new UsoCFDI();
$usoCFDI->getRegistroById(22);//El SAT define que para el complmento de pago, sólo se puede usar P01 por definir
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
$RegimenFiscal->getRegistroById($empresa->getRegimenFiscal());

$ImpSaldoAntTemp=0;         //Variable que se ocupa para guardar el total de las NC en caso de que exista alguna o algunas

if (!$pagoParcial->getNumeroParcialidadPago()) {                //*** obtiene el saldo anterior de los pagos. Nota:Es diferente el metodo al Controler_Comprobante_Pago *JT 03/10/18
    echo "Error: No se pudo obtener el número de parcialidad, contacte con el administrador del sistema";
    return;
}
//************************************************************************************************************                      *JT 10/10/18
$numParcialidad = $pagoParcial->getNumParcialidad();

if($numParcialidad>1){                  
    if (!$pagoParcial->getRegistrosPagoParcial()) {               
    echo "Error: No se pudo obtener los registros del Pago Parcial, contacte con el administrador del sistema";
        return;
}
    else
    {
        $ImpSaldoAnt = number_format($factura->getTotal() - $pagoParcial->getSaldoAnteriorE(), 2, ".", "");
        $numParcialidad = $pagoParcial->getNumParcialidad();
    }

}else{
        $ImpSaldoAnt = number_format($factura->getTotal(), 2, ".", "");
}

//*******************************************************************************************
$ImpPagado = number_format($monto, 2, ".", "");
$ImpSaldoInsoluto = number_format($ImpSaldoAnt - $monto, 2, ".", "");
$xml->setNumParcialidad($numParcialidad);
$xml->setImpSaldoAnt($ImpSaldoAnt);
$xml->setImpPagado($ImpPagado);
$xml->setImpSaldoInsoluto($ImpSaldoInsoluto);
$anadirImpPagado = "";
//**************************************************************************************************************

if(!empty($ImpPagado) && $ImpPagado != "0.00"){
    $anadirImpPagado = "|$ImpPagado";
}

$extra = "|".$xml->getNumParcialidad()."|".$xml->getImpSaldoAnt().$anadirImpPagado."|".$xml->getImpSaldoInsoluto();

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
$xml->setFechaPago(date( "Y-m-d", strtotime($pagoParcial->getFechapago()))."T".date( "H:m:s", strtotime($pagoParcial->getFechapago())));
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
$dprueba=$factura->getIdFactura();
$datosCuentas = $pagoParcial->getCuentasFiscales($factura->getIdFactura());
if($datosCuentas != false){
    $datosParticulaPago = $pagoParcial->getDatosBancarioBeneficiaria($pagoParcial->getCuentaBancaria());
    if($datosParticulaPago != false){
        $datosCuentas['RfcEmisorCtaBen'] = $datosParticulaPago['RfcEmisorCtaBen'];
        $datosCuentas['CtaBeneficiario'] = $datosParticulaPago['CtaBeneficiario'];
    }
}
$lista_claves_cuentas = array("02","03","04","05","28","29","99");
if($datosCuentas != false && in_array($aux->getClaveFormaPago($pagoParcial->getIdFormaPago()), $lista_claves_cuentas)){
    $xml->setRfcEmisorCtaOrd($datosCuentas['RfcEmisorCtaOrd']);
    $xml->setCtaOrdenante($datosCuentas['CtaOrdenante']);
    $xml->setRfcEmisorCtaBen($datosCuentas['RfcEmisorCtaBen']);
    $xml->setCtaBeneficiario($datosCuentas['CtaBeneficiario']);
}
if($pagoParcial->getIdTipoCadena() != "" && $pagoParcial->getCertPago() != "" && $pagoParcial->getCadPago() != "" && $pagoParcial->getSelloPago() != ""){
    $tipoCadena->getRegistrobyID($pagoParcial->getIdTipoCadena());
    $xml->setTipoCadPago( $tipoCadena->getTipoCadena() );
    $xml->setCertPago($pagoParcial->getCertPago());
    $xml->setCadPago($pagoParcial->getCadPago());
    $xml->setSelloPago($pagoParcial->getSelloPago());
}
if($pagoParcial->getRFCBancoEmisorOrd() != null && $pagoParcial->getRFCBancoEmisorOrd() != ""){
    $xml->setRfcEmisorCtaOrd($pagoParcial->getRFCBancoEmisorOrd());
}

if($pagoParcial->getNomBancoEmisorOrd() != null && $pagoParcial->getNomBancoEmisorOrd() != ""){
    $xml->setNomBancoOrdExt($pagoParcial->getNomBancoEmisorOrd());
}

if($pagoParcial->getCtaOrdenante() != null && $pagoParcial->getCtaOrdenante() != ""){
    $xml->setCtaOrdenante($pagoParcial->getCtaOrdenante());
}
$anadirSeriePago = "";
if($xml->getSeriePago() != ""){
    $anadirSeriePago = "|".$xml->getSeriePago();
}

$cadena_original = "||" . $xml->getVersion() . $anadirSeriePago . "|" . $xml->getFolio() . "|" . $xml->getFecha() . "|" . $xml->getNoCertificado();
$cadena_original.="|" . $xml->getSubTotal() . "|" . $xml->getMoneda() . "|" . $xml->getTotal() . "|" . $xml->getTipoDeComprobante() . "|" . $xml->getLugarExpedicion();
$cadena_original.="|" . $xml->getEmisor_rfc() . "|" . $xml->getEmisor_nombre() . "|" . $xml->getRegimen();
$cadena_original.="|" . $xml->getReceptor_rfc() . "|" . $xml->getReceptor_nombre() . "|" . $xml->getUsoCFDI();
$cadena_original.=trim(preg_replace('/\s\s+/', ' ', "|84111506|1|ACT|Pago|0|0"));
$cadena_original.= "|1.0|".$xml->getFechaPago()."|".$xml->getFormaDePagoP()."|".$xml->getMonedaP()."|".number_format($xml->getMonto(),2,".","");
/*Datos opcionales*/

if($xml->getNumOperacion() !== ""){
    $cadena_original .= ("|".$xml->getNumOperacion());
}
if($xml->getRfcEmisorCtaOrd() !== null && $xml->getRfcEmisorCtaOrd() !== ""){
    $cadena_original .= ("|".$xml->getRfcEmisorCtaOrd());
}
if($xml->getNomBancoOrdExt() !== null && $xml->getNomBancoOrdExt() !== ""){
    $cadena_original .= ("|".$xml->getNomBancoOrdExt());
}
if($xml->getCtaOrdenante() !== null && $xml->getCtaOrdenante() !== ""){
    $cadena_original .= ("|".$xml->getCtaOrdenante());
}
if($xml->getRfcEmisorCtaBen() !== null && $xml->getRfcEmisorCtaBen() !== ""){
    $cadena_original .= ("|".$xml->getRfcEmisorCtaBen());
}
if($xml->getCtaBeneficiario() !== null && $xml->getCtaBeneficiario() !== ""){
    $cadena_original .= ("|".$xml->getCtaBeneficiario());
}
if(     $xml->getTipoCadPago() !== null && $xml->getTipoCadPago() !== "" &&
        $xml->getCertPago() !== null && $xml->getCertPago() !== "" &&
        $xml->getCadPago() !== null && $xml->getCadPago() !== "" &&
        $xml->getSelloPago() !== null && $xml->getSelloPago() !== ""
        ){
    $cadena_original .= ("|".$xml->getTipoCadPago()."|".$xml->getCertPago()."|".$xml->getCadPago()."|".$xml->getSelloPago());
}

$anadirSerie = "";
if($factura->getSerie() !== ""){
    $anadirSerie = "|".$factura->getSerie();
}

$cadena_original.= "|".$xml->getIdDocumento().$anadirSerie."|".$xml->getFolioDR()."|".$xml->getMonedaDR()."|".$xml->getMetodoDePagoDR().$extra."||";
$key = '../../../CSD/' . $cfdi->getPem();
$fp = fopen($key, "r");
if (!$fp) {
    error_log("Unable to open keyfile $key");
}
$raw_key_data = fread($fp, 8192);
//$priv_key = fread($fp, 8192);
fclose($fp);
$pkeyid = openssl_get_privatekey($raw_key_data, $cfdi->getCsd_password());
if ($pkeyid === FALSE) {
    echo "Unable to extract private key from raw key data: " . openssl_error_string();
    return;
}
//$pkeyid = openssl_get_privatekey($priv_key, "12345678a");
if (!openssl_sign($cadena_original, $raw_sig, $pkeyid, "RSA-SHA256")) {
    echo "Unable to sign request $cadena_original: " . openssl_error_string();
    $sello = "";
    return;
}
//echo $cadena_original;
//echo $raw_sig;
$sello = base64_encode($raw_sig);
$xml->setSello($sello);
$base = new Base64Convert();
$base->setFile("../../../CSD/" . $cfdi->getCsd());
$xml->setCertificado($base->Convertbase64File());
$pac = new PAC();
$pac->setId_pac($empresa->getId_pac());
$pac->getRegistrobyID();
$soapclient = new nusoap_client($pac->getDireccion_timbrado(), $esWSDL = true);
$usuario = $pac->getUsuario();
$clave = $pac->getPassword();

$folder = "../../../XML/Pagos/" . $empresa->getRFC() . "/";
if (!file_exists($folder)) {
    mkdir($folder, 0777);
}

$folder2 = "../../../PDF/Pagos/" . $empresa->getRFC() . "/";
if(!file_exists($folder2)){
    mkdir("../../../PDF/Pagos/" . $empresa->getRFC() . "/", 0777);
}

$file = fopen("../../../XML/Pagos/" . $empresa->getRFC() . "/XML-" . $empresa->getRFC() . "-" . $pagoParcial->getId_pago() . ".xml", "w");

//echo "../../../XML/Pagos/" . $empresa->getRFC() . "/XML-" . $empresa->getRFC() . "-" . $pagoParcial->getId_pago() . ".xml";

$xmlcreado = $xml->CrearXMLPago();
fwrite($file, $xmlcreado);
fclose($file);
$pagoParcial->setPathXML($empresa->getRFC() . "/XML-" . $empresa->getRFC() . "-" . $pagoParcial->getId_pago() . ".xml");
$timbrado = FALSE;

$cfdix = (utf8_decode($xmlcreado));
$tim = array('cfdi' => $cfdix, 'usuario' => $usuario, 'clave' => $clave);
//Generamos el llamado al servicio de timbrado
$soap_timbrado = $soapclient->call('timbrado', $tim);
//Verificamos que el llamado se pudo hacer correctamente
if ($soap_timbrado == false) {
    echo "Error: No se logro contactar al PAC";
} else {
    if (isset($soap_timbrado['return']['timbre']) && $soap_timbrado['return']['timbre'] != "") {
        $timbrado = TRUE;
        $nombreArchivo = "XML/Pagos/" . $empresa->getRFC() . "/" . $xml->getEmisor_rfc() . "_" . $pagoParcial->getId_pago() . ".xml";
        $nombreArchivoPDF = "PDF/Pagos/" . $empresa->getRFC() . "/" . $xml->getEmisor_rfc() . "_" . $pagoParcial->getId_pago() . ".pdf";
   
        $file = fopen("../../../" . $nombreArchivo, "w");
        
        $xml_timbrado = $soap_timbrado['return']['timbre'];
        fwrite($file, utf8_encode($xml_timbrado));
        fclose($file);
        
        $xmls = new XMLReadSAT();
        $xmls->setFile("../../../" . $nombreArchivo);
        $xmls->LeerXML33();
        
        $pagoParcial->setPathXML($nombreArchivo);
        $pagoParcial->setFolioFiscal($xmls->getUuid());
        $pagoParcial->setFechaTimbrado($xmls->getFecha());
        $pagoParcial->setPathPDF($nombreArchivoPDF);
        if(!$pagoParcial->actualizarInfoTimbrado()){
            echo "Hubo un error al actualizar la información del pago, favor de reportar con el administrador";
            return;
        }
        
        
        $pdf = new PDFFactura('P','mm','Letter');             //Crea objeto PDF
        $pdf->AddPage('P', 'Legal'); //Agrega hoja, Vertical, Carta
        
        $pdf->setConceptos($factura->getConceptosPDF());
        $pdf->setFolioFiscal($pagoParcial->getFolioFiscal());
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
        $pdf->setHoraEmision($pdf->formatoFechaHora(str_replace("T", " ", $pagoParcial->getFechaTimbrado())));
        
        $arrayPago = array();
        array_push($arrayPago, $pagoParcial->getFechapago());
        array_push($arrayPago, $xml->getFormaDePagoP() . " " . $aux->getNombreFormaPago($pagoParcial->getIdFormaPago()));
        array_push($arrayPago, "MXN");
        array_push($arrayPago, "$".number_format($monto,2,".",","));
        array_push($arrayPago, $xml->getRfcEmisorCtaOrd());
        array_push($arrayPago, $xml->getNomBancoOrdExt());
        array_push($arrayPago, $xml->getCtaOrdenante());
        
        $pdf->setArrayPago($arrayPago);
        $arrayDocumentosRelacionados = array();
        $arrayDocumento = array();
        
        array_push($arrayDocumento, $factura->getFolioFiscal());
        array_push($arrayDocumento, $factura->getSerie().$factura->getFolio());
        array_push($arrayDocumento, "MXN");
        array_push($arrayDocumento, $aux->getClaveMetodoPago($factura->getMetodoPago()). " " . $aux->getNombreMetodoPago($factura->getMetodoPago()));
        array_push($arrayDocumento, $numParcialidad);
        array_push($arrayDocumento, "$".number_format($monto,2,".",","));
        array_push($arrayDocumento, "$".number_format($xml->getImpSaldoAnt(),2,".",","));
        array_push($arrayDocumento, "$".number_format($xml->getImpSaldoInsoluto(),2,".",","));
        array_push($arrayDocumentosRelacionados, $arrayDocumento);
        
        $pdf->setArrayDocumentosRelacionados($arrayDocumentosRelacionados);
        
        $tempDir = "../../../PDF/Pagos/" . $empresa->getRFC() . "/";
        $cadena = "https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?re=" . $pdf->getRFC_Emisor() . "&rr=" . $pdf->getRFC_Receptor() . "&fe=" . substr($xmls->getSelloSAT(), 0, 6);
        $cadena .= "&tt=0&id=" . $pdf->getFolioFiscal();
        QRcode::png($cadena, $tempDir . $pagoParcial->getId_pago() . ".png");
        $pdf->setCbb($tempDir . $pagoParcial->getId_pago() . ".png");
        $pdf->setSello_Digital($xmls->getSelloCSD());
        $pdf->setSello_Emisor($xmls->getSelloSAT());
        $pdf->setCadena_SAT($cadena_original);
        
        if(!empty($logoPac)){
            $pdf->setLogoPac($logoPac);
        }
        
        $pdf->CrearPDFPago(true);
        $pdf->Output("../../../" . $nombreArchivoPDF, 'F');
        
        echo "El pago se timbró exitosamente";
    }else{
        echo "<br/>El pago con referencia " . $pagoParcial->getReferencia(). " no se pudo timbrar<br/> Error: " . $soap_timbrado['return']['status'];
    }
}

?>

