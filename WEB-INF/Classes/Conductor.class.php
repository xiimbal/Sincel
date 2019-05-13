<?php

include_once("Conexion.class.php");
include_once ("Catalogo.class.php");

/**
 * Description of Conductor
 *
 * @author MAGG
 */
class Conductor {

    private $IdConductor;
    private $Nombre;
    private $ApellidoPaterno;
    private $ApellidoMaterno;
    private $IdUsuario;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_conductor WHERE IdConductor = $id;");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdConductor = $rs['IdConductor'];
            $this->Nombre = $rs['Nombre'];
            $this->ApellidoPaterno = $rs['ApellidoPaterno'];
            $this->ApellidoMaterno = $rs['ApellidoMaterno'];
            $this->IdUsuario = $rs['IdUsuario'];
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
    
    public function getRegitroByIdUsuario($id){
        $consulta = ("SELECT * FROM c_conductor WHERE IdUsuario = $id;");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdConductor = $rs['IdConductor'];
            $this->Nombre = $rs['Nombre'];
            $this->ApellidoPaterno = $rs['ApellidoPaterno'];
            $this->ApellidoMaterno = $rs['ApellidoMaterno'];
            $this->IdUsuario = $rs['IdUsuario'];
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
        $usuariov = "";
        $usuarion = "";
        if ($this->IdUsuario != "") {
            $usuariov = $this->IdUsuario.",";
            $usuarion = "IdUsuario,";
        }
        $consulta = ("INSERT INTO c_conductor(Nombre, ApellidoPaterno, ApellidoMaterno, " . $usuarion . " Activo, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) 
            VALUES('$this->Nombre','$this->ApellidoPaterno','$this->ApellidoMaterno'," . $usuariov . "$this->Activo,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');");
        $catalogo = new Catalogo(); 
        $this->id = $catalogo->insertarRegistro($consulta);
        if ($this->id !=NULL && $this->id !=0) {                        
            return true;
        }        
        return false;
    }

    public function editRegistro() {        
        $usuario = "";
        if ($this->IdUsuario != ""){
            $usuario = "IdUsuario = " . $this->IdUsuario.",";
        }
        $consulta = ("UPDATE c_conductor SET Nombre = '$this->Nombre', ApellidoPaterno = '$this->ApellidoPaterno', ApellidoMaterno='$this->ApellidoMaterno',
                        " . $usuario . " Activo = $this->Activo, UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', 
                        FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla' WHERE IdConductor = " . $this->IdConductor . ";");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistroByIdUsuario($idUsuario) {
        $consulta = "DELETE FROM `c_conductor` WHERE IdUsuario = $idUsuario;";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistro($id) {
        $consulta = "DELETE FROM `c_conductor` WHERE IdConductor = $id;";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getIdConductorByIdUsuario() {
        $consulta = "SELECT IdConductor FROM `c_conductor` WHERE IdUsuario = $this->IdUsuario;";
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        $id = 0;
        while ($rs = mysql_fetch_array($query)) {
            $id = $rs['IdConductor'];
        }        
        return $id;
    }

    public function getIdConductor() {
        return $this->IdConductor;
    }

    public function setIdConductor($IdConductor) {
        $this->IdConductor = $IdConductor;
    }

    public function getNombre() {
        return $this->Nombre;
    }

    public function setNombre($Nombre) {
        $this->Nombre = $Nombre;
    }

    public function getApellidoPaterno() {
        return $this->ApellidoPaterno;
    }

    public function setApellidoPaterno($ApellidoPaterno) {
        $this->ApellidoPaterno = $ApellidoPaterno;
    }

    public function getApellidoMaterno() {
        return $this->ApellidoMaterno;
    }

    public function setApellidoMaterno($ApellidoMaterno) {
        $this->ApellidoMaterno = $ApellidoMaterno;
    }

    public function getIdUsuario() {
        return $this->IdUsuario;
    }

    public function setIdUsuario($IdUsuario) {
        $this->IdUsuario = $IdUsuario;
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
