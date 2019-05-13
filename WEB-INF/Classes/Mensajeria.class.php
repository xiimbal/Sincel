<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

/**
 * Description of Mensajeria
 *
 * @author MAGG
 */
class Mensajeria {

    private $IdMensajeria;
    private $Nombre;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_mensajeria WHERE IdMensajeria = $id;");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdMensajeria = $rs['IdMensajeria'];
            $this->Nombre = $rs['Nombre'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO c_mensajeria(Nombre, Activo, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) 
            VALUES('$this->Nombre',$this->Activo,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');");
        $catalogo = new Catalogo(); $this->id = $catalogo->insertarRegistro($consulta);
        if ($this->id!=NULL && $this->id!=0) {                       
            return true;
        }        
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE c_mensajeria SET Nombre = '$this->Nombre',Activo = $this->Activo, UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', 
                        FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla' WHERE IdMensajeria = " . $this->IdMensajeria . ";");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistro($id) {
        $consulta = "DELETE FROM `c_mensajeria` WHERE IdMensajeria = $id;";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getIdMensajeria() {
        return $this->IdMensajeria;
    }

    public function setIdMensajeria($IdMensajeria) {
        $this->IdMensajeria = $IdMensajeria;
    }

    public function getNombre() {
        return $this->Nombre;
    }

    public function setNombre($Nombre) {
        $this->Nombre = $Nombre;
    }

    public function getActivo() {
        return $this->Activo;
    }

    public function setActivo($Activo) {
        $this->Activo = $Activo;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    public function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    public function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    public function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    public function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    public function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    public function getPantalla() {
        return $this->Pantalla;
    }

    public function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

}

?>
