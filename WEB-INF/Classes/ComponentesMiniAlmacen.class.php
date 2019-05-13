<?php
include_once ("Conexion.class.php");
include_once ("Catalogo.class.php");

class ComponentesMiniAlmacen {

    private $id;
    private $minialmacen;
    private $noParte;
    private $cantidadExistente;
    private $cantidadMinima;
    private $cantidadMaxima;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;

    public function getRegistroById($id, $id2) {
        $consulta = ("SELECT * FROM k_componentes_miniAlmacen WHERE IdMiniAlmacen='" . $id . "' AND NoParteComponente='" . $id2 . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->minialmacen = $rs['IdMiniAlmacen'];
            $this->noParte = $rs['NoParteComponente'];
            $this->cantidadExistente = $rs['CantidadExistencia'];
            $this->cantidadMinima = $rs['CantidadMinima'];
            $this->cantidadMaxima = $rs['CantidadMaxima'];
            return true;
        }
        return false;
    }

    public function verificar() {
        $consulta = ("SELECT * FROM k_componentes_miniAlmacen WHERE IdMiniAlmacen='" . $this->minialmacen . "' AND NoParteComponente='" . $this->noParte . "'");
        $catalogo = new Catalogo(); $verificar = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($verificar) == 0) {
            return true;
        }
        return false;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO k_componentes_miniAlmacen(IdMiniAlmacen,NoParteComponente,CantidadExistencia,CantidadMinima,CantidadMaxima,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->minialmacen . "','" . $this->noParte . "','" . $this->cantidadExistente . "','" . $this->cantidadMinima . "','" . $this->cantidadMaxima . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE k_componentes_miniAlmacen SET NoParteComponente = '" . $this->noParte . "',CantidadExistencia = '" . $this->cantidadExistente . "',CantidadMinima = '" . $this->cantidadMinima . "',CantidadMaxima = '" . $this->cantidadMaxima . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'
            WHERE IdMiniAlmacen='" . $this->minialmacen . "' AND NoParteComponente='" . $this->id . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM k_componentes_miniAlmacen WHERE IdMiniAlmacen='" . $this->minialmacen . "' AND NoParteComponente='" . $this->noParte . "';");
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

    public function getMinialmacen() {
        return $this->minialmacen;
    }

    public function setMinialmacen($minialmacen) {
        $this->minialmacen = $minialmacen;
    }

    public function getNoParte() {
        return $this->noParte;
    }

    public function setNoParte($noParte) {
        $this->noParte = $noParte;
    }

    public function getCantidadExistente() {
        return $this->cantidadExistente;
    }

    public function setCantidadExistente($cantidadExistente) {
        $this->cantidadExistente = $cantidadExistente;
    }

    public function getCantidadMinima() {
        return $this->cantidadMinima;
    }

    public function setCantidadMinima($cantidadMinima) {
        $this->cantidadMinima = $cantidadMinima;
    }

    public function getCantidadMaxima() {
        return $this->cantidadMaxima;
    }

    public function setCantidadMaxima($cantidadMaxima) {
        $this->cantidadMaxima = $cantidadMaxima;
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
