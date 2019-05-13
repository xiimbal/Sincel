<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class ProveedorServicio {

    private $id;
    private $idProveedor;
    private $idSucursal;
    private $idServicio;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;

    public function getRegistroById($id) {
        $consulta = ("SELECT ps.idProvSucServ,ps.IdProveedor,ps.IdSucursal,ps.IdServicio  FROM k_proveedorservicio ps WHERE ps.idProvSucServ='$id'");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idProveedor = $rs['IdProveedor'];
            $this->idSucursal = $rs['IdSucursal'];
            $this->idServicio = $rs['IdServicio'];
        }
        return $query;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO k_proveedorservicio(idProvSucServ,IdProveedor,IdSucursal,IdServicio,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                        VALUES(0,'" . $this->idProveedor . "','" . $this->idSucursal . "','" . $this->idServicio . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE k_proveedorservicio SET IdProveedor= '" . $this->idProveedor . "',IdSucursal= '" . $this->idSucursal . "',IdServicio= '" . $this->idServicio . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'
                WHERE idProvSucServ='" . $this->id . "'");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM k_proveedorservicio  WHERE idProvSucServ='" . $this->id . "'");
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

    public function getIdProveedor() {
        return $this->idProveedor;
    }

    public function setIdProveedor($idProveedor) {
        $this->idProveedor = $idProveedor;
    }

    public function getIdServicio() {
        return $this->idServicio;
    }

    public function setIdServicio($idServicio) {
        $this->idServicio = $idServicio;
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

    public function getIdSucursal() {
        return $this->idSucursal;
    }

    public function setIdSucursal($idSucursal) {
        $this->idSucursal = $idSucursal;
    }

}
