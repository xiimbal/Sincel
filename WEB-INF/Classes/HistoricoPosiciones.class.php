<?php

include_once("Catalogo.class.php");

/**
 * Description of HistoricoPosiciones
 *
 * @author MAGG
 */
class HistoricoPosiciones {

    private $IdUbicacion;
    private $IdUsuario;
    private $Latitud;
    private $Longitud;
    private $Radio;
    private $ClaveCliente;
    private $IdGiro;
    private $IdTipoContacto;
    private $Respuesta;
    private $IdWebService;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $empresa;
    
    public function newRegistro(){
        $ClaveCliente = "NULL";
        if(isset($this->ClaveCliente) && !empty($this->ClaveCliente)){
            $ClaveCliente = "'".$this->ClaveCliente."'";
        }
        $consulta = "INSERT INTO c_historicoposiciones(IdUbicacion, IdUsuario, Latitud, Longitud, Radio, ClaveCliente, "
                . "IdGiro, IdTipoContacto, Respuesta, IdWebService, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, "
                . "FechaUltimaModificacion, Pantalla) VALUES(0, $this->IdUsuario, $this->Latitud, $this->Longitud, $this->Radio, $ClaveCliente, "
                . "$this->IdGiro, $this->IdTipoContacto, '$this->Respuesta', $this->IdWebService, '$this->UsuarioCreacion', NOW(),'$this->UsuarioUltimaModificacion',"
                . "NOW(),'$this->Pantalla');";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->IdUbicacion = $catalogo->insertarRegistro($consulta);
        if ($this->IdUbicacion !=NULL && $this->IdUbicacion !=0) {                        
            return true;
        }        
        return false;
    }
    
    function getIdUbicacion() {
        return $this->IdUbicacion;
    }

    function getIdUsuario() {
        return $this->IdUsuario;
    }

    function getLatitud() {
        return $this->Latitud;
    }

    function getLongitud() {
        return $this->Longitud;
    }

    function getRadio() {
        return $this->Radio;
    }

    function getClaveCliente() {
        return $this->ClaveCliente;
    }

    function getIdGiro() {
        return $this->IdGiro;
    }

    function getIdTipoContacto() {
        return $this->IdTipoContacto;
    }

    function getRespuesta() {
        return $this->Respuesta;
    }

    function getIdWebService() {
        return $this->IdWebService;
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

    function setIdUbicacion($IdUbicacion) {
        $this->IdUbicacion = $IdUbicacion;
    }

    function setIdUsuario($IdUsuario) {
        $this->IdUsuario = $IdUsuario;
    }

    function setLatitud($Latitud) {
        $this->Latitud = $Latitud;
    }

    function setLongitud($Longitud) {
        $this->Longitud = $Longitud;
    }

    function setRadio($Radio) {
        $this->Radio = $Radio;
    }

    function setClaveCliente($ClaveCliente) {
        $this->ClaveCliente = $ClaveCliente;
    }

    function setIdGiro($IdGiro) {
        $this->IdGiro = $IdGiro;
    }

    function setIdTipoContacto($IdTipoContacto) {
        $this->IdTipoContacto = $IdTipoContacto;
    }

    function setRespuesta($Respuesta) {
        $this->Respuesta = $Respuesta;
    }

    function setIdWebService($IdWebService) {
        $this->IdWebService = $IdWebService;
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
