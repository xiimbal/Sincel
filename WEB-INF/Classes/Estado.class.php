<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class Estado {

    private $idEstado;
    private $nombre;
    private $area;
    private $idKFlujo;
    private $idFlujo;
    private $flujos;
    private $mostrarClientes;
    private $mostrarContactos;
    private $IdEstadoTicket;
    private $FlagValidacion;
    private $FlagCobrar;
    private $idEstadoFlujo;
    private $ordenFlujo;
    private $activo;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_estado WHERE IdEstado='" . $id . "'");
        $catalogo = new Catalogo(); 
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idEstado = $rs['IdEstado'];
            $this->nombre = $rs['Nombre'];
            $this->activo = $rs['Activo'];
            $this->area = $rs['IdArea'];
            $this->mostrarClientes = $rs['mostrarClientes'];
            $this->mostrarContactos = $rs['mostrarContactos'];
            $this->IdEstadoTicket = $rs['IdEstadoTicket'];
            $this->FlagValidacion = $rs['FlagValidacion'];
            $this->FlagCobrar = $rs['FlagCobrar'];
            $this->flujos = array();
            $consulta = "SELECT IdFlujo,Orden FROM `k_flujoestado` WHERE IdEstado = $id;";
            $result = $catalogo->obtenerLista($consulta);
            while($rs2 = mysql_fetch_array($result)){
                array_push($this->flujos, $rs2['IdFlujo']);
                $this->ordenFlujo = $rs2['Orden'];
            }
            
            return true;
        }
        return false;
    }

    public function newRegistro() {
        if($this->FlagValidacion != "1"){$this->FlagValidacion = "0";}
        if($this->FlagCobrar != "1"){$this->FlagCobrar = "0";}        
        $consulta = ("INSERT INTO c_estado(Nombre,IdArea,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla, FlagValidacion, FlagCobrar)
            VALUES('" . $this->nombre . "'," . $this->area . "," . $this->activo . ",'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "', $this->FlagValidacion, $this->FlagCobrar)");        
        $catalogo = new Catalogo(); $this->idEstado = $catalogo->insertarRegistro($consulta);
        if ($this->idEstado!=NULL && $this->idEstado!=0) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        if($this->FlagValidacion != "1"){$this->FlagValidacion = "0";}
        if($this->FlagCobrar != "1"){$this->FlagCobrar = "0";}       
        $consulta = ("UPDATE c_estado SET FlagValidacion = $this->FlagValidacion, FlagCobrar = $this->FlagCobrar, Nombre = '" . $this->nombre . "',IdArea=" . $this->area . ", Activo = " . $this->activo . ",UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE IdEstado='" . $this->idEstado . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function newFlujoEstado($idUltimoEstado) {
        $consulta = ("INSERT INTO k_flujoestado(IdKFlujo,IdFlujo,IdEstado,Orden,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,'" . $this->idFlujo . "','" . $idUltimoEstado . "','" . $this->ordenFlujo . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM c_estado WHERE IdEstado = '" . $this->idEstado . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteFlujoEstado() {
        $consulta = ("DELETE FROM k_flujoestado WHERE IdKFlujo = '" . $this->idKFlujo . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }        

    public function getIdEstado() {
        return $this->idEstado;
    }

    public function setIdEstado($idEstado) {
        $this->idEstado = $idEstado;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function getArea() {
        return $this->area;
    }

    public function setArea($area) {
        $this->area = $area;
    }

    public function getIdFlujo() {
        return $this->idFlujo;
    }

    public function getIdKFlujo() {
        return $this->idKFlujo;
    }

    public function setIdKFlujo($idKFlujo) {
        $this->idKFlujo = $idKFlujo;
    }

    public function setIdFlujo($idFlujo) {
        $this->idFlujo = $idFlujo;
    }

    public function getIdEstadoFlujo() {
        return $this->idEstadoFlujo;
    }

    public function setIdEstadoFlujo($idEstadoFlujo) {
        $this->idEstadoFlujo = $idEstadoFlujo;
    }

    public function getOrdenFlujo() {
        return $this->ordenFlujo;
    }

    public function setOrdenFlujo($ordenFlujo) {
        $this->ordenFlujo = $ordenFlujo;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->usuarioModificacion;
    }

    public function setUsuarioModificacion($usuarioModificacion) {
        $this->usuarioModificacion = $usuarioModificacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }

    function getFlujos() {
        return $this->flujos;
    }

    function setFlujos($flujos) {
        $this->flujos = $flujos;
    }

    function getMostrarClientes() {
        return $this->mostrarClientes;
    }

    function getMostrarContactos() {
        return $this->mostrarContactos;
    }

    function setMostrarClientes($mostrarClientes) {
        $this->mostrarClientes = $mostrarClientes;
    }

    function setMostrarContactos($mostrarContactos) {
        $this->mostrarContactos = $mostrarContactos;
    }

    function getIdEstadoTicket() {
        return $this->IdEstadoTicket;
    }

    function setIdEstadoTicket($IdEstadoTicket) {
        $this->IdEstadoTicket = $IdEstadoTicket;
    }
    
    function getFlagValidacion() {
        return $this->FlagValidacion;
    }

    function getFlagCobrar() {
        return $this->FlagCobrar;
    }

    function setFlagValidacion($FlagValidacion) {
        $this->FlagValidacion = $FlagValidacion;
    }

    function setFlagCobrar($FlagCobrar) {
        $this->FlagCobrar = $FlagCobrar;
    }


    
}

?>
