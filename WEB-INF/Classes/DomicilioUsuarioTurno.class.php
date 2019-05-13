<?php

    include_once("Catalogo.class.php");
    include_once ("DomicilioUsuarioTurnoDetalle.class.php");
    
class DomicilioUsuarioTurno {
    private $idDomicilio;
    private $idUsuario;
    private $turno;
    private $campania;
    private $slcTurnos;
    private $slcCampanias;
    private $area;
    private $contacto;
    private $vehiculo;
    private $codigoB;
    private $calle;
    private $exterior;
    private $interior;
    private $colonia;
    private $ciudad;
    private $estado;
    private $delegacion;
    private $cp;
    private $localidad;
    private $Latitud;
    private $Longitud;
    private $usuarioCreacion;
    private $usuarioUltimaCreacion;
    private $pantalla;
    
        public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_domicilio_usturno WHERE IdUsuario ='" . $id . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idDomicilio = $rs['IdDomicilio'];
            $this->idUsuario = $rs['IdUsuario'];
            $this->turno = $rs['IdTurno'];
            $this->campania = $rs['IdCampania'];
            $this->area = $rs['IdArea'];
            $this->contacto = $rs['IdFormaContacto'];
            $this->vehiculo=$rs['IdVehiculo'];
            $this->codigoB = $rs['Codigo'];
            $this->calle = $rs['Calle'];
            $this->exterior = $rs['NoExterior'];
            $this->interior = $rs['NoInterior'];
            $this->colonia = $rs['Colonia'];
            $this->ciudad = $rs['Ciudad'];
            $this->estado = $rs['Estado'];
            $this->delegacion = $rs['Delegacion'];
            $this->cp = $rs['CodigoPostal'];
            $this->localidad = $rs['Localidad'];
            $this->Latitud = $rs['Latitud'];
            $this->Longitud = $rs['Longitud'];
            
            return true;
        }
        return false;
    }
    
    public function newRegistro() {
        $catalogo = new Catalogo(); 
        $consulta = "SELECT IdDomicilio FROM `c_domicilio_usturno` WHERE IdUsuario = $this->idUsuario;";
        $result = $catalogo->obtenerLista($consulta);     
        if(!isset($this->Latitud) || empty($this->Latitud)){
            $this->Latitud = "NULL";
        }
        if(!isset($this->Longitud) || empty($this->Longitud)){
            $this->Longitud = "NULL";
        }
        if(!isset($this->codigoB) || empty($this->codigoB)){
            $this->codigoB = "NULL";
        }
        if(!isset($this->vehiculo) || empty($this->vehiculo)){
            $this->vehiculo = "NULL";
        }
        if(!isset($this->turno) || empty($this->turno)){
            $this->turno = "NULL";
        }
        if(!isset($this->campania) || empty($this->campania)){
            $this->campania = "NULL";
        }
        if(!isset($this->contacto) || empty($this->contacto)){
            $this->contacto = "NULL";
        }
        if(mysql_num_rows($result) == 0){
            $consulta = ("INSERT INTO c_domicilio_usturno(IdUsuario, IdTurno, IdCampania, IdArea, IdFormaContacto, IdVehiculo, Codigo, Calle, NoExterior, NoInterior,Colonia,Ciudad,Estado,Delegacion,CodigoPostal,Localidad,Latitud,Longitud,
                UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) 
            VALUES('$this->idUsuario',$this->turno,$this->campania,$this->area,$this->contacto,$this->vehiculo,'$this->codigoB','$this->calle','$this->exterior','$this->interior','$this->colonia','$this->ciudad','$this->estado','$this->delegacion','$this->cp','$this->localidad',"
                    . "$this->Latitud, $this->Longitud,'$this->usuarioCreacion',NOW(),'$this->usuarioUltimaCreacion',NOW(),'$this->pantalla');");
            $this->idDomicilio = $catalogo->insertarRegistro($consulta);
            if ($this->idDomicilio!=NULL && $this->idDomicilio != 0) {
                if((isset($this->slcCampanias) || !empty($this->slcCampanias)) && (isset($this->slcTurnos) || !empty($this->slcCampanias))){
                $this->insertarMultiConceptos($this->slcCampanias, $this->slcTurnos);
                }
                return true;
            }
            return false;
        }else{
            $consulta = ("UPDATE c_domicilio_usturno SET IdTurno =".$this->turno.",IdCampania =".$this->campania.",IdArea =".$this->area.",IdFormaContacto =".$this->contacto.",IdVehiculo =".$this->vehiculo.", Codigo =".$this->codigoB.", Calle = '".$this->calle."', NoExterior = '".$this->exterior."', NoInterior='".$this->interior."',Latitud='".$this->Latitud."',Longitud='".$this->Longitud."',
                        Colonia = '".$this->colonia."',Localidad = '".$this->localidad."', Ciudad ='".$this->ciudad."',Estado ='".$this->estado."',Delegacion ='".$this->delegacion."',CodigoPostal ='".$this->cp."',Ciudad ='".$this->ciudad."', UsuarioUltimaModificacion = '".$this->usuarioUltimaCreacion."', 
                        FechaUltimaModificacion = now(), Pantalla = '".$this->pantalla."' WHERE IdUsuario = " . $this->idUsuario . ";");
            $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                $this->insertarMultiConceptos($this->slcCampanias, $this->slcTurnos);
                return true;
            }
            return false;
        }        
    }

    public function editRegistro() {
        return $this->newRegistro();
    }

    public function deleteRegistro() {
        $consulta = "DELETE FROM c_domicilio_usturno WHERE IdUsuario ='" . $this->idUsuario . "';";
        $consulta = ($consulta);
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function insertarMultiConceptos($slcCampanias, $slcTurnos){
        $detalle = new DomicilioUsuarioTurnoDetalle();
        $detalle->setIdUsuario($this->idUsuario);
        $detalle->setUsuarioCreacion($this->usuarioCreacion);
        $detalle->setUsuarioUltimaModificacion($this->usuarioUltimaCreacion);
        $detalle->setPantalla($this->pantalla);
        $detalle->deleteRegistros();
        if(isset($slcCampanias) && !empty($slcCampanias)){
            foreach ($slcCampanias as $key => $value) {
                $detalle->setIdCampania($value);
                $detalle->setIdTurno($slcTurnos[$key]);
                $detalle->newRegistro();
            }
        }
    }

    public function getIdDomicilio() {
        return $this->idDomicilio;
    }

    public function getIdUsuario() {
        return $this->idUsuario;
    }
    
    public function getTurno() {
        return $this->turno;
    }

    public function getCampania() {
        return $this->campania;
    }
    
    public function getSlcTurnos() {
        return $this->slcTurnos;
    }

    public function getSlcCampanias() {
        return $this->slcCampanias;
    }
    
    public function getArea() {
        return $this->area;
    }
    
    public function getContacto() {
        return $this->contacto;
    }
    
    public function getVehiculo() {
        return $this->vehiculo;
    }
    
    public function getCodigoB() {
        return $this->codigoB;
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

    public function getCp() {
        return $this->cp;
    }
    
    public function getLocalidad() {
        return $this->localidad;
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

    public function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }
    
    public function setTurno($turno) {
        $this->turno = $turno;
    }

    public function setCampania($campania) {
        $this->campania = $campania;
    }
    
    public function setSlcTurnos($slcTurnos) {
        $this->slcTurnos = $slcTurnos;
    }

    public function setSlcCampanias($slcCampanias) {
        $this->slcCampanias = $slcCampanias;
    }
    
    public function setArea($area) {
        $this->area = $area;
    }
    
    public function setContacto($contacto) {
        $this->contacto = $contacto;
    }
    
    public function setVehiculo($vehiculo) {
        $this->vehiculo = $vehiculo;
    }

    public function setCodigoB($codigoB) {
        $this->codigoB = $codigoB;
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

    public function setCp($cp) {
        $this->cp = $cp;
    }
    
     public function setLocalidad($localidad) {
        $this->localidad = $localidad;
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
}

?>
