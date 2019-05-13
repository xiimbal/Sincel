<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

/**
 * Description of Vehiculo
 *
 * @author MAGG
 */
class Vehiculo {

    private $IdVehiculo;
    private $Placas;
    private $Modelo;
    private $Capacidad;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_vehiculo WHERE IdVehiculo = $id;");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdVehiculo = $rs['IdVehiculo'];
            $this->Placas = $rs['Placas'];
            $this->Modelo = $rs['Modelo'];
            $this->Capacidad = $rs['Capacidad'];
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
        $consulta = ("INSERT INTO c_vehiculo(Placas,Modelo,Capacidad,Activo, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) 
            VALUES('$this->Placas','$this->Modelo',$this->Capacidad,$this->Activo,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');");
        $catalogo = new Catalogo(); $this->id = $catalogo->insertarRegistro($consulta);
        if ($this->id!=NULL && $this->id!=0) {            
            return true;
        }        
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE c_vehiculo SET Placas = '$this->Placas',Modelo='" . $this->Modelo . "',Capacidad=" . $this->Capacidad . ",Activo = $this->Activo, UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', 
                        FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla' WHERE IdVehiculo = " . $this->IdVehiculo . ";");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistro($id) {
        $consulta = "DELETE FROM `c_vehiculo` WHERE IdVehiculo = $id;";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getIdVehiculo() {
        return $this->IdVehiculo;
    }

    public function setIdVehiculo($IdVehiculo) {
        $this->IdVehiculo = $IdVehiculo;
    }

    public function getPlacas() {
        return $this->Placas;
    }

    public function setPlacas($Placas) {
        $this->Placas = $Placas;
    }

    public function getModelo() {
        return $this->Modelo;
    }

    public function setModelo($Modelo) {
        $this->Modelo = $Modelo;
    }

    public function getCapacidad() {
        return $this->Capacidad;
    }

    public function setCapacidad($Capacidad) {
        $this->Capacidad = $Capacidad;
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
