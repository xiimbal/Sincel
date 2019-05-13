<?php

include_once("Catalogo.class.php");

class UsoCFDI {
    
    private $IdUsoCFDI;
    private $ClaveCFDI;
    private $Descripcion;
    private $Fisica;
    private $Moral;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;

    public function getRegistroById($id){
        $consulta = "SELECT * FROM `c_usocfdi` WHERE IdUsoCFDI = $id;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        //echo $consulta;
        while($rs = mysql_fetch_array($result)){
            $this->IdUsoCFDI = $rs['IdUsoCFDI'];
            $this->ClaveCFDI = $rs['ClaveCFDI'];
            $this->Descripcion = $rs['Descripcion'];
            $this->Fisica = $rs['Fisica'];
            $this->Moral = $rs['Moral'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
        }
    }
    
    function getIdUsoCFDI() {
        return $this->IdUsoCFDI;
    }

    function getClaveCFDI() {
        return $this->ClaveCFDI;
    }

    function getDescripcion() {
        return $this->Descripcion;
    }

    function getFisica() {
        return $this->Fisica;
    }

    function getMoral() {
        return $this->Moral;
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

    function setIdUsoCFDI($IdUsoCFDI) {
        $this->IdUsoCFDI = $IdUsoCFDI;
    }

    function setClaveCFDI($ClaveCFDI) {
        $this->ClaveCFDI = $ClaveCFDI;
    }

    function setDescripcion($Descripcion) {
        $this->Descripcion = $Descripcion;
    }

    function setFisica($Fisica) {
        $this->Fisica = $Fisica;
    }

    function setMoral($Moral) {
        $this->Moral = $Moral;
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
