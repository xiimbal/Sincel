<?php

class XMLAbraham {
    private $xmlns_xsi = "http://www.w3.org/2001/XMLSchema-instance";
    private $xsi_schemaLocation = "http://www.sat.gob.mx/cfd/2 http://www.sat.gob.mx/sitio_internet/cfd/2/cfdv22.xsd";
    private $xmlns_xsd = "http://www.w3.org/2001/XMLSchema";
    private $version = "3.2";
    private $folio = "";
    private $fecha = "";
    private $sello = "";
    private $noOrden;
    private $noProveedor;
    private $obsDentroXML;
    private $obsFueraXML;
    private $noAprobacion = "";
    private $anoAprobacion = "";
    private $condicionesPago = "";
    private $formaDePago = "";
    private $subTotal = "";
    private $total = "";
    private $tipoDeComprobante = "";
    private $metodoDePago = "";
    private $LugarExpedicion = "";
    private $xmlns = "http://www.sat.gob.mx/cfd/2";
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
    private $XML;
    private $Addendas;

    public function CrearXML() {
        $this->XML = new DomDocument('1.0', 'UTF-8');
        $comprobante = $this->XML->createElement("Comprobante");
        $comprobante->setAttribute("xmlns:xsi", $this->xmlns_xsi);
        $comprobante->setAttribute("version", $this->version);
        $comprobante->setAttribute("folio", $this->folio);
        $comprobante->setAttribute("fecha", $this->fecha);
        $comprobante->setAttribute("sello", $this->sello);
        $comprobante->setAttribute("orden", $this->noOrden);
        $comprobante->setAttribute("proveedor", $this->noProveedor);
        $comprobante->setAttribute("observaciones_dentroXML", $this->obsDentroXML);
        $comprobante->setAttribute("observaciones_fueraXML", $this->obsFueraXML);
        $comprobante->setAttribute("noAprobacion", $this->noAprobacion);
        $comprobante->setAttribute("anoAprobacion", $this->anoAprobacion);        
        $comprobante->setAttribute("formaDePago", $this->formaDePago);
        if($this->condicionesPago != ""){
            $comprobante->setAttribute("condicionesDePago", $this->condicionesPago);
        }
        $comprobante->setAttribute("subTotal", $this->subTotal);
        $comprobante->setAttribute("total", $this->total);
        $comprobante->setAttribute("tipoDeComprobante", $this->tipoDeComprobante);
        $comprobante->setAttribute("metodoDePago", $this->metodoDePago);
        $comprobante->setAttribute("LugarExpedicion", $this->LugarExpedicion);
        //$comprobante->setAttribute("xmlns", $this->xmlns);
        $comprobante->setAttribute("xsi:schemaLocation", $this->xsi_schemaLocation);
        $Emisor = $this->XML->createElement("Emisor");
        $Emisor->setAttribute("rfc", $this->Emisor_rfc);
        $Emisor->setAttribute("nombre", $this->Emisor_nombre);
        $Emisor_Dom = $this->XML->createElement("DomicilioFiscal");
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
        $RegimenFiscal = $this->XML->createElement("RegimenFiscal");
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
        $Receptor = $this->XML->createElement("Receptor");
        $Receptor->setAttribute("rfc", $this->Receptor_rfc);
        $Receptor->setAttribute("nombre", $this->Receptor_nombre);
        $Receptor_Dom = $this->XML->createElement("Domicilio");
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
        $Conceptos = $this->XML->createElement("Conceptos");        
        if ($this->Conceptos != NULL) {
            foreach ($this->Conceptos AS $value) {
                $Conceptos_aux = $this->XML->createElement("Concepto");
                $Conceptos_aux->setAttribute("cantidad", trim(preg_replace('/\s\s+/', ' ', $value[0])));
                $Conceptos_aux->setAttribute("unidad", trim(preg_replace('/\s\s+/', ' ', $value[1])));
                $Conceptos_aux->setAttribute("descripcion", trim(preg_replace('/\s\s+/', ' ', $value[2])));
                $Conceptos_aux->setAttribute("valorUnitario", trim(preg_replace('/\s\s+/', ' ', $value[3])));
                $Conceptos_aux->setAttribute("importe", trim(preg_replace('/\s\s+/', ' ', $value[4])));
                $Conceptos->appendChild($Conceptos_aux);
            }
        }
        $Impuestos = $this->XML->createElement("Impuestos");
        $Impuestos->setAttribute("totalImpuestosTrasladados", $this->Impuestos_totalImpuestosTrasladados);
        $Traslados = $this->XML->createElement("Traslados");
        if ($this->Impuestos_Trasladado != NULL) {
            foreach ($this->Impuestos_Trasladado as $value) {
                $Traslados_aux = $this->XML->createElement("Traslado");
                $Traslados_aux->setAttribute("impuesto", $value[0]);
                $Traslados_aux->setAttribute("tasa", $value[1]);
                $Traslados_aux->setAttribute("importe", $value[2]);                
                $Traslados->appendChild($Traslados_aux);
            }
        }        
        //Addendas
        $Addendas = $this->XML->createElement("cfdi:Addenda");        
        $Generales = $this->XML->createElement("Generales");
        if ($this->Addendas != NULL) {
            foreach ($this->Addendas as $value) {
                $Addendas_aux = $this->XML->createElement($value[0], $value[1]);                
                /*$Addendas_aux->setAttribute("impuesto", $value[0]);
                $Addendas_aux->setAttribute("tasa", $value[1]);
                $Addendas_aux->setAttribute("importe", $value[2]);*/
                $Generales->appendChild($Addendas_aux);
            }
        }
        
        $Emisor->appendChild($Emisor_Dom);
        $Emisor->appendChild($RegimenFiscal);
        $Emisor->appendChild($Emisor_Expedido);
        $comprobante->appendChild($Emisor);
        $Receptor->appendChild($Receptor_Dom);
        $comprobante->appendChild($Receptor);
        $comprobante->appendChild($Conceptos);
        $Impuestos->appendChild($Traslados);
        $comprobante->appendChild($Impuestos);
        if($this->Addendas != NULL){
            $Addendas->appendChild($Generales);
            $comprobante->appendChild($Addendas);
        }
        $this->XML->appendChild($comprobante);
        return $this->XML->saveXML();
    }

    public function getXmlns_xsi() {
        return $this->xmlns_xsi;
    }

    public function getXsi_schemaLocation() {
        return $this->xsi_schemaLocation;
    }

    public function getXmlns_xsd() {
        return $this->xmlns_xsd;
    }

    public function getVersion() {
        return $this->version;
    }

    public function getFolio() {
        return $this->folio;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getSello() {
        return $this->sello;
    }

    public function getNoAprobacion() {
        return $this->noAprobacion;
    }

    public function getAnoAprobacion() {
        return $this->anoAprobacion;
    }

    public function getFormaDePago() {
        return $this->formaDePago;
    }

    public function getSubTotal() {
        return $this->subTotal;
    }

    public function getTotal() {
        return $this->total;
    }

    public function getTipoDeComprobante() {
        return $this->tipoDeComprobante;
    }

    public function getMetodoDePago() {
        return $this->metodoDePago;
    }

    public function getLugarExpedicion() {
        return $this->LugarExpedicion;
    }

    public function getXmlns() {
        return $this->xmlns;
    }

    public function getEmisor_rfc() {
        return $this->Emisor_rfc;
    }

    public function getEmisor_nombre() {
        return $this->Emisor_nombre;
    }

    public function getEmisor_Dom_Calle() {
        return $this->Emisor_Dom_Calle;
    }

    public function getEmisor_Dom_NoExt() {
        return $this->Emisor_Dom_NoExt;
    }

    public function getEmisor_Dom_NoInt() {
        return $this->Emisor_Dom_NoInt;
    }

    public function getEmisor_Dom_Col() {
        return $this->Emisor_Dom_Col;
    }

    public function getEmisor_Dom_Mun() {
        return $this->Emisor_Dom_Mun;
    }

    public function getEmisor_Dom_Est() {
        return $this->Emisor_Dom_Est;
    }

    public function getEmisor_Dom_Pais() {
        return $this->Emisor_Dom_Pais;
    }

    public function getEmisor_Dom_CP() {
        return $this->Emisor_Dom_CP;
    }

    public function getExpedido_Calle() {
        return $this->Expedido_Calle;
    }

    public function getExpedido_NoExt() {
        return $this->Expedido_NoExt;
    }

    public function getExpedido_NoInt() {
        return $this->Expedido_NoInt;
    }

    public function getExpedido_Col() {
        return $this->Expedido_Col;
    }

    public function getExpedido_Mun() {
        return $this->Expedido_Mun;
    }

    public function getExpedido_Estado() {
        return $this->Expedido_Estado;
    }

    public function getExpedido_Pais() {
        return $this->Expedido_Pais;
    }

    public function getExpedido_CP() {
        return $this->Expedido_CP;
    }

    public function getRegimen() {
        return $this->Regimen;
    }

    public function getReceptor_rfc() {
        return $this->Receptor_rfc;
    }

    public function getReceptor_nombre() {
        return $this->Receptor_nombre;
    }

    public function getReceptor_Dom_Calle() {
        return $this->Receptor_Dom_Calle;
    }

    public function getReceptor_Dom_NoExt() {
        return $this->Receptor_Dom_NoExt;
    }

    public function getReceptor_Dom_NoInt() {
        return $this->Receptor_Dom_NoInt;
    }

    public function getReceptor_Dom_Col() {
        return $this->Receptor_Dom_Col;
    }

    public function getReceptor_Dom_Mun() {
        return $this->Receptor_Dom_Mun;
    }

    public function getReceptor_Dom_Est() {
        return $this->Receptor_Dom_Est;
    }

    public function getReceptor_Dom_Pais() {
        return $this->Receptor_Dom_Pais;
    }

    public function getReceptor_Dom_CP() {
        return $this->Receptor_Dom_CP;
    }

    public function getConceptos() {
        return $this->Conceptos;
    }

    public function getImpuestos_totalImpuestosTrasladados() {
        return $this->Impuestos_totalImpuestosTrasladados;
    }

    public function getImpuestos_Trasladado() {
        return $this->Impuestos_Trasladado;
    }

    public function getXML() {
        return $this->XML;
    }

    public function setXmlns_xsi($xmlns_xsi) {
        $this->xmlns_xsi = $xmlns_xsi;
    }

    public function setXsi_schemaLocation($xsi_schemaLocation) {
        $this->xsi_schemaLocation = $xsi_schemaLocation;
    }

    public function setXmlns_xsd($xmlns_xsd) {
        $this->xmlns_xsd = $xmlns_xsd;
    }

    public function setVersion($version) {
        $this->version = $version;
    }

    public function setFolio($folio) {
        $this->folio = $folio;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setSello($sello) {
        $this->sello = $sello;
    }

    public function setNoAprobacion($noAprobacion) {
        $this->noAprobacion = $noAprobacion;
    }

    public function setAnoAprobacion($anoAprobacion) {
        $this->anoAprobacion = $anoAprobacion;
    }

    public function setFormaDePago($formaDePago) {
        $this->formaDePago = $formaDePago;
    }

    public function setSubTotal($subTotal) {
        $this->subTotal = $subTotal;
    }

    public function setTotal($total) {
        $this->total = $total;
    }

    public function setTipoDeComprobante($tipoDeComprobante) {
        $this->tipoDeComprobante = $tipoDeComprobante;
    }

    public function setMetodoDePago($metodoDePago) {
        $this->metodoDePago = $metodoDePago;
    }

    public function setLugarExpedicion($LugarExpedicion) {
        $this->LugarExpedicion = $LugarExpedicion;
    }

    public function setXmlns($xmlns) {
        $this->xmlns = $xmlns;
    }

    public function setEmisor_rfc($Emisor_rfc) {
        $this->Emisor_rfc = $Emisor_rfc;
    }

    public function setEmisor_nombre($Emisor_nombre) {
        $this->Emisor_nombre = $Emisor_nombre;
    }

    public function setEmisor_Dom_Calle($Emisor_Dom_Calle) {
        $this->Emisor_Dom_Calle = $Emisor_Dom_Calle;
    }

    public function setEmisor_Dom_NoExt($Emisor_Dom_NoExt) {
        $this->Emisor_Dom_NoExt = $Emisor_Dom_NoExt;
    }

    public function setEmisor_Dom_NoInt($Emisor_Dom_NoInt) {
        $this->Emisor_Dom_NoInt = $Emisor_Dom_NoInt;
    }

    public function setEmisor_Dom_Col($Emisor_Dom_Col) {
        $this->Emisor_Dom_Col = $Emisor_Dom_Col;
    }

    public function setEmisor_Dom_Mun($Emisor_Dom_Mun) {
        $this->Emisor_Dom_Mun = $Emisor_Dom_Mun;
    }

    public function setEmisor_Dom_Est($Emisor_Dom_Est) {
        $this->Emisor_Dom_Est = $Emisor_Dom_Est;
    }

    public function setEmisor_Dom_Pais($Emisor_Dom_Pais) {
        $this->Emisor_Dom_Pais = $Emisor_Dom_Pais;
    }

    public function setEmisor_Dom_CP($Emisor_Dom_CP) {
        $this->Emisor_Dom_CP = $Emisor_Dom_CP;
    }

    public function setExpedido_Calle($Expedido_Calle) {
        $this->Expedido_Calle = $Expedido_Calle;
    }

    public function setExpedido_NoExt($Expedido_NoExt) {
        $this->Expedido_NoExt = $Expedido_NoExt;
    }

    public function setExpedido_NoInt($Expedido_NoInt) {
        $this->Expedido_NoInt = $Expedido_NoInt;
    }

    public function setExpedido_Col($Expedido_Col) {
        $this->Expedido_Col = $Expedido_Col;
    }

    public function setExpedido_Mun($Expedido_Mun) {
        $this->Expedido_Mun = $Expedido_Mun;
    }

    public function setExpedido_Estado($Expedido_Estado) {
        $this->Expedido_Estado = $Expedido_Estado;
    }

    public function setExpedido_Pais($Expedido_Pais) {
        $this->Expedido_Pais = $Expedido_Pais;
    }

    public function setExpedido_CP($Expedido_CP) {
        $this->Expedido_CP = $Expedido_CP;
    }

    public function setRegimen($Regimen) {
        $this->Regimen = $Regimen;
    }

    public function setReceptor_rfc($Receptor_rfc) {
        $this->Receptor_rfc = $Receptor_rfc;
    }

    public function setReceptor_nombre($Receptor_nombre) {
        $this->Receptor_nombre = $Receptor_nombre;
    }

    public function setReceptor_Dom_Calle($Receptor_Dom_Calle) {
        $this->Receptor_Dom_Calle = $Receptor_Dom_Calle;
    }

    public function setReceptor_Dom_NoExt($Receptor_Dom_NoExt) {
        $this->Receptor_Dom_NoExt = $Receptor_Dom_NoExt;
    }

    public function setReceptor_Dom_NoInt($Receptor_Dom_NoInt) {
        $this->Receptor_Dom_NoInt = $Receptor_Dom_NoInt;
    }

    public function setReceptor_Dom_Col($Receptor_Dom_Col) {
        $this->Receptor_Dom_Col = $Receptor_Dom_Col;
    }

    public function setReceptor_Dom_Mun($Receptor_Dom_Mun) {
        $this->Receptor_Dom_Mun = $Receptor_Dom_Mun;
    }

    public function setReceptor_Dom_Est($Receptor_Dom_Est) {
        $this->Receptor_Dom_Est = $Receptor_Dom_Est;
    }

    public function setReceptor_Dom_Pais($Receptor_Dom_Pais) {
        $this->Receptor_Dom_Pais = $Receptor_Dom_Pais;
    }

    public function setReceptor_Dom_CP($Receptor_Dom_CP) {
        $this->Receptor_Dom_CP = $Receptor_Dom_CP;
    }

    public function setConceptos($Conceptos) {
        $this->Conceptos = $Conceptos;
    }

    public function setImpuestos_totalImpuestosTrasladados($Impuestos_totalImpuestosTrasladados) {
        $this->Impuestos_totalImpuestosTrasladados = $Impuestos_totalImpuestosTrasladados;
    }

    public function setImpuestos_Trasladado($Impuestos_Trasladado) {
        $this->Impuestos_Trasladado = $Impuestos_Trasladado;
    }

    public function setXML($XML) {
        $this->XML = $XML;
    }
    
    public function getNoOrden() {
        return $this->noOrden;
    }

    public function setNoOrden($noOrden) {
        $this->noOrden = $noOrden;
    }

    public function getNoProveedor() {
        return $this->noProveedor;
    }

    public function setNoProveedor($noProveedor) {
        $this->noProveedor = $noProveedor;
    }

    public function getObsDentroXML() {
        return $this->obsDentroXML;
    }

    public function setObsDentroXML($obsDentroXML) {
        $this->obsDentroXML = $obsDentroXML;
    }

    public function getObsFueraXML() {
        return $this->obsFueraXML;
    }

    public function setObsFueraXML($obsFueraXML) {
        $this->obsFueraXML = $obsFueraXML;
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


}

?>
