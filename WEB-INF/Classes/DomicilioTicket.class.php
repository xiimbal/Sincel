<?php

include_once("Catalogo.class.php");

/**
 * Description of DomicilioTicket
 *
 * @author MAGG
 */
class DomicilioTicket {

    private $IdDomicilioTicket;
    private $IdTicket;
    private $ClaveZona;
    private $Calle;
    private $NoExterior;
    private $NoInterior;
    private $Colonia;
    private $Ciudad;
    private $Estado;
    private $Delegacion;
    private $Pais;
    private $CodigoPostal;
    private $Id_gzona;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $empresa;
    
    private $Facebook;
    private $Twitter;
    private $Horario;
    private $RFC;
    private $IdGiro;
    private $Calificacion;
    private $Foto;
    private $Sitioweb;
    private $Latitud;
    private $Longitud;
    private $EjecutivoCuenta;

    public function newRegistro() {
        if(!isset($this->Latitud) || empty($this->Latitud)){
            $this->Latitud = "NULL";
        }
        
        if(!isset($this->Longitud) || empty($this->Longitud)){
            $this->Longitud = "NULL";
        }
        $consulta = "INSERT INTO c_domicilioticket(IdDomicilioTicket, IdTicket, ClaveZona, Calle, NoExterior, NoInterior, Colonia, Ciudad, Estado, "
                . "Delegacion, Pais, CodigoPostal, Id_gzona, "
                . "Activo, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla, Latitud, Longitud) "
                . "VALUES(0, $this->IdTicket, '$this->ClaveZona', '$this->Calle', '$this->NoExterior', '$this->NoInterior', '$this->Colonia', '$this->Ciudad', "
                . "'$this->Estado','$this->Delegacion','$this->Pais','$this->CodigoPostal', NULL,"
                . "1, '$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla',$this->Latitud, $this->Longitud);";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->IdDomicilioTicket = $catalogo->insertarRegistro($consulta);
        if ($this->IdDomicilioTicket != NULL && $this->IdDomicilioTicket != 0) {
            return true;
        }
        return false;
    }
    
    public function updateDomicilioTicket(){
        if(!isset($this->Latitud) || empty($this->Latitud)){
            $this->Latitud = "NULL";
        }
        
        if(!isset($this->Longitud) || empty($this->Longitud)){
            $this->Longitud = "NULL";
        }
        $consulta = "UPDATE c_domicilioticket SET ClaveZona = '$this->ClaveZona', Calle = '$this->Calle', NoExterior = '$this->NoExterior', "
                . "NoInterior = '$this->NoInterior', Colonia = '$this->Colonia', Ciudad = '$this->Ciudad', Estado = '$this->Estado', "
                . "Delegacion = '$this->Delegacion', Pais = '$this->Pais', CodigoPostal = '$this->CodigoPostal', Id_gzona = NULL, "
                . "Activo = 1, UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = NOW(), Pantalla = '$this->Pantalla', "
                . "Latitud = $this->Latitud, Longitud = $this->Longitud "
                . "WHERE IdTicket = $this->IdTicket;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        if ($result != "0") {
            return true;
        }
        return false;
    }

    public function ticketDetalleCliente() {
        if(!isset($this->Calificacion) || empty($this->Calificacion)){
            $this->Calificacion = "NULL";
        }
        
        if(!isset($this->IdGiro) || empty($this->IdGiro)){
            $this->IdGiro = "NULL";
        }
        
    if(!isset($this->EjecutivoCuenta) || empty($this->EjecutivoCuenta)){
            $this->EjecutivoCuenta = "237";
        }
        
        $consulta = "INSERT INTO c_ticketcliente(IdTicket, Facebook, Twitter, Horario, RFC, IdGiro, Calificacion, Foto, Sitioweb, Latitud, Longitud, EjecutivoCuenta) "
                . "VALUES($this->IdTicket, '$this->Facebook', '$this->Twitter', '$this->Horario', '$this->RFC', $this->IdGiro, $this->Calificacion,"
                . "'$this->Foto', '$this->Sitioweb', $this->Latitud, $this->Longitud, $this->EjecutivoCuenta);";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        if ($result == 1) {
            return true;
        }
        return false;
    }

    function getIdDomicilioTicket() {
        return $this->IdDomicilioTicket;
    }

    function getIdTicket() {
        return $this->IdTicket;
    }

    function getClaveZona() {
        return $this->ClaveZona;
    }

    function getCalle() {
        return $this->Calle;
    }

    function getNoExterior() {
        return $this->NoExterior;
    }

    function getNoInterior() {
        return $this->NoInterior;
    }

    function getColonia() {
        return $this->Colonia;
    }

    function getCiudad() {
        return $this->Ciudad;
    }

    function getEstado() {
        return $this->Estado;
    }

    function getDelegacion() {
        return $this->Delegacion;
    }

    function getPais() {
        return $this->Pais;
    }

    function getCodigoPostal() {
        return $this->CodigoPostal;
    }

    function getId_gzona() {
        return $this->Id_gzona;
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

    function setIdDomicilioTicket($IdDomicilioTicket) {
        $this->IdDomicilioTicket = $IdDomicilioTicket;
    }

    function setIdTicket($IdTicket) {
        $this->IdTicket = $IdTicket;
    }

    function setClaveZona($ClaveZona) {
        $this->ClaveZona = $ClaveZona;
    }

    function setCalle($Calle) {
        $this->Calle = $Calle;
    }

    function setNoExterior($NoExterior) {
        $this->NoExterior = $NoExterior;
    }

    function setNoInterior($NoInterior) {
        $this->NoInterior = $NoInterior;
    }

    function setColonia($Colonia) {
        $this->Colonia = $Colonia;
    }

    function setCiudad($Ciudad) {
        $this->Ciudad = $Ciudad;
    }

    function setEstado($Estado) {
        $this->Estado = $Estado;
    }

    function setDelegacion($Delegacion) {
        $this->Delegacion = $Delegacion;
    }

    function setPais($Pais) {
        $this->Pais = $Pais;
    }

    function setCodigoPostal($CodigoPostal) {
        $this->CodigoPostal = $CodigoPostal;
    }

    function setId_gzona($Id_gzona) {
        $this->Id_gzona = $Id_gzona;
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

    function getFacebook() {
        return $this->Facebook;
    }

    function getTwitter() {
        return $this->Twitter;
    }

    function getHorario() {
        return $this->Horario;
    }

    function getRFC() {
        return $this->RFC;
    }

    function getIdGiro() {
        return $this->IdGiro;
    }

    function getCalificacion() {
        return $this->Calificacion;
    }

    function getFoto() {
        return $this->Foto;
    }

    function getSitioweb() {
        return $this->Sitioweb;
    }

    function getLatitud() {
        return $this->Latitud;
    }

    function getLongitud() {
        return $this->Longitud;
    }

    function setFacebook($Facebook) {
        $this->Facebook = $Facebook;
    }

    function setTwitter($Twitter) {
        $this->Twitter = $Twitter;
    }

    function setHorario($Horario) {
        $this->Horario = $Horario;
    }

    function setRFC($RFC) {
        $this->RFC = $RFC;
    }

    function setIdGiro($IdGiro) {
        $this->IdGiro = $IdGiro;
    }

    function setCalificacion($Calificacion) {
        $this->Calificacion = $Calificacion;
    }

    function setFoto($Foto) {
        $this->Foto = $Foto;
    }

    function setSitioweb($Sitioweb) {
        $this->Sitioweb = $Sitioweb;
    }

    function setLatitud($Latitud) {
        $this->Latitud = $Latitud;
    }

    function setLongitud($Longitud) {
        $this->Longitud = $Longitud;
    }
    
    function getEjecutivoCuenta() {
        return $this->EjecutivoCuenta;
    }

    function setEjecutivoCuenta($EjecutivoCuenta) {
        $this->EjecutivoCuenta = $EjecutivoCuenta;
    }
}
