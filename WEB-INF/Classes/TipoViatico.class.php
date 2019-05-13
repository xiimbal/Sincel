<?php

include_once("Catalogo.class.php");

/**
 * Description of TipoViatico
 *
 * @author MAGG
 */
class TipoViatico {
    private $idTipoViatico;
    private $nombre;
    private $IdEstado;
    private $activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $empresa;

    function getRegistroById($idViatico){
        $catalogo = new Catalogo();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);            
        }
        $consulta = "SELECT * FROM `c_tipoviatico` WHERE idTipoViatico = $idViatico;";
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $this->idTipoViatico = $rs['idTipoViatico'];
            $this->nombre = $rs['nombre'];
            $this->IdEstado = $rs['IdEstado'];
            $this->activo = $rs['activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }
    
    function getRegistroByIdEstado($idEstado){
        $catalogo = new Catalogo();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);            
        }
        $consulta = "SELECT * FROM `c_tipoviatico` WHERE IdEstado = $idEstado;";        
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $this->idTipoViatico = $rs['idTipoViatico'];
            $this->nombre = $rs['nombre'];
            $this->IdEstado = $rs['IdEstado'];            
            $this->activo = $rs['activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }
    
    function tieneEstadoViatico($idEstado){
        if($idEstado == 274){
            return true;
        }
               
        if($this->getRegistroByIdEstado($idEstado)){
            return true;
        }
        return false;
    }
    
    function getIdTipoViatico() {
        return $this->idTipoViatico;
    }

    function getNombre() {
        return $this->nombre;
    }

    function getActivo() {
        return $this->activo;
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

    function setIdTipoViatico($idTipoViatico) {
        $this->idTipoViatico = $idTipoViatico;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setActivo($activo) {
        $this->activo = $activo;
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
    
    function getIdEstado() {
        return $this->IdEstado;
    }

    function setIdEstado($IdEstado) {
        $this->IdEstado = $IdEstado;
    }
}
