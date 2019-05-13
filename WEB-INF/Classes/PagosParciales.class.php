<?php
include_once("Conexion.class.php");
include_once("Catalogo.class.php");

/**
 * Description of PagosParciales
 *
 * @author MAGG
 */
class PagosParciales {

    private $IdPagoParcial;
    private $IdFactura;
    private $Folio;
    private $ImportePagado;
    private $ImportePorPagar;
    private $FechaPago;
    private $Referencia;
    private $Observaciones;
    private $UsuarioCreacion;
    private $FechaCreacion;
    
    public function newRegistro(){          
        $consulta = "INSERT INTO c_pagosparciales(IdPagoParcial, IdFactura, Folio, ImportePagado, ImportePorPagar, FechaPago, Referencia, Observaciones, UsuarioCreacion, 
            FechaCreacion) VALUES(0,'$this->IdFactura','$this->Folio','$this->ImportePagado','$this->ImportePorPagar',NOW(),'$this->Referencia',
                '$this->Observaciones', '$this->UsuarioCreacion',NOW());";        
        $catalogo = new Catalogo(); $this->IdPagoParcial = $catalogo->insertarRegistro($consulta);      
        if ($this->IdPagoParcial!=NULL && $this->IdPagoParcial !=0) {                        
            return true;
        }        
        return false;
    }
    
    public function getIdPagoParcial() {
        return $this->IdPagoParcial;
    }

    public function setIdPagoParcial($IdPagoParcial) {
        $this->IdPagoParcial = $IdPagoParcial;
    }

    public function getIdFactura() {
        return $this->IdFactura;
    }

    public function setIdFactura($IdFactura) {
        $this->IdFactura = $IdFactura;
    }

    public function getFolio() {
        return $this->Folio;
    }

    public function setFolio($Folio) {
        $this->Folio = $Folio;
    }

    public function getImportePagado() {
        return $this->ImportePagado;
    }

    public function setImportePagado($ImportePagado) {
        $this->ImportePagado = $ImportePagado;
    }

    public function getImportePorPagar() {
        return $this->ImportePorPagar;
    }

    public function setImportePorPagar($ImportePorPagar) {
        $this->ImportePorPagar = $ImportePorPagar;
    }

    public function getFechaPago() {
        return $this->FechaPago;
    }

    public function setFechaPago($FechaPago) {
        $this->FechaPago = $FechaPago;
    }

    public function getReferencia() {
        return $this->Referencia;
    }

    public function setReferencia($Referencia) {
        $this->Referencia = $Referencia;
    }

    public function getObservaciones() {
        return $this->Observaciones;
    }

    public function setObservaciones($Observaciones) {
        $this->Observaciones = $Observaciones;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    public function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

}

?>
