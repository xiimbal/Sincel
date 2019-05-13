<?php

include_once("Catalogo.class.php");
/**
 * Description of TicketImpreso
 *
 * @author MiguelÁngel
 */
class TicketImpreso {
    private $IdTicket;
    private $IdUsuario;
    private $Fecha;
    private $Pantalla;
    private $empresa;
    
    public function newRegistro(){
        $consulta = "INSERT INTO k_ticketimpreso(IdTicket,Fecha,IdUsuario,Pantalla) VALUES($this->IdTicket, NOW(), $this->IdUsuario, '$this->Pantalla');";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        if($result == "1"){
            return true;
        }
        return false;
    }
    
    public function getRegistrosPorTicket(){
        $consulta = "SELECT DATE(t.Fecha) AS Fecha, TIME(t.Fecha) AS Hora, CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Usuario
            FROM k_ticketimpreso AS t
            LEFT JOIN c_usuario AS u ON u.IdUsuario = t.IdUsuario
            WHERE t.IdTicket = $this->IdTicket;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }
    
    function getIdTicket() {
        return $this->IdTicket;
    }

    function getIdUsuario() {
        return $this->IdUsuario;
    }

    function getFecha() {
        return $this->Fecha;
    }

    function getPantalla() {
        return $this->Pantalla;
    }

    function setIdTicket($IdTicket) {
        $this->IdTicket = $IdTicket;
    }

    function setIdUsuario($IdUsuario) {
        $this->IdUsuario = $IdUsuario;
    }

    function setFecha($Fecha) {
        $this->Fecha = $Fecha;
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
