<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class SolicitudRetiro {

    private $IdSolicitudRetiro;
    private $IdSolicitudRetiroGeneral;
    private $IdBitacora;
    private $ClaveLocalidad;
    private $IdAlmacen;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $id_lectura;
    private $empresa;

    public function newRegistro() {        
        $nombres = "IdSolicitudRetiroGeneral,IdBitacora,ClaveLocalidad,IdAlmacen,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,id_lectura";
        $values = "'" . $this->IdSolicitudRetiroGeneral . "','" . $this->IdBitacora . "','$this->ClaveLocalidad','" . $this->IdAlmacen . "','$this->Activo','$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'" . $this->Pantalla . "','$this->id_lectura'";
        $consulta = ("INSERT INTO c_solicitudretiro(" . $nombres . ")
            VALUES(" . $values . ")");
        $catalogo = new Catalogo(); 
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->IdSolicitudRetiro = $catalogo->insertarRegistro($consulta);
        if ($this->IdSolicitudRetiro!= NULL && $this->IdSolicitudRetiro!=0) {            
            return true;
        }        
        return false;
    }

    public function getId_lectura() {
        return $this->id_lectura;
    }

    public function setId_lectura($id_lectura) {
        $this->id_lectura = $id_lectura;
    }

    public function getIdSolicitudRetiro() {
        return $this->IdSolicitudRetiro;
    }

    public function getIdSolicitudRetiroGeneral() {
        return $this->IdSolicitudRetiroGeneral;
    }

    public function getIdBitacora() {
        return $this->IdBitacora;
    }

    public function getClaveLocalidad() {
        return $this->ClaveLocalidad;
    }

    public function getIdAlmacen() {
        return $this->IdAlmacen;
    }

    public function getActivo() {
        return $this->Activo;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
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

    public function setIdSolicitudRetiro($IdSolicitudRetiro) {
        $this->IdSolicitudRetiro = $IdSolicitudRetiro;
    }

    public function setIdSolicitudRetiroGeneral($IdSolicitudRetiroGeneral) {
        $this->IdSolicitudRetiroGeneral = $IdSolicitudRetiroGeneral;
    }

    public function setIdBitacora($IdBitacora) {
        $this->IdBitacora = $IdBitacora;
    }

    public function setClaveLocalidad($ClaveLocalidad) {
        $this->ClaveLocalidad = $ClaveLocalidad;
    }

    public function setIdAlmacen($IdAlmacen) {
        $this->IdAlmacen = $IdAlmacen;
    }

    public function setActivo($Activo) {
        $this->Activo = $Activo;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
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

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }


}
