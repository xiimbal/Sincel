<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
header('Content-Type: text/html; charset=utf-8');
include_once("../../Classes/ReporteFacturacion2.class.php");
include_once("../../Classes/XML_Facturacion.class.php");
include_once("../../Classes/Factura2.class.php");
include_once("../../Classes/Localidad.class.php");
include_once("../../Classes/Concepto.class.php");
include_once("../../Classes/Empresa.class.php");
include_once("../../Classes/Cliente.class.php");
require_once("../../Classes/nu_soap/nusoap.php");
include_once("../../Classes/PDFFactura.class.php");
include_once("../../Classes/EnLetras.class.php");
include_once("../../Classes/CFDI.class.php");
include_once("../../Classes/Base64Convert.class.php");
include_once("../../Classes/XMLReadSAT.class.php");
include_once("../../Classes/PAC.class.php");
include_once("../../Classes/FacturaExtra.class.php");
include_once("../../Classes/phpqrcode/qrlib.php");
include_once("../../Classes/Mail.class.php");
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/ParametroGlobal.class.php");
include_once("../../Classes/Contrato.class.php");
$parametroGlobal = new ParametroGlobal();
$parametros = "";
if (isset($_POST['form'])) {
    $parametros = "";
    parse_str($_POST['form'], $parametros);
}
$mail = new Mail();

if ($parametroGlobal->getRegistroById("8")) {
    $mail->setFrom($parametroGlobal->getValor());
} else {
    $mail->setFrom("scg-salida@scgenesis.mx");
}

$mail->setBody($_POST['comentario']);
$factura = new Factura();
$factura->setIdFactura($_POST['id']);
$factura->getRegistrobyID();
date_default_timezone_set("America/Mexico_City");
$reporte = new ReporteFacturacion2();
$reporte->setFolio($_POST['id']);
$factura = new Factura();
$factura->setIdFactura($_POST['id']);
$factura->getRegistrobyID();
$factura->setUsuarioUltimaModificacion($_SESSION['user']);
$factura->setPantalla("PHP Generar Factura");
$cero = "";
if ((date("i") - 1) < 10) {
    $cero = "0";
}
$factExtra = new FacturaExtra();
$factExtra->setId_factura($_POST['id']);
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
$subtotal = $concepto->subtotalbyFacturaObs($_POST['id']);
$empresa = new Empresa();
$empresa->setId($factura->getRFCEmisor());
$empresa->getRegistrobyID();
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

$fechaInicio = "";
$fechaFin = "";
$mes_contrato = "";
$total_meses = "";
//Si en el parametro del cliente está encendio mostrar el mes de contrato y además es una factura de arrendamiento
if ($ccliente->getMostarMesContrato() == "1" && $factura->getTipoArrendamiento() == "1") {
    $result = $contrato->getRegistroValidacion($factura->getRFCReceptor());
    while ($rs = mysql_fetch_array($result)) {
        $fechaInicio = $rs['FechaInicio'];
        $fechaFin = $rs['FechaTermino'];
    }

    if (!empty($fechaInicio) && !empty($fechaFin)) {
        $consulta = "SELECT (TIMESTAMPDIFF(MONTH,'$fechaInicio','" . $factura->getFechaFacturacion() . "')+1) AS MesContrato, "
                . "TIMESTAMPDIFF(MONTH,'$fechaInicio','$fechaFin') AS DuracionMeses;";
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            $mes_contrato = $rs['MesContrato'];
            $total_meses = $rs['DuracionMeses'];
        }
    }
}

//*AQUI SE CREA EL PDF
$pdf = new PDFFactura();             //Crea objeto PDF
$pdf->AddPage('P', 'Legal'); //Agrega hoja, Vertical, Carta
$pdf->setTitulo("PREFACTURA");
$pdf->setFolioFiscal("");
$pdf->setFolio($factura->getFolio());
$pdf->setLogo($empresa->getArchivoLogo());
$pdf->setCSD_Emisor("");
$pdf->setCSD_Sat("");
$pdf->setFecha_Cert("");
$pdf->setLugarExpedicion("MÉXICO, DISTRITO FEDERAL");
$pdf->setHoraEmision($factura->getFechaCreacion());
$pdf->setFormaPago($factura->getFormaPago());
$pdf->setMetodoPago($factura->getMetodoPago());
$pdf->setNumeroCtaPago($factura->getNumCtaPago());
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
$pdf->setPeriodo_Facturacion_Emisor($factura->getFechaFacturacionNombre());

$pdf->setNombre_Receptor($ccliente->getNombreRazonSocial());
$pdf->setRFC_Receptor($ccliente->getRFC());
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
$pdf->setSubtotal(number_format($subtotal, 2));
$pdf->setTabla($concepto->getConceptos_array());
$pdf->setIva(number_format($subtotal * .16, 2));
$pdf->setTotal(number_format($subtotal * 1.16, 2));
$letras = new EnLetras();
$pdf->setNum_Letra(strtoupper($letras->ValorEnLetras($subtotal * 1.16, " PESOS ")));
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
$cadena.="&tt=" . $tot . "." . $tod . "&id=" . $pdf->getFolioFiscal();
QRcode::png($cadena, $tempDir . $_POST['id'] . ".png");
$pdf->setCbb("");
$pdf->setLeyenda("");

if(!empty($mes_contrato) && is_numeric($mes_contrato) && !empty($total_meses) && is_numeric($total_meses)){
    $pdf->setMes_contrato("$mes_contrato de $total_meses");
}

$pdf->setIdPreFactura($factura->getIdFactura());
if($factura->getMostrarSerie() == "0"){
    $pdf->CrearPDF_PREF();
}else{
    $pdf->CrearPDF_DetalleSerie(true);
}
//$pdf->CrearPDF_PREF();
//Creacion de las cabeceras que generarán el archivo pdf
$name_pdf = $tempDir . "PREF_" . substr($pdf->getRFC_Emisor(), 0, 3) . "_" . $factura->getFolio() . ".pdf";
$pdf->Output($name_pdf);
$factura->setUsuarioUltimaModificacion($_SESSION['user']);
$factura->setUsuarioEnvio($_SESSION['user']);
$mail->setSubject($_POST['titulo']);
$mail->setAttachPDF($name_pdf);
$mail->setAttachXML("");
$correos = explode(";", $_POST['correos']);

$query4 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 8;");
while ($rs = mysql_fetch_array($query4)) {
    array_push($correos, $rs['correo']);
}

$correos_correctos = array();
foreach ($correos as $value) {//Se envia el correo a las direcciones guardadas en la base de datos.
    if (isset($value) && $value != "") {
        if (isset($value) && $value != NULL && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
            array_push($correos_correctos, $value);
        } else {
            echo "<br/>Error: correo electrónico inválido: <b>$value</b>";
        }
    }
}

if(!empty($correos_correctos)){                    
    $mail->setTo($correos_correctos);
    if ($mail->enviarMailPDF()) {
        $catalogo->obtenerLista("UPDATE c_factura SET FacturaEnviada=1 WHERE IdFactura=" . $_POST['id']);
        echo "<br/>Se envío un correo a los destinatarios: ";
        foreach ($correos_correctos as $value) {
            echo "<br/> $value";
        }
    } else {
        echo "Error: no se envío el correo " . $value . ".";
    }
}

?>