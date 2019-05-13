<?php

include_once ("Conexion.class.php");
include_once("Catalogo.class.php");

class PAC {

    private $id_pac;
    private $nombre;
    private $direccion_timbrado;
    private $direccion_cancelacion;
    private $usuario;
    private $password;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;

    public function getTabla() {
        $consulta = ("SELECT * FROM c_pac");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getRegistrobyID() {
        $consulta = ("SELECT * FROM c_pac WHERE id_pac=" . $this->id_pac);        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->nombre = $rs['nombre'];
            $this->direccion_timbrado = $rs['direccion_timbrado'];
            $this->direccion_cancelacion = $rs['direccion_cancelacion'];
            $this->usuario = $rs['usuario'];
            $this->password = $rs['password'];
            return true;
        }
        return false;
    }

    public function nuevoRegistro() {        
        $nombres = "nombre,direccion_timbrado,direccion_cancelacion,usuario,password,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla";
        $values = "'" . $this->nombre . "','" . $this->direccion_timbrado . "','" . $this->direccion_cancelacion . "','" . $this->usuario . "','" . $this->password . "','" . $this->UsuarioCreacion . "',NOW(),'" . $this->UsuarioUltimaModificacion . "',NOW(),'" . $this->Pantalla . "'";
        $consulta = ("INSERT INTO c_pac(" . $nombres . ")
            VALUES(" . $values . ")");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE c_pac SET nombre = '" . $this->nombre . "', direccion_timbrado = '" . $this->direccion_timbrado . "',direccion_cancelacion='" . $this->direccion_cancelacion . "',usuario = '" . $this->usuario . "',password = '" . $this->password . "',UsuarioUltimaModificacion='" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion=NOW(),Pantalla = '" . $this->Pantalla . "' WHERE id_pac='" . $this->id_pac . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistro() {
        $consulta = ("DELETE FROM c_pac WHERE id_pac = '" . $this->id_pac . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getId_pac() {
        return $this->id_pac;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getDireccion_timbrado() {
        return $this->direccion_timbrado;
    }

    public function getDireccion_cancelacion() {
        return $this->direccion_cancelacion;
    }

    public function getUsuario() {
        return $this->usuario;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    public function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    public function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    public function getPantalla() {
        return $this->Pantalla;
    }

    public function setId_pac($id_pac) {
        $this->id_pac = $id_pac;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setDireccion_timbrado($direccion_timbrado) {
        $this->direccion_timbrado = $direccion_timbrado;
    }

    public function setDireccion_cancelacion($direccion_cancelacion) {
        $this->direccion_cancelacion = $direccion_cancelacion;
    }

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    public function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    public function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    public function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

}
