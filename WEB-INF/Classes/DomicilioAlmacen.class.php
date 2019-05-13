<?php

include_once("Catalogo.class.php");

class DomicilioAlmacen {

    private $idDomicilio;
    private $idAlmacen;
    private $nombreAlmacen;
    private $calle;
    private $exterior;
    private $interior;
    private $colonia;
    private $ciudad;
    private $estado;
    private $delegacion;
    private $pais;
    private $cp;
    private $Latitud;
    private $Longitud;
    private $usuarioCreacion;
    private $usuarioUltimaCreacion;
    private $pantalla;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_domicilio_almacen WHERE IdAlmacen ='" . $id . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idDomicilio = $rs['IdDomicilio'];
            $this->idAlmacen = $rs['IdAlmacen'];
            $this->calle = $rs['Calle'];
            $this->exterior = $rs['NoExterior'];
            $this->interior = $rs['NoInterior'];
            $this->colonia = $rs['Colonia'];
            $this->ciudad = $rs['Ciudad'];
            $this->estado = $rs['Estado'];
            $this->delegacion = $rs['Delegacion'];
            $this->pais = $rs['Pais'];
            $this->cp = $rs['CodigoPostal'];
            $this->Latitud = $rs['Latitud'];
            $this->Longitud = $rs['Longitud'];
            return true;
        }
        return false;
    }

    public function getRegistroByIdAlmacen() {
        $consulta = ("SELECT da.*,(SELECT nombre_almacen 
                FROM c_almacen WHERE id_almacen = da.IdAlmacen) AS nombre_almacen
            FROM c_domicilio_almacen da WHERE da.IdAlmacen = $this->idAlmacen");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idDomicilio = $rs['IdDomicilio'];
            $this->idAlmacen = $rs['IdAlmacen'];
            $this->nombreAlmacen = $rs['nombre_almacen'];
            $this->calle = $rs['Calle'];
            $this->exterior = $rs['NoExterior'];
            $this->interior = $rs['NoInterior'];
            $this->colonia = $rs['Colonia'];
            $this->ciudad = $rs['Ciudad'];
            $this->estado = $rs['Estado'];
            $this->delegacion = $rs['Delegacion'];
            $this->pais = $rs['Pais'];
            $this->cp = $rs['CodigoPostal'];
            $this->Latitud = $rs['Latitud'];
            $this->Longitud = $rs['Longitud'];
            return true;
        }
        return false;
    }
    
    public function newRegistro() {
        $catalogo = new Catalogo(); 
        $consulta = "SELECT IdDomicilio FROM `c_domicilio_almacen` WHERE IdAlmacen = $this->idAlmacen;";
        $result = $catalogo->obtenerLista($consulta);     
        if(!isset($this->Latitud) || empty($this->Latitud)){
            $this->Latitud = "NULL";
        }
        if(!isset($this->Longitud) || empty($this->Longitud)){
            $this->Longitud = "NULL";
        }
        if(mysql_num_rows($result) == 0){
            $consulta = ("INSERT INTO c_domicilio_almacen(IdAlmacen, Calle, NoExterior, NoInterior,Colonia,Ciudad,Estado,Delegacion,Pais,CodigoPostal,Latitud,Longitud,
                UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) 
            VALUES('$this->idAlmacen','$this->calle','$this->exterior','$this->interior','$this->colonia','$this->ciudad','$this->estado','$this->delegacion','$this->pais','$this->cp',"
                    . "$this->Latitud, $this->Longitud,'$this->usuarioCreacion',NOW(),'$this->usuarioUltimaCreacion',NOW(),'$this->pantalla');");                            
            $this->idDomicilio = $catalogo->insertarRegistro($consulta);
            if ($this->idDomicilio!=NULL && $this->idDomicilio != 0) {
                return true;
            }
            return false;
        }else{
            $consulta = ("UPDATE c_domicilio_almacen SET Calle = '$this->calle', NoExterior = '$this->exterior', NoInterior='$this->interior',Latitud=$this->Latitud,Longitud=$this->Longitud,
                        Colonia = '$this->colonia',Pais = '$this->pais', Ciudad ='$this->ciudad',Estado ='$this->estado',Delegacion ='$this->delegacion',CodigoPostal ='$this->cp',Ciudad ='$this->ciudad', UsuarioUltimaModificacion = '$this->usuarioUltimaCreacion', 
                        FechaUltimaModificacion = now(), Pantalla = '$this->pantalla' WHERE IdAlmacen = " . $this->idAlmacen . ";");            
            $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return true;
            }
            return false;
        }        
    }

    public function editRegistro() {
        return $this->newRegistro();
    }

    public function deleteRegistro() {
        $consulta = "DELETE FROM c_domicilio_almacen WHERE IdAlmacen ='" . $this->idAlmacen . "';";
        $consulta = ($consulta);
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getIdDomicilio() {
        return $this->idDomicilio;
    }

    public function getIdAlmacen() {
        return $this->idAlmacen;
    }

    public function getCalle() {
        return $this->calle;
    }

    public function getExterior() {
        return $this->exterior;
    }

    public function getInterior() {
        return $this->interior;
    }

    public function getColonia() {
        return $this->colonia;
    }

    public function getCiudad() {
        return $this->ciudad;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getDelegacion() {
        return $this->delegacion;
    }

    public function getPais() {
        return $this->pais;
    }

    public function getCp() {
        return $this->cp;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function getUsuarioUltimaCreacion() {
        return $this->usuarioUltimaCreacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function setIdDomicilio($idDomicilio) {
        $this->idDomicilio = $idDomicilio;
    }

    public function setIdAlmacen($idAlmacen) {
        $this->idAlmacen = $idAlmacen;
    }

    public function setCalle($calle) {
        $this->calle = $calle;
    }

    public function setExterior($exterior) {
        $this->exterior = $exterior;
    }

    public function setInterior($interior) {
        $this->interior = $interior;
    }

    public function setColonia($colonia) {
        $this->colonia = $colonia;
    }

    public function setCiudad($ciudad) {
        $this->ciudad = $ciudad;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setDelegacion($delegacion) {
        $this->delegacion = $delegacion;
    }

    public function setPais($pais) {
        $this->pais = $pais;
    }

    public function setCp($cp) {
        $this->cp = $cp;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function setUsuarioUltimaCreacion($usuarioUltimaCreacion) {
        $this->usuarioUltimaCreacion = $usuarioUltimaCreacion;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }

    public function getLatitud() {
        return $this->Latitud;
    }

    public function getLongitud() {
        return $this->Longitud;
    }

    public function setLatitud($Latitud) {
        $this->Latitud = $Latitud;
    }

    public function setLongitud($Longitud) {
        $this->Longitud = $Longitud;
    }
    
    function getNombreAlmacen() {
        return $this->nombreAlmacen;
    }

    function setNombreAlmacen($nombreAlmacen) {
        $this->nombreAlmacen = $nombreAlmacen;
    }
}

?>