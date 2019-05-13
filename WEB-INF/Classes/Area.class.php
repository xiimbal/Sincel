<?php

include_once ("Conexion.class.php");
include_once ("Catalogo.class.php");

class Area {

    private $idArea;
    private $descripcion;
    private $activo;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_area WHERE IdArea='" . $id . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->idArea = $rs['IdArea'];
            $this->descripcion = $rs['Descripcion'];
            $this->activo = $rs['Activo'];
            return true;
        }
        return false;
    }
    
    public function getRegistroByEstado($id) {
        $consulta = ("SELECT * FROM `c_area` WHERE IdEstado = $id;");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->idArea = $rs['IdArea'];
            $this->descripcion = $rs['Descripcion'];
            $this->activo = $rs['Activo'];
            return true;
        }
        return false;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO c_area(IdArea,Descripcion,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,'" . $this->descripcion . "'," . $this->activo . ",'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE c_area SET Descripcion = '" . $this->descripcion . "', Activo = " . $this->activo . ",UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE IdArea='" . $this->idArea . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM c_area WHERE IdArea = '" . $this->idArea . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getIdArea() {
        return $this->idArea;
    }

    public function setIdArea($idArea) {
        $this->idArea = $idArea;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
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

}

?>
