<?php

include_once ("Catalogo.class.php");

class Presolicitud {
    
    private $id_presolicitud;
    private $Causa_Movimiento;
    private $id_almacen;
    private $user;
    private $comentario;
    private $tipoMovimiento;
    private $id_lectura;
    private $id_reporteHistorico;
    private $id_usuario;
    private $NoSerie;
    private $Fecha;
    private $UsuarioUltimaModificacion;
    private $pantalla;
    private $FechaUltimaModificacion;
    
    function presolici($Causa_Movimiento, $id_almacen, $user, $comentario, $tipoMovimiento, $id_lectura, $id_reporteHistorico, $id_usuario, $NoSerie, $Fecha) {
        $this->Causa_Movimiento = $Causa_Movimiento;
        $this->id_almacen = $id_almacen;
        $this->user = $user;
        $this->comentario = $comentario;
        $this->tipoMovimiento = $tipoMovimiento;
        $this->id_lectura = $id_lectura;
        $this->id_reporteHistorico = $id_reporteHistorico;
        $this->id_usuario = $id_usuario;
        $this->NoSerie = $NoSerie;
        $this->Fecha = $Fecha;
        $this->UsuarioUltimaModificacion = $_SESSION['user'];
        $this->pantalla = "Presolicitud de retiro";
        $this->FechaUltimaModificacion = date("Y-m-d");
    }
    
    function nuevoRegistro(){
        $consulta = ("insert into c_presolicitud values (0, '$this->Causa_Movimiento',$this->id_almacen,'$this->user'
            ,'$this->comentario',$this->tipoMovimiento,$this->id_lectura,$this->id_reporteHistorico, $this->id_usuario,"
            . "'$this->NoSerie','$this->Fecha','$this->UsuarioUltimaModificacion','$this->pantalla','$this->FechaUltimaModificacion')");
        $catalogo = new Catalogo();
        $this->id_presolicitud = $catalogo->insertarRegistro($consulta);
        if ($this->id_presolicitud != NULL && $this->id_presolicitud != 0) {
            return true;
        }
        return false;
    }
    
    function getId_presolicitud() {
        return $this->id_presolicitud;
    }

    function getCausa_Movimiento() {
        return $this->Causa_Movimiento;
    }

    function getId_almacen() {
        return $this->id_almacen;
    }

    function getUser() {
        return $this->user;
    }

    function getComentario() {
        return $this->comentario;
    }

    function getTipoMovimiento() {
        return $this->tipoMovimiento;
    }

    function getId_lectura() {
        return $this->id_lectura;
    }

    function getId_reporteHistorico() {
        return $this->id_reporteHistorico;
    }

    function getId_usuario() {
        return $this->id_usuario;
    }

    function getNoSerie() {
        return $this->NoSerie;
    }

    function getFecha() {
        return $this->Fecha;
    }

    function setId_presolicitud($id_presolicitud) {
        $this->id_presolicitud = $id_presolicitud;
    }

    function setCausa_Movimiento($Causa_Movimiento) {
        $this->Causa_Movimiento = $Causa_Movimiento;
    }

    function setId_almacen($id_almacen) {
        $this->id_almacen = $id_almacen;
    }

    function setUser($user) {
        $this->user = $user;
    }

    function setComentario($comentario) {
        $this->comentario = $comentario;
    }

    function setTipoMovimiento($tipoMovimiento) {
        $this->tipoMovimiento = $tipoMovimiento;
    }

    function setId_lectura($id_lectura) {
        $this->id_lectura = $id_lectura;
    }

    function setId_reporteHistorico($id_reporteHistorico) {
        $this->id_reporteHistorico = $id_reporteHistorico;
    }

    function setId_usuario($id_usuario) {
        $this->id_usuario = $id_usuario;
    }

    function setNoSerie($NoSerie) {
        $this->NoSerie = $NoSerie;
    }

    function setFecha($Fecha) {
        $this->Fecha = $Fecha;
    }


    
}
