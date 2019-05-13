<?php
 session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
//Importamos las librerias de NU-SOAP
require_once('../../Classes/nu_soap/nusoap.php');
 
// Creamos una instancia del Cliente Soap  
$soapclient = new nusoap_client('http://www.jonima.com.mx:3014/sefacturapac/TimbradoService?wsdl ',$esWSDL=true);
/*$soapclient->use_curl="true";
$soapclient->protocol_version='1.1';
$soapclient->soap_defencouding="UTF-8";
$soapclient-> decode_utf8=false;*/
 
//Parametros para timbrado
//1.cfdi: Comprobante a timbrar en version 3.0, debe ser un String 
//2.usuario: Usuario asignado por SefacturaPac para el emisor del CFDI a timbrar
//3.clave: Clave del usuario.

$cfdi='<?xml version="1.0" encoding="UTF-8"?><cfdi:Comprobante total="116" xmlns:cfdi="http://www.sat.gob.mx/cfd/3" TipoCambio="1.0000" Moneda="PESOS" metodoDePago="Efectivo" xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv3.xsd" version="3.0" serie="A" folio="1" subTotal="100" tipoDeComprobante="ingreso" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" formaDePago="PAGO EN UNA SOLA EXHIBICION" fecha="2011-06-12T23:05:35" sello="OFoaaoDMXHkr5TbBR7rPYFuAH+eRPHQp9zHt3C2U8naSOqHJajrI+2XbF9Kl+kP0Gb1xkbx6w0NHBtvijRgEfc+mfIPj1VBYYUYUc311t9IaiRLdFPrC3AOEM6Cai2jtE+0VOKIGReDY1jJ7LnEIIe23a6SY/EBXo9x587+TABI=" noCertificado="20001000000100001695" certificado="MIIFEjCCA/qgAwIBAgIUMjAwMDEwMDAwMDAxMDAwMDE2OTUwDQYJKoZIhvcNAQEFBQAwggFvMRgwFgYDVQQDDA9BLkMuIGRlIHBydWViYXMxLzAtBgNVBAoMJlNlcnZpY2lvIGRlIEFkbWluaXN0cmFjacOzbiBUcmlidXRhcmlhMTgwNgYDVQQLDC9BZG1pbmlzdHJhY2nDs24gZGUgU2VndXJpZGFkIGRlIGxhIEluZm9ybWFjacOzbjEpMCcGCSqGSIb3DQEJARYaYXNpc25ldEBwcnVlYmFzLnNhdC5nb2IubXgxJjAkBgNVBAkMHUF2LiBIaWRhbGdvIDc3LCBDb2wuIEd1ZXJyZXJvMQ4wDAYDVQQRDAUwNjMwMDELMAkGA1UEBhMCTVgxGTAXBgNVBAgMEERpc3RyaXRvIEZlZGVyYWwxEjAQBgNVBAcMCUNveW9hY8OhbjEVMBMGA1UELRMMU0FUOTcwNzAxTk4zMTIwMAYJKoZIhvcNAQkCDCNSZXNwb25zYWJsZTogSMOpY3RvciBPcm5lbGFzIEFyY2lnYTAeFw0xMDExMTkxODQ0NTlaFw0xMjExMTgxODQ0NTlaMIGrMR4wHAYDVQQDExVNQVJUSU4gQVJCQUlaQSBRVUlST1oxHjAcBgNVBCkTFU1BUlRJTiBBUkJBSVpBIFFVSVJPWjEeMBwGA1UEChMVTUFSVElOIEFSQkFJWkEgUVVJUk9aMRYwFAYDVQQtEw1BQVFNNjEwOTE3UUpBMRswGQYDVQQFExJBQVFNNjEwOTE3TURGTlNSMDgxFDASBgNVBAsTC1N1Y3Vyc2FsQVZMMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCcphXGAbrbUnaumkSTsbGrFIfkaajOpvP1RFcVcbpWe7JBNXAwShKIH79QGLYEc9ATBmlxtjAma0B4ZRBTjmQ4vQrp9LwT3bCNX+9J9lUOHGsCysyau3VxGNoCbhBxMYQP835LjAcy1d4AScOjGx8hxTZ6AUXtMmyEe+0NNQsJnQIDAQABo4HqMIHnMAwGA1UdEwEB/wQCMAAwCwYDVR0PBAQDAgbAMB0GA1UdDgQWBBQu1+baB5RWdTu3kJCg4IjlF32tHDAuBgNVHR8EJzAlMCOgIaAfhh1odHRwOi8vcGtpLnNhdC5nb2IubXgvc2F0LmNybDAzBggrBgEFBQcBAQQnMCUwIwYIKwYBBQUHMAGGF2h0dHA6Ly9vY3NwLnNhdC5nb2IubXgvMB8GA1UdIwQYMBaAFOtZfQQimlONnnEaoFiWKfU54KDFMBAGA1UdIAQJMAcwBQYDKgMEMBMGA1UdJQQMMAoGCCsGAQUFBwMCMA0GCSqGSIb3DQEBBQUAA4IBAQCrGSNzknMaltAwGL31KygIpD333C7HGJ2CbD6y1rTS6VdJQzHJb8fXcuzjcsZkkICHfyAu8BD6+kr7OhKPs1s+WjeUOUXTp3sdWepbR+b+QK4PK9ropyURnwVK1q28UMo4EalILJXDV/sdL6MW2SKk1y+BLi+o+HNJe49h72gsZaUyqpZ4RYtBZqo5495S3yCSwYaEN3gV5XCLuwddFKqfTrzIB3eytr07i195saBuVx2ihorO77+GiLb9AwWxuJKof6toM5yTLdpyMhiNBgOR4hJ5S5s/K+sSeYrjsUPlI4JktVrqpAywvmIZ+ix1w4FvLyFkueam57FqqRLp3vzv"><cfdi:Emisor rfc="AAQM610917QJA" nombre="EMPRESA DE PRUEBA"><cfdi:DomicilioFiscal codigoPostal="11000" localidad="Ciudad de México" noExterior="735" estado="DF" pais="México" noInterior="604" municipio="Miguel Hidalgo" colonia="Lomas de Chapultepec" calle="Palmas"/></cfdi:Emisor><cfdi:Receptor nombre="UNILA" rfc="APR0412108C5"><cfdi:Domicilio codigoPostal="09810" localidad="MEXICO" noExterior="100" estado="D.F." pais="MEXICO" noInterior="231" colonia="SANTA FE" calle="REFORMA"/></cfdi:Receptor><cfdi:Conceptos><cfdi:Concepto importe="100" valorUnitario="100" cantidad="1.0000" descripcion="asdfasf"/></cfdi:Conceptos><cfdi:Impuestos totalImpuestosTrasladados="16.0"><cfdi:Traslados><cfdi:Traslado importe="16" tasa="16.00" impuesto="IVA"/></cfdi:Traslados></cfdi:Impuestos></cfdi:Comprobante>';

$usuario = "PRUEBA1";
$clave = "12345678";

//Decodificamos el CFDI a UTF8
$cfdi= (utf8_decode($cfdi));

//Generamos el arreglo con los parametros para timbrado
$tim = array('cfdi'=>$cfdi,'usuario'=>$usuario,'clave'=>$clave);

//Generamos el llamado al servicio de timbrado
$soap_timbrado=$soapclient->call('timbrado',$tim);

//Verificamos que el llamado se pudo hacer correctamente
if($soap_timbrado==false){
	print "sin resultado";
}else{
	print "logre comunicacion\n";
	var_dump ($soap_timbrado);	
}
?>
