<?php

include_once("Catalogo.class.php");

/**
 * Description of TipoContacto
 *
 * @author MAGG
 */
class ContactoEmpleado {
    private $IdFormaContacto;
    private $Nombre;
    private $Descripcion;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    
    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM `c_formacontacto` WHERE IdFormaContacto = $id;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdFormaContacto = $rs['IdFormaContacto'];
            $this->Nombre = $rs['Nombre'];
            $this->Descripcion = $rs['Descripcion'];
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
    
    public function newRegistro(){
        $consulta = ("INSERT INTO c_formacontacto(IdFormaContacto, Nombre, Descripcion, Activo, UsuarioCreacion, "
                . "FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) VALUES"
                . "(0,'$this->Nombre','$this->Descripcion',$this->Activo,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');");        
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $this->IdFormaContacto = $catalogo->insertarRegistro($consulta);
        if ($this->IdFormaContacto!=NULL && $this->IdFormaContacto!=0) {           
            return true;
        }
        return false;
    }
            
    public function editRegistro() {
        $consulta = ("UPDATE c_formacontacto SET Nombre = '" . $this->Nombre . "',Descripcion='" . $this->Descripcion . "',
            Activo = " . $this->Activo . ",UsuarioUltimaModificacion = '" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion = NOW(),
            Pantalla = '" . $this->Pantalla . "' WHERE IdFormaContacto = '" . $this->IdFormaContacto . "';");
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
        $consulta = ("DELETE FROM c_formacontacto WHERE IdFormaContacto = '" . $this->IdFormaContacto . "';");
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
    
    function getIdFormaContacto() {
        return $this->IdFormaContacto;
    }

    function getNombre() {
        return $this->Nombre;
    }

    function getDescripcion() {
        return $this->Descripcion;
    }

    function getActivo() {
        return $this->Activo;
    }

    function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    function getPantalla() {
        return $this->Pantalla;
    }

    function setIdFormaContacto($IdFormaContacto) {
        $this->IdFormaContacto = $IdFormaContacto;
    }

    function setNombre($Nombre) {
        $this->Nombre = $Nombre;
    }

    function setDescripcion($Descripcion) {
        $this->Descripcion = $Descripcion;
    }

    function setActivo($Activo) {
        $this->Activo = $Activo;
    }

    function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }


}
