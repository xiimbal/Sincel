<?php

include_once("Conexion.class.php");
include_once ("Catalogo.class.php");

class Banco {
    
    private $idBanco;
    private $nombre;
    private $descripcion;
    private $RFC;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    
    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_banco WHERE IdBanco = $id;");
        $catalogo = new Catalogo(); 
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idBanco = $rs['IdBanco'];
            $this->nombre = $rs['Nombre'];
            $this->descripcion = $rs['Descripcion'];
            $this->RFC = $rs['RFC'];
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
    
    public function newRegistro() {        
        $consulta = ("INSERT INTO c_banco(Nombre, Descripcion , RFC, Activo, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) 
            VALUES('$this->nombre','$this->descripcion','$this->RFC',$this->Activo, '$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');");
        $catalogo = new Catalogo(); 
        $this->idBanco = $catalogo->insertarRegistro($consulta);
        if ($this->idBanco !=NULL && $this->idBanco !=0) {                        
            return true;
        }        
        return false;
    }
    
    public function editRegistro() {        
        $consulta = ("UPDATE c_banco SET Nombre = '$this->nombre',Descripcion = '$this->descripcion', RFC = '$this->RFC',
                        Activo = $this->Activo, UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', 
                        FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla' WHERE IdBanco = " . $this->idBanco . ";");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function deleteRegistro($idBanco){
        $consulta = "DELETE FROM `c_banco` WHERE IdBanco = $idBanco;";        
        $catalogo = new Catalogo(); 
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    function getIdBanco() {
        return $this->idBanco;
    }

    function getNombre() {
        return $this->nombre;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function setIdBanco($id_banco) {
        $this->idBanco = $id_banco;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setDescripcion($descrpcion) {
        $this->descripcion = $descrpcion;
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

    function getRFC() {
        return $this->RFC;
    }

    function setRFC($RFC) {
        $this->RFC = $RFC;
    }
}
