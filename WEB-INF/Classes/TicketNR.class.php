<?php

include_once("Catalogo.class.php");

/**
 * Description of TicketNR
 *
 * @author MAGG
 */
class TicketNR {
    private $IdTicket;
    private $IdNotaRemision;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    
    public function nuevoRegistro() {        
        $consulta = "INSERT INTO k_ticketnr(IdTicket,IdNotaRemision,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES($this->IdTicket,$this->IdNotaRemision,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }

        $id = $catalogo->obtenerLista($consulta);
        if ($id != 0) {
            return true;
        }
        
        return false;        
    }

    function getIdTicket() {
        return $this->IdTicket;
    }

    function getIdNotaRemision() {
        return $this->IdNotaRemision;
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

    function setIdTicket($IdTicket) {
        $this->IdTicket = $IdTicket;
    }

    function setIdNotaRemision($IdNotaRemision) {
        $this->IdNotaRemision = $IdNotaRemision;
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
