<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class ProveedorProducto {

    private $id;
    private $idProveedor;
    private $idSucursal;
    private $idProducto;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;

    public function getRegistroById($id) {
        $consulta = ("SELECT pp.Id_prov_suc_prod,pp.IdProveedor,pp.IdSucursal,pp.IdProducto FROM k_proveedorproducto pp WHERE pp.Id_prov_suc_prod='$id'");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->idProveedor = $rs['IdProveedor'];
            $this->idSucursal = $rs['IdSucursal'];
            $this->idProducto = $rs['IdProducto'];
        }
        return $query;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO k_proveedorproducto(Id_prov_suc_prod,IdProveedor,IdSucursal,IdProducto,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,'" . $this->idProveedor . "','" . $this->idSucursal . "','" . $this->idProducto . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE k_proveedorproducto SET IdProveedor= '" . $this->idProveedor . "',IdSucursal= '" . $this->idSucursal . "',IdProducto= '" . $this->idProducto . "',
                        UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'
                        WHERE Id_prov_suc_prod='" . $this->id . "'");
      
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM k_proveedorproducto WHERE Id_prov_suc_prod='" . $this->id . "'");
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

    public function getIdProducto() {
        return $this->idProducto;
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

    public function setIdProducto($idProducto) {
        $this->idProducto = $idProducto;
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
