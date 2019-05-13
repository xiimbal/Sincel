<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

/**
 * Description of Producto
 *
 * @author MAGG
 */
class Producto {

    private $id;
    private $nombre;
    private $descripcion;
    private $orden;
    private $activo;
    private $usuarioCreacion;
    private $fechaCreacion;
    private $UsuarioModificacion;
    private $fechaModificacion;
    private $pantalla;

    public function getUsuarios() {
        $consulta = ("SELECT * FROM `c_producto` ORDER BY Nombre");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM `c_producto` WHERE IdProducto = " . $id . ";");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->id = $id;
            $this->nombre = $rs['Nombre'];
            $this->descripcion = $rs['Descripcion'];
            $this->orden = $rs['Orden'];
            $this->activo = $rs['Activo'];
            $this->usuarioCreacion = $rs['UsuarioCreacion'];
            $this->fechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioModificacion = $rs['UsuarioUltimaModificacion'];
            $this->fechaModificacion = $rs['FechaUltimaModificacion'];
            $this->pantalla = $rs['Pantalla'];
        }
        return $query;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO c_producto (IdProducto,Nombre,Descripcion,Orden,Activo,UsuarioCreacion,FechaCreacion,
            UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES(0,'$this->nombre','$this->descripcion',$this->orden,$this->activo,
            '" . $this->usuarioCreacion . "',now(),'$this->UsuarioModificacion',now(),'$this->pantalla')");
        $catalogo = new Catalogo();
        $this->id = $catalogo->insertarRegistro($consulta);
        if ($this->id != NULL && $this->id != 0) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE c_producto SET Nombre = '$this->nombre', Descripcion = '$this->descripcion', Orden = $this->orden, Activo = $this->activo,
            UsuarioUltimaModificacion = '$this->UsuarioModificacion', FechaUltimaModificacion = now(), Pantalla = '$this->pantalla' 
            WHERE IdProducto = " . $this->id . ";");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistro() {
        $consulta = ("DELETE FROM c_producto WHERE IdProducto = " . $this->id . ";");
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

    public function getOrden() {
        return $this->orden;
    }

    public function setOrden($orden) {
        $this->orden = $orden;
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

    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->UsuarioModificacion;
    }

    public function setUsuarioModificacion($UsuarioModificacion) {
        $this->UsuarioModificacion = $UsuarioModificacion;
    }

    public function getFechaModificacion() {
        return $this->fechaModificacion;
    }

    public function setFechaModificacion($fechaModificacion) {
        $this->fechaModificacion = $fechaModificacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }

}

?>
