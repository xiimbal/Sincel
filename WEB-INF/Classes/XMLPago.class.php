<?php

class XMLPago {
    
    private $xmlns_xsi = "http://www.w3.org/2001/XMLSchema-instance";
    private $xsi_schemaLocation = "http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd http://www.sat.gob.mx/Pagos http://www.sat.gob.mx/sitio_internet/cfd/Pagos/Pagos10.xsd";
    private $xmlns_xsd = "http://www.w3.org/2001/XMLSchema";
    private $xmlns_pago10 = "http://www.sat.gob.mx/Pagos";
    private $version = "3.3";
    private $SeriePago = "";
    private $folio = "";
    private $fecha = "";
    private $sello = "";
    private $noCertificado = "";
    private $certificado = "";
    private $subTotal = "0";
    private $total = "0";
    private $moneda = "XXX";
    private $tipoDeComprobante = "P";
    private $LugarExpedicion = "";
    private $IvaCero = false;
    private $xmlns = "http://www.sat.gob.mx/cfd/3";
    private $Emisor_rfc = "";
    private $Emisor_nombre = "";
    private $Regimen = "";
    private $Receptor_rfc = "";
    private $Receptor_nombre = "";
    private $UsoCFDI = "";
    private $FechaPago = "";
    private $FormaDePagoP = "";
    private $MonedaP = "";
    private $TipoCambioP = "";
    private $Monto = "";
    private $NumOperacion;
    private $RfcEmisorCtaOrd;
    private $NomBancoOrdExt;
    private $CtaOrdenante;
    private $RfcEmisorCtaBen;
    private $CtaBeneficiario;
    private $TipoCadPago;
    private $CertPago;
    private $CadPago;
    private $SelloPago;
    private $IdDocumento = "";
    private $Serie;
    private $FolioDR = "";
    private $MonedaDR = "";
    private $TipoCambioDR = "";
    private $MetodoDePagoDR = "";
    private $NumParcialidad;
    private $ImpSaldoAnt;
    private $ImpPagado;
    private $ImpSaldoInsoluto;
    private $Conceptos; //Arrray bidimendional  Array(Array(Cantidad,Unidad,Descripcion,valorUnitario,importe),Array(Cantidad,Unidad,Descripcion,valorUnitario,importe))
    private $Impuestos_totalImpuestosTrasladados = "";
    private $totalImpuestosRetenidos="";
    private $Impuestos_Trasladado; //Array bidimensional Array(Array(Impuesto,Tasa,Importe),Array(Impuesto,Tasa,Importe))
    private $Impuestos_Retenidos;
    private $XML;
    
    public function CrearXMLPago(){        
        $this->XML = new DomDocument('1.0', 'UTF-8');
        $comprobante = $this->XML->createElement("cfdi:Comprobante");
        $comprobante->setAttribute("xmlns:xsi", $this->xmlns_xsi);
        $comprobante->setAttribute("xsi:schemaLocation", $this->xsi_schemaLocation);
        $comprobante->setAttribute("Version", $this->version);
        if(isset($this->SeriePago) && !empty($this->SeriePago)){
            $comprobante->setAttribute("Serie", $this->SeriePago);
        }
        $comprobante->setAttribute("Folio", $this->folio);
        $comprobante->setAttribute("Fecha", $this->fecha);
        $comprobante->setAttribute("Sello", $this->sello);
        $comprobante->setAttribute("NoCertificado", $this->noCertificado);
        $comprobante->setAttribute("Certificado", $this->certificado);
        $comprobante->setAttribute("SubTotal", $this->subTotal);
        $comprobante->setAttribute("Moneda", $this->moneda);
        $comprobante->setAttribute("Total", $this->total);
        $comprobante->setAttribute("TipoDeComprobante", $this->tipoDeComprobante);
        $comprobante->setAttribute("LugarExpedicion", $this->LugarExpedicion);
        $comprobante->setAttribute("xmlns:cfdi", $this->xmlns);
        $comprobante->setAttribute("xmlns:pago10", $this->xmlns_pago10);
        $Emisor = $this->XML->createElement("cfdi:Emisor");
        $Emisor->setAttribute("Rfc", $this->Emisor_rfc);
        $Emisor->setAttribute("Nombre", $this->Emisor_nombre);
        $Emisor->setAttribute("RegimenFiscal", $this->Regimen);
        $Receptor = $this->XML->createElement("cfdi:Receptor");
        $Receptor->setAttribute("Rfc", $this->Receptor_rfc);
        $Receptor->setAttribute("Nombre", $this->Receptor_nombre);
        $Receptor->setAttribute("UsoCFDI", $this->UsoCFDI);
        
        //Solo se registra un solo concepto en este elemento y siempre es el mismo por lo que es código duro.
        $Conceptos = $this->XML->createElement("cfdi:Conceptos");
        $Conceptos_aux = $this->XML->createElement("cfdi:Concepto");
        $Conceptos_aux->setAttribute("ClaveProdServ", "84111506");
        $Conceptos_aux->setAttribute("Cantidad", "1");
        $Conceptos_aux->setAttribute("ClaveUnidad", "ACT");
        $Conceptos_aux->setAttribute("Descripcion", "Pago");
        $Conceptos_aux->setAttribute("ValorUnitario", "0");
        $Conceptos_aux->setAttribute("Importe", "0");
        $Conceptos->appendChild($Conceptos_aux);
        
        $Complemento = $this->XML->createElement("cfdi:Complemento");
        $Pagos = $this->XML->createElement("pago10:Pagos");
        $Pagos->setAttribute("Version", "1.0");
        $Pago = $this->XML->createElement("pago10:Pago");
        $Pago->setAttribute("FechaPago", $this->FechaPago);
        $Pago->setAttribute("FormaDePagoP", $this->FormaDePagoP);
        $Pago->setAttribute("MonedaP", $this->MonedaP);
        $Pago->setAttribute("Monto", number_format($this->Monto,2,".",""));
        if(isset($this->NumOperacion) && !empty($this->NumOperacion)){
            $Pago->setAttribute("NumOperacion", $this->NumOperacion);
        }
        /*Cuentas bancarias*/
        if(isset($this->RfcEmisorCtaOrd) && !empty($this->RfcEmisorCtaOrd)){
            $Pago->setAttribute("RfcEmisorCtaOrd", $this->RfcEmisorCtaOrd);
        }
        if(isset($this->NomBancoOrdExt) && !empty($this->NomBancoOrdExt)){
            $Pago->setAttribute("NomBancoOrdExt", $this->NomBancoOrdExt);
        }
        if(isset($this->CtaOrdenante) && !empty($this->CtaOrdenante)){
            $Pago->setAttribute("CtaOrdenante", $this->CtaOrdenante);
        }
        if(isset($this->RfcEmisorCtaBen) && !empty($this->RfcEmisorCtaBen)){
            $Pago->setAttribute("RfcEmisorCtaBen", $this->RfcEmisorCtaBen);
        }
        if(isset($this->CtaBeneficiario) && !empty($this->CtaBeneficiario)){
            $Pago->setAttribute("CtaBeneficiario", $this->CtaBeneficiario);
        }
        if(isset($this->TipoCadPago) && !empty($this->TipoCadPago)){
            $Pago->setAttribute("TipoCadPago", $this->TipoCadPago);
        }
        if(isset($this->CertPago) && !empty($this->CertPago)){
            $Pago->setAttribute("CertPago", $this->CertPago);
        }
        if(isset($this->CadPago) && !empty($this->CadPago)){
            $Pago->setAttribute("CadPago", $this->CadPago);
        }
        if(isset($this->SelloPago) && !empty($this->SelloPago)){
            $Pago->setAttribute("SelloPago", $this->SelloPago);
        }
        
        $DoctoRelacionado = $this->XML->createElement("pago10:DoctoRelacionado");
        $DoctoRelacionado->setAttribute("IdDocumento", $this->IdDocumento);
        if(isset($this->Serie) && !empty($this->Serie)){
            $DoctoRelacionado->setAttribute("Serie", $this->Serie);
        }
        $DoctoRelacionado->setAttribute("Folio", $this->FolioDR);
        $DoctoRelacionado->setAttribute("MonedaDR", $this->MonedaDR);
        $DoctoRelacionado->setAttribute("MetodoDePagoDR", $this->MetodoDePagoDR);
        if(isset($this->NumParcialidad) && !empty($this->NumParcialidad)){
            $DoctoRelacionado->setAttribute("NumParcialidad", $this->NumParcialidad);
            $DoctoRelacionado->setAttribute("ImpSaldoAnt", $this->ImpSaldoAnt);
            if(isset($this->ImpPagado) && !empty($this->ImpPagado) && $this->ImpPagado != "0.00"){
                $DoctoRelacionado->setAttribute("ImpPagado", $this->ImpPagado);
            }
            $DoctoRelacionado->setAttribute("ImpSaldoInsoluto", $this->ImpSaldoInsoluto);
        }
        $Pago->appendChild($DoctoRelacionado);
        $Pagos->appendChild($Pago);
        $Complemento->appendChild($Pagos);
        
        $comprobante->appendChild($Emisor);
        $comprobante->appendChild($Receptor);
        $comprobante->appendChild($Conceptos);
        $comprobante->appendChild($Complemento);
        
        $this->XML->appendChild($comprobante);
        return $this->XML->saveXML();
    }
    
    function getXmlns_xsi() {
        return $this->xmlns_xsi;
    }

    function getXsi_schemaLocation() {
        return $this->xsi_schemaLocation;
    }

    function getXmlns_xsd() {
        return $this->xmlns_xsd;
    }

    function getXmlns_pago10() {
        return $this->xmlns_pago10;
    }

    function getVersion() {
        return $this->version;
    }

    function getFolio() {
        return $this->folio;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getSello() {
        return $this->sello;
    }

    function getNoCertificado() {
        return $this->noCertificado;
    }

    function getCertificado() {
        return $this->certificado;
    }

    function getSubTotal() {
        return $this->subTotal;
    }

    function getTotal() {
        return $this->total;
    }

    function getMoneda() {
        return $this->moneda;
    }

    function getTipoDeComprobante() {
        return $this->tipoDeComprobante;
    }

    function getLugarExpedicion() {
        return $this->LugarExpedicion;
    }

    function getIvaCero() {
        return $this->IvaCero;
    }

    function getXmlns() {
        return $this->xmlns;
    }

    function getEmisor_rfc() {
        return $this->Emisor_rfc;
    }

    function getEmisor_nombre() {
        return $this->Emisor_nombre;
    }

    function getRegimen() {
        return $this->Regimen;
    }

    function getReceptor_rfc() {
        return $this->Receptor_rfc;
    }

    function getReceptor_nombre() {
        return $this->Receptor_nombre;
    }

    function getUsoCFDI() {
        return $this->UsoCFDI;
    }

    function getFechaPago() {
        return $this->FechaPago;
    }

    function getFormaDePagoP() {
        return $this->FormaDePagoP;
    }

    function getMonedaP() {
        return $this->MonedaP;
    }

    function getTipoCambioP() {
        return $this->TipoCambioP;
    }

    function getMonto() {
        return $this->Monto;
    }

    function getIdDocumento() {
        return $this->IdDocumento;
    }

    function getFolioDR() {
        return $this->FolioDR;
    }

    function getMonedaDR() {
        return $this->MonedaDR;
    }

    function getTipoCambioDR() {
        return $this->TipoCambioDR;
    }

    function getMetodoDePagoDR() {
        return $this->MetodoDePagoDR;
    }

    function getConceptos() {
        return $this->Conceptos;
    }

    function getImpuestos_totalImpuestosTrasladados() {
        return $this->Impuestos_totalImpuestosTrasladados;
    }

    function getTotalImpuestosRetenidos() {
        return $this->totalImpuestosRetenidos;
    }

    function getImpuestos_Trasladado() {
        return $this->Impuestos_Trasladado;
    }

    function getImpuestos_Retenidos() {
        return $this->Impuestos_Retenidos;
    }

    function getXML() {
        return $this->XML;
    }

    function setXmlns_xsi($xmlns_xsi) {
        $this->xmlns_xsi = $xmlns_xsi;
    }

    function setXsi_schemaLocation($xsi_schemaLocation) {
        $this->xsi_schemaLocation = $xsi_schemaLocation;
    }

    function setXmlns_xsd($xmlns_xsd) {
        $this->xmlns_xsd = $xmlns_xsd;
    }

    function setXmlns_pago10($xmlns_pago10) {
        $this->xmlns_pago10 = $xmlns_pago10;
    }

    function setVersion($version) {
        $this->version = $version;
    }

    function setFolio($folio) {
        $this->folio = $folio;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setSello($sello) {
        $this->sello = $sello;
    }

    function setNoCertificado($noCertificado) {
        $this->noCertificado = $noCertificado;
    }

    function setCertificado($certificado) {
        $this->certificado = $certificado;
    }

    function setSubTotal($subTotal) {
        $this->subTotal = $subTotal;
    }

    function setTotal($total) {
        $this->total = $total;
    }

    function setMoneda($moneda) {
        $this->moneda = $moneda;
    }

    function setTipoDeComprobante($tipoDeComprobante) {
        $this->tipoDeComprobante = $tipoDeComprobante;
    }

    function setLugarExpedicion($LugarExpedicion) {
        $this->LugarExpedicion = $LugarExpedicion;
    }

    function setIvaCero($IvaCero) {
        $this->IvaCero = $IvaCero;
    }

    function setXmlns($xmlns) {
        $this->xmlns = $xmlns;
    }

    function setEmisor_rfc($Emisor_rfc) {
        $this->Emisor_rfc = $Emisor_rfc;
    }

    function setEmisor_nombre($Emisor_nombre) {
        $this->Emisor_nombre = $Emisor_nombre;
    }

    function setRegimen($Regimen) {
        $this->Regimen = $Regimen;
    }

    function setReceptor_rfc($Receptor_rfc) {
        $this->Receptor_rfc = $Receptor_rfc;
    }

    function setReceptor_nombre($Receptor_nombre) {
        $this->Receptor_nombre = $Receptor_nombre;
    }

    function setUsoCFDI($UsoCFDI) {
        $this->UsoCFDI = $UsoCFDI;
    }

    function setFechaPago($FechaPago) {
        $this->FechaPago = $FechaPago;
    }

    function setFormaDePagoP($FormaDePagoP) {
        $this->FormaDePagoP = $FormaDePagoP;
    }

    function setMonedaP($MonedaP) {
        $this->MonedaP = $MonedaP;
    }

    function setTipoCambioP($TipoCambioP) {
        $this->TipoCambioP = $TipoCambioP;
    }

    function setMonto($Monto) {
        $this->Monto = $Monto;
    }

    function setIdDocumento($IdDocumento) {
        $this->IdDocumento = $IdDocumento;
    }

    function setFolioDR($FolioDR) {
        $this->FolioDR = $FolioDR;
    }

    function setMonedaDR($MonedaDR) {
        $this->MonedaDR = $MonedaDR;
    }

    function setTipoCambioDR($TipoCambioDR) {
        $this->TipoCambioDR = $TipoCambioDR;
    }

    function setMetodoDePagoDR($MetodoDePagoDR) {
        $this->MetodoDePagoDR = $MetodoDePagoDR;
    }

    function setConceptos($Conceptos) {
        $this->Conceptos = $Conceptos;
    }

    function setImpuestos_totalImpuestosTrasladados($Impuestos_totalImpuestosTrasladados) {
        $this->Impuestos_totalImpuestosTrasladados = $Impuestos_totalImpuestosTrasladados;
    }

    function setTotalImpuestosRetenidos($totalImpuestosRetenidos) {
        $this->totalImpuestosRetenidos = $totalImpuestosRetenidos;
    }

    function setImpuestos_Trasladado($Impuestos_Trasladado) {
        $this->Impuestos_Trasladado = $Impuestos_Trasladado;
    }

    function setImpuestos_Retenidos($Impuestos_Retenidos) {
        $this->Impuestos_Retenidos = $Impuestos_Retenidos;
    }

    function setXML($XML) {
        $this->XML = $XML;
    }
    
    function getNumParcialidad() {
        return $this->NumParcialidad;
    }

    function setNumParcialidad($NumParcialidad) {
        $this->NumParcialidad = $NumParcialidad;
    }
    
    function getImpSaldoAnt() {
        return $this->ImpSaldoAnt;
    }

    function getImpSaldoInsoluto() {
        return $this->ImpSaldoInsoluto;
    }

    function setImpSaldoAnt($ImpSaldoAnt) {
        $this->ImpSaldoAnt = $ImpSaldoAnt;
    }

    function setImpSaldoInsoluto($ImpSaldoInsoluto) {
        $this->ImpSaldoInsoluto = $ImpSaldoInsoluto;
    }

    function getSerie() {
        return $this->Serie;
    }

    function setSerie($Serie) {
        $this->Serie = $Serie;
    }
    
    function getImpPagado() {
        return $this->ImpPagado;
    }

    function setImpPagado($ImpPagado) {
        $this->ImpPagado = $ImpPagado;
    }
    
    function getNumOperacion() {
        return $this->NumOperacion;
    }

    function setNumOperacion($NumOperacion) {
        $this->NumOperacion = $NumOperacion;
    }
    
    function getRfcEmisorCtaOrd() {
        return $this->RfcEmisorCtaOrd;
    }

    function getCtaOrdenante() {
        return $this->CtaOrdenante;
    }

    function getRfcEmisorCtaBen() {
        return $this->RfcEmisorCtaBen;
    }

    function getCtaBeneficiario() {
        return $this->CtaBeneficiario;
    }

    function setRfcEmisorCtaOrd($RfcEmisorCtaOrd) {
        $this->RfcEmisorCtaOrd = $RfcEmisorCtaOrd;
    }

    function setCtaOrdenante($CtaOrdenante) {
        $this->CtaOrdenante = $CtaOrdenante;
    }

    function setRfcEmisorCtaBen($RfcEmisorCtaBen) {
        $this->RfcEmisorCtaBen = $RfcEmisorCtaBen;
    }

    function setCtaBeneficiario($CtaBeneficiario) {
        $this->CtaBeneficiario = $CtaBeneficiario;
    }
    
    function getSeriePago() {
        return $this->SeriePago;
    }

    function setSeriePago($SeriePago) {
        $this->SeriePago = $SeriePago;
    }
    
    function getTipoCadPago() {
        return $this->TipoCadPago;
    }

    function getCertPago() {
        return $this->CertPago;
    }

    function getCadPago() {
        return $this->CadPago;
    }

    function getSelloPago() {
        return $this->SelloPago;
    }

    function setTipoCadPago($TipoCadPago) {
        $this->TipoCadPago = $TipoCadPago;
    }

    function setCertPago($CertPago) {
        $this->CertPago = $CertPago;
    }

    function setCadPago($CadPago) {
        $this->CadPago = $CadPago;
    }

    function setSelloPago($SelloPago) {
        $this->SelloPago = $SelloPago;
    }
    function getNomBancoOrdExt() {
        return $this->NomBancoOrdExt;
    }

    function setNomBancoOrdExt($NomBancoOrdExt) {
        $this->NomBancoOrdExt = $NomBancoOrdExt;
    }
}

