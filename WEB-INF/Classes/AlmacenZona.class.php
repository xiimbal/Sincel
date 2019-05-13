<?php

include_once ("Conexion.class.php");
include_once ("Catalogo.class.php");

class AlmacenZona {

    private $id;
    private $idAlmacen;
    private $idGZona;
    private $claveZona;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;

    public function getRegistroById($id, $id2) {
        $consulta = ("SELECT * FROM k_almacenzona WHERE IdAlmacen='" . $id . "' AND ClaveZona='" . $id2 . "'");
        $catalogo = new Catalogo();
	$query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->idAlmacen = $rs['IdAlmacen'];
            $this->claveZona = $rs['ClaveZona'];
            $this->idGZona = $rs['IdGZona'];
        }
        return $query;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO k_almacenzona(IdAlmacen,ClaveZona,idGZona,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->idAlmacen . "','" . $this->claveZona . "','" . $this->idGZona . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo();
	$query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE k_almacenzona SET ClaveZona= '" . $this->claveZona . "',idGZona='" . $this->idGZona . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'  WHERE IdAlmacen='" . $this->idAlmacen . "' AND ClaveZona ='" . $this->id . "';");
        $catalogo = new Catalogo();
	$query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM k_almacenzona WHERE IdAlmacen='" . $this->idAlmacen . "' AND ClaveZona ='" . $this->claveZona . "';");
        $catalogo = new Catalogo();
	$query = $catalogo->obtenerLista($consulta);
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

    public function getIdAlmacen() {
        return $this->idAlmacen;
    }

    public function setIdAlmacen($idAlmacen) {
        $this->idAlmacen = $idAlmacen;
    }

    public function getIdGZona() {
        return $this->idGZona;
    }

    public function setIdGZona($idGZona) {
        $this->idGZona = $idGZona;
    }

    public function getClaveZona() {
        return $this->claveZona;
    }

    public function setClaveZona($claveZona) {
        $this->claveZona = $claveZona;
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
