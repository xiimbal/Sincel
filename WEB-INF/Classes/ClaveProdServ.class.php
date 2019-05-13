<?php

include_once("Catalogo.class.php");

class ClaveProdServ {
    
    private $IdProdServ;
    private $ClaveProdServ;
    private $Descripcion;
    private $IncluirIVATrasladado;
    private $IncluirIEPSTrasladado;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $empresa;

    function getIdByClaveProdServ(){
        $catalogo = new Catalogo();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = "SELECT * FROM c_claveprodserv WHERE ClaveProdServ = '$this->ClaveProdServ'";
        //echo $consulta;
        $result = $catalogo->obtenerLista($consulta);
        if($rs = mysql_fetch_array($result)){
            $this->IdProdServ = $rs['IdProdServ'];
            $this->ClaveProdServ = $rs['ClaveProdServ'];
            $this->Descripcion = $rs['Descripcion'];
            $this->IncluirIEPSTrasladado = $rs['IncluirIEPSTrasladado'];
            $this->IncluirIVATrasladado = $rs['IncluirIVATrasladado'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }
    
    function getRegistroById($id){
        $catalogo = new Catalogo();
        $consulta = "SELECT * FROM c_claveprodserv WHERE IdProdServ = '$id'";
        $result = $catalogo->obtenerLista($consulta);
        if($rs = mysql_fetch_array($result)){
            $this->IdProdServ = $rs['IdProdServ'];
            $this->ClaveProdServ = $rs['ClaveProdServ'];
            $this->Descripcion = $rs['Descripcion'];
            $this->IncluirIEPSTrasladado = $rs['IncluirIEPSTrasladado'];
            $this->IncluirIVATrasladado = $rs['IncluirIVATrasladado'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }
    
    function getIdProdServ() {
        return $this->IdProdServ;
    }

    function getClaveProdServ() {
        return $this->ClaveProdServ;
    }

    function getDescripcion() {
        return $this->Descripcion;
    }

    function getIncluirIVATrasladado() {
        return $this->IncluirIVATrasladado;
    }

    function getIncluirIEPSTrasladado() {
        return $this->IncluirIEPSTrasladado;
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

    function setIdProdServ($IdProdServ) {
        $this->IdProdServ = $IdProdServ;
    }

    function setClaveProdServ($ClaveProdServ) {
        $this->ClaveProdServ = $ClaveProdServ;
    }

    function setDescripcion($Descripcion) {
        $this->Descripcion = $Descripcion;
    }

    function setIncluirIVATrasladado($IncluirIVATrasladado) {
        $this->IncluirIVATrasladado = $IncluirIVATrasladado;
    }

    function setIncluirIEPSTrasladado($IncluirIEPSTrasladado) {
        $this->IncluirIEPSTrasladado = $IncluirIEPSTrasladado;
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
    
    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
}
