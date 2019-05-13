<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class SolicitudRetiroGeneral {

    private $IdSolicitudRetiroGeneral;
    private $Aceptada;
    private $Clave;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $Contestado;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaAutorizacion;
    private $FechaReporte;
    private $TipoReporte;
    private $Causa_Movimiento;
    private $empresa;
    
    public function newRegistro() {        
        $nombres = "Aceptada,Clave,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Contestado,Activo,UsuarioCreacion,FechaReporte,TipoReporte,Causa_Movimiento";
        $values = "'" . $this->Aceptada . "','" . $this->Clave . "',NOW(),'" . $this->UsuarioUltimaModificacion . "',NOW(),'$this->Pantalla','$this->Contestado','$this->Activo','" . $this->UsuarioCreacion . "','$this->FechaReporte','$this->TipoReporte','$this->Causa_Movimiento'";
        $consulta = ("INSERT INTO c_solictudretirogeneral(" . $nombres . ")
            VALUES(" . $values . ")");
        $catalogo = new Catalogo(); 
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->IdSolicitudRetiroGeneral = $catalogo->insertarRegistro($consulta);
        if ($this->IdSolicitudRetiroGeneral!= NULL && $this->IdSolicitudRetiroGeneral!=0) {            
            return true;
        }        
        return false;
    }

    public function getCausa_Movimiento() {
        return $this->Causa_Movimiento;
    }

    public function setCausa_Movimiento($Causa_Movimiento) {
        $this->Causa_Movimiento = $Causa_Movimiento;
    }

    public function getFechaReporte() {
        return $this->FechaReporte;
    }

    public function getTipoReporte() {
        return $this->TipoReporte;
    }

    public function setFechaReporte($FechaReporte) {
        $this->FechaReporte = $FechaReporte;
    }

    public function setTipoReporte($TipoReporte) {
        $this->TipoReporte = $TipoReporte;
    }

    public function getFechaAutorizacion() {
        return $this->FechaAutorizacion;
    }

    public function setFechaAutorizacion($FechaAutorizacion) {
        $this->FechaAutorizacion = $FechaAutorizacion;
    }

    public function getIdSolicitudRetiroGeneral() {
        return $this->IdSolicitudRetiroGeneral;
    }

    public function getAceptada() {
        return $this->Aceptada;
    }

    public function getClave() {
        return $this->Clave;
    }

    public function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    public function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    public function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    public function getPantalla() {
        return $this->Pantalla;
    }

    public function getContestado() {
        return $this->Contestado;
    }

    public function getActivo() {
        return $this->Activo;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function setIdSolicitudRetiroGeneral($IdSolicitudRetiroGeneral) {
        $this->IdSolicitudRetiroGeneral = $IdSolicitudRetiroGeneral;
    }

    public function setAceptada($Aceptada) {
        $this->Aceptada = $Aceptada;
    }

    public function setClave($Clave) {
        $this->Clave = $Clave;
    }

    public function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    public function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    public function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    public function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

    public function setContestado($Contestado) {
        $this->Contestado = $Contestado;
    }

    public function setActivo($Activo) {
        $this->Activo = $Activo;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
}
