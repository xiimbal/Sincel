<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class TicketAuxiliar {

    private $IdTicketAuxiliar;
    private $IdTicket;
    private $Form;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $empresa;

    public function getRegistroByIdTicket($id) {
        $consulta = ("SELECT * FROM k_ticket_auxiliar kt WHERE kt.IdTicket='$id'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdTicketAuxiliar = $rs['IdTicketAuxiliar'];
            $this->IdTicket = $rs['IdTicket'];
            $this->Form = $rs['Form'];
            $this->activo = $rs['Activo'];
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
        $consulta = "INSERT INTO k_ticket_auxiliar(IdTicketAuxiliar,IdTicket,Form,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,'" . $this->IdTicket . "','" . $this->Form . "'," . $this->Activo . ",'" . $this->UsuarioCreacion . "',now(),'" . $this->UsuarioUltimaModificacion . "',now(),'" . $this->Pantalla . "')";
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

    function getIdTicketAuxiliar() {
        return $this->IdTicketAuxiliar;
    }

    function getIdTicket() {
        return $this->IdTicket;
    }

    function getForm() {
        return $this->Form;
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

    function setIdTicketAuxiliar($IdTicketAuxiliar) {
        $this->IdTicketAuxiliar = $IdTicketAuxiliar;
    }

    function setIdTicket($IdTicket) {
        $this->IdTicket = $IdTicket;
    }

    function setForm($Form) {
        $this->Form = $Form;
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
