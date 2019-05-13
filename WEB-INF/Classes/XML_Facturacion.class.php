<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class XML_Facturacion {

    private $xmlns_xsi = "http://www.w3.org/2001/XMLSchema-instance";
    private $xmlns_TimbreFiscalDigital="http://www.sat.gob.mx/TimbreFiscalDigital";
    private $xsi_schemaLocation="http://www.sat.gob.mx/cfd/3  http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd";
    private $xmlns_xsd = "http://www.w3.org/2001/XMLSchema";
    private $xmlns_cfdi="http://www.sat.gob.mx/cfd/3";
    private $version = "3.3";
    private $folio = "";
    private $serie;
    private $fecha = "";
    private $sello = "";
    private $noCertificado="";
    private $certificado="";
    private $Moneda="MXN";
    private $TipoCambio;
    private $noAprobacion = "";
    private $anoAprobacion = "";
    private $formaDePago = "";
    private $subTotal = "";
    private $porcentaje_descuento;
    private $descuento;
    private $total = "";
    private $tipoDeComprobante = "";
    private $metodoDePago = "";
    private $condicionesPago = "";
    private $LugarExpedicion = "";
    private $xmlns = "http://www.sat.gob.mx/cfd/3";
    private $Emisor_rfc = "";
    private $Emisor_nombre = "";
    private $Emisor_Dom_Calle = "";
    private $Emisor_Dom_NoExt = "";
    private $Emisor_Dom_NoInt = "";
    private $Emisor_Dom_Col = "";
    private $Emisor_Dom_Mun = "";
    private $Emisor_Dom_Est = "";
    private $Emisor_Dom_Pais = "";
    private $Emisor_Dom_CP = "";
    private $Expedido_Calle = "";
    private $Expedido_NoExt = "";
    private $Expedido_NoInt = "";
    private $Expedido_Col = "";
    private $Expedido_Mun = "";
    private $Expedido_Estado = "";
    private $Expedido_Pais = "";
    private $Expedido_CP = "";
    private $Regimen = "Régimen General de Ley de Personas Morales";
    private $Receptor_rfc = "";
    private $Receptor_nombre = "";
    private $Receptor_Dom_Calle = "";
    private $Receptor_Dom_NoExt = "";
    private $Receptor_Dom_NoInt = "";
    private $Receptor_Dom_Col = "";
    private $Receptor_Dom_Mun = "";
    private $Receptor_Dom_Est = "";
    private $Receptor_Dom_Pais = "";
    private $Receptor_Dom_CP = "";
    private $Conceptos; //Arrray bidimendional  Array(Array(Cantidad,Unidad,Descripcion,valorUnitario,importe),Array(Cantidad,Unidad,Descripcion,valorUnitario,importe))
    private $Impuestos_totalImpuestosTrasladados = "";
    private $Impuestos_Trasladado; //Array bidimensional Array(Array(Impuesto,Tasa,Importe),Array(Impuesto,Tasa,Importe))
    private $Addendas; //Array bidimensional
    private $XML;
    private $UsoCFDI;
    private $PrefijoSerie;
    //Para NDC
    private $TipoRelacion;
    private $UUIDRelacionado;

    public function CrearXML() {
        $this->XML = new DomDocument('1.0', 'UTF-8');
        $comprobante = $this->XML->createElement("cfdi:Comprobante");
        //$comprobante->setAttribute("xmlns", $this->xmlns);
        $comprobante->setAttribute("xmlns:xsi", $this->xmlns_xsi);
        $comprobante->setAttribute("xmlns:TimbreFiscalDigital", $this->xmlns_TimbreFiscalDigital);
        $comprobante->setAttribute("xsi:schemaLocation", $this->xsi_schemaLocation);
        $comprobante->setAttribute("version", $this->version);
        $comprobante->setAttribute("folio", $this->folio);
        $comprobante->setAttribute("fecha", $this->fecha);
        $comprobante->setAttribute("sello", $this->sello);                
        $comprobante->setAttribute("formaDePago", $this->formaDePago);
        if($this->condicionesPago != ""){
            $comprobante->setAttribute("condicionesDePago", $this->condicionesPago);
        }
        if(isset($this->PrefijoSerie) && $this->PrefijoSerie != ""){
            $comprobante->setAttribute("serie", $this->PrefijoSerie);
        }
        $comprobante->setAttribute("noCertificado", $this->noCertificado);
        $comprobante->setAttribute("certificado", $this->certificado);
        $comprobante->setAttribute("subTotal", $this->subTotal);
        $comprobante->setAttribute("Moneda", $this->Moneda);
        $comprobante->setAttribute("total", $this->total);
        $comprobante->setAttribute("tipoDeComprobante", $this->tipoDeComprobante);
        $comprobante->setAttribute("metodoDePago", $this->metodoDePago);
        $comprobante->setAttribute("LugarExpedicion", $this->LugarExpedicion);
        $comprobante->setAttribute("xmlns:cfdi", $this->xmlns_cfdi);
        $Emisor = $this->XML->createElement("cfdi:Emisor");
        $Emisor->setAttribute("rfc", $this->Emisor_rfc);
        $Emisor->setAttribute("nombre", $this->Emisor_nombre);
        $Emisor_Dom = $this->XML->createElement("cfdi:DomicilioFiscal");
        $Emisor_Dom->setAttribute("calle", $this->Emisor_Dom_Calle);
        $Emisor_Dom->setAttribute("noExterior", $this->Emisor_Dom_NoExt);
        if ($this->Emisor_Dom_NoInt != "") {
            $Emisor_Dom->setAttribute("noInterior", $this->Emisor_Dom_NoInt);
        }
        $Emisor_Dom->setAttribute("colonia", $this->Emisor_Dom_Col);
        $Emisor_Dom->setAttribute("municipio", $this->Emisor_Dom_Mun);
        $Emisor_Dom->setAttribute("estado", $this->Emisor_Dom_Est);
        $Emisor_Dom->setAttribute("pais", $this->Emisor_Dom_Pais);
        $Emisor_Dom->setAttribute("codigoPostal", $this->Emisor_Dom_CP);
        $RegimenFiscal = $this->XML->createElement("cfdi:RegimenFiscal");
        $RegimenFiscal->setAttribute("Regimen", $this->Regimen);
        $Emisor_Expedido = $this->XML->createElement("ExpedidoEn");
        $Emisor_Expedido->setAttribute("calle", $this->Expedido_Calle);
        $Emisor_Expedido->setAttribute("noExterior", $this->Expedido_NoExt);
        if ($this->Expedido_NoInt != "") {
            $Emisor_Expedido->setAttribute("noInterior", $this->Expedido_NoInt);
        }
        $Emisor_Expedido->setAttribute("colonia", $this->Expedido_Col);
        $Emisor_Expedido->setAttribute("municipio", $this->Expedido_Mun);
        $Emisor_Expedido->setAttribute("estado", $this->Expedido_Estado);
        $Emisor_Expedido->setAttribute("pais", $this->Expedido_Pais);
        $Emisor_Expedido->setAttribute("codigoPostal", $this->Expedido_CP);
        $Emisor_Regimen = $this->XML->createElement("RegimenFiscal");
        $Emisor_Regimen->setAttribute("Regimen", $this->Regimen);
        $Receptor = $this->XML->createElement("cfdi:Receptor");
        $Receptor->setAttribute("rfc", $this->Receptor_rfc);
        $Receptor->setAttribute("nombre", $this->Receptor_nombre);
        $Receptor_Dom = $this->XML->createElement("cfdi:Domicilio");
        $Receptor_Dom->setAttribute("calle", $this->Receptor_Dom_Calle);
        $Receptor_Dom->setAttribute("noExterior", $this->Receptor_Dom_NoExt);
        if ($this->Receptor_Dom_NoInt != "") {
            $Receptor_Dom->setAttribute("noInterior", $this->Receptor_Dom_NoInt);
        }
        $Receptor_Dom->setAttribute("colonia", $this->Receptor_Dom_Col);
        $Receptor_Dom->setAttribute("municipio", $this->Receptor_Dom_Mun);
        $Receptor_Dom->setAttribute("estado", $this->Receptor_Dom_Est);
        $Receptor_Dom->setAttribute("pais", $this->Receptor_Dom_Pais);
        $Receptor_Dom->setAttribute("codigoPostal", $this->Receptor_Dom_CP);
        $Conceptos = $this->XML->createElement("cfdi:Conceptos");
        if ($this->Conceptos != NULL) {
            foreach ($this->Conceptos AS $value) {
                $Conceptos_aux = $this->XML->createElement("cfdi:Concepto");
                $valor = str_replace(",", "", $value[0]);
                $Conceptos_aux->setAttribute("cantidad", trim(preg_replace('/\s\s+/', ' ', $valor)));
                $Conceptos_aux->setAttribute("unidad", trim(preg_replace('/\s\s+/', ' ', $value[1])));
                $Conceptos_aux->setAttribute("descripcion", trim(preg_replace('/\s\s+/', ' ', $value[2])));
                $Conceptos_aux->setAttribute("valorUnitario", trim(preg_replace('/\s\s+/', ' ', $value[3])));
                $Conceptos_aux->setAttribute("importe", trim(preg_replace('/\s\s+/', ' ', $value[4])));
                $Conceptos->appendChild($Conceptos_aux);
            }
        }
        //Impuestos y traslados
        $Impuestos = $this->XML->createElement("cfdi:Impuestos");
        $Impuestos->setAttribute("totalImpuestosTrasladados", $this->Impuestos_totalImpuestosTrasladados);
        $Traslados = $this->XML->createElement("cfdi:Traslados");
        if ($this->Impuestos_Trasladado != NULL) {
            foreach ($this->Impuestos_Trasladado as $value) {
                $Traslados_aux = $this->XML->createElement("cfdi:Traslado");
                $Traslados_aux->setAttribute("impuesto", $value[0]);
                $Traslados_aux->setAttribute("tasa", $value[1]);
                $Traslados_aux->setAttribute("importe", $value[2]);
                $Traslados->appendChild($Traslados_aux);
            }
        }
        //Addendas
        /*$Addendas = $this->XML->createElement("cfdi:Addenda");        
        $Generales = $this->XML->createElement("Generales");
        if ($this->Addendas != NULL) {
            foreach ($this->Addendas as $value) {
                $Addendas_aux = $this->XML->createElement($value[0], $value[1]);                                
                $Generales->appendChild($Addendas_aux);
            }
        }*/
        $Emisor->appendChild($Emisor_Dom);
        $Emisor->appendChild($RegimenFiscal);
        //$Emisor->appendChild($Emisor_Expedido);
        //$Emisor->appendChild($Emisor_Regimen);
        $comprobante->appendChild($Emisor);
        $Receptor->appendChild($Receptor_Dom);
        $comprobante->appendChild($Receptor);
        $comprobante->appendChild($Conceptos);
        $Impuestos->appendChild($Traslados);
        $comprobante->appendChild($Impuestos);
        /*if($this->Addendas != NULL){
            $Addendas->appendChild($Generales);
            $comprobante->appendChild($Addendas);
        }*/
        $this->XML->appendChild($comprobante);
        return $this->XML->saveXML();
    }
    
    public function CrearXML33() {
        $this->xmlns_xsi = "http://www.w3.org/2001/XMLSchema-instance";
        $this->xsi_schemaLocation = "http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv33.xsd";
        $this->XML = new DomDocument('1.0', 'UTF-8');
        $comprobante = $this->XML->createElement("cfdi:Comprobante");
        //$comprobante->setAttribute("xmlns", $this->xmlns);
        $comprobante->setAttribute("xmlns:xsi", $this->xmlns_xsi);
        $comprobante->setAttribute("xsi:schemaLocation", $this->xsi_schemaLocation);
        $comprobante->setAttribute("Version", $this->version);
        if(!empty($this->PrefijoSerie)){
            $comprobante->setAttribute("Serie", $this->PrefijoSerie);
        }
        $comprobante->setAttribute("Folio", $this->folio);
        $comprobante->setAttribute("Fecha", $this->fecha);
        $comprobante->setAttribute("Sello", $this->sello);
        $comprobante->setAttribute("FormaPago", $this->formaDePago);
        if(isset($this->condicionesPago) && !empty($this->condicionesPago)){
            $comprobante->setAttribute("CondicionesDePago", $this->condicionesPago);
        }
        $comprobante->setAttribute("NoCertificado", $this->noCertificado);
        $comprobante->setAttribute("Certificado", $this->certificado);
        $comprobante->setAttribute("SubTotal", $this->subTotal);
        if(isset($this->descuento) && !empty($this->descuento)){
            $comprobante->setAttribute("Descuento", $this->descuento);
        }
        $comprobante->setAttribute("Moneda", $this->Moneda);        
        $comprobante->setAttribute("TipoCambio", $this->TipoCambio);
        $comprobante->setAttribute("Total", $this->total);
        $comprobante->setAttribute("TipoDeComprobante", $this->tipoDeComprobante);
        $comprobante->setAttribute("MetodoPago", $this->metodoDePago);
        $comprobante->setAttribute("LugarExpedicion", $this->LugarExpedicion);
        $comprobante->setAttribute("xmlns:cfdi", $this->xmlns_cfdi);
        
        if(isset($this->UUIDRelacionado) && !empty($this->UUIDRelacionado)){
            $Relacionado = $this->XML->createElement("cfdi:CfdiRelacionados");
            $Relacionado->setAttribute("TipoRelacion", $this->TipoRelacion);
            foreach ($this->UUIDRelacionado as $value) {
                $Relacion = $this->XML->createElement("cfdi:CfdiRelacionado");
                $Relacion->setAttribute("UUID", $value);
                $Relacionado->appendChild($Relacion);
            }
        }
        
        $Emisor = $this->XML->createElement("cfdi:Emisor");
        $Emisor->setAttribute("Rfc", $this->Emisor_rfc);
        $Emisor->setAttribute("Nombre", $this->Emisor_nombre);
        $Emisor->setAttribute("RegimenFiscal", $this->Regimen);
        
        $Receptor = $this->XML->createElement("cfdi:Receptor");
        $Receptor->setAttribute("Rfc", $this->Receptor_rfc);
        $Receptor->setAttribute("Nombre", $this->Receptor_nombre);
        $Receptor->setAttribute("UsoCFDI", $this->UsoCFDI);
        
        $Conceptos = $this->XML->createElement("cfdi:Conceptos");
        if ($this->Conceptos != NULL) {
            $totalImpuestosTrasladadosSuma = 0;
            foreach ($this->Conceptos AS $value) {
                if($value[6] == 0){
                    continue;
                }
                $Conceptos_aux = $this->XML->createElement("cfdi:Concepto");
                $valor = str_replace(",", "", $value[0]);
                $Conceptos_aux->setAttribute("Cantidad", trim(preg_replace('/\s\s+/', ' ', $valor)));
                $Conceptos_aux->setAttribute("ClaveProdServ", trim(preg_replace('/\s\s+/', ' ', $value[1])));
                $Conceptos_aux->setAttribute("ClaveUnidad", trim(preg_replace('/\s\s+/', ' ', $value[2])));
                $Conceptos_aux->setAttribute("Unidad", trim(preg_replace('/\s\s+/', ' ', $value[3])));
                $desc = trim(preg_replace('/\s\s+/', ' ', $value[4]));
                if(strlen($desc) >= 1000){
                    $desc = substr($desc, 0, 1000);
                }
                $Conceptos_aux->setAttribute("Descripcion", trim(preg_replace('/\s\s+/', ' ', $desc)));
                $Conceptos_aux->setAttribute("ValorUnitario", trim(preg_replace('/\s\s+/', ' ', $value[5])));
                $Conceptos_aux->setAttribute("Importe", trim(preg_replace('/\s\s+/', ' ',  number_format($value[6],4,".",""))) );
                $subtotal = trim(preg_replace('/\s\s+/', ' ', $value[6]));
                $descuento = 0;
                if(isset($value[8]) && !empty($value[8])){//Si hay descuento por partida
                    if(isset($value[9]) && $value[9] == "1"){//El descuento es por porcentaje
                        $descuento = number_format($subtotal * ($value[8] / 100),2,".","");
                    }else {
                        $descuento = number_format($value[8],2,".","");
                    }                    
                }
                
                if(isset($this->porcentaje_descuento) && !empty($this->porcentaje_descuento)){
                    $descuento += number_format($subtotal * (float)(trim(preg_replace('/\s\s+/', ' ', $this->porcentaje_descuento)) / 100),2,".","");                    
                }
                
                if(!empty($descuento)){
                    $descuento = number_format($descuento,2,".","");
                    $subtotal = $subtotal - $descuento;
                    $Conceptos_aux->setAttribute("Descuento", trim(preg_replace('/\s\s+/', ' ', $descuento )));
                }
                
                $ImpuestosConceptos = $this->XML->createElement("cfdi:Impuestos");
                $TrasladosConceptos = $this->XML->createElement("cfdi:Traslados");
                $TrasladoTraslados = $this->XML->createElement("cfdi:Traslado");
                $TrasladoTraslados->setAttribute("Base", trim(preg_replace('/\s\s+/', ' ', number_format($subtotal,4,".",""))));
                $TrasladoTraslados->setAttribute("Impuesto", "002");    //Clave IVA
                if(!$this->IvaCero){
                    $totalImpuestosTrasladadosSuma  += number_format($subtotal * 0.16,4,".","");
                    $TrasladoTraslados->setAttribute("TipoFactor", "Tasa");
                    $TrasladoTraslados->setAttribute("TasaOCuota", "0.160000");
                    $TrasladoTraslados->setAttribute("Importe", number_format(trim(preg_replace('/\s\s+/', ' ', $subtotal)) * 0.16,4,".",""));
                }else{
                    $TrasladoTraslados->setAttribute("TipoFactor", "Exento");
                }
                $TrasladosConceptos->appendChild($TrasladoTraslados);
                $ImpuestosConceptos->appendChild($TrasladosConceptos);
                $Conceptos_aux->appendChild($ImpuestosConceptos);
                $Conceptos->appendChild($Conceptos_aux);
            }
        }
        
        if(!$this->IvaCero){
            $Impuestos = $this->XML->createElement("cfdi:Impuestos");
            $Impuestos->setAttribute("TotalImpuestosTrasladados", number_format($totalImpuestosTrasladadosSuma,2,".",""));
            $Traslados = $this->XML->createElement("cfdi:Traslados");
            if ($this->Impuestos_Trasladado != NULL) {
                foreach ($this->Impuestos_Trasladado as $value) {
                    $Traslados_aux = $this->XML->createElement("cfdi:Traslado");
                    $Traslados_aux->setAttribute("Impuesto", $value[0]);
                    $Traslados_aux->setAttribute("TipoFactor", "Tasa");
                    $Traslados_aux->setAttribute("TasaOCuota", $value[1]);
                    $Traslados_aux->setAttribute("Importe", number_format($totalImpuestosTrasladadosSuma,2,".",""));
                    $Traslados->appendChild($Traslados_aux);
                }
            }

            if(!empty($this->totalImpuestosRetenidos)){
                $Impuestos->setAttribute("TotalImpuestosRetenidos", $this->totalImpuestosRetenidos);
                $Retenciones = $this->XML->createElement("cfdi:Retenciones");
                if ($this->Impuestos_Retenidos != NULL) {
                    foreach ($this->Impuestos_Retenidos as $value) {
                        $Traslados_aux = $this->XML->createElement("cfdi:Retencion");
                        $Traslados_aux->setAttribute("Impuesto", $value[0]);                
                        $Traslados_aux->setAttribute("Importe", $value[1]);
                        $Retenciones->appendChild($Traslados_aux);
                    }
                }
            }
        }                
        if(isset($this->UUIDRelacionado) && !empty($this->UUIDRelacionado)){
            $comprobante->appendChild($Relacionado);
        }
        $comprobante->appendChild($Emisor);
        $comprobante->appendChild($Receptor);
        $comprobante->appendChild($Conceptos);
        if(!empty($this->totalImpuestosRetenidos)){
            $Impuestos->appendChild($Retenciones);
        }
        if(!$this->IvaCero){
            $Impuestos->appendChild($Traslados);
            $comprobante->appendChild($Impuestos);
        }
        
        /*Addendas*/
        /*if(!empty($this->Addendas)){            
            $Addenda = $this->XML->createElement("cfdi:Addenda");            
            $Generales = $this->XML->createElement("Generales");
            foreach ($this->Addendas as $value) {
                $aux = $this->XML->createElement($value[0], $value[1]);
                $Generales->appendChild($aux);
                
            }               
            $Addenda->appendChild($Generales);
            $comprobante->appendChild($Addenda);
        }*/
        
        $this->XML->appendChild($comprobante);
        return $this->XML->saveXML();
    }

    public function getXmlns_xsi() {
        return $this->xmlns_xsi;
    }

    public function setXmlns_xsi($xmlns_xsi) {
        $this->xmlns_xsi = $xmlns_xsi;
    }

    public function getXmlns_xsd() {
        return $this->xmlns_xsd;
    }

    public function setXmlns_xsd($xmlns_xsd) {
        $this->xmlns_xsd = $xmlns_xsd;
    }

    public function getVersion() {
        return $this->version;
    }

    public function setVersion($version) {
        $this->version = $version;
    }

    public function getFolio() {
        return $this->folio;
    }

    public function setFolio($folio) {
        $this->folio = $folio;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function getSello() {
        return $this->sello;
    }

    public function setSello($sello) {
        $this->sello = $sello;
    }

    public function getNoAprobacion() {
        return $this->noAprobacion;
    }

    public function setNoAprobacion($noAprobacion) {
        $this->noAprobacion = $noAprobacion;
    }

    public function getAnoAprobacion() {
        return $this->anoAprobacion;
    }

    public function setAnoAprobacion($anoAprobacion) {
        $this->anoAprobacion = $anoAprobacion;
    }

    public function getFormaDePago() {
        return $this->formaDePago;
    }

    public function setFormaDePago($formaDePago) {
        $this->formaDePago = $formaDePago;
    }

    public function getSubTotal() {
        return $this->subTotal;
    }

    public function setSubTotal($subTotal) {
        $this->subTotal = $subTotal;
    }

    public function getTotal() {
        return $this->total;
    }

    public function setTotal($total) {
        $this->total = $total;
    }

    public function getTipoDeComprobante() {
        return $this->tipoDeComprobante;
    }

    public function setTipoDeComprobante($tipoDeComprobante) {
        $this->tipoDeComprobante = $tipoDeComprobante;
    }

    public function getMetodoDePago() {
        return $this->metodoDePago;
    }

    public function setMetodoDePago($metodoDePago) {
        $this->metodoDePago = $metodoDePago;
    }

    public function getLugarExpedicion() {
        return $this->LugarExpedicion;
    }

    public function setLugarExpedicion($LugarExpedicion) {
        $this->LugarExpedicion = $LugarExpedicion;
    }

    public function getXmlns() {
        return $this->xmlns;
    }

    public function setXmlns($xmlns) {
        $this->xmlns = $xmlns;
    }

    public function getXsi_schemaLocation() {
        return $this->xsi_schemaLocation;
    }

    public function setXsi_schemaLocation($xsi_schemaLocation) {
        $this->xsi_schemaLocation = $xsi_schemaLocation;
    }

    public function getEmisor_rfc() {
        return $this->Emisor_rfc;
    }

    public function setEmisor_rfc($Emisor_rfc) {
        $this->Emisor_rfc = $Emisor_rfc;
    }

    public function getEmisor_nombre() {
        return $this->Emisor_nombre;
    }

    public function setEmisor_nombre($Emisor_nombre) {
        $this->Emisor_nombre = $Emisor_nombre;
    }

    public function getEmisor_Dom_Calle() {
        return $this->Emisor_Dom_Calle;
    }

    public function setEmisor_Dom_Calle($Emisor_Dom_Calle) {
        $this->Emisor_Dom_Calle = $Emisor_Dom_Calle;
    }

    public function getEmisor_Dom_NoExt() {
        return $this->Emisor_Dom_NoExt;
    }

    public function setEmisor_Dom_NoExt($Emisor_Dom_NoExt) {
        $this->Emisor_Dom_NoExt = $Emisor_Dom_NoExt;
    }

    public function getEmisor_Dom_NoInt() {
        return $this->Emisor_Dom_NoInt;
    }

    public function setEmisor_Dom_NoInt($Emisor_Dom_NoInt) {
        $this->Emisor_Dom_NoInt = $Emisor_Dom_NoInt;
    }

    public function getEmisor_Dom_Col() {
        return $this->Emisor_Dom_Col;
    }

    public function setEmisor_Dom_Col($Emisor_Dom_Col) {
        $this->Emisor_Dom_Col = $Emisor_Dom_Col;
    }

    public function getEmisor_Dom_Mun() {
        return $this->Emisor_Dom_Mun;
    }

    public function setEmisor_Dom_Mun($Emisor_Dom_Mun) {
        $this->Emisor_Dom_Mun = $Emisor_Dom_Mun;
    }

    public function getEmisor_Dom_Est() {
        return $this->Emisor_Dom_Est;
    }

    public function setEmisor_Dom_Est($Emisor_Dom_Est) {
        $this->Emisor_Dom_Est = $Emisor_Dom_Est;
    }

    public function getEmisor_Dom_Pais() {
        return $this->Emisor_Dom_Pais;
    }

    public function setEmisor_Dom_Pais($Emisor_Dom_Pais) {
        $this->Emisor_Dom_Pais = $Emisor_Dom_Pais;
    }

    public function getEmisor_Dom_CP() {
        return $this->Emisor_Dom_CP;
    }

    public function setEmisor_Dom_CP($Emisor_Dom_CP) {
        $this->Emisor_Dom_CP = $Emisor_Dom_CP;
    }

    public function getExpedido_Calle() {
        return $this->Expedido_Calle;
    }

    public function setExpedido_Calle($Expedido_Calle) {
        $this->Expedido_Calle = $Expedido_Calle;
    }

    public function getExpedido_NoExt() {
        return $this->Expedido_NoExt;
    }

    public function setExpedido_NoExt($Expedido_NoExt) {
        $this->Expedido_NoExt = $Expedido_NoExt;
    }

    public function getExpedido_NoInt() {
        return $this->Expedido_NoInt;
    }

    public function setExpedido_NoInt($Expedido_NoInt) {
        $this->Expedido_NoInt = $Expedido_NoInt;
    }

    public function getExpedido_Col() {
        return $this->Expedido_Col;
    }

    public function setExpedido_Col($Expedido_Col) {
        $this->Expedido_Col = $Expedido_Col;
    }

    public function getExpedido_Mun() {
        return $this->Expedido_Mun;
    }

    public function setExpedido_Mun($Expedido_Mun) {
        $this->Expedido_Mun = $Expedido_Mun;
    }

    public function getExpedido_Estado() {
        return $this->Expedido_Estado;
    }

    public function setExpedido_Estado($Expedido_Estado) {
        $this->Expedido_Estado = $Expedido_Estado;
    }

    public function getExpedido_Pais() {
        return $this->Expedido_Pais;
    }

    public function setExpedido_Pais($Expedido_Pais) {
        $this->Expedido_Pais = $Expedido_Pais;
    }

    public function getExpedido_CP() {
        return $this->Expedido_CP;
    }

    public function setExpedido_CP($Expedido_CP) {
        $this->Expedido_CP = $Expedido_CP;
    }

    public function getRegimen() {
        return $this->Regimen;
    }

    public function setRegimen($Regimen) {
        $this->Regimen = $Regimen;
    }

    public function getReceptor_rfc() {
        return $this->Receptor_rfc;
    }

    public function setReceptor_rfc($Receptor_rfc) {
        $this->Receptor_rfc = $Receptor_rfc;
    }

    public function getReceptor_nombre() {
        return $this->Receptor_nombre;
    }

    public function setReceptor_nombre($Receptor_nombre) {
        $this->Receptor_nombre = $Receptor_nombre;
    }

    public function getReceptor_Dom_Calle() {
        return $this->Receptor_Dom_Calle;
    }

    public function setReceptor_Dom_Calle($Receptor_Dom_Calle) {
        $this->Receptor_Dom_Calle = $Receptor_Dom_Calle;
    }

    public function getReceptor_Dom_NoExt() {
        return $this->Receptor_Dom_NoExt;
    }

    public function setReceptor_Dom_NoExt($Receptor_Dom_NoExt) {
        $this->Receptor_Dom_NoExt = $Receptor_Dom_NoExt;
    }

    public function getReceptor_Dom_NoInt() {
        return $this->Receptor_Dom_NoInt;
    }

    public function setReceptor_Dom_NoInt($Receptor_Dom_NoInt) {
        $this->Receptor_Dom_NoInt = $Receptor_Dom_NoInt;
    }

    public function getReceptor_Dom_Col() {
        return $this->Receptor_Dom_Col;
    }

    public function setReceptor_Dom_Col($Receptor_Dom_Col) {
        $this->Receptor_Dom_Col = $Receptor_Dom_Col;
    }

    public function getReceptor_Dom_Mun() {
        return $this->Receptor_Dom_Mun;
    }

    public function setReceptor_Dom_Mun($Receptor_Dom_Mun) {
        $this->Receptor_Dom_Mun = $Receptor_Dom_Mun;
    }

    public function getReceptor_Dom_Est() {
        return $this->Receptor_Dom_Est;
    }

    public function setReceptor_Dom_Est($Receptor_Dom_Est) {
        $this->Receptor_Dom_Est = $Receptor_Dom_Est;
    }

    public function getReceptor_Dom_Pais() {
        return $this->Receptor_Dom_Pais;
    }

    public function setReceptor_Dom_Pais($Receptor_Dom_Pais) {
        $this->Receptor_Dom_Pais = $Receptor_Dom_Pais;
    }

    public function getReceptor_Dom_CP() {
        return $this->Receptor_Dom_CP;
    }

    public function setReceptor_Dom_CP($Receptor_Dom_CP) {
        $this->Receptor_Dom_CP = $Receptor_Dom_CP;
    }

    public function getConceptos() {
        return $this->Conceptos;
    }

    public function setConceptos($Conceptos) {
        $this->Conceptos = $Conceptos;
    }

    public function getImpuestos_totalImpuestosTrasladados() {
        return $this->Impuestos_totalImpuestosTrasladados;
    }

    public function setImpuestos_totalImpuestosTrasladados($Impuestos_totalImpuestosTrasladados) {
        $this->Impuestos_totalImpuestosTrasladados = $Impuestos_totalImpuestosTrasladados;
    }

    public function getImpuestos_Trasladado() {
        return $this->Impuestos_Trasladado;
    }

    public function setImpuestos_Trasladado($Impuestos_Trasladado) {
        $this->Impuestos_Trasladado = $Impuestos_Trasladado;
    }
    public function getXmlns_TimbreFiscalDigital() {
        return $this->xmlns_TimbreFiscalDigital;
    }

    public function getXmlns_cfdi() {
        return $this->xmlns_cfdi;
    }

    public function getNoCertificado() {
        return $this->noCertificado;
    }

    public function getCertificado() {
        return $this->certificado;
    }

    public function getMoneda() {
        return $this->Moneda;
    }

    public function getXML() {
        return $this->XML;
    }

    public function setXmlns_TimbreFiscalDigital($xmlns_TimbreFiscalDigital) {
        $this->xmlns_TimbreFiscalDigital = $xmlns_TimbreFiscalDigital;
    }

    public function setXmlns_cfdi($xmlns_cfdi) {
        $this->xmlns_cfdi = $xmlns_cfdi;
    }

    public function setNoCertificado($noCertificado) {
        $this->noCertificado = $noCertificado;
    }

    public function setCertificado($certificado) {
        $this->certificado = $certificado;
    }

    public function setMoneda($Moneda) {
        $this->Moneda = $Moneda;
    }

    public function setXML($XML) {
        $this->XML = $XML;
    }

    function getAddendas() {
        return $this->Addendas;
    }

    function setAddendas($Addendas) {
        $this->Addendas = $Addendas;
    }
    
    function getCondicionesPago() {
        return $this->condicionesPago;
    }

    function setCondicionesPago($condicionesPago) {
        $this->condicionesPago = $condicionesPago;
    }
    
    function getPrefijoSerie() {
        return $this->PrefijoSerie;
    }

    function setPrefijoSerie($PrefijoSerie) {
        $this->PrefijoSerie = $PrefijoSerie;
    }

    function getUsoCFDI() {
        return $this->UsoCFDI;
    }

    function setUsoCFDI($UsoCFDI) {
        $this->UsoCFDI = $UsoCFDI;
    }

    function getDescuento() {
        return $this->descuento;
    }

    function setDescuento($descuento) {
        $this->descuento = $descuento;
    }
    
    function getPorcentaje_descuento() {
        return $this->porcentaje_descuento;
    }

    function setPorcentaje_descuento($porcentaje_descuento) {
        $this->porcentaje_descuento = $porcentaje_descuento;
    }
    
    function getSerie() {
        return $this->serie;
    }

    function setSerie($serie) {
        $this->serie = $serie;
    }

    function getTipoRelacion() {
        return $this->TipoRelacion;
    }

    function getUUIDRelacionado() {
        return $this->UUIDRelacionado;
    }

    function setTipoRelacion($TipoRelacion) {
        $this->TipoRelacion = $TipoRelacion;
    }

    function setUUIDRelacionado($UUIDRelacionado) {
        $this->UUIDRelacionado = $UUIDRelacionado;
    }
    
    function getTipoCambio() {
        return $this->TipoCambio;
    }

    function setTipoCambio($TipoCambio) {
        $this->TipoCambio = $TipoCambio;
    }
}

?>
