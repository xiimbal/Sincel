<?php

include_once("Catalogo.class.php");

/**
 * Description of Giro
 *
 * @author MAGG
 */
class Giro {
    private $IdGiro;
    private $Nombre;
    private $Descripcion;
    private $Orden;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    
    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_giro WHERE IdGiro = '" . $id . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdGiro = $rs['IdGiro'];
            $this->Nombre = $rs['Nombre'];
            $this->Descripcion = $rs['Descripcion'];
            $this->Orden = $rs['Orden'];
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
        $consulta = ("INSERT INTO c_giro(IdGiro,Nombre,Descripcion,Orden,Activo,UsuarioCreacion,FechaCreacion,
            UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,'$this->Nombre','$this->Descripcion',1,$this->Activo,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla')");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE c_giro SET Nombre = '$this->Nombre', Descripcion = '$this->Descripcion',
            Activo = " . $this->Activo . ",UsuarioUltimaModificacion = '" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion = NOW(),
            Pantalla = '" . $this->Pantalla . "' WHERE IdGiro='" . $this->IdGiro . "';");
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
        $consulta = ("DELETE FROM c_giro WHERE IdGiro = '" . $this->IdGiro . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getIdGiro() {
        return $this->IdGiro;
    }

    public function setIdGiro($IdGiro) {
        $this->IdGiro = $IdGiro;
    }

    public function getNombre() {
        return $this->Nombre;
    }

    public function setNombre($Nombre) {
        $this->Nombre = $Nombre;
    }

    public function getDescripcion() {
        return $this->Descripcion;
    }

    public function setDescripcion($Descripcion) {
        $this->Descripcion = $Descripcion;
    }

    public function getOrden() {
        return $this->Orden;
    }

    public function setOrden($Orden) {
        $this->Orden = $Orden;
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
