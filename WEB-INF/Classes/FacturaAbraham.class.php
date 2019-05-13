<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once("ConexionFacturacion.class.php");
include_once("CatalogoFacturacion.class.php");

class FacturaAbraham {

    private $IdFactura;
    private $RFCReceptor;
    private $RFCEmisor;
    private $NombreReceptor;
    private $NombreEmisor;
    private $FechaFacturacion;
    private $Folio;
    private $Serie;
    private $FacturaXML;
    private $PathXML;
    private $PeriodoFacturacion;
    private $EstadoFactura;
    private $FechaModificacion;
    private $TipoComprobante;
    private $IdFacturaRelacion;
    private $PathPDF;
    private $FacturaEnviada;
    private $Observaciones;
    private $FacturaPagada;
    private $FechaPago;
    private $NumTransaccion;
    private $CentrosCosto;
    private $Descuento;
    private $Total;
    private $cfdiXML;
    private $cfdiTimbrado;
    private $cfdiRespPac;
    private $folioFiscal;
    private $EstatusFactura;
    private $CanceladaSAT;
    private $PendienteCancelar;
    private $UsuarioCreacion;
    private $UsuarioModificacion;
    private $UsuarioEnvio;
    private $FechaEnvio;
    private $TipoFactura;
    private $TipoArrendamiento;
    private $IdSerie;
    private $diasCredito;
    private $IdUsoCFDI;
    private $CFDI33;
    private $FormaPago;
    private $MetodoPago;
    private $IdTipoMoneda;
    private $TipoCambio;
    
    public function newRegistro() {
        //$this->conn = new ConexionFacturacion();
        $catalogo = new CatalogoFacturacion();
        //$this->cfdiXML = $this->string_to_ascii($this->cfdiXML);
        $this->NombreReceptor = str_replace("'", "´", $this->NombreReceptor);
        $this->FacturaXML = str_replace("'", "´", $this->FacturaXML);
        $this->cfdiXML = str_replace("'", "´", $this->cfdiXML);
        $this->cfdiTimbrado = str_replace("'", "´", $this->cfdiTimbrado);
        
        $this->cfdiXML = '';
        $this->cfdiTimbrado = $this->string_to_ascii($this->cfdiTimbrado);
        $this->FacturaXML = $this->string_to_ascii($this->FacturaXML);

        if (!isset($this->TipoArrendamiento) || $this->TipoArrendamiento == "") {
            $this->TipoArrendamiento = "NULL";
        }        
        if (!isset($this->IdSerie) || $this->IdSerie == "") {
            $this->IdSerie = "NULL";
        }
        if (!isset($this->diasCredito) || $this->diasCredito == "") {
            $this->diasCredito = "NULL";
        }
        if (!isset($this->IdUsoCFDI) || $this->IdUsoCFDI == "") {
            $this->IdUsoCFDI = "NULL";
        }
        if (!isset($this->CFDI33) || $this->CFDI33 == "") {
            $this->CFDI33 = "NULL";
        }
        if (!isset($this->FormaPago) || $this->FormaPago == "") {
            $this->FormaPago = "NULL";
        }
        if (!isset($this->MetodoPago) || $this->MetodoPago == "") {
            $this->MetodoPago = "NULL";
        }
        if (!isset($this->Descuento) || empty($this->Descuento)) {
            $this->Descuento = "NULL";
        }
        
        if (!isset($this->IdTipoMoneda) || $this->IdTipoMoneda == "") {
            $this->IdTipoMoneda = "1";
        }
        
        if (!isset($this->TipoCambio) || $this->TipoCambio == "") {
            $this->TipoCambio = "1";
        }
        
        $consulta = 'INSERT INTO c_factura(RFCReceptor,RFCEmisor,NombreReceptor,NombreEmisor,FechaFacturacion,Folio,Serie,FacturaXML,PathXML,
            PeriodoFacturacion,EstadoFactura,FechaModificacion,TipoComprobante,IdFacturaRelacion,PathPDF,FacturaEnviada,Observaciones,
            FacturaPagada,FechaPago,NumTransaccion,CentrosCosto,Descuento,Total,cfdiXML,cfdiTimbrado,cfdiRespPac,folioFiscal,EstatusFactura,CanceladaSAT,
            PendienteCancelar,UsuarioCreacion,UsuarioModificacion,UsuarioEnvio,FechaEnvio,TipoFactura,TipoArrendamiento, IdSerie,DiasCredito,
            IdUsoCFDI,CFDI33,MetodoPago,FormaPago,IdTipoMoneda,TipoCambio) 
            VALUES(\'' . $this->RFCReceptor . '\',\'' . $this->RFCEmisor . '\',\'' . $this->NombreReceptor . '\',\'' . $this->NombreEmisor . '\',NOW(),\'' . $this->Folio . '\',
                \'' . $this->Serie . '\',\'' . $this->FacturaXML . '\',\'' . $this->PathXML . '\',\'' . $this->PeriodoFacturacion . '\',' . $this->EstadoFactura . ',NOW(),\'' . $this->TipoComprobante . '\',
                ' . $this->IdFacturaRelacion . ',\'' . $this->PathPDF . '\',' . $this->FacturaEnviada . ',\'' . $this->Observaciones . '\',' . $this->FacturaPagada . ',
                null,null,\'' . $this->CentrosCosto . '\',\'' . $this->Descuento . '\',\'' . $this->Total . '\',\'' . $this->cfdiXML . '\',\'' . $this->cfdiTimbrado . '\',\'\',
                \'' . $this->folioFiscal . '\',null,' . $this->CanceladaSAT . ',\'' . $this->PendienteCancelar . '\',\'' . $this->UsuarioCreacion . '\',\'' . $this->UsuarioModificacion . '\',
                null,null,' . $this->TipoFactura . ',' . $this->TipoArrendamiento . ',' . $this->IdSerie . ', ' . $this->diasCredito.','.$this->IdUsoCFDI.','.$this->CFDI33.',\'' .$this->MetodoPago.'\','
                . '\'' .$this->FormaPago.'\','.$this->IdTipoMoneda.','.$this->TipoCambio.');';
        //echo $consulta;
        $id = $catalogo->insertarRegistro($consulta);
        if ($id != null && $id != "0") {
            $this->IdFactura = $id;
            return true;
        }
        return false;
    }

    function string_to_ascii($string) {
        $aux = "";
        $ascii = NULL;
        for ($i = 0; $i < strlen($string); $i++) {
            $ascii = ord($string[$i]);
            if ($ascii >= 32 && $ascii != 243) {
                $aux .= $string[$i];
            }
        }
        return $aux;
    }

    public function getIdFactura() {
        return $this->IdFactura;
    }

    public function getRFCReceptor() {
        return $this->RFCReceptor;
    }

    public function getRFCEmisor() {
        return $this->RFCEmisor;
    }

    public function getNombreReceptor() {
        return $this->NombreReceptor;
    }

    public function getNombreEmisor() {
        return $this->NombreEmisor;
    }

    public function getFechaFacturacion() {
        return $this->FechaFacturacion;
    }

    public function getFolio() {
        return $this->Folio;
    }

    public function getSerie() {
        return $this->Serie;
    }

    public function getFacturaXML() {
        return $this->FacturaXML;
    }

    public function getPathXML() {
        return $this->PathXML;
    }

    public function getPeriodoFacturacion() {
        return $this->PeriodoFacturacion;
    }

    public function getEstadoFactura() {
        return $this->EstadoFactura;
    }

    public function getFechaModificacion() {
        return $this->FechaModificacion;
    }

    public function getTipoComprobante() {
        return $this->TipoComprobante;
    }

    public function getIdFacturaRelacion() {
        return $this->IdFacturaRelacion;
    }

    public function getPathPDF() {
        return $this->PathPDF;
    }

    public function getFacturaEnviada() {
        return $this->FacturaEnviada;
    }

    public function getObservaciones() {
        return $this->Observaciones;
    }

    public function getFacturaPagada() {
        return $this->FacturaPagada;
    }

    public function getFechaPago() {
        return $this->FechaPago;
    }

    public function getNumTransaccion() {
        return $this->NumTransaccion;
    }

    public function getCentrosCosto() {
        return $this->CentrosCosto;
    }

    public function getTotal() {
        return $this->Total;
    }

    public function getCfdiXML() {
        return $this->cfdiXML;
    }

    public function getCfdiTimbrado() {
        return $this->cfdiTimbrado;
    }

    public function getCfdiRespPac() {
        return $this->cfdiRespPac;
    }

    public function getFolioFiscal() {
        return $this->folioFiscal;
    }

    public function getEstatusFactura() {
        return $this->EstatusFactura;
    }

    public function getCanceladaSAT() {
        return $this->CanceladaSAT;
    }

    public function getPendienteCancelar() {
        return $this->PendienteCancelar;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->UsuarioModificacion;
    }

    public function getUsuarioEnvio() {
        return $this->UsuarioEnvio;
    }

    public function getFechaEnvio() {
        return $this->FechaEnvio;
    }

    public function getTipoFactura() {
        return $this->TipoFactura;
    }

    public function setIdFactura($IdFactura) {
        $this->IdFactura = $IdFactura;
    }

    public function setRFCReceptor($RFCReceptor) {
        $this->RFCReceptor = $RFCReceptor;
    }

    public function setRFCEmisor($RFCEmisor) {
        $this->RFCEmisor = $RFCEmisor;
    }

    public function setNombreReceptor($NombreReceptor) {
        $this->NombreReceptor = $NombreReceptor;
    }

    public function setNombreEmisor($NombreEmisor) {
        $this->NombreEmisor = $NombreEmisor;
    }

    public function setFechaFacturacion($FechaFacturacion) {
        $this->FechaFacturacion = $FechaFacturacion;
    }

    public function setFolio($Folio) {
        $this->Folio = $Folio;
    }

    public function setSerie($Serie) {
        $this->Serie = $Serie;
    }

    public function setFacturaXML($FacturaXML) {
        $this->FacturaXML = $FacturaXML;
    }

    public function setPathXML($PathXML) {
        $this->PathXML = $PathXML;
    }

    public function setPeriodoFacturacion($PeriodoFacturacion) {
        $this->PeriodoFacturacion = $PeriodoFacturacion;
    }

    public function setEstadoFactura($EstadoFactura) {
        $this->EstadoFactura = $EstadoFactura;
    }

    public function setFechaModificacion($FechaModificacion) {
        $this->FechaModificacion = $FechaModificacion;
    }

    public function setTipoComprobante($TipoComprobante) {
        $this->TipoComprobante = $TipoComprobante;
    }

    public function setIdFacturaRelacion($IdFacturaRelacion) {
        $this->IdFacturaRelacion = $IdFacturaRelacion;
    }

    public function setPathPDF($PathPDF) {
        $this->PathPDF = $PathPDF;
    }

    public function setFacturaEnviada($FacturaEnviada) {
        $this->FacturaEnviada = $FacturaEnviada;
    }

    public function setObservaciones($Observaciones) {
        $this->Observaciones = $Observaciones;
    }

    public function setFacturaPagada($FacturaPagada) {
        $this->FacturaPagada = $FacturaPagada;
    }

    public function setFechaPago($FechaPago) {
        $this->FechaPago = $FechaPago;
    }

    public function setNumTransaccion($NumTransaccion) {
        $this->NumTransaccion = $NumTransaccion;
    }

    public function setCentrosCosto($CentrosCosto) {
        $this->CentrosCosto = $CentrosCosto;
    }

    public function setTotal($Total) {
        $this->Total = $Total;
    }

    public function setCfdiXML($cfdiXML) {
        $this->cfdiXML = $cfdiXML;
    }

    public function setCfdiTimbrado($cfdiTimbrado) {
        $this->cfdiTimbrado = $cfdiTimbrado;
    }

    public function setCfdiRespPac($cfdiRespPac) {
        $this->cfdiRespPac = $cfdiRespPac;
    }

    public function setFolioFiscal($folioFiscal) {
        $this->folioFiscal = $folioFiscal;
    }

    public function setEstatusFactura($EstatusFactura) {
        $this->EstatusFactura = $EstatusFactura;
    }

    public function setCanceladaSAT($CanceladaSAT) {
        $this->CanceladaSAT = $CanceladaSAT;
    }

    public function setPendienteCancelar($PendienteCancelar) {
        $this->PendienteCancelar = $PendienteCancelar;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function setUsuarioModificacion($UsuarioModificacion) {
        $this->UsuarioModificacion = $UsuarioModificacion;
    }

    public function setUsuarioEnvio($UsuarioEnvio) {
        $this->UsuarioEnvio = $UsuarioEnvio;
    }

    public function setFechaEnvio($FechaEnvio) {
        $this->FechaEnvio = $FechaEnvio;
    }

    public function setTipoFactura($TipoFactura) {
        $this->TipoFactura = $TipoFactura;
    }

    public function getTipoArrendamiento() {
        return $this->TipoArrendamiento;
    }

    public function setTipoArrendamiento($TipoArrendamiento) {
        $this->TipoArrendamiento = $TipoArrendamiento;
    }

    function getIdSerie() {
        return $this->IdSerie;
    }

    function setIdSerie($IdSerie) {
        $this->IdSerie = $IdSerie;
    }

    function getDiasCredito() {
        return $this->diasCredito;
    }

    function setDiasCredito($diasCredito) {
        $this->diasCredito = $diasCredito;
    }
    
    function getIdUsoCFDI() {
        return $this->IdUsoCFDI;
    }

    function getCFDI33() {
        return $this->CFDI33;
    }

    function setIdUsoCFDI($IdUsoCFDI) {
        $this->IdUsoCFDI = $IdUsoCFDI;
    }

    function setCFDI33($CFDI33) {
        $this->CFDI33 = $CFDI33;
    }
    
    function getFormaPago() {
        return $this->FormaPago;
    }

    function getMetodoPago() {
        return $this->MetodoPago;
    }

    function setFormaPago($FormaPago) {
        $this->FormaPago = $FormaPago;
    }

    function setMetodoPago($MetodoPago) {
        $this->MetodoPago = $MetodoPago;
    }

    function getDescuento() {
        return $this->Descuento;
    }

    function setDescuento($Descuento) {
        $this->Descuento = $Descuento;
    }

    function getIdTipoMoneda() {
        return $this->IdTipoMoneda;
    }

    function getTipoCambio() {
        return $this->TipoCambio;
    }

    function setIdTipoMoneda($IdTipoMoneda) {
        $this->IdTipoMoneda = $IdTipoMoneda;
    }

    function setTipoCambio($TipoCambio) {
        $this->TipoCambio = $TipoCambio;
    }


}
