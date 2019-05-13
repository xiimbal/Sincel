<?php
include_once("Conexion.class.php");
include_once("Catalogo.class.php");
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Productos_Genesis{
    private $id;
    private $nombre;
    private $id_precio;
    private $usuarioCreacion;
    private $fechaCreacion;
    private $UsuarioModificacion;
    private $fechaModificacion;
    private $pantalla;
    
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

    public function getId_precio() {
        return $this->id_precio;
    }

    public function setId_precio($id_precio) {
        $this->id_precio = $id_precio;
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
    
    public function actualizar(){
        $consulta = ("UPDATE c_productos_genesis SET c_productos_genesis.FechaUltimaModificacion=NOW(),
c_productos_genesis.Id_Precio='".$this->id_precio."',c_productos_genesis.Nombre='".$this->nombre."',
c_productos_genesis.UsuarioUltimaModificacion='".$this->UsuarioModificacion."'
WHERE c_productos_genesis.Id_Producto_Genesis=".$this->id.";");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function insertar(){
        $consulta = ("INSERT INTO c_productos_genesis(Nombre,Id_Precio,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
VALUES('".$this->nombre."','".$this->id_precio."','".$this->usuarioCreacion."',NOW(),'".$this->UsuarioModificacion."',NOW(),'PHP Productos_Genesis');");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function getRegistro(){
        $consulta = ("SELECT c_productos_genesis.Nombre AS Nombre,c_productos_genesis.Id_Precio AS IdPrecio FROM c_productos_genesis WHERE c_productos_genesis.Id_Producto_Genesis=".$this->id);
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->nombre = $rs['Nombre'];
            $this->id_precio = $rs['IdPrecio'];
        }
        return $query;
    }
    public function deleteRegistro() {
        $consulta = ("DELETE FROM c_productos_genesis WHERE c_productos_genesis.Id_Producto_Genesis = " . $this->id . ";");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
}
?>
