<?php

include_once("Catalogo.class.php");

class DetalleEspecial {
    
    private $IdKEspecial;
    private $IdEspecial;
    private $PesoBruto;
    private $Tara;
    private $Neto;
    private $CostoTotal;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $empresa;
    
    public function newRegistro() {    
        $consulta = ("INSERT INTO k_especial(IdKEspecial,IdEspecial,PesoBruto,Tara,Neto,CostoTotal,Activo,
            UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES(0,$this->IdEspecial,$this->PesoBruto,$this->Tara,$this->Neto,$this->CostoTotal,$this->Activo,
            '$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');");
        $catalogo = new Catalogo(); 
        if(isset($this->empresa) && !empty($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $this->IdKEspecial = $catalogo->insertarRegistro($consulta);
        if ($this->IdKEspecial !=NULL && $this->IdKEspecial !=0) {                        
            return true;
        }        
        return false;
    }
    
    public function getRegistroByIdEspecial($id) {
        $consulta = ("SELECT * FROM k_especial WHERE IdEspecial = $id;");
        $catalogo = new Catalogo();
        if(isset($this->empresa) && !empty($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdKEspecial = $rs['IdKEspecial'];
            $this->IdEspecial = $rs['IdEspecial'];
            $this->PesoBruto = $rs['PesoBruto'];
            $this->Tara = $rs['Tara'];
            $this->Neto = $rs['Neto'];
            $this->CostoTotal = $rs['CostoTotal'];
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
    
    function getIdKEspecial() {
        return $this->IdKEspecial;
    }

    function getPesoBruto() {
        return $this->PesoBruto;
    }

    function getTara() {
        return $this->Tara;
    }

    function getNeto() {
        return $this->Neto;
    }

    function getCostoTotal() {
        return $this->CostoTotal;
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

    function getEmpresa() {
        return $this->empresa;
    }

    function setIdKEspecial($IdKEspecial) {
        $this->IdKEspecial = $IdKEspecial;
    }

    function setPesoBruto($PesoBruto) {
        $this->PesoBruto = $PesoBruto;
    }

    function setTara($Tara) {
        $this->Tara = $Tara;
    }

    function setNeto($Neto) {
        $this->Neto = $Neto;
    }

    function setCostoTotal($CostoTotal) {
        $this->CostoTotal = $CostoTotal;
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

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
    
    function getIdEspecial() {
        return $this->IdEspecial;
    }

    function setIdEspecial($IdEspecial) {
        $this->IdEspecial = $IdEspecial;
    }
}
