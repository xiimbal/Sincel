<?php

include_once ("Conexion.class.php");
include_once("Catalogo.class.php");

class PartesEquipo {

    private $id;
    private $noPartesEquipo;
    private $noParteComponente;
    private $soportadoMax;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;

    public function getRegistroById($id, $id2) {
        $consulta = ("SELECT * FROM k_parteequipo WHERE NoParteEquipo='" . $id . "' AND NoParteComponente='" . $id2 . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->noParteEquipo = $rs['NoParteEquipo'];
            $this->noParteComponente = $rs['NoParteComponente'];
            $this->soportadoMax = $rs['SoportadoMaximo'];
        }
        return $query;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO k_parteequipo(NoParteEquipo,NoParteComponente,SoportadoMaximo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->noPartesEquipo . "','" . $this->noParteComponente . "','" . $this->soportadoMax . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE k_parteequipo SET SoportadoMaximo = '" . $this->soportadoMax . "', NoParteComponente='" . $this->noParteComponente . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE NoParteEquipo='" . $this->noPartesEquipo . "' AND NoParteComponente='" . $this->id . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM k_parteequipo WHERE NoParteEquipo='" . $this->noPartesEquipo . "' AND NoParteComponente='" . $this->noParteComponente . "';");
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

    public function getNoPartesEquipo() {
        return $this->noPartesEquipo;
    }

    public function setNoPartesEquipo($noPartesEquipo) {
        $this->noPartesEquipo = $noPartesEquipo;
    }

    public function getNoParteComponente() {
        return $this->noParteComponente;
    }

    public function setNoParteComponente($noParteComponente) {
        $this->noParteComponente = $noParteComponente;
    }

    public function getSoportadoMax() {
        return $this->soportadoMax;
    }

    public function setSoportadoMax($soportadoMax) {
        $this->soportadoMax = $soportadoMax;
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
