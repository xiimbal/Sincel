<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class ProveedorZona {

    private $id;
    private $idProveedor;
    private $idSucursal;
    private $gZona;
    private $idZona;
    private $TiempoMaxSolucion;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM k_proveedorzona pz WHERE pz.id_prov_suc_zona='$id'");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idProveedor = $rs['IdProveedor'];
            $this->idSucursal = $rs['IdSucursal'];
            $this->gZona = $rs['idGZona'];
            $this->idZona = $rs['ClaveZona'];
            $this->TiempoMaxSolucion = $rs['TiempoMaximoSolucion'];
        }
        return $query;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO k_proveedorzona(id_prov_suc_zona,IdProveedor,IdSucursal,idGZona,ClaveZona,TiempoMaximoSolucion,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,'" . $this->idProveedor . "','" . $this->idSucursal . "','" . $this->gZona . "','" . $this->idZona . "','" . $this->TiempoMaxSolucion . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE k_proveedorzona SET IdProveedor= '" . $this->idProveedor . "',IdSucursal='" . $this->idSucursal . "',idGZona= '" . $this->gZona . "',ClaveZona= '" . $this->idZona . "',TiempoMaximoSolucion='" . $this->TiempoMaxSolucion . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'
                WHERE id_prov_suc_zona='" . $this->id . "'");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM k_proveedorzona  WHERE id_prov_suc_zona='" . $this->id . "'");
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

    public function getIdProveedor() {
        return $this->idProveedor;
    }

    public function getIdSucursal() {
        return $this->idSucursal;
    }

    public function getGZona() {
        return $this->gZona;
    }

    public function getIdZona() {
        return $this->idZona;
    }

    public function getTiempoMaxSolucion() {
        return $this->TiempoMaxSolucion;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->usuarioModificacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setIdProveedor($idProveedor) {
        $this->idProveedor = $idProveedor;
    }

    public function setIdSucursal($idSucursal) {
        $this->idSucursal = $idSucursal;
    }

    public function setGZona($gZona) {
        $this->gZona = $gZona;
    }

    public function setIdZona($idZona) {
        $this->idZona = $idZona;
    }

    public function setTiempoMaxSolucion($TiempoMaxSolucion) {
        $this->TiempoMaxSolucion = $TiempoMaxSolucion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function setUsuarioModificacion($usuarioModificacion) {
        $this->usuarioModificacion = $usuarioModificacion;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }

}
