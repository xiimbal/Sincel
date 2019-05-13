<?php

include_once ("Conexion.class.php");
include_once("Catalogo.class.php");

class ResponsableAlmacen {

    private $id;
    private $id2;
    private $usuario;
    private $almacen;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;

    public function getRegistroById($id, $id2) {
        $consulta = ("SELECT * FROM k_responsablealmacen WHERE IdUsuario='" . $id . "' AND IdAlmacen='" . $id2 . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->usuario = $rs['IdUsuario'];
            $this->almacen = $rs['IdAlmacen'];
        }
        return $query;
    }

    public function newRegistro() {
        $consulta = ("SELECT * FROM k_responsablealmacen WHERE IdUsuario='" . $this->usuario . "' AND IdAlmacen='" . $this->almacen . "'");
        $catalogo = new Catalogo(); $verificar = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($verificar) == 0) {
            $consulta = ("INSERT INTO k_responsablealmacen(IdUsuario,IdAlmacen,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->usuario . "','" . $this->almacen . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
            $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return "1"; //REGISTRADO
            }
            return "2"; //NO SE REGISTRO
        } else {
            return "3"; //EXISTE 
        }
    }

    public function editRegistro() {
        $consulta = ("SELECT * FROM k_responsablealmacen WHERE IdUsuario='" . $this->usuario . "' AND IdAlmacen='" . $this->almacen . "'");
        $catalogo = new Catalogo(); $verificar = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($verificar) == 0) {            
            $consulta = ("UPDATE k_responsablealmacen SET IdUsuario='" . $this->usuario . "',IdAlmacen= '" . $this->almacen . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' 
                WHERE IdUsuario='" . $this->id . "' AND IdAlmacen ='" . $this->id2 . "';");
            $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return "1"; //REGISTRADO
            }
            return "2"; //NO SE REGISTRO
        } else {
            return "3"; //EXISTE   
        }
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM k_responsablealmacen WHERE IdUsuario='" . $this->usuario . "' AND IdAlmacen ='" . $this->almacen . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getId2() {
        return $this->id2;
    }

    public function setId2($id2) {
        $this->id2 = $id2;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getUsuario() {
        return $this->usuario;
    }

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    public function getAlmacen() {
        return $this->almacen;
    }

    public function setAlmacen($almacen) {
        $this->almacen = $almacen;
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
