<?php

include_once ("Conexion.class.php");
include_once("Catalogo.class.php");

class TecnicoZona {

    private $id;
    private $id2;
    private $idUsuario;
    private $gZona;
    private $claveZona;
    private $activo;
    private $usuarioCreacion;
    private $usuarioModificaciona;
    private $pantalla;

    public function getRegistroById($id, $id2) {
        $consulta = ("SELECT * FROM k_tecnicozona WHERE ClaveTecnico='" . $id . "' AND ClaveZona='" . $id2 . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->idUsuario = $rs['ClaveTecnico'];
            $this->gZona = $rs['idGZona'];
            $this->claveZona = $rs['ClaveZona'];
            $this->activo = $rs['Activo'];
        }
        return $query;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO k_tecnicozona(ClaveTecnico,idGZona,ClaveZona,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->idUsuario . "','" . $this->gZona . "','" . $this->claveZona . "'," . $this->activo . ",'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificaciona . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE k_tecnicozona SET ClaveZona = '" . $this->claveZona . "',idGZona='" . $this->gZona . "',Activo=" . $this->activo . ",UsuarioUltimaModificacion = '" . $this->usuarioModificaciona . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' 
            WHERE ClaveTecnico='" . $this->id . "' AND ClaveZona='" . $this->id2 . "';");
       $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM k_tecnicozona WHERE ClaveTecnico='" . $this->idUsuario . "' AND ClaveZona='" . $this->claveZona . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getId2() {
        return $this->id2;
    }

    public function setId2($id2) {
        $this->id2 = $id2;
    }

    public function getIdUsuario() {
        return $this->idUsuario;
    }

    public function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    public function getGZona() {
        return $this->gZona;
    }

    public function setGZona($gZona) {
        $this->gZona = $gZona;
    }

    public function getClaveZona() {
        return $this->claveZona;
    }

    public function setClaveZona($claveZona) {
        $this->claveZona = $claveZona;
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

    public function getUsuarioModificaciona() {
        return $this->usuarioModificaciona;
    }

    public function setUsuarioModificaciona($usuarioModificaciona) {
        $this->usuarioModificaciona = $usuarioModificaciona;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }

}

?>
