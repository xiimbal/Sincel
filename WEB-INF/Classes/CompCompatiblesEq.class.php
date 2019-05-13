<?php

include_once ("Conexion.class.php");
include_once ("Catalogo.class.php");

class CompCompatiblesEq {

    private $id;
    private $noParteEquipo;
    private $noParteComponente;
    private $soportado;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $empresa;

    public function getRegistroById($id, $id2) {
        $consulta = ("SELECT * FROM k_equipocomponentecompatible WHERE NoParteEquipo='" . $id . "' AND NoParteComponente='" . $id2 . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->noParteEquipo = $rs['NoParteEquipo'];
            $this->noParteComponente = $rs['NoParteComponente'];
            $this->soportado = $rs['Soportado'];
        }
        return $query;
    }

    public function getComponentesCompatibles($NoParteEquipo, $TipoComponente) {
        $consulta = ("SELECT kecc.NoParteComponente 
            FROM `k_equipocomponentecompatible` AS kecc 
            LEFT JOIN c_componente AS c ON c.NoParte = kecc.NoParteComponente
            WHERE kecc.NoParteEquipo = '$NoParteEquipo' AND c.IdTipoComponente = $TipoComponente;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        $compatibles = array();
        while ($rs = mysql_fetch_array($query)) {
            array_push($compatibles, $rs['NoParteComponente']);
        }
        return $compatibles;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO k_equipocomponentecompatible(NoParteEquipo,NoParteComponente,Soportado,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->noParteEquipo . "','" . $this->noParteComponente . "','" . $this->soportado . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE k_equipocomponentecompatible SET Soportado = '" . $this->soportado . "', NoParteComponente='" . $this->noParteComponente . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE NoParteEquipo='" . $this->noParteEquipo . "' AND NoParteComponente='" . $this->id . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM k_equipocomponentecompatible WHERE NoParteEquipo='" . $this->noParteEquipo . "' AND NoParteComponente='" . $this->noParteComponente . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
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

    public function getNoParteEquipo() {
        return $this->noParteEquipo;
    }

    public function setNoParteEquipo($noParteEquipo) {
        $this->noParteEquipo = $noParteEquipo;
    }

    public function getNoParteComponente() {
        return $this->noParteComponente;
    }

    public function setNoParteComponente($noParteComponente) {
        $this->noParteComponente = $noParteComponente;
    }

    public function getSoportado() {
        return $this->soportado;
    }

    public function setSoportado($soportado) {
        $this->soportado = $soportado;
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

    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}

?>
