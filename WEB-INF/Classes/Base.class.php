<?php

include_once("Catalogo.class.php");

class Base {
    private $IdBase;
    private $Nombre;
    private $Descripcion;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $empresa;
    
    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM `c_base_operador` WHERE IdBase = $id;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdBase = $rs['IdBase'];
            $this->Nombre = $rs['Nombre'];
            $this->Descripcion = $rs['Descripcion'];
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
    
    public function newRegistro(){
        $consulta = ("INSERT INTO c_base_operador(IdBase, Nombre, Descripcion, Activo, UsuarioCreacion, "
                . "FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) VALUES"
                . "(0,'$this->Nombre','$this->Descripcion',$this->Activo,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');");        
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $this->IdBase = $catalogo->insertarRegistro($consulta);
        if ($this->IdBase!=NULL && $this->IdBase!=0) {           
            return true;
        }
        return false;
    }
            
    public function editRegistro() {
        $consulta = ("UPDATE c_base_operador SET Nombre = '" . $this->Nombre . "',Descripcion='" . $this->Descripcion . "',
            Activo = " . $this->Activo . ",UsuarioUltimaModificacion = '" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion = NOW(),
            Pantalla = '" . $this->Pantalla . "' WHERE IdBase = '" . $this->IdBase . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM c_base_operador WHERE IdBase = '" . $this->IdBase . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    function getIdBase() {
        return $this->IdBase;
    }

    function getNombre() {
        return $this->Nombre;
    }

    function getDescripcion() {
        return $this->Descripcion;
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

    function setIdBase($IdBase) {
        $this->IdBase = $IdBase;
    }

    function setNombre($Nombre) {
        $this->Nombre = $Nombre;
    }

    function setDescripcion($Descripcion) {
        $this->Descripcion = $Descripcion;
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
    
    function setEmpresa($Empresa) {
        $this->empresa = $Empresa;
    }


}
