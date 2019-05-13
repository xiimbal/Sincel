<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class UnidadMedidaSAT {
    
    private $IdUnidadMedida;
    private $ClaveUnidad;
    private $UnidadMedida;
    private $Simbolo;
    private $Servicio;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $tabla = "c_unidadmedidaSAT";
  
    
    public function getRegistroByClaveUnidad() {
        $consulta = ("SELECT * FROM $this->tabla WHERE ClaveUnidad = '$this->ClaveUnidad';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->IdUnidadMedida = $rs['IdUnidadMedida'];
            $this->UnidadMedida = $rs['UnidadMedida'];
            $this->Simbolo = $rs['Simbolo'];
            $this->Servicio = $rs['Servicio'];
            $this->Activo = $rs['Activo'];
            return true;
        }
        return false;
    }

    function getIdUnidadMedida() {
        return $this->IdUnidadMedida;
    }

    function getClaveUnidad() {
        return $this->ClaveUnidad;
    }

    function getUnidadMedida() {
        return $this->UnidadMedida;
    }

    function getSimbolo() {
        return $this->Simbolo;
    }

    function getServicio() {
        return $this->Servicio;
    }

    function getActivo() {
        return $this->Activo;
    }

    function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    function getPantalla() {
        return $this->Pantalla;
    }

    function setIdUnidadMedida($IdUnidadMedida) {
        $this->IdUnidadMedida = $IdUnidadMedida;
    }

    function setClaveUnidad($ClaveUnidad) {
        $this->ClaveUnidad = $ClaveUnidad;
    }

    function setUnidadMedida($UnidadMedida) {
        $this->UnidadMedida = $UnidadMedida;
    }

    function setSimbolo($Simbolo) {
        $this->Simbolo = $Simbolo;
    }

    function setServicio($Servicio) {
        $this->Servicio = $Servicio;
    }

    function setActivo($Activo) {
        $this->Activo = $Activo;
    }

    function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

}
