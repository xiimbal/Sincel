<?php

include_once ("Conexion.class.php");
include_once ("Catalogo.class.php");

class Campania {
    
    private $idCampania;
    private $activo;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $localidad;
    private $cliente;
    private $descripcion;
    private $area;
    
    public function getRegistroById($id) {
        $catalogo = new Catalogo();
	$query = $catalogo->obtenerLista("SELECT CA.*, CCC.ClaveCliente FROM c_area CA JOIN c_centrocosto CCC ON CA.ClaveCentroCosto=CCC.ClaveCentroCosto WHERE IdArea='" . $id . "'");        
        if ($rs = mysql_fetch_array($query)) {
            $this->idCampania = $rs['IdArea'];
            $this->cliente = $rs['ClaveCliente'];
            $this->localidad = $rs['ClaveCentroCosto'];
            $this->descripcion= $rs['Descripcion'];
            $this->area= $rs['IdEstado'];
            $this->activo = $rs['Activo'];
            return true;
        }
        return false;
    }
    
     public function newRegistro() { 
        $catalogo = new Catalogo();
        $consulta = "INSERT INTO c_area(Descripcion,IdEstado,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,ClaveCentroCosto)
            VALUES('". $this->descripcion . "','" . $this->area . "','". $this->activo . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla ."','".$this->localidad."');";
        
        
	$this->idCampania = $catalogo->insertarRegistro($consulta);             
        if ($this->idCampania != NULL && $this->idCampania != 0) {
            return true;
        }
        return false;
    }
    
    
    public function editRegistro() {
        $catalogo = new Catalogo();
        
            $query = $catalogo->obtenerLista("UPDATE c_area SET Descripcion ='". $this->descripcion . "', Activo = " . $this->activo . ",UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "', ClaveCentroCosto ='". $this->localidad . "', IdEstado ='". $this->area . "' WHERE IdArea='" . $this->idCampania . "';");
        
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistro() {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("DELETE FROM c_area WHERE IdArea = '" . $this->idCampania . "';");        
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function getIdCampania() {
        return $this->idCampania;
    }

    public function setIdCampania($idCampania) {
        $this->idCampania = $idCampania;
    }
    
    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }
    
    public function getArea() {
        return $this->area;
    }

    public function setArea($area) {
        $this->area = $area;
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
    
    public function getLocalidad() {
        return $this->localidad;
    }

    public function setLocalidad($localidad) {
        $this->localidad = $localidad;
    }
    
    public function getCliente() {
        return $this->cliente;
    }

    public function setCliente($cliente) {
        $this->cliente = $cliente;
    }

}
?>