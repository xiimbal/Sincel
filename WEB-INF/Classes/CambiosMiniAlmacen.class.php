<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CambiosMiniAlmacen
 *
 * @author MiguelÁngel
 */
class CambiosMiniAlmacen {

    private $IdCambio;
    private $IdAlmacen;
    private $NoParte;
    private $Fecha;
    private $ExistenciaAnterior;
    private $ApartadoAnterior;
    private $MinimoAnterior;
    private $MaximoAnterior;
    private $UbicacionAnterior;
    private $ExistenciaNuevo;
    private $ApartadoNuevo;
    private $MinimoNuevo;
    private $MaximoNuevo;
    private $UbicacionNuevo;
    private $Comentario;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    
    public function newRegistro() {        
        $consulta = ("INSERT INTO k_cambiosminialmacen(IdCambio,IdAlmacen,NoParte,Fecha,
            ExistenciaAnterior,ApartadoAnterior,MinimoAnterior,MaximoAnterior,UbicacionAnterior, 
            ExistenciaNuevo,ApartadoNuevo,MinimoNuevo,MaximoNuevo,UbicacionNuevo,Comentario,
            UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla ) 
            VALUES(0,$this->IdAlmacen,'$this->NoParte',NOW(),"
                . "$this->ExistenciaAnterior,$this->ApartadoAnterior,$this->MinimoAnterior,$this->MaximoAnterior,'$this->UbicacionAnterior',"
                . "$this->ExistenciaNuevo,$this->ApartadoNuevo,$this->MinimoNuevo,$this->MaximoNuevo,'$this->UbicacionNuevo','$this->Comentario',"
                . "'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');");
        $catalogo = new Catalogo(); 
        $this->IdCambio = $catalogo->insertarRegistro($consulta);
        if ($this->IdCambio !=NULL && $this->IdCambio !=0) {                        
            return true;
        }        
        return false;
    }

    function getIdCambio() {
        return $this->IdCambio;
    }

    function getIdAlmacen() {
        return $this->IdAlmacen;
    }

    function getNoParte() {
        return $this->NoParte;
    }

    function getFecha() {
        return $this->Fecha;
    }

    function getExistenciaAnterior() {
        return $this->ExistenciaAnterior;
    }

    function getApartadoAnterior() {
        return $this->ApartadoAnterior;
    }

    function getMinimoAnterior() {
        return $this->MinimoAnterior;
    }

    function getMaximoAnterior() {
        return $this->MaximoAnterior;
    }

    function getUbicacionAnterior() {
        return $this->UbicacionAnterior;
    }

    function getExistenciaNuevo() {
        return $this->ExistenciaNuevo;
    }

    function getApartadoNuevo() {
        return $this->ApartadoNuevo;
    }

    function getMinimoNuevo() {
        return $this->MinimoNuevo;
    }

    function getMaximoNuevo() {
        return $this->MaximoNuevo;
    }

    function getUbicacionNuevo() {
        return $this->UbicacionNuevo;
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

    function setIdCambio($IdCambio) {
        $this->IdCambio = $IdCambio;
    }

    function setIdAlmacen($IdAlmacen) {
        $this->IdAlmacen = $IdAlmacen;
    }

    function setNoParte($NoParte) {
        $this->NoParte = $NoParte;
    }

    function setFecha($Fecha) {
        $this->Fecha = $Fecha;
    }

    function setExistenciaAnterior($ExistenciaAnterior) {
        $this->ExistenciaAnterior = $ExistenciaAnterior;
    }

    function setApartadoAnterior($ApartadoAnterior) {
        $this->ApartadoAnterior = $ApartadoAnterior;
    }

    function setMinimoAnterior($MinimoAnterior) {
        $this->MinimoAnterior = $MinimoAnterior;
    }

    function setMaximoAnterior($MaximoAnterior) {
        $this->MaximoAnterior = $MaximoAnterior;
    }

    function setUbicacionAnterior($UbicacionAnterior) {
        $this->UbicacionAnterior = $UbicacionAnterior;
    }

    function setExistenciaNuevo($ExistenciaNuevo) {
        $this->ExistenciaNuevo = $ExistenciaNuevo;
    }

    function setApartadoNuevo($ApartadoNuevo) {
        $this->ApartadoNuevo = $ApartadoNuevo;
    }

    function setMinimoNuevo($MinimoNuevo) {
        $this->MinimoNuevo = $MinimoNuevo;
    }

    function setMaximoNuevo($MaximoNuevo) {
        $this->MaximoNuevo = $MaximoNuevo;
    }

    function setUbicacionNuevo($UbicacionNuevo) {
        $this->UbicacionNuevo = $UbicacionNuevo;
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

    function getComentario() {
        return $this->Comentario;
    }

    function setComentario($Comentario) {
        $this->Comentario = $Comentario;
    }
}
