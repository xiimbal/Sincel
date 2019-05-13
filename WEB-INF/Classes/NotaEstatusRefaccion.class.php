<?php

include_once ("Conexion.class.php");
include_once("Catalogo.class.php");

class NotaEstatusRefaccion {

    private $id;
    private $idNota;
    private $nota;
    private $noParte;
    private $cantidad;
    private $estatus;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $usuarioSolicitud;

    public function newRegistro() {
        $consulta = ("INSERT INTO k_notaatendidas(IdNotaAtendidad,UsuarioSolicitud,IdNota,NoParteRefaccion,IdEstatusRefaccion,CantidadAtendida,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,'".$this->usuarioSolicitud."','" . $this->nota . "','" . $this->noParte . "','" . $this->estatus . "','" . $this->cantidad . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
 

    public function editRegistro() {
        $consulta = ("UPDATE k_notaatendidas SET CantidadAtendida = '" . $this->cantidad . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE IdNotaAtendidad='" . $this->id . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function obtenerCantidad() {
        $consulta = ("SELECT * FROM k_notaatendidas WHERE IdNotaAtendidad='" . $this->id . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->cantidad = $rs['CantidadAtendida'];
        }
        return $this->cantidad;
    }
    public function getUsuarioSolicitud() {
        return $this->usuarioSolicitud;
    }

    public function setUsuarioSolicitud($usuarioSolicitud) {
        $this->usuarioSolicitud = $usuarioSolicitud;
    }

        public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getIdNota() {
        return $this->idNota;
    }

    public function setIdNota($idNota) {
        $this->idNota = $idNota;
    }

    public function getNota() {
        return $this->nota;
    }

    public function setNota($nota) {
        $this->nota = $nota;
    }

    public function getNoParte() {
        return $this->noParte;
    }

    public function setNoParte($noParte) {
        $this->noParte = $noParte;
    }

    public function getCantidad() {
        return $this->cantidad;
    }

    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    public function getEstatus() {
        return $this->estatus;
    }

    public function setEstatus($estatus) {
        $this->estatus = $estatus;
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

}

?>
