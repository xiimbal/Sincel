<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class XMLReadAbraham2 {

    private $xmlns;
    private $xmlns_xsi;
    private $version;
    private $folio;
    private $serie;
    private $fecha;
    private $sello;
    private $orden;
    private $proveedor;
    private $observaciones_dentroXML;
    private $noAprobacion;
    private $anoAprobacion;
    private $formaDePago;
    private $usoCFDI;
    private $subTotal;
    private $total;
    private $tipoDeComprobante;
    private $metodoDePago;
    private $LugarExpedicion;
    private $xsi_schemaLocation;
    private $Emisor_rfc;
    private $Emisor_nombre;
    private $Emisor_Calle;
    private $Emisor_noExterior;
    private $Emisor_noInterior;
    private $Emisor_colonia;
    private $Emisor_Municipio;
    private $Emisor_estado;
    private $Emisor_pais;
    private $Emisor_codigopostal;
    private $Emisor_regimen;
    private $Emisor_Calle_fiscal;
    private $Emisor_noExterior_fiscal;
    private $Emisor_noInterior_fiscal = "";
    private $Emisor_Municipio_fiscal;
    private $Emisor_colonia_fiscal;
    private $Emisor_estado_fiscal;
    private $Emisor_pais_fiscal;
    private $Emisor_codigopostal_fiscal;
    private $Receptor_rfc;
    private $Receptor_nombre;
    private $Receptor_Calle;
    private $Receptor_noExterior;
    private $Receptor_noInterior = "";
    private $Receptor_colonia;
    private $Receptor_Municipio;
    private $Receptor_estado;
    private $Receptor_pais;
    private $Receptor_codigopostal;
    private $conceptos; //[cantidad,unidad,descripcion,valorUnitario,importe]
    private $totalImpuestosTrasladados;
    private $impuesto;
    private $tasa;
    private $importe;

    public function ReadXML($xml) {
        $xml = simplexml_load_string($xml);
        $attrs = $xml->attributes();
        $this->xmlns = $attrs['Xmlns'];
        $this->xmlns_xsi = $attrs['Xmlns:xsi'];
        $this->version = $attrs['Version'];
        $this->folio = $attrs['Folio'];
        $this->serie = $attrs['Serie'];
        $this->fecha = $attrs['Fecha'];
        $this->sello = $attrs['Sello'];
        $this->orden = $attrs['Orden'];
        $this->proveedor = $attrs['Proveedor'];
        $this->observaciones_dentroXML = $attrs['observaciones_dentroXML'];
        $this->observaciones_fueraXML = $attrs['observaciones_fueraXML'];
       
        $this->formaDePago = $attrs['FormaPago'];
        
        $this->subTotal = $attrs['SubTotal'];
        $this->total = $attrs['Total'];
        $this->tipoDeComprobante = $attrs['TipoDeComprobante'];
        $this->metodoDePago = $attrs['MetodoPago'];
        $this->LugarExpedicion = $attrs['LugarExpedicion'];
        $this->xsi_schemaLocation = $attrs['xsi:schemaLocation'];
        $attrs = $xml->Emisor->attributes();
        if (isset($attrs) && count($attrs) > 0) {
            $this->Emisor_rfc = $attrs['Rfc'];
            $this->Emisor_nombre = $attrs['Nombre'];
        } else {
            return false;
        }
        $attrs = $xml->Emisor->DomicilioFiscal->attributes();
        $this->Emisor_Calle = $attrs['Calle'];
        $this->Emisor_Calle_fiscal =$attrs['Calle'];
        $this->Emisor_noExterior = $attrs['NoExterior'];
        $this->Emisor_noExterior_fiscal =$attrs['NoExterior'];
        if ($attrs['noInterior']) {
            $this->Emisor_noInterior = $attrs['NoInterior'];
            $this->Emisor_noInterior_fiscal = $attrs['NoInterior'];
        }
        $this->Emisor_colonia = $attrs['Colonia'];
        $this->Emisor_colonia_fiscal = $attrs['Colonia'];
        $this->Emisor_Municipio = $attrs['Municipio'];
        $this->Emisor_Municipio_fiscal = $attrs['Municipio'];
        $this->Emisor_estado = $attrs['Estado'];
        $this->Emisor_estado_fiscal = $attrs['Estado'];
        $this->Emisor_pais = $attrs['Pais'];
        $this->Emisor_pais_fiscal = $attrs['Pais'];
        $this->Emisor_codigopostal = $attrs['CodigoPostal'];
        $this->Emisor_codigopostal_fiscal = $attrs['CodigoPostal'];

        $attrs = $xml->Receptor->attributes();
        $this->Receptor_rfc = $attrs['Rfc'];
        $this->Receptor_nombre = $attrs['Nombre'];
        $this->usoCFDI = $attrs['UsoCFDI'];
        $attrs = $xml->Receptor->Domicilio->attributes();
        $this->Receptor_Calle = $attrs['Calle'];
        $this->Receptor_noExterior = $attrs['NoExterior'];
        if ($attrs['noInterior']) {
            $this->Receptor_noInterior = $attrs['noInterior'];
        }
        $this->Receptor_colonia = $attrs['colonia'];
        $this->Receptor_Municipio = $attrs['municipio'];
        $this->Receptor_estado = $attrs['estado'];
        $this->Receptor_pais = $attrs['pais'];
        $this->Receptor_codigopostal = $attrs['codigoPostal'];
        $this->conceptos = Array();
        foreach ($xml->Conceptos->children() as $a => $b) {
            $attrs = $b->attributes();
            $array = Array((float)$attrs['Cantidad'], $attrs['ClaveProdServ'], $attrs['ClaveUnidad'],  $attrs['Unidad'], $attrs['Descripcion'], (float)$attrs['ValorUnitario'], (float)$attrs['Importe']);
            //echo $attrs['cantidad'].",,".$attrs['unidad'].",,".$attrs['descripcion'].",,".$attrs['valorUnitario'].",,".$attrs['importe'];
            array_push($this->conceptos, $array);
        }
        $attrs = $xml->Impuestos->Traslados->Traslado->attributes();
        return true;
    }

    public function getReceptor_colonia() {
        return $this->Receptor_colonia;
    }

    public function setReceptor_colonia($Receptor_colonia) {
        $this->Receptor_colonia = $Receptor_colonia;
    }

    public function getEmisor_colonia() {
        return $this->Emisor_colonia;
    }

    public function getEmisor_colonia_fiscal() {
        return $this->Emisor_colonia_fiscal;
    }

    public function setEmisor_colonia($Emisor_colonia) {
        $this->Emisor_colonia = $Emisor_colonia;
    }

    public function setEmisor_colonia_fiscal($Emisor_colonia_fiscal) {
        $this->Emisor_colonia_fiscal = $Emisor_colonia_fiscal;
    }

    public function getXmlns() {
        return $this->xmlns;
    }

    public function getXmlns_xsi() {
        return $this->xmlns_xsi;
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

    public function getOrden() {
        return $this->orden;
    }

    public function getProveedor() {
        return $this->proveedor;
    }

    public function getObservaciones_dentroXML() {
        return $this->observaciones_dentroXML;
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

    public function getXsi_schemaLocation() {
        return $this->xsi_schemaLocation;
    }

    public function getEmisor_rfc() {
        return $this->Emisor_rfc;
    }

    public function getEmisor_nombre() {
        return $this->Emisor_nombre;
    }

    public function getEmisor_Calle() {
        return $this->Emisor_Calle;
    }

    public function getEmisor_noExterior() {
        return $this->Emisor_noExterior;
    }

    public function getEmisor_noInterior() {
        return $this->Emisor_noInterior;
    }

    public function getEmisor_Municipio() {
        return $this->Emisor_Municipio;
    }

    public function getEmisor_estado() {
        return $this->Emisor_estado;
    }

    public function getEmisor_pais() {
        return $this->Emisor_pais;
    }

    public function getEmisor_codigopostal() {
        return $this->Emisor_codigopostal;
    }

    public function getEmisor_regimen() {
        return $this->Emisor_regimen;
    }

    public function getEmisor_Calle_fiscal() {
        return $this->Emisor_Calle_fiscal;
    }

    public function getEmisor_noExterior_fiscal() {
        return $this->Emisor_noExterior_fiscal;
    }

    public function getEmisor_noInterior_fiscal() {
        return $this->Emisor_noInterior_fiscal;
    }

    public function getEmisor_Municipio_fiscal() {
        return $this->Emisor_Municipio_fiscal;
    }

    public function getEmisor_estado_fiscal() {
        return $this->Emisor_estado_fiscal;
    }

    public function getEmisor_pais_fiscal() {
        return $this->Emisor_pais_fiscal;
    }

    public function getEmisor_codigopostal_fiscal() {
        return $this->Emisor_codigopostal_fiscal;
    }

    public function getReceptor_rfc() {
        return $this->Receptor_rfc;
    }

    public function getReceptor_nombre() {
        return $this->Receptor_nombre;
    }

    public function getReceptor_Calle() {
        return $this->Receptor_Calle;
    }

    public function getReceptor_noExterior() {
        return $this->Receptor_noExterior;
    }

    public function getReceptor_noInterior() {
        return $this->Receptor_noInterior;
    }

    public function getReceptor_Municipio() {
        return $this->Receptor_Municipio;
    }

    public function getReceptor_estado() {
        return $this->Receptor_estado;
    }

    public function getReceptor_pais() {
        return $this->Receptor_pais;
    }

    public function getReceptor_codigopostal() {
        return $this->Receptor_codigopostal;
    }

    public function getConceptos() {
        return $this->conceptos;
    }

    public function getTotalImpuestosTrasladados() {
        return $this->totalImpuestosTrasladados;
    }

    public function getImpuesto() {
        return $this->impuesto;
    }

    public function getTasa() {
        return $this->tasa;
    }

    public function getImporte() {
        return $this->importe;
    }

    public function setXmlns($xmlns) {
        $this->xmlns = $xmlns;
    }

    public function setXmlns_xsi($xmlns_xsi) {
        $this->xmlns_xsi = $xmlns_xsi;
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

    public function setOrden($orden) {
        $this->orden = $orden;
    }

    public function setProveedor($proveedor) {
        $this->proveedor = $proveedor;
    }

    public function setObservaciones_dentroXML($observaciones_dentroXML) {
        $this->observaciones_dentroXML = $observaciones_dentroXML;
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

    public function setXsi_schemaLocation($xsi_schemaLocation) {
        $this->xsi_schemaLocation = $xsi_schemaLocation;
    }

    public function setEmisor_rfc($Emisor_rfc) {
        $this->Emisor_rfc = $Emisor_rfc;
    }

    public function setEmisor_nombre($Emisor_nombre) {
        $this->Emisor_nombre = $Emisor_nombre;
    }

    public function setEmisor_Calle($Emisor_Calle) {
        $this->Emisor_Calle = $Emisor_Calle;
    }

    public function setEmisor_noExterior($Emisor_noExterior) {
        $this->Emisor_noExterior = $Emisor_noExterior;
    }

    public function setEmisor_noInterior($Emisor_noInterior) {
        $this->Emisor_noInterior = $Emisor_noInterior;
    }

    public function setEmisor_Municipio($Emisor_Municipio) {
        $this->Emisor_Municipio = $Emisor_Municipio;
    }

    public function setEmisor_estado($Emisor_estado) {
        $this->Emisor_estado = $Emisor_estado;
    }

    public function setEmisor_pais($Emisor_pais) {
        $this->Emisor_pais = $Emisor_pais;
    }

    public function setEmisor_codigopostal($Emisor_codigopostal) {
        $this->Emisor_codigopostal = $Emisor_codigopostal;
    }

    public function setEmisor_regimen($Emisor_regimen) {
        $this->Emisor_regimen = $Emisor_regimen;
    }

    public function setEmisor_Calle_fiscal($Emisor_Calle_fiscal) {
        $this->Emisor_Calle_fiscal = $Emisor_Calle_fiscal;
    }

    public function setEmisor_noExterior_fiscal($Emisor_noExterior_fiscal) {
        $this->Emisor_noExterior_fiscal = $Emisor_noExterior_fiscal;
    }

    public function setEmisor_noInterior_fiscal($Emisor_noInterior_fiscal) {
        $this->Emisor_noInterior_fiscal = $Emisor_noInterior_fiscal;
    }

    public function setEmisor_Municipio_fiscal($Emisor_Municipio_fiscal) {
        $this->Emisor_Municipio_fiscal = $Emisor_Municipio_fiscal;
    }

    public function setEmisor_estado_fiscal($Emisor_estado_fiscal) {
        $this->Emisor_estado_fiscal = $Emisor_estado_fiscal;
    }

    public function setEmisor_pais_fiscal($Emisor_pais_fiscal) {
        $this->Emisor_pais_fiscal = $Emisor_pais_fiscal;
    }

    public function setEmisor_codigopostal_fiscal($Emisor_codigopostal_fiscal) {
        $this->Emisor_codigopostal_fiscal = $Emisor_codigopostal_fiscal;
    }

    public function setReceptor_rfc($Receptor_rfc) {
        $this->Receptor_rfc = $Receptor_rfc;
    }

    public function setReceptor_nombre($Receptor_nombre) {
        $this->Receptor_nombre = $Receptor_nombre;
    }

    public function setReceptor_Calle($Receptor_Calle) {
        $this->Receptor_Calle = $Receptor_Calle;
    }

    public function setReceptor_noExterior($Receptor_noExterior) {
        $this->Receptor_noExterior = $Receptor_noExterior;
    }

    public function setReceptor_noInterior($Receptor_noInterior) {
        $this->Receptor_noInterior = $Receptor_noInterior;
    }

    public function setReceptor_Municipio($Receptor_Municipio) {
        $this->Receptor_Municipio = $Receptor_Municipio;
    }

    public function setReceptor_estado($Receptor_estado) {
        $this->Receptor_estado = $Receptor_estado;
    }

    public function setReceptor_pais($Receptor_pais) {
        $this->Receptor_pais = $Receptor_pais;
    }

    public function setReceptor_codigopostal($Receptor_codigopostal) {
        $this->Receptor_codigopostal = $Receptor_codigopostal;
    }

    public function setConceptos($conceptos) {
        $this->conceptos = $conceptos;
    }

    public function setTotalImpuestosTrasladados($totalImpuestosTrasladados) {
        $this->totalImpuestosTrasladados = $totalImpuestosTrasladados;
    }

    public function setImpuesto($impuesto) {
        $this->impuesto = $impuesto;
    }

    public function setTasa($tasa) {
        $this->tasa = $tasa;
    }

    public function setImporte($importe) {
        $this->importe = $importe;
    }

    function getSerie() {
        return $this->serie;
    }

    function setSerie($serie) {
        $this->serie = $serie;
    }
    function getUsoCFDI() {
        return $this->usoCFDI;
    }

    function setUsoCFDI($usoCFDI) {
        $this->usoCFDI = $usoCFDI;
    }


}
