<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class Zona {

    private $idZona;
    private $nombre;
    private $descripcion;
    private $idGZona;
    private $orden;
    private $activo;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $empresa;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_zona WHERE ClaveZona='" . $id . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idZona = $rs['ClaveZona'];
            $this->nombre = $rs['NombreZona'];
            $this->descripcion = $rs['Descripcion'];
            $this->idGZona = $rs['fk_id_gzona'];
            $this->orden = $rs['Orden'];
            $this->activo = $rs['Activo'];
            return true;
        }
        return false;
    }

    public function newRegistro() {
        $consulta = ("SELECT MAX(CAST(ClaveZona AS UNSIGNED)) AS maximo FROM `c_zona`;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query2 = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query2)) {
            $maximo = (int) $rs['maximo'];
            if ($maximo == "" || $maximo == 0) {
                $maximo = 1000;
            }
            $maximo++;
            $query = $catalogo->obtenerLista("INSERT INTO c_zona(ClaveZona,NombreZona,Descripcion,fk_id_gzona,Orden,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $maximo . "','" . $this->nombre . "','" . $this->descripcion . "','" . $this->idGZona . "','" . $this->orden . "'," . $this->activo . ",'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
            if ($query == 1) {
                return true;
            }
            return false;
        }
        $query = $catalogo->obtenerLista("INSERT INTO c_zona(ClaveZona,NombreZona,Descripcion,fk_id_gzona,Orden,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->idZona . "','" . $this->nombre . "','" . $this->descripcion . "','" . $this->idGZona . "','" . $this->orden . "'," . $this->activo . ",'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE c_zona SET NombreZona = '" . $this->nombre . "',Descripcion = '" . $this->descripcion . "',fk_id_gzona = '" . $this->idGZona . "',Orden = '" . $this->orden . "', Activo = " . $this->activo . ",UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE ClaveZona='" . $this->idZona . "';");
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
        $consulta = ("DELETE FROM c_zona WHERE ClaveZona = '" . $this->idZona . "';");
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

    public function getIdZona() {
        return $this->idZona;
    }

    public function setIdZona($idZona) {
        $this->idZona = $idZona;
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

    public function getIdGZona() {
        return $this->idGZona;
    }

    public function setIdGZona($idGZona) {
        $this->idGZona = $idGZona;
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

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
}

?>
