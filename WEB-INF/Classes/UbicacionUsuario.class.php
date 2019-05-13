<?php

include_once("Catalogo.class.php");

/**
 * Description of UbicacionUsuario
 *
 * @author MiguelÁngel
 */
class UbicacionUsuario {

    private $IdUbicacion;
    private $IdUsuario;
    private $Fecha;
    private $Latitud;
    private $Longitud;
    private $PorcentajeBateria;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $empresa;
    private $IdNotaTicket;

    public function newRegistro(){
        $catalogo = new Catalogo();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        
        if(!isset($this->IdNotaTicket) || empty($this->IdNotaTicket)){
            $this->IdNotaTicket = "NULL";
        }
        
        $consulta = "INSERT INTO c_ubicacionusuario(IdUbicacion, IdUsuario, Fecha, Latitud, Longitud,PorcentajeBateria, "
                . "UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla, IdNotaTicket) "
                . "VALUES(0,$this->IdUsuario,NOW(),$this->Latitud, $this->Longitud, $this->PorcentajeBateria,"
                . "'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla', $this->IdNotaTicket);";
        $this->IdUbicacion = $catalogo->insertarRegistro($consulta);
        if ($this->IdUbicacion != null && $this->IdUbicacion != 0) {
            return true;
        }
        return false;
    }
    
    public function getLastUbication($IdUsuario){
        $catalogo = new Catalogo();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        
        $consulta = "SELECT IdUbicacion, Latitud, Longitud, Fecha, IdUsuario FROM `c_ubicacionusuario` WHERE IdUsuario = $IdUsuario ORDER BY IdUbicacion DESC LIMIT 0,1;";
        $result = $catalogo->obtenerLista($consulta);
        
        return $result;
    }
    
    public function actualizarFechaUbicacion($IdUbicacion){
        $catalogo = new Catalogo();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        
        $consulta = "UPDATE `c_ubicacionusuario` SET Fecha = NOW() WHERE IdUbicacion = $IdUbicacion;";
        $query = $catalogo->obtenerLista($consulta);
        if($query == "1"){
            return true;
        }
        return false;        
    }
    
    public function obtenerUbicacion($Arraysuarios){
        $catalogo = new Catalogo();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        
        $where = "";
        if(!empty($Arraysuarios)){
            $where = "WHERE ubu.IdUsuario IN($Arraysuarios)";
        }
        
        $consulta = "SELECT MAX(ubu.IdUbicacion) AS IdUbicacion, u.Loggin, CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Nombre,
            ubu.Latitud, ubu.Longitud, u.IdUsuario, u.IdPuesto, t.IdTicket, nt.IdNotaTicket, nt.IdEstatusAtencion, nt.FechaHora, ubu.PorcentajeBateria
            FROM `c_ubicacionusuario` AS ubu
            LEFT JOIN c_usuario AS u ON u.IdUsuario = ubu.IdUsuario
            LEFT JOIN c_ticket AS t ON t.IdTicket = (SELECT MAX(IdTicket) FROM k_tecnicoticket WHERE IdUsuario = u.IdUsuario)
            LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)
            $where
            GROUP BY u.IdUsuario ORDER BY Nombre;";
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }
    
    function getIdUbicacion() {
        return $this->IdUbicacion;
    }

    function getIdUsuario() {
        return $this->IdUsuario;
    }

    function getFecha() {
        return $this->Fecha;
    }

    function getLatitud() {
        return $this->Latitud;
    }

    function getLongitud() {
        return $this->Longitud;
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

    function setFecha($Fecha) {
        $this->Fecha = $Fecha;
    }

    function setLatitud($Latitud) {
        $this->Latitud = $Latitud;
    }

    function setLongitud($Longitud) {
        $this->Longitud = $Longitud;
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

    function getPorcentajeBateria() {
        return $this->PorcentajeBateria;
    }

    function setPorcentajeBateria($PorcentajeBateria) {
        $this->PorcentajeBateria = $PorcentajeBateria;
    }
    
    function getIdNotaTicket() {
        return $this->IdNotaTicket;
    }

    function setIdNotaTicket($IdNotaTicket) {
        $this->IdNotaTicket = $IdNotaTicket;
    }
}
