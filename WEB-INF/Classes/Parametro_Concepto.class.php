<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class Parametro_Concepto {

    private $Id_concepto_adicional;
    private $Id_parametro;
    private $Nivel_facturacion;
    private $Descripcion;
    private $Cantidad;
    private $PrecioUnitario;
    private $Unidad_medida;
    private $IdProductoSAT;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;

    public function newRegistro() {       
        if(!isset($this->IdProductoSAT) || empty($this->IdProductoSAT)){
            $this->IdProductoSAT = 51334;
        }
        $consulta = "INSERT INTO k_parametro_concepto(Id_parametro, Nivel_facturacion,Descripcion, Cantidad,PrecioUnitario,Unidad_medida,IdProductoSAT,UsuarioCreacion,
                FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
                VALUES('$this->Id_parametro','$this->Nivel_facturacion','$this->Descripcion','$this->Cantidad','$this->PrecioUnitario','$this->Unidad_medida',$this->IdProductoSAT,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla');";
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {            
            return true;
        }        
        return false;
    }

    public function deletebyParametro($id) {        
        $consulta = "DELETE FROM k_parametro_concepto WHERE Id_parametro='$id';";
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {            
            return true;
        }        
        return false;
    }

    public function getCantidad() {
        return $this->Cantidad;
    }

    public function getPrecioUnitario() {
        return $this->PrecioUnitario;
    }

    public function setCantidad($Cantidad) {
        $this->Cantidad = $Cantidad;
    }

    public function setPrecioUnitario($PrecioUnitario) {
        $this->PrecioUnitario = $PrecioUnitario;
    }

    public function getId_concepto_adicional() {
        return $this->Id_concepto_adicional;
    }

    public function getId_parametro() {
        return $this->Id_parametro;
    }

    public function getNivel_facturacion() {
        return $this->Nivel_facturacion;
    }

    public function getDescripcion() {
        return $this->Descripcion;
    }

    public function getUnidad_medida() {
        return $this->Unidad_medida;
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

    public function setId_concepto_adicional($Id_concepto_adicional) {
        $this->Id_concepto_adicional = $Id_concepto_adicional;
    }

    public function setId_parametro($Id_parametro) {
        $this->Id_parametro = $Id_parametro;
    }

    public function setNivel_facturacion($Nivel_facturacion) {
        $this->Nivel_facturacion = $Nivel_facturacion;
    }

    public function setDescripcion($Descripcion) {
        $this->Descripcion = $Descripcion;
    }

    public function setUnidad_medida($Unidad_medida) {
        $this->Unidad_medida = $Unidad_medida;
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

    function getIdProductoSAT() {
        return $this->IdProductoSAT;
    }

    function setIdProductoSAT($IdProductoSAT) {
        $this->IdProductoSAT = $IdProductoSAT;
    }
}
