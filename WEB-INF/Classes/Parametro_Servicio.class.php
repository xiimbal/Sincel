<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class Parametro_Servicio {

    private $Id_servicio_parametros;
    private $Id_servicio;
    private $Id_parametro;
    private $Descripcion;
    private $Tipo;
    private $Renta;
    private $Impresiones;
    private $Excedente;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;

    public function newRegistro() {        
        $consulta = "INSERT INTO k_parametro_servicio(Id_servicio, Id_parametro,Descripcion, Tipo,UsuarioCreacion,
                FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Renta,Impresiones,Excedente) 
                VALUES('$this->Id_servicio','$this->Id_parametro','$this->Descripcion','$this->Tipo','$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla','$this->Renta','$this->Impresiones','$this->Excedente');";
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {            
            return true;
        }        
        return false;
    }

    public function deletebyParametro($id) {        
        $consulta = "DELETE FROM k_parametro_servicio WHERE Id_parametro='$id';";
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {            
            return true;
        }        
        return false;
    }

    public function getId_servicio_parametros() {
        return $this->Id_servicio_parametros;
    }

    public function getId_servicio() {
        return $this->Id_servicio;
    }

    public function getId_parametro() {
        return $this->Id_parametro;
    }

    public function getDescripcion() {
        return $this->Descripcion;
    }

    public function getTipo() {
        return $this->Tipo;
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

    public function setId_servicio_parametros($Id_servicio_parametros) {
        $this->Id_servicio_parametros = $Id_servicio_parametros;
    }

    public function setId_servicio($Id_servicio) {
        $this->Id_servicio = $Id_servicio;
    }

    public function setId_parametro($Id_parametro) {
        $this->Id_parametro = $Id_parametro;
    }

    public function setDescripcion($Descripcion) {
        $this->Descripcion = $Descripcion;
    }

    public function setTipo($Tipo) {
        $this->Tipo = $Tipo;
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

    public function getRenta() {
        return $this->Renta;
    }

    public function getImpresiones() {
        return $this->Impresiones;
    }

    public function getExcedente() {
        return $this->Excedente;
    }

    public function setRenta($Renta) {
        $this->Renta = $Renta;
    }

    public function setImpresiones($Impresiones) {
        $this->Impresiones = $Impresiones;
    }

    public function setExcedente($Excedente) {
        $this->Excedente = $Excedente;
    }

}
