<?php

include_once("Catalogo.class.php");

/**
 * Description of TicketRelacion
 *
 * @author MAGG
 */
class TicketRelacion {
    private $IdRelacionTicket;
    private $IdTicketMultiple;
    private $IdTicketSimple;
    private $Estatus;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;

    public function newRegistro(){
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        if(empty($this->IdTicketSimple)){
            return true;
        }
        $consulta = "SELECT IdRelacionTicket FROM k_relacion_tickets WHERE IdTicketSimple = $this->IdTicketSimple  AND IdTicketMultiple = $this->IdTicketMultiple;";        
        $result = $catalogo->obtenerLista($consulta);
        if(mysql_num_rows($result) == 0){
            $consulta = ("INSERT INTO k_relacion_tickets(IdRelacionTicket,IdTicketMultiple,IdTicketSimple,Estatus,
                UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
                VALUES(NULL,'$this->IdTicketMultiple','$this->IdTicketSimple',$this->Estatus,
                    '$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');");        
            $this->IdRelacionTicket = $catalogo->insertarRegistro($consulta);

            if ($this->IdRelacionTicket != NULL && $this->IdRelacionTicket != 0) {            
                return true;
            }
            return false;
        }
        return true;
    }
    
    public function getRegistroTicketMultiple(){
         $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = ("SELECT * FROM k_relacion_tickets WHERE IdTicketMultiple=$this->IdTicketMultiple;");
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function deleteRegistroTicketMultiple(){
         $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = ("DELETE FROM k_relacion_tickets WHERE IdTicketMultiple=$this->IdTicketMultiple;");
        $query = $catalogo->obtenerLista($consulta);
        if($query != "0"){
            return true;
        }
        return false;
    }
    
    function getIdRelacionTicket() {
        return $this->IdRelacionTicket;
    }

    function getIdTicketMultiple() {
        return $this->IdTicketMultiple;
    }

    function getIdTicketSimple() {
        return $this->IdTicketSimple;
    }

    function getEstatus() {
        return $this->Estatus;
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

    function setIdRelacionTicket($IdRelacionTicket) {
        $this->IdRelacionTicket = $IdRelacionTicket;
    }

    function setIdTicketMultiple($IdTicketMultiple) {
        $this->IdTicketMultiple = $IdTicketMultiple;
    }

    function setIdTicketSimple($IdTicketSimple) {
        $this->IdTicketSimple = $IdTicketSimple;
    }

    function setEstatus($Estatus) {
        $this->Estatus = $Estatus;
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
