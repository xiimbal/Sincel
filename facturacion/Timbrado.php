<?php

//Importamos las librerias de NU-SOAP
require_once('..\WEB-INF\Classes\nu_soap\nusoap.php');
 
// Creamos una instancia del Cliente Soap  
$soapclient = new nusoap_client('http://www.jonima.com.mx:3014/sefacturapac/TimbradoService?wsdl',$esWSDL=true);
/*$soapclient->use_curl="true";
$soapclient->protocol_version='1.1';
$soapclient->soap_defencouding="UTF-8";
$soapclient-> decode_utf8=false;*/
 
//Parametros para timbrado
//1.cfdi: Comprobante a timbrar en version 3.0, debe ser un String 
//2.usuario: Usuario asignado por SefacturaPac para el emisor del CFDI a timbrar
//3.clave: Clave del usuario.

$cfdi='<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante total="365.00" xmlns:cfdi="http://www.sat.gob.mx/cfd/3" LugarExpedicion="Localidad, Distrito Federal" TipoCambio="1.0" fecha="2013-12-16T10:13:16" sello="OFoaaoDMXHkr5TbBR7rPYFuAH+eRPHQp9zHt3C2U8naSOqHJajrI+2XbF9Kl+kP0Gb1xkbx6w0NHBtvijRgEfc+mfIPj1VBYYUYUc311t9IaiRLdFPrC3AOEM6Cai2jtE+0VOKIGReDY1jJ7LnEIIe23a6SY/EBXo9x587+TABI=" Moneda="M.N." xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital" metodoDePago="No identificado" noCertificado="20001000000100005867" xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd http://www.sat.gob.mx/TimbreFiscalDigital http://www.sat.gob.mx/sitio_internet/TimbreFiscalDigital/TimbreFiscalDigital.xsd" certificado="MIIEdDCCA1ygAwIBAgIUMjAwMDEwMDAwMDAxMDAwMDU4NjcwDQYJKoZIhvcNAQEFBQAwggFvMRgwFgYDVQQDDA9BLkMuIGRlIHBydWViYXMxLzAtBgNVBAoMJlNlcnZpY2lvIGRlIEFkbWluaXN0cmFjacOzbiBUcmlidXRhcmlhMTgwNgYDVQQLDC9BZG1pbmlzdHJhY2nDs24gZGUgU2VndXJpZGFkIGRlIGxhIEluZm9ybWFjacOzbjEpMCcGCSqGSIb3DQEJARYaYXNpc25ldEBwcnVlYmFzLnNhdC5nb2IubXgxJjAkBgNVBAkMHUF2LiBIaWRhbGdvIDc3LCBDb2wuIEd1ZXJyZXJvMQ4wDAYDVQQRDAUwNjMwMDELMAkGA1UEBhMCTVgxGTAXBgNVBAgMEERpc3RyaXRvIEZlZGVyYWwxEjAQBgNVBAcMCUNveW9hY8OhbjEVMBMGA1UELRMMU0FUOTcwNzAxTk4zMTIwMAYJKoZIhvcNAQkCDCNSZXNwb25zYWJsZTogSMOpY3RvciBPcm5lbGFzIEFyY2lnYTAeFw0xMjA3MjcxNzAyMDBaFw0xNjA3MjcxNzAyMDBaMIHbMSkwJwYDVQQDEyBBQ0NFTSBTRVJWSUNJT1MgRU1QUkVTQVJJQUxFUyBTQzEpMCcGA1UEKRMgQUNDRU0gU0VSVklDSU9TIEVNUFJFU0FSSUFMRVMgU0MxKTAnBgNVBAoTIEFDQ0VNIFNFUlZJQ0lPUyBFTVBSRVNBUklBTEVTIFNDMSUwIwYDVQQtExxBQUEwMTAxMDFBQUEgLyBIRUdUNzYxMDAzNFMyMR4wHAYDVQQFExUgLyBIRUdUNzYxMDAzTURGUk5OMDkxETAPBgNVBAsTCFVuaWRhZCAxMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC2TTQSPONBOVxpXv9wLYo8jezBrb34i/tLx8jGdtyy27BcesOav2c1NS/Gdv10u9SkWtwdy34uRAVe7H0a3VMRLHAkvp2qMCHaZc4T8k47Jtb9wrOEh/XFS8LgT4y5OQYo6civfXXdlvxWU/gdM/e6I2lg6FGorP8H4GPAJ/qCNwIDAQABox0wGzAMBgNVHRMBAf8EAjAAMAsGA1UdDwQEAwIGwDANBgkqhkiG9w0BAQUFAAOCAQEATxMecTpMbdhSHo6KVUg4QVF4Op2IBhiMaOrtrXBdJgzGotUFcJgdBCMjtTZXSlq1S4DG1jr8p4NzQlzxsdTxaB8nSKJ4KEMgIT7E62xRUj15jI49qFz7f2uMttZLNThipunsN/NF1XtvESMTDwQFvas/Ugig6qwEfSZc0MDxMpKLEkEePmQwtZD+zXFSMVa6hmOu4M+FzGiRXbj4YJXn9Myjd8xbL/c+9UIcrYoZskxDvMxc6/6M3rNNDY3OFhBK+V/sPMzWWGt8S1yjmtPfXgFs1t65AZ2hcTwTAuHrKwDatJ1ZPfa482ZBROAAX1waz7WwXp0gso7sDCm2/yUVww==" version="3.2" serie="B" folio="3" subTotal="200.00" tipoDeComprobante="egreso" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" formaDePago="Pago en una sola exhibición">
<cfdi:Emisor nombre="DEMOSTRACION" rfc="AAA010101AAA">
<cfdi:DomicilioFiscal codigoPostal="11000" localidad="Localidad" estado="Distrito Federal" pais="MEXICO" municipio="Municipio" colonia="Colonia" calle="Calle" referencia="Referencia"/>
<cfdi:RegimenFiscal Regimen="b"/>
</cfdi:Emisor>
<cfdi:Receptor nombre="Cooperativa La Cruz Azul, S.C.L." rfc="CCA950819TGA">
<cfdi:Domicilio pais="México"/>
</cfdi:Receptor>
<cfdi:Conceptos>
<cfdi:Concepto importe="200" valorUnitario="200" cantidad="1" descripcion="Otro EGRESO" unidad="NA"/>
</cfdi:Conceptos>
<cfdi:Impuestos totalImpuestosTrasladados="32">
<cfdi:Traslados>
<cfdi:Traslado importe="32" tasa="16" impuesto="IVA"/>
</cfdi:Traslados>
</cfdi:Impuestos>
<cfdi:Complemento>
</cfdi:Complemento>
</cfdi:Comprobante>';


$usuario = "SSY0606018A9";
$clave = "SSY0606018A9";

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
