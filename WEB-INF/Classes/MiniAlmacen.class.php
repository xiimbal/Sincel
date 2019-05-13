<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class MiniAlmacen {

    private $idminiAlmacen;
    private $nombre;
    private $descripcion;
    private $claveCentroCosto;
    private $claveEncargado;
    private $activo;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_minialmacen WHERE IdMiniAlmacen='" . $id . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idminiAlmacen = $rs['IdMiniAlmacen'];
            $this->nombre = $rs['Nombre'];
            $this->descripcion = $rs['Descripcion'];
            $this->claveCentroCosto = $rs['ClaveCentroCosto'];
            $this->claveEncargado = $rs['ClaveEncargado'];
            $this->activo = $rs['Activo'];
            return true;
        }
        return false;
    }

    public function verificar() {
        $consulta = ("SELECT * FROM c_minialmacen WHERE ClaveCentroCosto='" . $this->claveCentroCosto . "'");
        $catalogo = new Catalogo(); $verificar = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($verificar) == 0) {
            return true;
        }
        return false;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO c_minialmacen(IdMiniAlmacen,Nombre,Descripcion,ClaveCentroCosto,ClaveEncargado,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,'" . $this->nombre . "','" . $this->descripcion . "','" . $this->claveCentroCosto . "','" . $this->claveEncargado . "'," . $this->activo . ",'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE c_minialmacen SET Nombre = '" . $this->nombre . "',Descripcion = '" . $this->descripcion . "',ClaveCentroCosto = '" . $this->claveCentroCosto . "',ClaveEncargado = '" . $this->claveEncargado . "', Activo = " . $this->activo . ",UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE IdMiniAlmacen='" . $this->idminiAlmacen . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM c_minialmacen WHERE IdMiniAlmacen = '" . $this->idminiAlmacen . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getIdminiAlmacen() {
        return $this->idminiAlmacen;
    }

    public function setIdminiAlmacen($idminiAlmacen) {
        $this->idminiAlmacen = $idminiAlmacen;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function getClaveCentroCosto() {
        return $this->claveCentroCosto;
    }

    public function setClaveCentroCosto($claveCentroCosto) {
        $this->claveCentroCosto = $claveCentroCosto;
    }

    public function getClaveEncargado() {
        return $this->claveEncargado;
    }

    public function setClaveEncargado($claveEncargado) {
        $this->claveEncargado = $claveEncargado;
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
